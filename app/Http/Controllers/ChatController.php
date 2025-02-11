<?php

namespace App\Http\Controllers;

use App\Models\Chat;
use App\Models\Message;
use App\Events\NewMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Validator;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


class ChatController extends Controller
{
    public function index()
    {
        $chats = Auth::user()->chats()->with('participants', 'lastChat')->get();
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



        $user = User::whereId(auth()->id())->first();
        // Broadcast the message to the Ably channel
        broadcast(new NewMessage($message, $user))->toOthers();
        
        $notifiables = $chat->participants;

        foreach ($notifiables as $k => $u) {
            $response = fcm("New chat Message", $request->content, $u->device_id);
            \Log::channel('chat')->info("New fcm message response", ['response' => $response]);
        }

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

    public function readMessage(Chat $chat, Message $message)
    {
        // Ensure the user is a participant in the chat
        if (!$chat->participants()->where('user_id', Auth::id())->exists()) {
            return get_error_response('Unauthorized access to this chat', 403);
        }
    
        // Check if 'read_by' column exists, if not, add it
        if (!Schema::hasColumn('messages', 'read_by')) {
            Schema::table('messages', function (Blueprint $table) {
                $table->json('read_by')->nullable()->after('content');
            });
        }
    
        // Mark the message as read (ensure `read_by` is stored as JSON array)
        $readBy = $message->read_by ? json_decode($message->read_by, true) : [];
        if (!in_array(Auth::id(), $readBy)) {
            $readBy[] = Auth::id();
            $message->update(['read_by' => json_encode($readBy)]);
        }
    
        return get_success_response($message->fresh(), 'Message marked as read');
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
