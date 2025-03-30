<?php

use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\PositionsController;
use Illuminate\Support\Facades\Route;

Route::controller(EmployeeController::class)->prefix('employees')->group(function () {
    Route::get('/data', 'data')->name('employee.table');
    Route::get('/users/search', 'search')->name('search');
})->middleware('auth');

Route::controller(PositionsController::class)->prefix('positions')->group(function () {
    Route::get('/data', 'data')->name('position.table');
})->middleware('auth');

