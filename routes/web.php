<?php

use App\Livewire\Settings\Appearance;
use App\Livewire\Settings\Password;
use App\Livewire\Settings\Profile;
use Illuminate\Support\Facades\Route;
use App\Livewire\LandingPage;
use App\Livewire\FullscreenDashboard;
use App\Livewire\Settings\PengaturanAplikasi;


Route::get('/', LandingPage::class)->name('landing');
Route::get('/tv', FullscreenDashboard::class)->name('landing.full-screen');

Route::get('survey', \App\Livewire\Survey\FormSurvey::class)->name('survey.form');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Route::get('settings/profile', Profile::class)->name('settings.profile');
    Route::get('settings/password', Password::class)->name('settings.password');
    Route::get('settings/appearance', Appearance::class)->name('settings.appearance');

    Route::get('/pengaturan', PengaturanAplikasi::class)->name('pengaturan');


    Route::prefix('master')->name('master.')->group(function () {
        Route::get('satker', \App\Livewire\Satker\ManajemenSatker::class)->name('satker');
        Route::get('kuesioner', \App\Livewire\Kuesioner\ManajemenKuesioner::class)->name('kuesioner');
    });
});

require __DIR__.'/auth.php';
