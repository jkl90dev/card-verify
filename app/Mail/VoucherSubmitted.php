<?php

namespace App\Mail;

use App\Models\VoucherRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/**
 * Mailable envoyé à l'agent/administrateur lorsqu'une nouvelle demande
 * de voucher (activation ou remboursement) est soumise sur la plateforme.
 */
class VoucherSubmitted extends Mailable
{
    use Queueable;

    /**
     * La demande de vérification associée.
     *
     * @var VoucherRequest
     */
    public $voucherRequest;

    /**
     * Crée une nouvelle instance du mailable.
     *
     * @param VoucherRequest $voucherRequest La demande fraîchement soumise
     */
    public function __construct(VoucherRequest $voucherRequest)
    {
        $this->voucherRequest = $voucherRequest;
    }

    /**
     * Retourne l'enveloppe du message (sujet de notification).
     */
    public function envelope(): Envelope
    {
        $type = $this->voucherRequest->voucherType->name;
        $reqType = $this->voucherRequest->request_type === 'refund' ? 'Remboursement' : 'Activation';

        return new Envelope(
            subject: "[Nouveau Ticket] Demande de {$reqType} - {$type}",
        );
    }

    /**
     * Retourne la définition du contenu du message (vue Blade utilisée).
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.submitted',
        );
    }
}
