<?php

namespace App\Http\Controllers;

use App\Models\ChatUser;
use Illuminate\Support\Facades\Auth;

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
}
