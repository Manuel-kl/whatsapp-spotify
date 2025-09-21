<?php

namespace App\Http\Controllers;

use App\Models\ChatUser;
use Illuminate\Support\Facades\Auth;

class MessagesController extends Controller
{
    public function index()
    {
        $chatUsers = ChatUser::all();

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
}
