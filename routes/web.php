<?php

use App\Http\Controllers\BuktiTfController;
use App\Livewire\LandingPage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', LandingPage::class)->name('landing');

Route::livewire('/admin/login', 'pages::admin.login')->name('login');

Route::post('/logout', function () {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();

    return redirect()->route('login');
})->middleware('auth')->name('logout');

Route::middleware('auth')->group(function () {
    Route::livewire('/admin', 'pages::admin.registrations')->name('admin.registrations');
    Route::livewire('/admin/tim', 'pages::admin.teams')->name('admin.teams');
    Route::livewire('/admin/setting', 'pages::admin.settings')->name('admin.settings');
    Route::get('/admin/bukti-tf/{registration}', [BuktiTfController::class, 'show'])
        ->name('admin.bukti-tf');

    Route::redirect('/admin/gereja', '/admin/setting');
    Route::redirect('/admin/pembayaran', '/admin/setting');
});

Route::redirect('/login', '/admin/login');
