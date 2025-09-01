<?php

// use App\Livewire\Settings\Appearance;
use App\Livewire\Settings\Password;
use App\Livewire\Settings\Profile;
use Illuminate\Support\Facades\Route;
use App\Livewire\LandingPage;
use App\Livewire\FullscreenDashboard;
use App\Livewire\Settings\PengaturanAplikasi;
use App\Livewire\Admin\Dashboard\Index as AdminDashboard;
use App\Livewire\Admin\Dashboard\SurveyDetail;
use App\Livewire\Responden;
use App\Livewire\Admin\Reviews\Index as ReviewsPage;
use App\Livewire\Survey\FormSurvey;


Route::get('/', LandingPage::class)->name('landing');
Route::get('/tv', FullscreenDashboard::class)->name('landing.full-screen');

Route::get('/survey/{satker?}', FormSurvey::class)->name('survey.form');

Route::get('/dashboard', AdminDashboard::class)
    ->middleware(['auth', 'verified']) // Gunakan middleware yang sama seperti sebelumnya
    ->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::get('/surveys/{jawabanSurvey}', SurveyDetail::class)->name('admin.surveys.show');
    Route::get('/admin/reviews', ReviewsPage::class)->name('admin.reviews.index');

    Route::get('/responden', Responden::class)->name('admin.responden');
    Route::redirect('settings', 'settings/profile');

    Route::get('settings/profile', Profile::class)->name('settings.profile');
    Route::get('settings/password', Password::class)->name('settings.password');
    // Route::get('settings/appearance', Appearance::class)->name('settings.appearance');

    Route::get('/pengaturan', PengaturanAplikasi::class)->name('pengaturan');

    Route::prefix('master')->name('master.')->group(function () {
        Route::get('satker', \App\Livewire\Satker\ManajemenSatker::class)->name('satker');
        Route::get('kuesioner', \App\Livewire\Kuesioner\ManajemenKuesioner::class)->name('kuesioner');
    });
});

require __DIR__.'/auth.php';
