<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Usuario extends Model
{
    protected $table = 'usuario';

    protected function casts(): array
    {
        return ['is_professor' => 'boolean'];
    }

    protected $fillable = [
        'nome',
        'email',
        'senha',
        'data_nascimento',
        'cpf',
    ];
}
