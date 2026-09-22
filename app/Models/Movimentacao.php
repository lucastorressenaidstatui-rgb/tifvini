<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Movimentacao extends Model
{
    protected $table = 'movimentacoes';

    protected $fillable = [
        'usuario_id',
        'nome',
        'matricula',
        'turma',
        'saida',
        'retorno',
        'observacao',
    ];

    protected function casts(): array
    {
        return [
            'saida' => 'datetime',
            'retorno' => 'datetime',
        ];
    }
}
