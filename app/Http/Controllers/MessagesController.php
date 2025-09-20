<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\WhatsappMessage;
use App\Http\Controllers\Controller;

class MessagesController extends Controller
{
    public function index()
    {
        $messages = WhatsappMessage::all();
        return response()->json([
            'success' => true,
            'data'   => $messages,
        ]);
    }
}
