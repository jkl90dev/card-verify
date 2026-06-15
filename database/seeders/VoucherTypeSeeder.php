<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Seeder : Données initiales pour les types de vouchers.
 *
 * Peuple la table voucher_types avec les principaux types de coupons numériques
 * acceptés par la plateforme. Chaque type inclut :
 * - Un nom commercial reconnaissable
 * - Une expression régulière de validation du format du code
 * - Des instructions de vérification à destination des agents
 */
class VoucherTypeSeeder extends Seeder
{
    /**
     * Insère les types de vouchers initiaux en base de données.
     */
    public function run(): void
    {
        $types = [
            [
                'name'               => 'Amazon Gift Card',
                'code_format_regex'  => '/^[A-Z0-9]{4}-[A-Z0-9]{6}-[A-Z0-9]{4,5}$/',
                'instructions'       => 'Vérifier la validité sur le portail Amazon. Format type : AQXX-XXXXXX-XXXX(X).',
                'is_active'          => true,
                'created_at'         => now(),
                'updated_at'         => now(),
            ],
            [
                'name'               => 'Steam Wallet Code',
                'code_format_regex'  => '/^[A-Z0-9]{5}-[A-Z0-9]{5}-[A-Z0-9]{5}$/',
                'instructions'       => 'Code de portefeuille Steam. Format type : XXXXX-XXXXX-XXXXX.',
                'is_active'          => true,
                'created_at'         => now(),
                'updated_at'         => now(),
            ],
            [
                'name'               => 'Google Play Gift Card',
                'code_format_regex'  => '/^(?:[A-Z0-9]{4}-){3}[A-Z0-9]{4}$|^[A-Z0-9]{16}$/',
                'instructions'       => 'Code Google Play. Format type : 16 caractères ou blocs de 4 séparés par des tirets.',
                'is_active'          => true,
                'created_at'         => now(),
                'updated_at'         => now(),
            ],
            [
                'name'               => 'Netflix Gift Card',
                'code_format_regex'  => '/^[A-Z0-9]{11}$|^[A-Z0-9]{16}$/',
                'instructions'       => 'Code Netflix. Généralement 11 ou 16 caractères alphanumériques.',
                'is_active'          => true,
                'created_at'         => now(),
                'updated_at'         => now(),
            ],
            [
                'name'               => 'Apple / iTunes',
                'code_format_regex'  => '/^[xX][a-zA-Z0-9]{15}$/',
                'instructions'       => 'Code Apple ou iTunes commençant par la lettre X, suivi de 15 caractères alphanumériques.',
                'is_active'          => true,
                'created_at'         => now(),
                'updated_at'         => now(),
            ],
            [
                'name'               => 'Paysafecard',
                'code_format_regex'  => '/^(?:\d{4}-){3}\d{4}$|^\d{16}$/',
                'instructions'       => 'Code PIN Paysafecard de 16 chiffres, avec ou sans tirets de séparation.',
                'is_active'          => true,
                'created_at'         => now(),
                'updated_at'         => now(),
            ],
            [
                'name'               => 'Transcash',
                'code_format_regex'  => '/^\d{12}$/',
                'instructions'       => 'Code de recharge Transcash composé de 12 chiffres.',
                'is_active'          => true,
                'created_at'         => now(),
                'updated_at'         => now(),
            ],
            [
                'name'               => 'Neosurf',
                'code_format_regex'  => '/^[A-Za-z0-9]{10}$/',
                'instructions'       => 'Code Neosurf composé de 10 caractères alphanumériques.',
                'is_active'          => true,
                'created_at'         => now(),
                'updated_at'         => now(),
            ],
            [
                'name'               => 'PCS',
                'code_format_regex'  => '/^\d{10}$|^\d{12}$/',
                'instructions'       => 'Recharge PCS composée de 10 ou 12 chiffres.',
                'is_active'          => true,
                'created_at'         => now(),
                'updated_at'         => now(),
            ],
        ];

        DB::table('voucher_types')->insert($types);
    }
}
