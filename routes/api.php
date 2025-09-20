<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MessagesController;
use App\Http\Controllers\WhatsappController;
use App\Http\Controllers\Auth\AuthController;

Route::post('/sign-up', [AuthController::class, 'register']);
Route::post('/sign-in', [AuthController::class, 'login']);



Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/sign-out', [AuthController::class, 'logout']);

    Route::post('/whatsapp/send-message', [WhatsappController::class, 'sendMessage']);

    Route::get('/messages', [MessagesController::class, 'index']);
});
Route::any('/whatsapp/webhook', [WhatsappController::class, 'handleWebhook']);
