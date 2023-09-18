<?php

use App\Http\Controllers\SprintController;
use App\Http\Controllers\UserAvailabilityController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/user', [UserController::class, 'index'])->name('user.index');
Route::post('/user/register', [UserController::class, 'register'])->name('user.register');

Route::get('/user_availability', [UserAvailabilityController::class, 'index'])->name('user_availability.index');

Route::get('/sprint', [SprintController::class, 'index'])->name('sprint.index');
Route::get('/sprint/register', [SprintController::class, 'register'])->name('sprint.register');