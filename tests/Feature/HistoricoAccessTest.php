<?php

namespace Tests\Feature;

use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HistoricoAccessTest extends TestCase
{
    use RefreshDatabase;

    private function createUsuario(bool $professor = false): Usuario
    {
        $usuario = new Usuario;
        $usuario->nome = 'Pessoa de teste';
        $usuario->email = 'pessoa@example.test';
        $usuario->senha = md5('senha-teste');
        $usuario->data_nascimento = '1990-01-01';
        $usuario->is_professor = $professor;
        $usuario->save();

        return $usuario;
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/historico')->assertRedirect('/login');
    }

    public function test_regular_user_cannot_open_history(): void
    {
        $usuario = $this->createUsuario();
        $this->withSession(['usuario_id' => $usuario->id])->get('/historico')->assertForbidden();
    }

    public function test_professor_can_log_in_and_open_history(): void
    {
        $usuario = $this->createUsuario(true);
        $this->postJson('/login', ['email' => $usuario->email, 'senha' => 'senha-teste'])
            ->assertOk()->assertJsonPath('redirect', route('historico'))
            ->assertSessionHas('usuario_id', $usuario->id);
        $this->get('/historico')->assertOk()->assertViewIs('historico')
            ->assertSee('Tempo médio fora')->assertHeader('Cache-Control', 'no-store, private');
    }

    public function test_selected_profile_must_match_the_account(): void
    {
        $usuario = $this->createUsuario();

        $this->postJson('/login', ['email' => $usuario->email, 'senha' => 'senha-teste', 'perfil' => 'professor'])
            ->assertUnprocessable()->assertSessionMissing('usuario_id');

        $this->postJson('/login', ['email' => $usuario->email, 'senha' => 'senha-teste', 'perfil' => 'aluno'])
            ->assertOk()->assertJsonPath('redirect', route('registro'));
    }

    public function test_regular_user_login_does_not_grant_professor_access(): void
    {
        $usuario = $this->createUsuario();
        $this->postJson('/login', ['email' => $usuario->email, 'senha' => 'senha-teste', 'is_professor' => true])
            ->assertOk()->assertJsonPath('redirect', route('registro'));
        $this->get('/historico')->assertForbidden();
    }

    public function test_invalid_password_does_not_create_session(): void
    {
        $usuario = $this->createUsuario(true);
        $this->postJson('/login', ['email' => $usuario->email, 'senha' => 'incorreta'])
            ->assertUnprocessable()->assertSessionMissing('usuario_id');
        $this->get('/historico')->assertRedirect('/login');
    }

    public function test_revoked_professor_is_blocked_even_with_existing_session(): void
    {
        $usuario = $this->createUsuario(true);
        $this->withSession(['usuario_id' => $usuario->id]);
        $this->artisan('professor:autorizar', ['email' => $usuario->email, '--revogar' => true])->assertSuccessful();
        $this->get('/historico')->assertForbidden();
    }

    public function test_logout_removes_access(): void
    {
        $usuario = $this->createUsuario(true);
        $this->withSession(['usuario_id' => $usuario->id])->post('/logout')->assertRedirect('/login');
        $this->get('/historico')->assertRedirect('/login');
    }

    public function test_authorization_command_grants_access_only_to_existing_user(): void
    {
        $usuario = $this->createUsuario();
        $this->artisan('professor:autorizar', ['email' => $usuario->email])->assertSuccessful();
        $this->withSession(['usuario_id' => $usuario->id])->get('/historico')->assertOk();
        $this->artisan('professor:autorizar', ['email' => 'missing@example.test'])->assertFailed();
    }

    public function test_registration_cannot_grant_professor_role(): void
    {
        $this->postJson('/api/cadastro_usuario', [
            'nome' => 'Novo usuário', 'email' => 'novo@example.test', 'senha' => 'senha-teste',
            'cpf' => '12345678901', 'data_nascimento' => '1990-01-01', 'perfil' => 'aluno', 'is_professor' => true,
        ])->assertOk()->assertJsonPath('erro', 'n');
        $this->assertDatabaseHas('usuario', ['email' => 'novo@example.test', 'is_professor' => false]);
    }

    public function test_registered_student_can_log_in(): void
    {
        $this->postJson('/api/cadastro_usuario', [
            'nome' => 'Aluno Novo',
            'email' => 'aluno.novo@example.test',
            'senha' => 'senha-teste',
            'cpf' => '12345678901',
            'data_nascimento' => '2005-01-01',
            'perfil' => 'aluno',
        ])->assertOk()->assertJsonPath('erro', 'n');

        $this->postJson('/login', [
            'email' => 'aluno.novo@example.test',
            'senha' => 'senha-teste',
            'perfil' => 'aluno',
        ])->assertOk()->assertJsonPath('redirect', route('registro'));
    }

    public function test_registered_professor_can_log_in_and_open_history(): void
    {
        $this->postJson('/api/cadastro_usuario', [
            'nome' => 'Professor Novo',
            'email' => 'professor.novo@example.test',
            'senha' => 'senha-teste',
            'cpf' => '12345678901',
            'data_nascimento' => '1990-01-01',
            'perfil' => 'professor',
        ])->assertOk()->assertJsonPath('erro', 'n');

        $this->assertDatabaseHas('usuario', ['email' => 'professor.novo@example.test', 'is_professor' => true]);

        $this->postJson('/login', [
            'email' => 'professor.novo@example.test',
            'senha' => 'senha-teste',
            'perfil' => 'professor',
        ])->assertOk()->assertJsonPath('redirect', route('historico'));

        $this->get('/historico')->assertOk();
    }
}
