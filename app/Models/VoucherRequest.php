<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modèle représentant une demande de vérification de voucher soumise par un utilisateur.
 * Stocke les données de demande en clair sans hachage ni chiffrement.
 */
class VoucherRequest extends Model
{
    use HasFactory, HasUlids;

    /**
     * Les champs pouvant être assignés en masse.
     *
     * @var array<string>
     */
    protected $fillable = [
        'request_type',
        'fullname',
        'email_user',
        'phone',
        'voucher_type_id',
        'code1',
        'code2',
        'amount',
        'user_comment',
        'status',
    ];

    /**
     * Relation : cette demande appartient à un type de voucher.
     */
    public function voucherType(): BelongsTo
    {
        return $this->belongsTo(VoucherType::class);
    }
}
