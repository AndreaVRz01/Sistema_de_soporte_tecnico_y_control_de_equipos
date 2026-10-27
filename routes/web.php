<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes - SIGA Enterprise
|--------------------------------------------------------------------------
| Rutas para el portal público y la consola privada de operaciones
*/

// Portal Público: Catálogo de Equipos y Solicitud de Préstamos
Route::get('/', function () {
    return view('public.catalog');
})->name('public.home');

Route::get('/catalogo', function () {
    return view('public.catalog');
})->name('public.catalog');

// Portal Privado: Consola Operativa y Dashboard
Route::get('/dashboard', function () {
    return view('private.dashboard');
})->name('dashboard');
