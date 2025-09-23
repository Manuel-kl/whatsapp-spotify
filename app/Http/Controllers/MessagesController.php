<?php

namespace App\Http\Controllers;

use App\Models\ChatUser;
use Illuminate\Http\Request;

class MessagesController extends Controller
{
    public function index()
    {
        $chatUsers = ChatUser::with('whatsappMessages')->get();

        foreach ($chatUsers as $chatUser) {
            $messages = $chatUser->whatsappMessages;
            foreach ($messages as $message) {
                $message->body = $message->body;
            }
        }

        return response()->json([
            'success' => true,
            'data' => $chatUsers,
        ]);
    }

    public function chatUsers()
    {
        $chatUsers = ChatUser::all();

        return response()->json([
            'success' => true,
            'data' => $chatUsers,
        ]);
    }

    public function chatUser(ChatUser $chatUser)
    {
        $chatUser->load('whatsappMessages');

        return response()->json([
            'success' => true,
            'data' => $chatUser,
        ]);
    }

    public function updateChatUser(ChatUser $chatUser, Request $request)
    {
        $name = $request->input('name');

        if (!$name) {
            return response()->json([
                'success' => false,
                'message' => 'Name is required',
            ], 400);
        }

        $chatUser->name = $name;
        $chatUser->save();

        return response()->json([
            'success' => true,
            'message' => 'Chat user updated successfully',
            'data' => $chatUser,
        ]);
    }

    public function sendMessage(Request $request)
    {
        $whatsappController = app(WhatsappController::class);
        return $whatsappController->sendMessage($request);
    }
}
