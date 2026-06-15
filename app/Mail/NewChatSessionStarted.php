<?php

namespace App\Mail;

use App\Models\ChatSession;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewChatSessionStarted extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * La session de chat associée.
     *
     * @var ChatSession
     */
    public $chatSession;

    /**
     * Le premier message envoyé.
     *
     * @var string
     */
    public $firstMessage;

    /**
     * Crée une nouvelle instance du mailable.
     */
    public function __construct(ChatSession $chatSession, string $firstMessage)
    {
        $this->chatSession = $chatSession;
        $this->firstMessage = $firstMessage;
    }

    /**
     * Retourne l'enveloppe du message.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "[Nouveau Chat] Discussion démarrée par {$this->chatSession->user_name}",
        );
    }

    /**
     * Retourne la définition du contenu du message.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.new-chat',
        );
    }
}
