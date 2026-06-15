<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration : Création de la table des types de vouchers.
 *
 * Cette table référence les différents types de coupons acceptés par la plateforme
 * (ex : Amazon Gift Card, Steam, Google Play, etc.), leur expression régulière de
 * validation du format de code, et leur statut d'activation.
 */
return new class extends Migration
{
    /**
     * Exécute la migration — crée la table voucher_types.
     */
    public function up(): void
    {
        Schema::create('voucher_types', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);                     // Nom du type de voucher (ex: "Amazon Gift Card")
            $table->string('code_format_regex')->nullable(); // Expression régulière de validation du format du code
            $table->text('instructions')->nullable();         // Consignes de vérification pour les agents
            $table->boolean('is_active')->default(true);     // Indique si ce type est accepté sur la plateforme
            $table->timestamps();
        });
    }

    /**
     * Annule la migration — supprime la table voucher_types.
     */
    public function down(): void
    {
        Schema::dropIfExists('voucher_types');
    }
};
