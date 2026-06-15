<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatMessage extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'chat_session_id',
        'sender',
        'message',
        'is_read',
        'attachment_path',
        'attachment_type',
    ];

    /**
     * Helper: Check if message contains an attachment.
     */
    public function hasAttachment(): bool
    {
        return !empty($this->attachment_path);
    }

    /**
     * Helper: Check if attachment is an image.
     */
    public function isImage(): bool
    {
        return $this->attachment_type === 'image';
    }

    /**
     * Helper: Check if attachment is an audio/voice note.
     */
    public function isAudio(): bool
    {
        return $this->attachment_type === 'audio';
    }

    /**
     * Relationship: A message belongs to a chat session.
     */
    public function chatSession(): BelongsTo
    {
        return $this->belongsTo(ChatSession::class);
    }
}
