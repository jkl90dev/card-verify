<?php

namespace App\Mail;

use App\Models\VoucherRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Mailable envoyé à l'utilisateur lorsque le statut de sa demande de vérification
 * de voucher est mis à jour (validé ou rejeté) par un agent.
 *
 * Le sujet du courriel s'adapte dynamiquement selon le résultat de la vérification.
 */
class VoucherStatusUpdated extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * La demande de vérification dont le statut vient d'être mis à jour.
     *
     * @var VoucherRequest
     */
    public $voucherRequest;

    /**
     * Crée une nouvelle instance du mailable.
     *
     * @param VoucherRequest $voucherRequest La demande dont le statut a changé
     */
    public function __construct(VoucherRequest $voucherRequest)
    {
        $this->voucherRequest = $voucherRequest;
    }

    /**
     * Retourne l'enveloppe du message avec un sujet dynamique selon le résultat.
     */
    public function envelope(): Envelope
    {
        $libelleStatut = $this->voucherRequest->status === 'validated'
            ? 'VALIDÉ ✅'
            : 'REJETÉ ❌';

        return new Envelope(
            subject: "Mise à jour de votre demande : Voucher {$libelleStatut}",
        );
    }

    /**
     * Retourne la définition du contenu du message (vue Blade utilisée).
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.status_updated',
        );
    }
}
