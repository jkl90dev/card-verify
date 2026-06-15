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
