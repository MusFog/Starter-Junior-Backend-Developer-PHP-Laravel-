<?php

use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\PositionsController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;


Route::middleware(['auth'])->group(function () {
    Route::view('/', 'employees-list')->name('employees-list');
    Route::view('/positions', 'positions-list')->name('positions-list');
    Route::view('/positions-create', 'positions-create')->name('positions-list.create');
    Route::view('/employees-create', 'employees-create')->name('employees-list.create');
});

Route::controller(PositionsController::class)->prefix('positions')->group(function () {
    Route::post('/add', 'addPosition')->name('positions.create');
    Route::post('/update', 'updatePosition')->name('positions.update');
    Route::get('/edit/{id}', 'edit')->name('positions.edit');
    Route::get('/get', 'getPositionsListAdmin')->name('positions.get');
    Route::delete('/remove/{id}', 'removePosition')->name('positions.remove');
})->middleware('auth');

Route::controller(EmployeeController::class)->prefix('employees')->group(function () {
    Route::post('/add', 'addEmployee')->name('employees.create');
    Route::post('/update', 'updateEmployee')->name('employees.update');
    Route::get('/edit/{id}', 'edit')->name('employees.edit');
    Route::delete('/remove/{id}', 'removeEmployee')->name('employees.remove');
    Route::post('/image/upload',  'image')->name('employee.image');
    Route::post('/image/remove',  'removeImage')->name('employee.remove_image');
})->middleware('auth');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
})->middleware('auth');

require __DIR__.'/auth.php';
