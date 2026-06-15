<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration : Création de la table des demandes de vérification de vouchers.
 *
 * Cette table est le cœur de la plateforme. Elle stocke chaque demande soumise par
 * un utilisateur, avec le code chiffré (AES-256), son empreinte SHA-256 anti-doublon,
 * le statut de traitement, et les informations de décision de l'agent.
 */
return new class extends Migration
{
    /**
     * Exécute la migration — crée la table voucher_requests.
     */
    public function up(): void
    {
        Schema::create('voucher_requests', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('request_type')->default('activation'); // 'activation' ou 'refund'
            $table->string('fullname');
            $table->string('email_user');
            $table->string('phone')->nullable();
            $table->foreignId('voucher_type_id')->constrained('voucher_types')->onDelete('cascade');
            $table->string('code1')->nullable();
            $table->string('code2')->nullable();
            $table->string('amount')->nullable();
            $table->text('user_comment')->nullable();
            $table->string('status')->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Annule la migration — supprime la table voucher_requests.
     */
    public function down(): void
    {
        Schema::dropIfExists('voucher_requests');
    }
};
