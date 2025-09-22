<?php

use App\Http\Controllers\AiController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\MessagesController;
use App\Http\Controllers\Spotify\AccountController;
use App\Http\Controllers\Spotify\PlaylistController;
use App\Http\Controllers\Spotify\TrackController;
use App\Http\Controllers\SpotifyController;
use App\Http\Controllers\WhatsappController;
use Illuminate\Support\Facades\Route;

Route::post('/sign-up', [AuthController::class, 'register']);
Route::post('/sign-in', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/sign-out', [AuthController::class, 'logout']);

    Route::post('/whatsapp/send-message', [WhatsappController::class, 'sendMessage']);

    Route::get('/messages', [MessagesController::class, 'index']);
    Route::get('/chats', [MessagesController::class, 'chatUsers']);
    Route::get('/chat/{chatUser}', [MessagesController::class, 'chatUser']);
    Route::post('/chat/user/{chatUser}', [MessagesController::class, 'updateChatUser']);
});
Route::any('/whatsapp/webhook', [WhatsappController::class, 'handleWebhook']);
Route::post('/ai', [AiController::class, 'generatePlaylist']);
Route::post('/ai/mood', [AiController::class, 'getMood']);
Route::post('/ai/brutal-boss', [AiController::class, 'sendBrutalBossMessage']);

// Route::get('/spotify/test-token', [SpotifyController::class, 'testToken']);
Route::get('/spotify/connection-status', [AccountController::class, 'checkConnection']);
Route::get('/spotify/user-profile', [AccountController::class, 'getUserProfile']);
Route::post('/spotify/disconnect', [AccountController::class, 'disconnect']);

// playlists
Route::get('/spotify/playlists', [PlaylistController::class, 'playlists']);
Route::post('/spotify/playlists/create', [PlaylistController::class, 'createPlaylist']);
Route::get('/spotify/playlists/job-status/{jobId}', [PlaylistController::class, 'getJobStatus']);
Route::post('/spotify/playlists/clear', [PlaylistController::class, 'clearPlaylist']);
Route::post('/spotify/playlists/delete-song', [PlaylistController::class, 'deleteSongFromPlaylist']);
Route::post('/spotify/playlists/tracks', [PlaylistController::class, 'getPlaylistTracks']);

Route::get('/spotify/top-tracks', [PlaylistController::class, 'topTracks']);
Route::get('/spotify/top-artists', [PlaylistController::class, 'topArtists']);
Route::get('/spotify/tracks', [TrackController::class, 'getMultipleTracks']);
Route::post('/spotify/tracks/search', [TrackController::class, 'searchMultipleTracks']);
