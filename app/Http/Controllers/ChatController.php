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
        $chats = Auth::user()->chats()->with('participants', 'lastChat')->latest()->where('chats._account_type', active_role())->get();
        return get_success_response($chats);
    }

    public function store(Request $request)
    {
        // Check if 'read_by' column exists, if not, add it (This should be done in a migration)
        if (!Schema::hasColumn('chats', '_account_type')) {
            Schema::table('chats', function (Blueprint $table) {
                $table->string('_account_type')->nullable();
            });
        }
        
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

    public function readMessage(Request $request, $chatId, $messageId)
    {
        $chat = Chat::find($chatId);
        
        if (!$chat) {
            return get_error_response('Chat not found', ['error' => 'Chat not found'], 404);
        }
    
        // Ensure the user is a participant in the chat
        if (!$chat->participants()->where('user_id', auth()->id())->exists()) {
            return get_error_response('Unauthorized access to this chat', ['error' => 'Unauthorized access to this chat'], 403);
        }
    
        // Check if 'read_by' column exists, if not, add it (This should be done in a migration)
        if (!Schema::hasColumn('messages', 'read_by')) {
            Schema::table('messages', function (Blueprint $table) {
                $table->json('read_by')->nullable()->after('content');
            });
        }
    
        $message = Message::find($messageId);
    
        if (!$message) {
            return get_error_response('Message not found', ['error' => 'Message not found'], 404);
        }
    
        // Decode read_by as an array or initialize an empty array if null
        $readBy = is_array($message->read_by) ? $message->read_by : json_decode($message->read_by, true) ?? [];
    
        // Check if the user has already marked it as read
        if (!in_array(auth()->id(), $readBy)) {
            $readBy[] = auth()->id();
            $message->update(['read_by' => json_encode($readBy)]);
    
            return get_success_response($message->fresh(), 'Message marked as read');
        }
    
        return get_error_response(['error' => 'Message already read']);
    }
    
}
