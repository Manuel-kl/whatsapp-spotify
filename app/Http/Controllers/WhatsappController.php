<?php

namespace App\Http\Controllers;

use App\Models\ChatUser;
use App\Models\WhatsappMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Http\Controllers\AiController;
use App\Jobs\GeneratePlaylistJob;

class WhatsappController extends Controller
{
    public function sendMessage(Request $request)
    {
        $validated = $request->validate([
            'to' => 'required|string',
            'body' => 'required|string',
        ]);

        return $this->sendWhatsAppMessage($validated['to'], $validated['body'], 'message');
    }

    public function sendWhatsAppMessage($to, $body, $type = 'message')
    {
        $phoneNumberId = config('whatsapp.business_phone_id');
        $accessToken = config('whatsapp.access_token');
        $apiVersion = config('whatsapp.api_version');
        $baseUrl = config('whatsapp.base_url');

        $endpoint = "{$baseUrl}{$apiVersion}/{$phoneNumberId}/messages";

        logger('Sending WhatsApp message', [
            'to' => $to,
            'body' => $body,
            'type' => $type,
            'endpoint' => $endpoint,
            'phone_id' => $phoneNumberId,
            'has_token' => !empty($accessToken)
        ]);

        $response = Http::withToken($accessToken)->post($endpoint, [
            'messaging_product' => 'whatsapp',
            'to' => $to,
            'type' => 'text',
            'text' => [
                'body' => $body,
            ],
        ]);

        $respJson = $response->json();

        logger('WhatsApp API response', [
            'status' => $response->status(),
            'response' => $respJson,
            'success' => $response->successful()
        ]);

        if (!empty($respJson['messages'][0]['id']) && $type === 'message') {
            $waId = $respJson['contacts'][0]['wa_id'];
            $chatUser = ChatUser::where('phone', $waId)->first();

            if (!$chatUser) {
                $chatUser = ChatUser::create([
                    'phone' => $waId,
                ]);
            }

            WhatsappMessage::create([
                'chat_user_id' => $chatUser->id,
                'wamid' => $respJson['messages'][0]['id'],
                'from' => $phoneNumberId,
                'to' => $to,
                'body' => $body,
                'type' => 'text',
                'status' => null,
                'timestamp' => now(),
                'location' => $respJson['messages'][0]['location'] ?? null,
            ]);
        }

        return response()->json($respJson);
    }

    public function sendInteractiveButton($to, $message, $activity)
    {
        $phoneNumberId = config('whatsapp.business_phone_id');
        $accessToken = config('whatsapp.access_token');
        $apiVersion = config('whatsapp.api_version');
        $baseUrl = config('whatsapp.base_url');

        $endpoint = "{$baseUrl}{$apiVersion}/{$phoneNumberId}/messages";

        logger('Sending WhatsApp interactive message', [
            'to' => $to,
            'message' => $message,
            'activity' => $activity,
            'endpoint' => $endpoint,
            'phone_id' => $phoneNumberId,
            'has_token' => !empty($accessToken)
        ]);

        $response = Http::withToken($accessToken)->post($endpoint, [
            'messaging_product' => 'whatsapp',
            'to' => $to,
            'type' => 'interactive',
            'interactive' => [
                'type' => 'button',
                'body' => [
                    'text' => $message
                ],
                'action' => [
                    'buttons' => [
                        [
                            'type' => 'reply',
                            'reply' => [
                                'id' => 'generate_playlist_yes_' . base64_encode($activity),
                                'title' => 'Yes ✅'
                            ]
                        ],
                        [
                            'type' => 'reply',
                            'reply' => [
                                'id' => 'generate_playlist_no',
                                'title' => 'No ❌'
                            ]
                        ]
                    ]
                ]
            ]
        ]);

        $respJson = $response->json();

        logger('WhatsApp Interactive API response', [
            'status' => $response->status(),
            'response' => $respJson,
            'success' => $response->successful()
        ]);

        if (!empty($respJson['messages'][0]['id'])) {
            $waId = $respJson['contacts'][0]['wa_id'];
            $chatUser = ChatUser::where('phone', $waId)->first();

            if (!$chatUser) {
                $chatUser = ChatUser::create([
                    'phone' => $waId,
                ]);
            }

            WhatsappMessage::create([
                'chat_user_id' => $chatUser->id,
                'wamid' => $respJson['messages'][0]['id'],
                'from' => $phoneNumberId,
                'to' => $to,
                'body' => $message,
                'type' => 'interactive',
                'status' => null,
                'timestamp' => now(),
                'location' => null,
            ]);
        }

        return $respJson;
    }

