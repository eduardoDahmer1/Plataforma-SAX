<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UploadController;

// Página inicial - exibe os uploads mais recentes
Route::get('/', [UploadController::class, 'index'])->name('pages.home');

// Página com todos os uploads
Route::get('/uploads', [UploadController::class, 'allUploads'])->name('uploads.index');

// Rotas resource para uploads
Route::resource('uploads', UploadController::class)->except(['index']); // Excluímos o método index do resource

// Rota para visualizar um upload específico
Route::get('uploads/{id}', [UploadController::class, 'show'])->name('uploads.show');
