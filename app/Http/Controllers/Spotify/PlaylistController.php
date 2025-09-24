<?php

namespace App\Http\Controllers\Spotify;

use App\Http\Controllers\Controller;
use App\Jobs\GeneratePlaylistJob;
use App\Service\SpotifyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class PlaylistController extends Controller
{
    protected SpotifyService $spotifyService;

    public function __construct(SpotifyService $spotifyService)
    {
        $this->spotifyService = $spotifyService;
    }

    public function playlists()
    {
        try {
            $response = $this->spotifyService->getPlaylists();

            // Format the playlists to match the frontend expectations
            $formattedPlaylists = [];
            
            foreach ($response['items'] as $playlist) {
                $formattedPlaylists[] = [
                    'id' => $playlist['id'],
                    'name' => $playlist['name'],
                    'description' => $playlist['description'] ?? '',
                    'public' => $playlist['public'],
                    'track_count' => $playlist['tracks']['total'] ?? 0,
                    'href' => $playlist['href'],
                    'uri' => $playlist['uri'],
                    'external_urls' => $playlist['external_urls'] ?? [],
                    'images' => $playlist['images'] ?? [],
                    'owner' => [
                        'id' => $playlist['owner']['id'],
                        'display_name' => $playlist['owner']['display_name'] ?? null,
                    ],
                ];
            }

            return response()->json([
                'success' => true,
                'data' => $formattedPlaylists,
                'total' => $response['total'],
                'limit' => $response['limit'],
                'offset' => $response['offset'],
                'next' => $response['next'],
                'previous' => $response['previous'],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to get playlists',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public static function getTopTracksData()
    {
        try {
            $spotifyService = app(SpotifyService::class);
            $response = $spotifyService->getTopTracks();

            $formattedTracks = [];

            foreach ($response['items'] as $track) {
                $formattedTracks[] = [
                    'song_name' => $track['name'],
                    'song_id' => $track['id'],
                    'song_url' => $track['external_urls']['spotify'],
                    'song_uri' => $track['uri'],
                    'artist_name' => $track['artists'][0]['name'],
                    'artist_id' => $track['artists'][0]['id'],
                    'artist_url' => $track['artists'][0]['external_urls']['spotify'],
                    'all_artists' => array_map(function ($artist) {
                        return [
                            'name' => $artist['name'],
                            'id' => $artist['id'],
                            'url' => $artist['external_urls']['spotify'],
                        ];
                    }, $track['artists']),
                    'album_name' => $track['album']['name'],
                    'album_type' => $track['album']['album_type'],
                    'album_id' => $track['album']['id'],
                    'album_url' => $track['album']['external_urls']['spotify'],
                    'album_image' => $track['album']['images'][0]['url'] ?? null,
                    'release_date' => $track['album']['release_date'],
                    'popularity' => $track['popularity'],
                    'duration_ms' => $track['duration_ms'],
                    'explicit' => $track['explicit'],
                    'preview_url' => $track['preview_url'],
                ];
            }

            return [
                'tracks' => $formattedTracks,
                'total' => $response['total'],
                'limit' => $response['limit'],
                'offset' => $response['offset'],
                'next' => $response['next'],
                'previous' => $response['previous'],
            ];
        } catch (\Exception $e) {
            throw new \Exception('Failed to get top tracks data: '.$e->getMessage());
        }
    }

    public function topTracks()
    {
        try {
            return response()->json(self::getTopTracksData());
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to get top tracks',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public static function getTopArtistsData()
    {
        try {
            $spotifyService = app(SpotifyService::class);
            $topArtists = $spotifyService->getTopArtists();

            $transformedArtists = array_map(function ($artist) {
                return [
                    'id' => $artist['id'],
                    'name' => $artist['name'],
                    'popularity' => $artist['popularity'],
                    'genres' => $artist['genres'],
                    'followers' => $artist['followers']['total'],
                    'image' => $artist['images'][0]['url'] ?? null,
                    'spotify_url' => $artist['external_urls']['spotify'],
                ];
            }, $topArtists['items']);

            return [
                'items' => $transformedArtists,
                'total' => $topArtists['total'],
                'limit' => $topArtists['limit'],
                'offset' => $topArtists['offset'],
                'next' => $topArtists['next'],
                'previous' => $topArtists['previous'],
            ];
        } catch (\Exception $e) {
            throw new \Exception('Failed to get top artists data: '.$e->getMessage());
        }
    }

    public function topArtists()
    {
        try {
            return response()->json(self::getTopArtistsData());
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to get top artists',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function createPlaylist(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:100',
                'description' => 'nullable|string|max:300',
                'public' => 'boolean',
            ]);

            $user = $this->spotifyService->getCurrentUser();
            $response = $this->spotifyService->createPlaylist(
                $request->name,
                $request->description ?? '',
                $request->boolean('public', false)
            );

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $response['id'],
                    'name' => $response['name'],
                    'description' => $response['description'] ?? '',
                    'public' => $response['public'],
                    'track_count' => 0,
                    'href' => $response['href'],
                    'uri' => $response['uri'],
                    'external_urls' => $response['external_urls'] ?? [],
                ],
                'message' => 'Playlist created successfully',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Validation failed',
                'message' => $e->getMessage(),
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to create playlist',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function getJobStatus(string $jobId)
    {
        try {
            $jobData = Cache::get("playlist_job:{$jobId}");

            if (!$jobData) {
                return response()->json([
                    'error' => 'Job not found or expired',
                ], 404);
            }

            return response()->json($jobData);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to get job status',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function clearPlaylist(Request $request)
    {
        try {
            $request->validate([
                'playlist_id' => 'required|string',
            ]);

            $result = $this->spotifyService->removeAllTracksFromPlaylist($request->playlist_id);

            return response()->json([
                'success' => true,
                'message' => 'All tracks removed from playlist successfully',
                'result' => $result,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'error' => 'Validation failed',
                'message' => $e->getMessage(),
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to clear playlist',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function deleteSongFromPlaylist(Request $request)
    {
        try {
            $request->validate([
                'playlist_id' => 'required|string',
                'track_uri' => 'required|string',
            ]);

            $this->spotifyService->deleteSongFromPlaylist($request->playlist_id, $request->track_uri);

            return response()->json([
                'success' => true,
                'message' => 'Song deleted from playlist successfully',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Validation failed',
                'message' => $e->getMessage(),
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to delete song from playlist',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function getPlaylistTracks(Request $request)
    {
        try {
            $request->validate([
                'playlist_id' => 'required|string',
            ]);

            $response = $this->spotifyService->getPlaylistTracks($request->playlist_id);

            $formattedTracks = [];
            foreach ($response['items'] as $item) {
                if (!$item['track']) {
                    continue;
                }

                $track = $item['track'];
                $formattedTracks[] = [
                    'track' => [
                        'id' => $track['id'],
                        'name' => $track['name'],
                        'uri' => $track['uri'],
                        'duration_ms' => $track['duration_ms'],
                        'artists' => array_map(function ($artist) {
                            return [
                                'id' => $artist['id'],
                                'name' => $artist['name'],
                            ];
                        }, $track['artists']),
                        'album' => [
                            'id' => $track['album']['id'],
                            'name' => $track['album']['name'],
                        ],
                        'external_urls' => $track['external_urls'] ?? [],
                        'preview_url' => $track['preview_url'],
                        'added_at' => $item['added_at'],
                        'added_by' => $item['added_by']['id'] ?? null,
                    ],
                    'song_name' => $track['name'],
                    'song_id' => $track['id'],
                    'song_url' => $track['external_urls']['spotify'],
                    'song_uri' => $track['uri'],
                    'artist_name' => $track['artists'][0]['name'],
                    'artist_id' => $track['artists'][0]['id'],
                    'artist_url' => $track['artists'][0]['external_urls']['spotify'],
                    'all_artists' => array_map(function ($artist) {
                        return [
                            'name' => $artist['name'],
                            'id' => $artist['id'],
                            'url' => $artist['external_urls']['spotify'],
                        ];
                    }, $track['artists']),
                    'album_name' => $track['album']['name'],
                    'album_type' => $track['album']['album_type'],
                    'album_id' => $track['album']['id'],
                    'album_url' => $track['album']['external_urls']['spotify'],
                    'album_image' => $track['album']['images'][0]['url'] ?? null,
                    'release_date' => $track['album']['release_date'],
                    'popularity' => $track['popularity'],
                    'duration_ms' => $track['duration_ms'],
                    'explicit' => $track['explicit'],
                    'preview_url' => $track['preview_url'],
                    'added_at' => $item['added_at'],
                    'added_by' => $item['added_by']['id'] ?? null,
                ];
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'tracks' => $formattedTracks,
                    'total' => $response['total'],
                    'limit' => $response['limit'],
                    'offset' => $response['offset'],
                    'next' => $response['next'],
                    'previous' => $response['previous'],
                ]
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Validation failed',
                'message' => $e->getMessage(),
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to get playlist tracks',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