    public function handleWebhook(Request $request)
    {
        $payload = $request->all();
        if ($request->isMethod('get')) {
            $mode = $request->query('hub_mode');
            $token = $request->query('hub_verify_token');
            $challenge = $request->query('hub_challenge');

            if ($mode === 'subscribe' && $token === config('whatsapp.verify_token')) {
                return response($challenge, 200);
            }

            return response('Forbidden', 403);
        }

        if (!$this->isValidMessageWebhook($payload)) {
            return response()->json(['error' => 'Invalid webhook payload'], 400);
        }
        logger('webhook payload', $payload);
        $value = $payload['entry'][0]['changes'][0]['value'] ?? [];

        if (!empty($value['messages'])) {
            logger('value messages', $value['messages']);
            foreach ($value['messages'] as $msg) {
                $conversationData = [];

                if (isset($msg['context']['id'])) {
                    $conversationData['conversation_id'] = $msg['context']['id'];
                }

                $from = $msg['from'] ?? null;

                $chatUser = ChatUser::where('phone', $from)->first();

                if (!$chatUser) {
                    $chatUser = ChatUser::create([
                        'phone' => $from,
                    ]);
                }

                if (!$chatUser) {
                    logger('chatUser not created');
                    logger('from', ['from' => $from]);
                    return response()->json(['status' => 'received']);
                }

                WhatsappMessage::updateOrCreate(
                    ['wamid' => $msg['id']],
                    array_merge([
                        'chat_user_id' => $chatUser->id,
                        'from' => $msg['from'] ?? null,
                        'to' => $value['metadata']['display_phone_number'] ?? null,
                        'body' => $this->extractMessageBody($msg),
                        'type' => $msg['type'] ?? null,
                        'status' => null,
                        'timestamp' => isset($msg['timestamp']) ? now()->setTimestamp($msg['timestamp']) : now(),
                    ], $conversationData)
                );

                $autoReplyPhone = config('whatsapp.auto_reply_phone');
                logger('autoReplyPhone', ['autoReplyPhone' => $autoReplyPhone]);
                logger('from', ['from' => $from]);
                if ($autoReplyPhone && $from === $autoReplyPhone) {
                    logger('processing auto reply');
                    $this->processAutoReply($msg, $from);
                }
            }
        }

        if (!empty($value['statuses'])) {
            logger('value statuses', $value['statuses']);
            foreach ($value['statuses'] as $status) {
                $conversationData = [];
                if (isset($status['conversation']['id'])) {
                    $conversationData['conversation_id'] = $status['conversation']['id'];
                }
                if (isset($status['conversation']['expiration_timestamp'])) {
                    $conversationData['conversation_expires_at'] = now()->setTimestamp($status['conversation']['expiration_timestamp']);
                }
                if (isset($status['conversation']['origin']['type'])) {
                    $conversationData['conversation_origin_type'] = $status['conversation']['origin']['type'];
                }

                $pricingData = [];
                if (isset($status['pricing']['billable'])) {
                    $pricingData['pricing_billable'] = $status['pricing']['billable'];
                }
                if (isset($status['pricing']['pricing_model'])) {
                    $pricingData['pricing_pricing_model'] = $status['pricing']['pricing_model'];
                }
                if (isset($status['pricing']['category'])) {
                    $pricingData['pricing_category'] = $status['pricing']['category'];
                }
                if (isset($status['pricing']['type'])) {
                    $pricingData['pricing_type'] = $status['pricing']['type'];
                }

                WhatsappMessage::where('wamid', $status['id'])->update(array_merge([
                    'status' => $status['status'] ?? null,
                    'timestamp' => isset($status['timestamp']) ? now()->setTimestamp($status['timestamp']) : now(),
                ], $conversationData, $pricingData));
            }
        }

        return response()->json(['status' => 'received']);
    }

