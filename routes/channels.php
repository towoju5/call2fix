<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

 
Broadcast::channel('chat.{chatId}', function ($user, $chatId) {
    return $user->chats->contains($chatId); // Ensure user is a participant in the chat
});