<?php

namespace Tests\Feature;

use App\Models\TokenUsuario;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MobileApiControllerTest extends TestCase
{
    use RefreshDatabase;

    private function usuario(bool $professor = false): Usuario
    {
        $usuario = new Usuario;
        $usuario->nome = $professor ? 'Professor Teste' : 'Aluno Teste';
        $usuario->email = $professor ? 'professor@example.test' : 'aluno@example.test';
        $usuario->senha = 'senha-de-teste';
        $usuario->data_nascimento = '2000-01-01';
        $usuario->is_professor = $professor;
        $usuario->save();

        return $usuario;
    }

    private function token(Usuario $usuario): string
    {
        $token = TokenUsuario::create([
            'usuario_id' => $usuario->id,
            'token' => str_repeat((string) $usuario->id, 32),
            'valido_ate' => now()->addDay(),
        ]);

        return $token->token;
    }

    public function test_returns_401_without_valid_token(): void
    {
        $this->getJson('/api/mobile/me')->assertUnauthorized();
        $this->getJson('/api/mobile/movimentacoes')->assertUnauthorized();
        $this->postJson('/api/mobile/movimentacoes', [])->assertUnauthorized();
    }

    public function test_student_sees_only_own_movements_and_professor_sees_all(): void
    {
        $aluno = $this->usuario();
        $professor = $this->usuario(true);
        $tokenAluno = $this->token($aluno);
        $tokenProfessor = $this->token($professor);

        $this->withToken($tokenAluno)->postJson('/api/mobile/movimentacoes', [
            'matricula' => '20260001',
            'turma' => '2A',
            'tipo' => 'Saída',
            'data' => '2026-09-21',
            'horario' => '10:00',
        ])->assertCreated()->assertJsonPath('movimentacao.nome', 'Aluno Teste');

        $this->assertDatabaseHas('movimentacoes', ['usuario_id' => $aluno->id, 'matricula' => '20260001']);
        $this->assertDatabaseHas('frequencias', ['nome_aluno' => 'Aluno Teste', 'tipo_registro' => 'saida']);

        $this->withToken($tokenAluno)->getJson('/api/mobile/movimentacoes')
            ->assertOk()->assertJsonCount(1, 'movimentacoes');
        $this->withToken($tokenProfessor)->getJson('/api/mobile/movimentacoes')
            ->assertOk()->assertJsonCount(1, 'movimentacoes');
        $this->withToken($tokenProfessor)->postJson('/api/mobile/movimentacoes', [])->assertForbidden();
        $this->withToken($tokenProfessor)->getJson('/api/mobile/me')
            ->assertOk()->assertJsonPath('usuario.is_professor', true);
    }

    public function test_return_requires_earlier_exit_and_closes_it(): void
    {
        $aluno = $this->usuario();
        $token = $this->token($aluno);
        $dados = ['matricula' => '20260001', 'turma' => '2A', 'data' => '2026-09-21', 'horario' => '10:00', 'tipo' => 'Entrada'];

        $this->withToken($token)->postJson('/api/mobile/movimentacoes', $dados)->assertUnprocessable();
        $this->assertDatabaseCount('movimentacoes', 0);

        $this->withToken($token)->postJson('/api/mobile/movimentacoes', [...$dados, 'tipo' => 'Saída'])->assertCreated();
        $this->withToken($token)->postJson('/api/mobile/movimentacoes', [...$dados, 'horario' => '10:30'])->assertOk();

        $this->assertDatabaseHas('frequencias', ['matricula' => '20260001', 'tipo_registro' => 'entrada']);
        $this->withToken($token)->getJson('/api/mobile/movimentacoes')
            ->assertJsonCount(1, 'movimentacoes')
            ->assertJsonPath('movimentacoes.0.retorno', '2026-09-21T13:30:00+00:00');
    }
}
