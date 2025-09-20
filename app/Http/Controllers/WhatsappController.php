<?php

namespace App\Http\Controllers;

use App\Models\WhatsappMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsappController extends Controller
{
    public function sendMessage(Request $request)
    {
        $validated = $request->validate([
            'to' => 'required|string',
            'body' => 'required|string',
        ]);

        $phoneNumberId = config('whatsapp.business_phone_id');
        $accessToken = config('whatsapp.access_token');
        $apiVersion = config('whatsapp.api_version');
        $baseUrl = config('whatsapp.base_url');

        $endpoint = "{$baseUrl}{$apiVersion}/{$phoneNumberId}/messages";

        $response = Http::withToken($accessToken)->post($endpoint, [
            'messaging_product' => 'whatsapp',
            'to' => $validated['to'],
            'type' => 'text',
            'text' => [
                'body' => $validated['body'],
            ],
        ]);

        $respJson = $response->json();

        if (! empty($respJson['messages'][0]['id'])) {
            WhatsappMessage::create([
                'wamid' => $respJson['messages'][0]['id'],
                'from' => $phoneNumberId,
                'to' => $validated['to'],
                'body' => $validated['body'],
                'type' => 'text',
                'status' => null,
                'timestamp' => now(),
            ]);
        }

        return response()->json($respJson);
    }

    public function handleWebhook(Request $request)
    {
        $payload = $request->all();

        if ($request->isMethod('get')) {
            Log::info('Received GET request on WhatsApp webhook', [
                'query' => $request->query(),
            ]);
            $mode = $request->query('hub_mode');
            $token = $request->query('hub_verify_token');
            $challenge = $request->query('hub_challenge');

            if ($mode === 'subscribe' && $token === config('whatsapp.verify_token')) {
                Log::info('Webhook verification successful', [
                    'mode' => $mode,
                    'token' => $token,
                    'challenge' => $challenge,
                ]);

                return response($challenge, 200);
            }

            Log::warning('Webhook verification failed', [
                'mode' => $mode,
                'token' => $token,
                'config_token' => config('whatsapp.verify_token')
            ]);

            return response('Forbidden', 403);
        }

        if (! $this->isValidMessageWebhook($payload)) {
            return response()->json(['error' => 'Invalid webhook payload'], 400);
        }

        $value = $payload['entry'][0]['changes'][0]['value'] ?? [];

        if (! empty($value['messages'])) {
            foreach ($value['messages'] as $msg) {
                $conversationData = [];
                if (isset($msg['context']['id'])) {
                    $conversationData['conversation_id'] = $msg['context']['id'];
                }

                WhatsappMessage::updateOrCreate(
                    ['wamid' => $msg['id']],
                    array_merge([
                        'from' => $msg['from'] ?? null,
                        'to' => $value['metadata']['display_phone_number'] ?? null,
                        'body' => $msg['text']['body'] ?? null,
                        'type' => $msg['type'] ?? null,
                        'status' => null,
                        'timestamp' => isset($msg['timestamp']) ? now()->setTimestamp($msg['timestamp']) : now(),
                    ], $conversationData)
                );
            }
        }

        if (! empty($value['statuses'])) {
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

    private function isValidMessageWebhook($payload)
    {
        if (! isset($payload['object']) || ! isset($payload['entry'])) {
            return false;
        }

        if ($payload['object'] !== 'whatsapp_business_account') {
            return false;
        }

        if (! is_array($payload['entry']) || empty($payload['entry'])) {
            return false;
        }

        $entry = $payload['entry'][0];
        if (! isset($entry['changes']) || ! is_array($entry['changes']) || empty($entry['changes'])) {
            return false;
        }

        $change = $entry['changes'][0];
        if (! isset($change['value']) || ! is_array($change['value'])) {
            return false;
        }

        if (! isset($change['field']) || $change['field'] !== 'messages') {
            return false;
        }

        $value = $change['value'];
        if (! isset($value['messaging_product']) || $value['messaging_product'] !== 'whatsapp') {
            return false;
        }

        return true;
    }
}
