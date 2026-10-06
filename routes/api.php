<?php

use App\Http\Controllers\LoginController;
use App\Http\Controllers\MobileApiController;
use App\Http\Controllers\UsuarioController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/login', [LoginController::class, 'login_api']);

Route::post('/cadastro_usuario', [UsuarioController::class, 'cadastro_usuario']);

Route::prefix('mobile')->group(function (): void {
    Route::get('/me', [MobileApiController::class, 'me']);
    Route::get('/movimentacoes', [MobileApiController::class, 'index']);
    Route::post('/movimentacoes', [MobileApiController::class, 'store']);
});
