<?php

use App\Models\Usuario;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('professor:autorizar {email} {--revogar}', function () {
    $usuario = Usuario::where('email', $this->argument('email'))->first();
    if (! $usuario) {
        $this->error('Conta não encontrada. Cadastre o usuário primeiro.');

        return 1;
    }
    $usuario->is_professor = ! $this->option('revogar');
    $usuario->save();
    $this->info($usuario->is_professor ? 'Acesso de professor autorizado.' : 'Acesso de professor revogado.');

    return 0;
})->purpose('Autoriza ou revoga o acesso de professor de uma conta existente');
