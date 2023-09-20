<?php

use App\Http\Controllers\FreeController;
use App\Http\Controllers\SprintController;
use App\Http\Controllers\TaskController;
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

// Route::get('/sprint', [SprintController::class, 'index'])->name('sprint.index');
// Route::post('/sprint/register', [SprintController::class, 'register'])->name('sprint.register');

Route::get('/sprint', [SprintController::class,'index'])->name('sprint.index');
Route::post('/sprint/store', [SprintController::class, 'store'])->name('sprint.register');
Route::get('/sprint/template', [SprintController::class, 'edit_template']);
Route::post('/sprint/update_template', [SprintController::class, 'update_template']);

Route::post('user/delete/{user_id}', [UserController::class, 'delete']);

Route::get('/free', [FreeController::class, 'index']);
Route::get('/free/edit', [FreeController::class, 'edit']);
Route::post('/free/update', [FreeController::class, 'update']);

Route::get('/task', [TaskController::class, 'index'])->name('task.index');
Route::get('/task/latest_sprint', [TaskController::class, 'latestSprint'])->name('task.latest_sprint');
Route::get('/task/create', [TaskController::class, 'create'])->name('task.create');
Route::post('/task/store', [TaskController::class, 'store'])->name('task.store');
Route::get('/task/edit/{task_id}', [TaskController::class, 'edit'])->name('task.edit');
Route::post('/task/update/{task_id}/', [TaskController::class, 'update'])->name('task.update');
Route::post('/task/destroy/{id}/', [TaskController::class, 'destroy'])->name('task.destroy');
Route::post('/task/copy/{task_id}/', [TaskController::class, 'copy'])->name('task.copy');