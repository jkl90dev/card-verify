<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

/**
 * Seeder principal de la base de données.
 *
 * Ce seeder orchestre l'exécution de tous les seeders de la plateforme.
 * Il est invoqué via la commande : php artisan db:seed
 *
 * Ordre d'exécution :
 * 1. VoucherTypeSeeder — Peuple les types de vouchers acceptés
 */
class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Peuple la base de données avec les données initiales de la plateforme.
     */
    public function run(): void
    {
        $this->call([
            VoucherTypeSeeder::class, // Types de vouchers acceptés par la plateforme
        ]);
    }
}
