<?php

namespace App\Service;

use App\Models\SpotifyToken;
use Illuminate\Support\Facades\Http;

class SpotifyService
{
    protected string $clientId;

    protected string $clientSecret;

    protected string $redirectUri;

    protected string $tokenUrl = 'https://accounts.spotify.com/api/token';

    protected string $authorizeUrl = 'https://accounts.spotify.com/authorize';

    public function __construct()
    {
        $this->clientId = config('services.spotify.client_id');
        $this->clientSecret = config('services.spotify.client_secret');
        $this->redirectUri = config('services.spotify.redirect', url('/spotify/callback'));
    }

    public function requestClientCredentialsToken(): array
    {
        $response = Http::asForm()->post($this->tokenUrl, [
            'grant_type' => 'client_credentials',
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
        ]);

        if ($response->failed()) {
            throw new \Exception('Failed to request Spotify access token: '.$response->body());
        }

        return $response->json();
    }

    public function getClientCredentialsToken(): string
    {
        $tokenData = $this->requestClientCredentialsToken();

        return $tokenData['access_token'] ?? throw new \Exception('Access token not found in response');
    }

    public function getAuthorizationUrl(array $scopes = []): string
    {
        $params = [
            'client_id' => $this->clientId,
            'response_type' => 'code',
            'redirect_uri' => $this->redirectUri,
            'scope' => implode(' ', $scopes),
            'show_dialog' => true,
        ];

        return $this->authorizeUrl.'?'.http_build_query($params);
    }

    public function requestAccessTokenWithCode(string $code): array
    {
        $response = Http::asForm()->post($this->tokenUrl, [
            'grant_type' => 'authorization_code',
            'code' => $code,
            'redirect_uri' => $this->redirectUri,
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
        ]);

        if ($response->failed()) {
            throw new \Exception('Failed to request Spotify access token with code: '.$response->body());
        }

        return $response->json();
    }

    public function refreshAccessToken(string $refreshToken): array
    {
        $response = Http::asForm()->post($this->tokenUrl, [
            'grant_type' => 'refresh_token',
            'refresh_token' => $refreshToken,
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
        ]);

        if ($response->failed()) {
            throw new \Exception('Failed to refresh Spotify access token: '.$response->body());
        }

        return $response->json();
    }

    public function getCurrentAccessToken(): string
    {
        $spotifyToken = SpotifyToken::first();
        if (!$spotifyToken) {
            throw new \Exception('No Spotify token found. Please re-authorize.');
        }

        if ($spotifyToken->isExpired() || $spotifyToken->willExpireSoon()) {

            if (!$spotifyToken->refresh_token) {
                throw new \Exception('Refresh token not available. Please re-authorize.');
            }

            try {
                $newTokenData = $this->refreshAccessToken($spotifyToken->refresh_token);

                $spotifyToken->update([
                    'access_token' => $newTokenData['access_token'],
                    'token_type' => $newTokenData['token_type'],
                    'expires_in' => $newTokenData['expires_in'],
                    'scope' => $newTokenData['scope'] ?? $spotifyToken->scope,
                    'expires_at' => now()->addSeconds($newTokenData['expires_in'] ?? 3600),
                    'refresh_token' => $newTokenData['refresh_token'] ?? $spotifyToken->refresh_token,
                ]);

                return $newTokenData['access_token'];
            } catch (\Exception $e) {
                throw new \Exception('Failed to refresh access token. Please re-authorize.');
            }
        }

        return $spotifyToken->access_token;
    }

    public function makeApiCall(string $method, string $endpoint, array $data = []): array
    {
        $accessToken = $this->getCurrentAccessToken();
        $url = 'https://api.spotify.com/v1'.$endpoint;

        $httpClient = Http::withHeaders([
            'Authorization' => 'Bearer '.$accessToken,
            'Content-Type' => 'application/json',
        ]);

        if (strtoupper($method) === 'GET' && !empty($data)) {
            $url .= '?'.http_build_query($data);
            $response = $httpClient->get($url);
        } else {
            $response = $httpClient->{strtolower($method)}($url, $data);
        }

        if ($response->failed()) {
            throw new \Exception('Spotify API call failed: '.$response->body());
        }

        return $response->json();
    }

    public function getCurrentUser(): array
    {
        return $this->makeApiCall('GET', '/me');
    }

    public function createPlaylist(string $name, string $description = '', bool $public = false): array
    {
        $user = $this->getCurrentUser();

        return $this->makeApiCall('POST', "/users/{$user['id']}/playlists", [
            'name' => $name,
            'description' => $description,
            'public' => $public,
        ]);
    }

    public function removeAllTracksFromPlaylist(string $playlistId): array
    {
        $playlist = $this->makeApiCall('GET', "/playlists/{$playlistId}/tracks");

        if (empty($playlist['items'])) {
            return ['message' => 'Playlist is already empty'];
        }

        $trackUris = array_map(function ($item) {
            return ['uri' => $item['track']['uri']];
        }, $playlist['items']);

        return $this->makeApiCall('DELETE', "/playlists/{$playlistId}/tracks", [
            'tracks' => $trackUris,
        ]);
    }

    public function deleteSongFromPlaylist(string $playlistId, string $trackId): array
    {
        $trackUri = str_starts_with($trackId, 'spotify:track:') ? $trackId : "spotify:track:{$trackId}";

        return $this->makeApiCall('DELETE', "/playlists/{$playlistId}/tracks", [
            'tracks' => [[
                'uri' => $trackUri,
            ]],
        ]);
    }

    public function getPlaylistTracks(string $playlistId): array
    {
        return $this->makeApiCall('GET', "/playlists/{$playlistId}/tracks");
    }

    public function addTracksToPlaylist(string $playlistId, array $trackUris): array
    {
        return $this->makeApiCall('POST', "/playlists/{$playlistId}/tracks", [
            'uris' => $trackUris,
        ]);
    }

    public function searchTracks(string $query, int $limit = 20): array
    {
        $response = $this->makeApiCall('GET', '/search', [
            'q' => $query,
            'type' => 'track',
            'limit' => $limit,
        ]);

        return $response['tracks'] ?? [];
    }

    public function getPlaylists(): array
    {
        return $this->makeApiCall('GET', '/me/playlists');
    }

    public function getTopTracks(): array
    {
        return $this->makeApiCall('GET', '/me/top/tracks');
    }

    public function getTopArtists(): array
    {
        return $this->makeApiCall('GET', '/me/top/artists');
    }

    public function getMultipleTracks(array $ids): array
    {
        return $this->makeApiCall('GET', '/tracks', [
            'ids' => implode(',', $ids),
        ]);
    }
}
