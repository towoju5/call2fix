<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Chat extends BaseModel
{
    use HasFactory;

    protected $fillable = ['name'];

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    public function participants()
    {
        return $this->belongsToMany(User::class, 'chat_user');
    }

    public function last_message()
    {
        // get the last chat])
    }
    
}
