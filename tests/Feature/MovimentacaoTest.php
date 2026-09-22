<?php

namespace Tests\Feature;

use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MovimentacaoTest extends TestCase
{
    use RefreshDatabase;

    private function criarUsuario(bool $professor = false): Usuario
    {
        $usuario = new Usuario;
        $usuario->nome = $professor ? 'Professor' : 'Aluno';
        $usuario->email = $professor ? 'professor@example.test' : 'aluno@example.test';
        $usuario->senha = md5('senha-teste');
        $usuario->data_nascimento = '2000-01-01';
        $usuario->is_professor = $professor;
        $usuario->save();

        return $usuario;
    }

    private function dados(string $tipo = 'Saída', string $horario = '10:00'): array
    {
        return [
            'nome' => 'Aluno Teste',
            'matricula' => '20260001',
            'turma' => '2A',
            'tipo' => $tipo,
            'data' => '2026-09-21',
            'horario' => $horario,
            'observacao' => 'Teste de movimentação',
        ];
    }

    public function test_saida_e_entrada_do_aluno_aparecem_no_historico_do_professor(): void
    {
        $aluno = $this->criarUsuario();
        $professor = $this->criarUsuario(true);

        $this->withSession(['usuario_id' => $aluno->id])
            ->postJson(route('registro.store'), $this->dados())
            ->assertCreated();

        $this->withSession(['usuario_id' => $professor->id])
            ->get(route('historico'))
            ->assertOk()
            ->assertSee('Aluno Teste');

        $this->getJson(route('historico.movimentacoes'))
            ->assertOk()
            ->assertJsonPath('movimentacoes.0.matricula', '20260001')
            ->assertJsonPath('movimentacoes.0.retorno', null);

        $this->withSession(['usuario_id' => $aluno->id])
            ->postJson(route('registro.store'), $this->dados('Entrada', '10:30'))
            ->assertOk();

        $this->withSession(['usuario_id' => $professor->id])
            ->getJson(route('historico.movimentacoes'))
            ->assertJsonCount(1, 'movimentacoes')
            ->assertJsonPath('movimentacoes.0.retorno', '2026-09-21T13:30:00+00:00');

        $this->withSession(['usuario_id' => $aluno->id])
            ->get(route('meu-historico'))
            ->assertOk()
            ->assertSee('Concluído')
            ->assertSee('30 min');
    }

    public function test_entrada_sem_saida_e_rejeitada(): void
    {
        $aluno = $this->criarUsuario();

        $this->withSession(['usuario_id' => $aluno->id])
            ->postJson(route('registro.store'), $this->dados('Entrada'))
            ->assertUnprocessable();

        $this->assertDatabaseCount('movimentacoes', 0);
    }

    public function test_apenas_alunos_podem_registrar_e_apenas_professores_podem_listar(): void
    {
        $this->postJson(route('registro.store'), $this->dados())->assertForbidden();
        $this->getJson(route('historico.movimentacoes'))->assertRedirect(route('login'));

        $professor = $this->criarUsuario(true);
        $this->withSession(['usuario_id' => $professor->id])
            ->postJson(route('registro.store'), $this->dados())->assertForbidden();

        $aluno = $this->criarUsuario();
        $this->withSession(['usuario_id' => $aluno->id])
            ->getJson(route('historico.movimentacoes'))->assertForbidden();
    }

    public function test_aluno_ve_apenas_suas_movimentacoes(): void
    {
        $aluno = $this->criarUsuario();
        $outroAluno = new Usuario;
        $outroAluno->nome = 'Outro Aluno';
        $outroAluno->email = 'outro@example.test';
        $outroAluno->senha = md5('senha-teste');
        $outroAluno->data_nascimento = '2000-01-01';
        $outroAluno->is_professor = false;
        $outroAluno->save();

        $dadosDoAluno = $this->dados();
        $dadosDoAluno['observacao'] = 'Minha observação';
        $this->withSession(['usuario_id' => $aluno->id])
            ->postJson(route('registro.store'), $dadosDoAluno)->assertCreated();

        $dadosDoOutro = $this->dados();
        $dadosDoOutro['observacao'] = 'Observação particular';
        $this->withSession(['usuario_id' => $outroAluno->id])
            ->postJson(route('registro.store'), $dadosDoOutro)->assertCreated();

        $this->withSession(['usuario_id' => $aluno->id])
            ->get(route('meu-historico'))
            ->assertOk()
            ->assertSee('Minha observação')
            ->assertDontSee('Observação particular')
            ->assertSee('Aguardando retorno');

        $this->withSession(['usuario_id' => $outroAluno->id])
            ->get(route('meu-historico'))
            ->assertOk()
            ->assertSee('Observação particular')
            ->assertDontSee('Minha observação');
    }

    public function test_meu_historico_exige_conta_de_aluno(): void
    {
        $this->get(route('meu-historico'))->assertRedirect(route('login'));

        $professor = $this->criarUsuario(true);
        $this->withSession(['usuario_id' => $professor->id])
            ->get(route('meu-historico'))->assertForbidden();
    }

    public function test_relatorio_filtra_turma_e_periodo_para_professor(): void
    {
        $aluno = $this->criarUsuario();
        $professor = $this->criarUsuario(true);

        $primeiro = $this->dados('Saída', '23:50');
        $primeiro['observacao'] = 'Registro incluído';
        $this->withSession(['usuario_id' => $aluno->id])
            ->postJson(route('registro.store'), $primeiro)->assertCreated();

        $outraTurma = $this->dados();
        $outraTurma['turma'] = '3B';
        $outraTurma['observacao'] = 'Outra turma';
        $this->postJson(route('registro.store'), $outraTurma)->assertCreated();

        $outroDia = $this->dados('Saída', '00:10');
        $outroDia['data'] = '2026-09-22';
        $outroDia['observacao'] = 'Outro dia';
        $this->postJson(route('registro.store'), $outroDia)->assertCreated();

        $this->withSession(['usuario_id' => $professor->id])
            ->get(route('historico.relatorio', ['turma' => '2A', 'inicio' => '2026-09-21', 'fim' => '2026-09-21']))
            ->assertOk()
            ->assertSee('Registro incluído')
            ->assertDontSee('Outra turma')
            ->assertDontSee('Outro dia')
            ->assertSee('Salvar em PDF');
    }

    public function test_relatorio_exige_professor_e_rejeita_periodo_invalido(): void
    {
        $this->get(route('historico.relatorio'))->assertRedirect(route('login'));

        $aluno = $this->criarUsuario();
        $this->withSession(['usuario_id' => $aluno->id])
            ->get(route('historico.relatorio'))->assertForbidden();

        $professor = $this->criarUsuario(true);
        $this->withSession(['usuario_id' => $professor->id])
            ->get(route('historico.relatorio', ['inicio' => '2026-09-22', 'fim' => '2026-09-21']))
            ->assertSessionHasErrors('fim');
    }
}
