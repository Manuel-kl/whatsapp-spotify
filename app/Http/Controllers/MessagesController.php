<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

class MessagesController extends Controller
{
    public function index()
    {
        $messages = Auth::user()->whatsappMessages;

        return response()->json([
            'success' => true,
            'data' => $messages,
        ]);
    }
}
