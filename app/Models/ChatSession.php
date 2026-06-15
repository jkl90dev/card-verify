<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ChatSession extends Model
{
    use HasFactory, HasUlids;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'user_name',
        'user_email',
        'status',
    ];

    /**
     * Relationship: A chat session has many messages.
     */
    public function messages(): HasMany
    {
        return $this->hasMany(ChatMessage::class)->orderBy('created_at', 'asc');
    }

    /**
     * Relationship: Unread messages for the agent (sent by the user).
     */
    public function unreadMessagesForAgent(): HasMany
    {
        return $this->hasMany(ChatMessage::class)
            ->where('sender', 'user')
            ->where('is_read', false);
    }

    /**
     * Relationship: Unread messages for the user (sent by the agent).
     */
    public function unreadMessagesForUser(): HasMany
    {
        return $this->hasMany(ChatMessage::class)
            ->where('sender', 'agent')
            ->where('is_read', false);
    }
}
