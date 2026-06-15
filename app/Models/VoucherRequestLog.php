<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modèle représentant une entrée du journal d'audit d'une demande de vérification.
 *
 * Chaque entrée enregistre une action effectuée sur une demande (soumission, ouverture,
 * validation, rejet, envoi d'email, etc.), l'acteur responsable, et les métadonnées contextuelles.
 */
class VoucherRequestLog extends Model
{
    use HasFactory;

    /**
     * Les champs pouvant être assignés en masse.
     *
     * @var array<string>
     */
    protected $fillable = [
        'voucher_request_id',
        'actor_name',
        'action',
        'metadata',
    ];

    /**
     * Transtypage automatique des attributs.
     * Les métadonnées sont stockées en JSON et automatiquement décodées en tableau PHP.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'metadata' => 'array',
    ];

    /**
     * Relation : cette entrée de journal appartient à une demande de vérification.
     */
    public function voucherRequest(): BelongsTo
    {
        return $this->belongsTo(VoucherRequest::class);
    }

    /**
     * Alias francophone vers la demande parente.
     */
    public function demandeVerification(): BelongsTo
    {
        return $this->voucherRequest();
    }
}
