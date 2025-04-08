<?php

use Illuminate\Support\Facades\Route;

// web.php
Route::get('/', fn() => view('pages.home'))->name('pages.home');
Route::get('/sobre-nos', fn() => view('pages.sobre-nos'))->name('pages.sobre');
Route::get('/contato', fn() => view('pages.contato'))->name('pages.contato');
