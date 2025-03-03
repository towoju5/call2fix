<?php

namespace App\Http\Controllers;

use App\Models\Chat;
use App\Models\Message;
use App\Events\NewMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Validator;

class ChatController extends Controller
{
    public function index()
    {
        $chats = Auth::user()->chats()->with('participants')->get();
        return get_success_response($chats);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'participants' => 'required|array|min:1',
            'participants.*' => 'exists:users,id'
        ]);

        $chat = Chat::create(['name' => $validated['name']]);
        $chat->participants()->attach($validated['participants']);

        return get_success_response($chat->load('participants'), 'Chat created successfully');
    }

    public function sendMessage(Request $request, Chat $chat)
    {
        $validated = $request->validate([
            'content' => 'required|string'
        ]);

        // Create message
        $message = $chat->messages()->create([
            'user_id' => Auth::id(),
            'content' => $validated['content']
        ]);

        // Broadcast the message to the Ably channel
        broadcast(new NewMessage($message))->toOthers();  
        return get_success_response($message->load('user'));
    }


    public function show(Chat $chat)
    {
        return get_success_response($chat->load('participants', 'messages.user'));
    }

    public function updateChat(Request $request, $messageId)
    {
        // $validated = Validator::make($request->all(), [
    }

    // public function sendMessage(Request $request, Chat $chat)
    // {
    //     $validated = $request->validate([
    //         'content' => 'required|string'
    //     ]);

    //     $message = $chat->messages()->create([
    //         'user_id' => Auth::id(),
    //         'content' => $validated['content']
    //     ]);

    //     broadcast(new NewMessage($message))->toOthers();

    //     return get_success_response($message->load('user'));
    // }
}
