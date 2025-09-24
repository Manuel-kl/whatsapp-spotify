<?php

use App\Http\Controllers\ChatController;
use App\Http\Controllers\SpotifyController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/chat', [ChatController::class, 'index'])->name('chat.index');
Route::get('/spotify-playlists', function() {
    return view('spotify-playlists');
})->name('spotify.playlists');

Route::get('/dashboard', [SpotifyController::class, 'dashboard'])->name('dashboard');
Route::get('/spotify/authorize', [SpotifyController::class, 'redirectToSpotify'])->name('spotify.authorize');
Route::get('/spotify/callback', [SpotifyController::class, 'handleSpotifyCallback'])->name('spotify.callback');
Route::post('/spotify/disconnect', [SpotifyController::class, 'disconnect'])->name('spotify.disconnect');
