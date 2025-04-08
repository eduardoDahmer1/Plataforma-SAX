<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UploadController;

// Página inicial - exibe os uploads mais recentes
Route::get('/', [UploadController::class, 'index'])->name('pages.home');

// Páginas estáticas
Route::get('/sobre-nos', fn() => view('pages.sobre-nos'))->name('pages.sobre');
Route::get('/contato', fn() => view('pages.contato'))->name('pages.contato');

// Página com todos os uploads
Route::get('/uploads', [UploadController::class, 'allUploads'])->name('uploads.index');

// Rotas resource para uploads
Route::resource('uploads', UploadController::class)->except(['index']); // Excluímos o método index do resource
