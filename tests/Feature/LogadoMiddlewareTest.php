<?php

namespace Tests\Feature;

use App\Models\TokenUsuario;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LogadoMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    public function test_rotas_protegidas_redirecionam_visitantes_para_login(): void
    {
        foreach (['/registro', '/meu-historico', '/historico', '/historico/movimentacoes', '/historico/relatorio'] as $rota) {
            $this->get($rota)->assertRedirect('/login');
        }

        $this->post('/registro')->assertRedirect('/login');
        $this->postJson('/registro')->assertForbidden()
            ->assertJsonPath('mensagem', 'Entre na sua conta para continuar.');
        $this->get('/login')->assertOk();
        $this->get('/cadastro_usuario')->assertOk();
    }

    public function test_cookie_valido_autentica_aluno_sem_sessao_anterior(): void
    {
        $this->freezeTime();
        $usuario = $this->criarUsuario();
        $token = $this->criarToken($usuario);

        $this->withUnencryptedCookie('token_usuario', $token->token)
            ->get('/registro')->assertOk()->assertViewHas('usuario', $usuario)
            ->assertSessionHas('usuario_id', $usuario->id);

        $this->get('/meu-historico')->assertOk();
        $this->get('/historico')->assertForbidden();
    }

    public function test_cookie_valido_permite_historico_do_professor(): void
    {
        $this->freezeTime();
        $usuario = $this->criarUsuario(true);
        $token = $this->criarToken($usuario);

        $this->withUnencryptedCookie('token_usuario', $token->token)
            ->get('/historico')->assertOk()->assertSessionHas('usuario_id', $usuario->id);
    }

    public function test_cookie_invalido_remove_cookie_e_sessao(): void
    {
        $usuario = $this->criarUsuario();

        $this->withSession(['usuario_id' => $usuario->id])
            ->withUnencryptedCookie('token_usuario', 'token-inexistente')
            ->get('/registro')->assertRedirect('/login')
            ->assertCookieExpired('token_usuario')->assertSessionMissing('usuario_id');
    }

    public function test_token_expirado_bloqueia_acesso_mesmo_com_sessao(): void
    {
        $this->freezeTime();
        $usuario = $this->criarUsuario();
        $token = $this->criarToken($usuario);
        $this->travel(2)->days();

        $this->withSession(['usuario_id' => $usuario->id, 'token_usuario' => $token->token])
            ->get('/registro')->assertRedirect('/login')
            ->assertCookieExpired('token_usuario')->assertSessionMissing('usuario_id');
    }

    public function test_cookie_de_usuario_excluido_nao_permite_acesso(): void
    {
        $this->freezeTime();
        $usuario = $this->criarUsuario();
        $token = $this->criarToken($usuario);
        $usuario->delete();

        $this->withUnencryptedCookie('token_usuario', $token->token)
            ->get('/registro')->assertRedirect('/login')->assertCookieExpired('token_usuario');
    }

    public function test_login_emite_cookie_e_logout_revoga_token(): void
    {
        $this->freezeTime();
        $usuario = $this->criarUsuario();
        $response = $this->postJson('/login', ['email' => $usuario->email, 'senha' => 'senha-teste']);
        $response->assertOk()->assertCookieNotExpired('token_usuario');
        $cookie = $response->getCookie('token_usuario', false);
        $this->assertTrue($cookie->isHttpOnly());
        $this->assertDatabaseHas('token_usuario', [
            'usuario_id' => $usuario->id,
            'token' => $cookie->getValue(),
            'valido_ate' => now()->addDays(2)->toDateTimeString(),
        ]);

        $this->app['session']->flush();
        $this->withUnencryptedCookie('token_usuario', $cookie->getValue())
            ->get('/registro')->assertOk();
        $this->post('/logout')->assertRedirect('/login')
            ->assertCookieExpired('token_usuario')->assertSessionMissing('usuario_id');
        $this->assertDatabaseMissing('token_usuario', ['token' => $cookie->getValue()]);
        $this->get('/registro')->assertRedirect('/login');
    }

    public function test_novo_login_revoga_token_anterior_do_navegador(): void
    {
        $this->freezeTime();
        $usuario = $this->criarUsuario();
        $token = $this->criarToken($usuario);

        $this->withUnencryptedCookie('token_usuario', $token->token)
            ->withCredentials()
            ->postJson('/login', ['email' => $usuario->email, 'senha' => 'senha-teste'])
            ->assertOk()->assertCookieNotExpired('token_usuario');

        $this->assertDatabaseMissing('token_usuario', ['token' => $token->token]);
        $this->assertDatabaseCount('token_usuario', 1);
    }

    private function criarUsuario(bool $professor = false): Usuario
    {
        $usuario = new Usuario;
        $usuario->nome = 'Pessoa de teste';
        $usuario->email = 'pessoa@example.test';
        $usuario->senha = md5('senha-teste');
        $usuario->data_nascimento = '2000-01-01';
        $usuario->is_professor = $professor;
        $usuario->save();

        return $usuario;
    }

    private function criarToken(Usuario $usuario): TokenUsuario
    {
        return TokenUsuario::create([
            'usuario_id' => $usuario->id,
            'token' => 'token-de-teste',
            'valido_ate' => now()->addDays(2),
        ]);
    }
}
