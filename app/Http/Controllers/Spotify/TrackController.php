<?php

namespace App\Http\Controllers\Spotify;

use App\Http\Controllers\Controller;
use App\Service\SpotifyService;
use Illuminate\Http\Request;

class TrackController extends Controller
{
    public function getMultipleTracks(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'required|string',
        ]);

        $spotifyService = app(SpotifyService::class);
        $response = $spotifyService->getMultipleTracks($request->ids);
        $tracks = $response['tracks'] ?? [];

        $filteredTracks = array_map(function ($track) {
            if (!$track) {
                return null;
            }

            return [
                'id' => $track['id'],
                'name' => $track['name'],
                'artists' => array_map(function ($artist) {
                    return [
                        'id' => $artist['id'],
                        'name' => $artist['name'],
                    ];
                }, $track['artists'] ?? []),
                'album' => [
                    'id' => $track['album']['id'] ?? null,
                    'name' => $track['album']['name'] ?? null,
                    'release_date' => $track['album']['release_date'] ?? null,
                ],
                'duration_ms' => $track['duration_ms'] ?? null,
                'popularity' => $track['popularity'] ?? null,
                'preview_url' => $track['preview_url'] ?? null,
                'uri' => $track['uri'] ?? null,
                'external_urls' => $track['external_urls'] ?? null,
            ];
        }, $tracks);

        return response()->json([
            'tracks' => array_filter($filteredTracks),
        ]);
    }

    public function searchMultipleTracks(Request $request)
    {
        $request->validate([
            'search_terms' => 'required|array',
            'search_terms.*' => 'required|string',
            'limit' => 'nullable|integer|min:1|max:50',
        ]);

        $spotifyService = app(SpotifyService::class);
        $limit = $request->limit ?? 5;
        $results = [];

        foreach ($request->search_terms as $searchTerm) {
            try {
                $searchResult = $spotifyService->searchTracks($searchTerm, $limit);
                $tracks = $searchResult['items'] ?? [];

                $filteredTracks = array_map(function ($track) {
                    return [
                        'id' => $track['id'],
                        'name' => $track['name'],
                        'artists' => array_map(function ($artist) {
                            return [
                                'id' => $artist['id'],
                                'name' => $artist['name'],
                            ];
                        }, $track['artists'] ?? []),
                        'album' => [
                            'id' => $track['album']['id'] ?? null,
                            'name' => $track['album']['name'] ?? null,
                            'release_date' => $track['album']['release_date'] ?? null,
                        ],
                        'duration_ms' => $track['duration_ms'] ?? null,
                        'popularity' => $track['popularity'] ?? null,
                        'preview_url' => $track['preview_url'] ?? null,
                        'uri' => $track['uri'] ?? null,
                        'external_urls' => $track['external_urls'] ?? null,
                    ];
                }, $tracks);

                $results[] = [
                    'search_term' => $searchTerm,
                    'tracks' => $filteredTracks,
                    'total' => $searchResult['total'] ?? 0,
                ];
            } catch (\Exception $e) {
                $results[] = [
                    'search_term' => $searchTerm,
                    'tracks' => [],
                    'total' => 0,
                    'error' => $e->getMessage(),
                ];
            }
        }

        return response()->json([
            'results' => $results,
            'total_searches' => count($request->search_terms),
        ]);
    }
}
