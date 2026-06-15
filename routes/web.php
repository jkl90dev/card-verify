<?php

/**
 * Plateforme de Vouchers et Support de Chat
 * Fait par JKL90DEV
 */

use Illuminate\Support\Facades\Route;

Route::livewire('/', 'submit-voucher');

Route::livewire('/agent/chat-dashboard', 'chat-dashboard')->name('agent.chat');

Route::get('/lang/{locale}', function (string $locale) {
    if (in_array($locale, ['fr', 'en', 'de', 'es', 'it', 'nl'])) {
        session(['locale' => $locale]);
    }
    return redirect()->back();
})->name('locale.switch');

Route::get('/agent/link-storage', function () {
    $expectedKey = config('app.chat_agent_key');
    if (request()->query('key') !== $expectedKey) {
        abort(403, 'Accès Refusé');
    }
    
    try {
        Illuminate\Support\Facades\Artisan::call('storage:link');
        return 'Le lien symbolique de stockage a été créé avec succès !';
    } catch (\Exception $e) {
        return 'Erreur lors de la création du lien : ' . $e->getMessage();
    }
})->name('agent.link-storage');

Route::get('/agent/seed-database', function () {
    $expectedKey = config('app.chat_agent_key');
    if (request()->query('key') !== $expectedKey) {
        abort(403, 'Accès Refusé');
    }
    
    try {
        Illuminate\Support\Facades\Schema::disableForeignKeyConstraints();
        Illuminate\Support\Facades\DB::table('voucher_types')->truncate();
        Illuminate\Support\Facades\Schema::enableForeignKeyConstraints();
        
        Illuminate\Support\Facades\Artisan::call('db:seed', ['--force' => true]);
        return 'Base de données peuplée avec succès !';
    } catch (\Exception $e) {
        return 'Erreur lors du seeding : ' . $e->getMessage();
    }
})->name('agent.seed-database');
