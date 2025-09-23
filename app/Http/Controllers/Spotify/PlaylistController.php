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
            $playlists = $this->spotifyService->getPlaylists();

            return response()->json($playlists);
        } catch (\Exception $e) {
            return response()->json([
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
                'activity' => 'required|string',
                'playlist_name' => 'nullable|string|max:100',
                'playlist_description' => 'nullable|string|max:300',
                'public' => 'boolean',
            ]);

            $jobId = Str::uuid()->toString();

            Cache::put("playlist_job:{$jobId}", [
                'job_id' => $jobId,
                'status' => 'pending',
                'progress' => 0,
                'message' => 'Starting playlist generation...',
                'created_at' => now()->toISOString(),
                'updated_at' => now()->toISOString(),
            ], 3600);

            GeneratePlaylistJob::dispatch(
                $request->activity,
                null,
                $jobId,
                $request->playlist_name,
                $request->playlist_description,
                $request->boolean('public', false),
                $request->user() ? $request->user()->id : null
            )->onQueue('default');

            return response()->json([
                'success' => true,
                'job_id' => $jobId,
                'status' => 'pending',
                'message' => 'Playlist generation started. Use the job_id to check progress.',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'error' => 'Validation failed',
                'message' => $e->getMessage(),
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
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
                'track_id' => 'required|string',
            ]);

            $this->spotifyService->deleteSongFromPlaylist($request->playlist_id, $request->track_id);

            return response()->json([
                'success' => true,
                'message' => 'Song deleted from playlist successfully',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'error' => 'Validation failed',
                'message' => $e->getMessage(),
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
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
                'tracks' => $formattedTracks,
                'total' => $response['total'],
                'limit' => $response['limit'],
                'offset' => $response['offset'],
                'next' => $response['next'],
                'previous' => $response['previous'],
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'error' => 'Validation failed',
                'message' => $e->getMessage(),
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to get playlist tracks',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
