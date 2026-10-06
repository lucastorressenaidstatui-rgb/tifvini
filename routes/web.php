<?php

use App\Http\Controllers\LoginController;
use App\Http\Controllers\MovimentacaoController;
use App\Http\Controllers\UsuarioController;
use App\Http\Middleware\EnsureProfessor;
use App\Http\Middleware\LogadoMiddleware;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});
Route::get('/login', [LoginController::class, 'login_html'])->name('login');
Route::post('/login', [LoginController::class, 'login_web'])->middleware('throttle:5,1');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::get('/cadastro_usuario', [UsuarioController::class, 'cadastro_usuario_html']);

Route::middleware(LogadoMiddleware::class)->group(function () {
    Route::get('/registro', [MovimentacaoController::class, 'registro'])->name('registro');
    Route::post('/registro', [MovimentacaoController::class, 'store'])->name('registro.store');
    Route::get('/meu-historico', [MovimentacaoController::class, 'meuHistorico'])->name('meu-historico');

    Route::get('/historico', [MovimentacaoController::class, 'historico'])
        ->middleware(EnsureProfessor::class)
        ->name('historico');
    Route::get('/historico/movimentacoes', [MovimentacaoController::class, 'listar'])
        ->middleware(EnsureProfessor::class)
        ->name('historico.movimentacoes');
    Route::get('/historico/relatorio', [MovimentacaoController::class, 'relatorio'])
        ->middleware(EnsureProfessor::class)
        ->name('historico.relatorio');
});
