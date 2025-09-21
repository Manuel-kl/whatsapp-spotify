<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\MessagesController;
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
    Route::get("/chat/{chatUser}", [MessagesController::class, 'chatUser']);
});
Route::any('/whatsapp/webhook', [WhatsappController::class, 'handleWebhook']);
