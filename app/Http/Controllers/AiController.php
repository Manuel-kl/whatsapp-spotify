<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Spotify\PlaylistController;
use App\Service\AiService;
use App\Models\WhatsappMessage;
use App\Models\ChatUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class AiController extends Controller
{
    protected AiService $aiService;

    public function __construct(AiService $aiService)
    {
        $this->aiService = $aiService;
    }

    public function generatePlaylist(Request $request)
    {
        $geminiKey = config('prism.providers.gemini.api_key');
        if (!$geminiKey) {
            return response()->json(['error' => 'Gemini API key is not set'], 500);
        }

        $payload = $request->validate([
            'activity' => 'required|string',
        ]);

        $songs = $this->generatePlaylistSongs($payload['activity']);

        return response()->json($songs);
    }

    public function generatePlaylistSongs(string $activity): array
    {
        $topTracksData = PlaylistController::getTopTracksData();
        $topArtistsData = PlaylistController::getTopArtistsData();

        $prompts = json_decode(file_get_contents(resource_path('prompts/ai_prompts.json')), true);
        $systemPrompt = $prompts['playlistGenerator']['system'];

        $userMessage = "User's activity: {$activity}";

        if (!empty($topTracksData['tracks'])) {
            $userMessage .= "\n\nUser's Top Tracks from Spotify:";
            foreach ($topTracksData['tracks'] as $track) {
                $userMessage .= "\n- {$track['song_name']} by {$track['artist_name']}";
            }
        }

        if (!empty($topArtistsData['items'])) {
            $userMessage .= "\n\nUser's Top Artists from Spotify:";
            foreach ($topArtistsData['items'] as $artist) {
                $genres = !empty($artist['genres']) ? ' ('.implode(', ', $artist['genres']).')' : '';
                $userMessage .= "\n- {$artist['name']}{$genres}";
            }
        }

        $playlist = $this->aiService->sendAiRequest(
            $userMessage,
            $systemPrompt
        );

        $songs = $this->parseSongList($playlist->text);

        return [
            'songs' => $songs,
            'text' => $playlist->text,
            'finish_reason' => $playlist->finishReason->name,
            'id' => $playlist->meta->id ?? null,
        ];
    }

    private function parseSongList(string $text): array
    {
        $songs = [];
        $lines = explode("\n", trim($text));

        foreach ($lines as $line) {
            $line = trim($line);

            if (empty($line)) {
                continue;
            }

            $line = preg_replace('/^-\s*/', '', $line);

            if (strpos($line, ' - ') !== false) {
                $parts = explode(' - ', $line, 2);
                if (count($parts) === 2) {
                    $songs[] = [
                        'artist' => trim($parts[0]),
                        'title' => trim($parts[1]),
                    ];
                }
            }
        }

        return $songs;
    }

    public function getMood(Request $request)
    {
        $payload = $request->validate([
            'message' => 'required|string',
        ]);

        $mood = $this->getMoodFromMessage($payload['message']);

        return response()->json([
            'mood' => $mood,
        ]);
    }

    public function getMoodFromMessage(string $message): string
    {
        $prompts = json_decode(file_get_contents(resource_path('prompts/ai_prompts.json')), true);
        $systemPrompt = $prompts['moodSelector']['system'];

        $mood = $this->aiService->sendAiRequest($message, $systemPrompt);

        return trim($mood->text);
    }

    public function generatePlaylistName(string $activity): string
    {
        $prompts = json_decode(file_get_contents(resource_path('prompts/ai_prompts.json')), true);
        $systemPrompt = $prompts['playlistNamer']['system'];

        $name = $this->aiService->sendAiRequest($activity, $systemPrompt);

        return trim($name->text);
    }

    public function detectPlaylistIntent(string $message, string $userPhone = null): bool
    {
        $prompts = json_decode(file_get_contents(resource_path('prompts/ai_prompts.json')), true);
        $systemPrompt = $prompts['intentDetector']['system'];

        if ($userPhone) {
            $messageHistory = $this->getMessageHistoryForUser($userPhone);
            $response = $this->aiService->sendAiRequestWithHistory($message, $systemPrompt, $messageHistory);
        } else {
            $response = $this->aiService->sendAiRequest($message, $systemPrompt);
        }

        $intent = trim(strtoupper($response->text));

        return $intent === 'YES';
    }

    public function generateConversationalResponse(string $message, string $userPhone = null): string
    {
        $prompts = json_decode(file_get_contents(resource_path('prompts/ai_prompts.json')), true);
        $systemPrompt = $prompts['conversational']['system'];

        if ($userPhone) {
            $messageHistory = $this->getMessageHistoryForUser($userPhone);
            $response = $this->aiService->sendAiRequestWithHistory($message, $systemPrompt, $messageHistory);
        } else {
            $response = $this->aiService->sendAiRequest($message, $systemPrompt);
        }

        return trim($response->text);
    }

    public function generatePlaylistSuggestionMessage(string $userActivity, string $userPhone = null): string
    {
        $prompts = json_decode(file_get_contents(resource_path('prompts/ai_prompts.json')), true);
        $systemPrompt = $prompts['playlistSuggestion']['system'];

        if ($userPhone) {
            $messageHistory = $this->getMessageHistoryForUser($userPhone);
            $response = $this->aiService->sendAiRequestWithHistory($userActivity, $systemPrompt, $messageHistory);
        } else {
            $response = $this->aiService->sendAiRequest($userActivity, $systemPrompt);
        }

        return trim($response->text);
    }

    public function getMessageHistoryForUser(string $userPhone, int $limit = 20): array
    {
        $chatUser = ChatUser::where('phone', $userPhone)->first();

        if (!$chatUser) {
            return [];
        }

        $messages = WhatsappMessage::where('chat_user_id', $chatUser->id)
            ->whereNotNull('body')
            ->orderBy('timestamp', 'desc')
            ->limit($limit)
            ->get()
            ->reverse()
            ->toArray();

        return $messages;
    }

    public function sendBrutalBossMessage(Request $request)
    {
        $validated = $request->validate([
            'to' => 'required|string',
        ]);

        $testNumber = $validated['to'];

        $prompts = json_decode(file_get_contents(resource_path('prompts/ai_prompts.json')), true);
        $systemPrompt = $prompts['brutalBoss']['system'];
        $userMessage = 'Generate a brutal morning wake-up message for today.';
        $aiResponse = $this->aiService->sendAiRequest($userMessage, $systemPrompt);
        $brutalMessage = trim($aiResponse->text);

        $whatsappController = app(WhatsappController::class);
        $response = $whatsappController->sendWhatsAppMessage($testNumber, $brutalMessage, 'brutal_boss');

        return response()->json([
            'message' => $brutalMessage,
            'whatsapp_response' => $response->getData(),
            'sent_to' => $testNumber,
        ]);
    }
}
