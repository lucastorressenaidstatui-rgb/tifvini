<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Frequencia extends Model
{
    protected $table = 'frequencias';

    protected $fillable = [
        'nome_aluno',
        'matricula',
        'turma',
        'tipo_registro',
        'data_registro',
        'hora_registro',
        'observacao',
    ];
}
