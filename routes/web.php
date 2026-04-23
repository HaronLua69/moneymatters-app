<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');
    Route::view('transactions', 'pages.transactions')->name('transactions');
    Route::view('transactions/add', 'pages.add-transaction')->name('transactions.add');
    Route::view('reports', 'pages.reports')->name('reports');
});

require __DIR__.'/settings.php';
