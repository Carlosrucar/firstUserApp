<?php

use Illuminate\Support\Facades\Route;

Auth::routes();

Route::get('/', [App\Http\Controllers\HomeController::class, 'index'])->name('index');
Route::get('/home', [App\Http\Controllers\HomeController::class, 'home'])->name('home');
Route::get('/admin', [App\Http\Controllers\AdministratorsController::class, 'index'])->name('admin.index');
Route::get('/profile', [App\Http\Controllers\ProfileController::class, 'showProfileForm'])->name('profile.show');
Route::post('/profile', [App\Http\Controllers\ProfileController::class, 'updateProfile'])->name('profile.update');