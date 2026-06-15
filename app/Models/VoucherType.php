<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Modèle représentant un type de voucher accepté par la plateforme.
 *
 * Chaque type possède un nom, une expression régulière de validation du format de code,
 * des instructions pour les agents, et un indicateur d'activation.
 */
class VoucherType extends Model
{
    use HasFactory;

    /**
     * Les champs pouvant être assignés en masse.
     *
     * @var array<string>
     */
    protected $fillable = [
        'name',
        'code_format_regex',
        'instructions',
        'is_active',
    ];

    /**
     * Transtypage automatique des attributs.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Relation : ce type de voucher possède plusieurs demandes de vérification.
     */
    public function voucherRequests(): HasMany
    {
        return $this->hasMany(VoucherRequest::class);
    }

    /**
     * Alias francophone de la relation demandes de vérification.
     */
    public function demandesVerification(): HasMany
    {
        return $this->voucherRequests();
    }
}
