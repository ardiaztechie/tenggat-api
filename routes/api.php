<?php

use App\Http\Controllers\TugasController;
use Illuminate\Support\Facades\Route;

// "statistik" harus didaftarkan sebelum apiResource agar tidak dianggap sebagai {id}.
Route::get('tugas/statistik', [TugasController::class, 'statistik']);

Route::post('tugas/{id}/foto', [TugasController::class, 'unggahFoto']);
Route::delete('tugas/{id}/foto', [TugasController::class, 'hapusFoto']);
Route::get('tugas/{id}/foto', [TugasController::class, 'lihatFoto']);

Route::apiResource('tugas', TugasController::class)
    ->except(['show'])
    ->parameters(['tugas' => 'id']);