    private function processAutoReply($msg, $from)
    {
        logger('processing auto reply', ['msg' => $msg, 'from' => $from]);
        if ($msg['type'] === 'interactive') {
            logger('processing interactive');
            $this->handleButtonInteraction($msg, $from);
            return;
        }

        if ($msg['type'] === 'text' && !empty($msg['text']['body'])) {
            logger('processing text');
            $aiController = app(AiController::class);
            $hasPlaylistIntent = $aiController->detectPlaylistIntent($msg['text']['body'], $from);

            if ($hasPlaylistIntent) {
                logger('has playlist intent');
                $playlistSuggestionMessage = $aiController->generatePlaylistSuggestionMessage($msg['text']['body'], $from);

                $this->sendInteractiveButton(
                    $from,
                    $playlistSuggestionMessage,
                    $msg['text']['body']
                );
            } else {
                // normal chat response conversation
                logger('normal chat response conversation');
                $conversationalResponse = $aiController->generateConversationalResponse($msg['text']['body'], $from);
                $this->sendWhatsAppMessage($from, $conversationalResponse, 'auto_reply');
            }
        }
    }

    private function handleButtonInteraction($msg, $from)
    {
        $buttonId = $msg['interactive']['button_reply']['id'] ?? '';

        if (strpos($buttonId, 'generate_playlist_yes_') === 0) {
            $activityEncoded = str_replace('generate_playlist_yes_', '', $buttonId);
            $activity = base64_decode($activityEncoded);

            GeneratePlaylistJob::dispatch($activity, $from);

            $this->sendWhatsAppMessage($from, "Perfect! I'm generating your playlist now. This might take a moment... 🎵", 'auto_reply');

        } elseif ($buttonId === 'generate_playlist_no') {
            $this->sendWhatsAppMessage($from, "No problem! Let me know if you need anything else.", 'auto_reply');
        }
    }

    private function extractMessageBody($msg)
    {
        if ($msg['type'] === 'text' && !empty($msg['text']['body'])) {
            return $msg['text']['body'];
        }

        if ($msg['type'] === 'interactive' && !empty($msg['interactive']['button_reply'])) {
            $buttonReply = $msg['interactive']['button_reply'];
            return "Button clicked: " . ($buttonReply['title'] ?? '') . " (ID: " . ($buttonReply['id'] ?? '') . ")";
        }

        return null;
    }

    private function isValidMessageWebhook($payload)
    {
        if (!isset($payload['object']) || !isset($payload['entry'])) {
            return false;
        }

        if ($payload['object'] !== 'whatsapp_business_account') {
            return false;
        }

        if (!is_array($payload['entry']) || empty($payload['entry'])) {
            return false;
        }

        $entry = $payload['entry'][0];
        if (!isset($entry['changes']) || !is_array($entry['changes']) || empty($entry['changes'])) {
            return false;
        }

        $change = $entry['changes'][0];
        if (!isset($change['value']) || !is_array($change['value'])) {
            return false;
        }

        if (!isset($change['field']) || $change['field'] !== 'messages') {
            return false;
        }

        $value = $change['value'];
        if (!isset($value['messaging_product']) || $value['messaging_product'] !== 'whatsapp') {
            return false;
        }

        return true;
    }
}
