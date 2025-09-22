<?php

namespace App\Http\Controllers\Spotify;

use App\Http\Controllers\Controller;
use App\Models\SpotifyToken;
use App\Service\SpotifyService;

class AccountController extends Controller
{
    protected SpotifyService $spotifyService;

    public function __construct(SpotifyService $spotifyService)
    {
        $this->spotifyService = $spotifyService;
    }

    public function checkConnection()
    {
        $spotifyToken = SpotifyToken::first();
        if (!$spotifyToken) {
            return response()->json([
                'connected' => false,
                'message' => 'No Spotify access token found',
            ]);
        }

        try {
            $this->spotifyService->getCurrentAccessToken();

            return response()->json([
                'connected' => true,
                'expires_at' => $spotifyToken->expires_at,
                'message' => 'Spotify account is connected',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'connected' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function disconnect()
    {
        $spotifyToken = SpotifyToken::first();

        if (!$spotifyToken) {
            return response()->json([
                'message' => 'No Spotify access token found',
            ]);
        }

        $spotifyToken->delete();

        return response()->json([
            'message' => 'Spotify account disconnected successfully',
        ]);
    }

    public function getUserProfile()
    {
        try {
            $userProfile = $this->spotifyService->getCurrentUser();

            return response()->json([
                'user' => $userProfile,
                'message' => 'Successfully retrieved user profile from Spotify',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
