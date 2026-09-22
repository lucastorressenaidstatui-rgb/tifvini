<?php

namespace Tests\Feature;

use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_cadastro_e_login_de_aluno_e_professor(): void
    {
        foreach (['aluno', 'professor'] as $perfil) {
            $email = $perfil.'@example.test';

            $this->postJson('/api/cadastro_usuario', [
                'nome' => 'Usuario Teste',
                'email' => $email,
                'senha' => 'senha-segura',
                'cpf' => '12345678901',
                'data_nascimento' => '2000-01-01',
                'perfil' => $perfil,
            ])->assertOk()->assertJsonPath('erro', 'n');

            $usuario = Usuario::where('email', $email)->firstOrFail();
            $this->assertTrue(Hash::check('senha-segura', $usuario->senha));
            $this->assertSame($perfil === 'professor', $usuario->is_professor);

            $this->postJson('/login', [
                'email' => $email,
                'senha' => 'senha-segura',
                'perfil' => $perfil,
            ])->assertOk()->assertJsonPath('redirect', route($perfil === 'professor' ? 'historico' : 'registro'));

            $this->assertSame($usuario->id, session('usuario_id'));
            $this->post(route('logout'))->assertRedirect(route('login'));
        }
    }

    public function test_login_de_conta_antiga_atualiza_a_senha(): void
    {
        $usuario = new Usuario;
        $usuario->nome = 'Conta Antiga';
        $usuario->email = 'antiga@example.test';
        $usuario->senha = md5('senha-antiga');
        $usuario->data_nascimento = '2000-01-01';
        $usuario->save();

        $this->postJson('/login', [
            'email' => $usuario->email,
            'senha' => 'senha-errada',
            'perfil' => 'aluno',
        ])->assertUnprocessable();

        $this->postJson('/login', [
            'email' => $usuario->email,
            'senha' => 'senha-antiga',
            'perfil' => 'aluno',
        ])->assertOk()->assertJsonPath('erro', 'n');

        $this->assertTrue(Hash::check('senha-antiga', $usuario->fresh()->senha));
    }
}
