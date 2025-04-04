<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends BaseModel
{
    use HasFactory;

    protected $fillable = ['chat_id', 'user_id', 'content', 'read_by'];

    public function chat()
    {
        return $this->belongsTo(Chat::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    protected $casts = [
        'read_by' => 'array'
    ];
}
