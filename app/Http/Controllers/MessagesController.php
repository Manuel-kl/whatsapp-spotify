<?php

namespace App\Http\Controllers;

use App\Models\WhatsappMessage;

class MessagesController extends Controller
{
    public function index()
    {
        $messages = WhatsappMessage::all();

        return response()->json([
            'success' => true,
            'data' => $messages,
        ]);
    }
}
