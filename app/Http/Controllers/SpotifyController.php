<?php

namespace App\Http\Controllers;

use App\Models\SpotifyToken;
use App\Service\SpotifyService;
use Illuminate\Http\Request;

class SpotifyController extends Controller
{
    protected SpotifyService $spotifyService;

    public function __construct(SpotifyService $spotifyService)
    {
        $this->spotifyService = $spotifyService;
    }

    public function testToken()
    {
        try {
            $token = $this->spotifyService->getClientCredentialsToken();

            return response()->json([
                'access_token' => $token,
                'message' => 'Successfully retrieved Spotify access token',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function dashboard()
    {
        $spotifyToken = SpotifyToken::first();
        if (!$spotifyToken) {
            return redirect()->route('spotify.authorize');
        }

        if ($spotifyToken->expires_at < now()) {
            $this->spotifyService->refreshAccessToken($spotifyToken->refresh_token);
        }

        return view('dashboard', compact('spotifyToken'));
    }

    public function redirectToSpotify()
    {
        $scopes = [
            'user-read-private',
            'user-read-email',
            'playlist-read-private',
            'playlist-modify-private',
            'playlist-modify-public',
            'user-library-read',
            'user-library-modify',
            'user-read-playback-state',
            'user-modify-playback-state',
            'user-read-currently-playing',
            'user-top-read',
            'user-read-recently-played',
        ];

        $authUrl = $this->spotifyService->getAuthorizationUrl($scopes);
        $spotifyToken = SpotifyToken::first();

        if (!$spotifyToken) {
            return view('spotify.connect', compact('authUrl'));
        }

        if ($spotifyToken->expires_at < now()) {
            try {
                $this->spotifyService->refreshAccessToken($spotifyToken->refresh_token);
                $spotifyToken = $spotifyToken->fresh();

                return view('dashboard', compact('spotifyToken'));
            } catch (\Exception $e) {
                return view('spotify.connect', compact('authUrl'));
            }
        }

        return view('spotify.connect', compact('authUrl'));
    }

    public function handleSpotifyCallback(Request $request)
    {
        $code = $request->get('code');
        $error = $request->get('error');

        if ($error) {
            return response()->json([
                'error' => $error,
            ], 400);
        }

        if (!$code) {
            return response()->json([
                'error' => 'No authorization code provided',
            ], 400);
        }

        try {
            $tokenData = $this->spotifyService->requestAccessTokenWithCode($code);

            $spotifyToken = SpotifyToken::create([
                'access_token' => $tokenData['access_token'],
                'refresh_token' => $tokenData['refresh_token'] ?? null,
                'token_type' => $tokenData['token_type'],
                'expires_in' => $tokenData['expires_in'],
                'scope' => $tokenData['scope'],
                'expires_at' => now()->addSeconds($tokenData['expires_in'] ?? 3600),
            ]);

            return redirect()->route('dashboard')->with('success', 'Successfully connected your Spotify account!');
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
