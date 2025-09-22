<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Spotify\PlaylistController;
use App\Service\AiService;
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

    public function detectPlaylistIntent(string $message): bool
    {
        $prompts = json_decode(file_get_contents(resource_path('prompts/ai_prompts.json')), true);
        $systemPrompt = $prompts['intentDetector']['system'];

        $response = $this->aiService->sendAiRequest($message, $systemPrompt);
        $intent = trim(strtoupper($response->text));

        return $intent === 'YES';
    }

    public function sendBrutalBossMessage(Request $request)
    {
        $validated = $request->validate([
            'to' => 'required|string',
        ]);

        $testNumber = $validated['to'] ?? '1234567890';

        $prompts = json_decode(file_get_contents(resource_path('prompts/ai_prompts.json')), true);
        $systemPrompt = $prompts['brutalBoss']['system'];
        $userMessage = 'Generate a brutal morning wake-up message for today.';
        $aiResponse = $this->aiService->sendAiRequest($userMessage, $systemPrompt);
        $brutalMessage = trim($aiResponse->text);

        $phoneNumberId = config('whatsapp.business_phone_id');
        $accessToken = config('whatsapp.access_token');
        $apiVersion = config('whatsapp.api_version');
        $baseUrl = config('whatsapp.base_url');
        $endpoint = "{$baseUrl}{$apiVersion}/{$phoneNumberId}/messages";

        $response = Http::withToken($accessToken)->post($endpoint, [
            'messaging_product' => 'whatsapp',
            'to' => $testNumber,
            'type' => 'text',
            'text' => [
                'body' => $brutalMessage,
            ],
        ]);

        return response()->json([
            'message' => $brutalMessage,
            'whatsapp_response' => $response->json(),
            'sent_to' => $testNumber,
        ]);
    }
}
