<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');
    Route::view('budgets', 'pages.budgets')->name('budgets');
    Route::view('transactions', 'pages.transactions')->name('transactions');
    Route::view('transactions/add', 'pages.add-transaction')->name('transactions.add');
    Route::view('calculator/loan', 'pages.calculator.loan')->name('calculator.loan');
    Route::view('calculator/what-if', 'pages.calculator.what-if')->name('calculator.what-if');
    Route::view('reports', 'pages.reports')->name('reports');
});

require __DIR__.'/settings.php';
