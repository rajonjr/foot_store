<?php

use Illuminate\Support\Facades\Route;

Route::redirect('/', '/maillots/france-domicile-2026');

Route::livewire('/maillots/france-domicile-2026', 'pages::produit.show')
    ->name('produit.show');
