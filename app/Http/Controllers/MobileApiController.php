<?php

namespace App\Http\Controllers;

use App\Models\Frequencia;
use App\Models\Movimentacao;
use App\Models\TokenUsuario;
use App\Models\Usuario;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MobileApiController extends Controller
{
    public function me(Request $request): JsonResponse
    {
        $usuario = $this->usuario($request);

        return response()->json(['usuario' => $this->dadosUsuario($usuario)]);
    }

    public function index(Request $request): JsonResponse
    {
        $usuario = $this->usuario($request);
        $consulta = Movimentacao::query();

        if (! $usuario->is_professor) {
            $consulta->where('usuario_id', $usuario->id);
        }

        $movimentacoes = $consulta->orderByDesc('saida')->get()->map(fn (Movimentacao $movimentacao): array => $this->dadosMovimentacao($movimentacao));

        return response()->json(['movimentacoes' => $movimentacoes]);
    }

    public function store(Request $request): JsonResponse
    {
        $usuario = $this->usuario($request);

        if ($usuario->is_professor) {
            return response()->json(['mensagem' => 'Entre como aluno para registrar uma movimentação.'], 403);
        }

        $dados = $request->validate([
            'matricula' => ['required', 'string', 'max:50'],
            'turma' => ['required', 'string', 'max:100'],
            'tipo' => ['required', 'in:Entrada,Saída'],
            'data' => ['required', 'date_format:Y-m-d'],
            'horario' => ['required', 'date_format:H:i'],
            'observacao' => ['nullable', 'string', 'max:500'],
        ]);

        $momento = Carbon::createFromFormat('Y-m-d H:i', $dados['data'].' '.$dados['horario'], 'America/Sao_Paulo')->utc();

        if ($dados['tipo'] === 'Saída') {
            $movimentacao = DB::transaction(function () use ($usuario, $dados, $momento): Movimentacao {
                $movimentacao = Movimentacao::create([
                    'usuario_id' => $usuario->id,
                    'nome' => $usuario->nome,
                    'matricula' => $dados['matricula'],
                    'turma' => $dados['turma'],
                    'saida' => $momento,
                    'observacao' => $dados['observacao'] ?? null,
                ]);

                $this->registrarFrequencia($dados, 'saida', $usuario->nome);

                return $movimentacao;
            });

            return response()->json(['mensagem' => 'Saída registrada com sucesso.', 'movimentacao' => $this->dadosMovimentacao($movimentacao)], 201);
        }

        $saida = Movimentacao::where('usuario_id', $usuario->id)
            ->where('matricula', $dados['matricula'])
            ->whereNull('retorno')
            ->where('saida', '<=', $momento)
            ->latest('saida')
            ->first();

        if (! $saida) {
            return response()->json(['mensagem' => 'Não há uma saída anterior sem retorno para esta matrícula.'], 422);
        }

        DB::transaction(function () use ($saida, $momento, $dados, $usuario): void {
            $saida->update(['retorno' => $momento]);
            $this->registrarFrequencia($dados, 'entrada', $usuario->nome);
        });

        return response()->json(['mensagem' => 'Entrada registrada com sucesso.', 'movimentacao' => $this->dadosMovimentacao($saida->fresh())]);
    }

    private function usuario(Request $request): Usuario
    {
        $token = $request->bearerToken();
        $registro = $token ? TokenUsuario::where('token', $token)->where('valido_ate', '>', now())->first() : null;
        $usuario = $registro ? Usuario::find($registro->usuario_id) : null;

        if (! $usuario) {
            abort(401, 'Sessão inválida ou expirada. Entre novamente.');
        }

        return $usuario;
    }

    /** @return array{id: int, nome: string, email: string, is_professor: bool} */
    private function dadosUsuario(Usuario $usuario): array
    {
        return [
            'id' => $usuario->id,
            'nome' => $usuario->nome,
            'email' => $usuario->email,
            'is_professor' => $usuario->is_professor,
        ];
    }

    /** @return array{id: int, nome: string, matricula: string, turma: string, saida: string, retorno: ?string, observacao: ?string} */
    private function dadosMovimentacao(Movimentacao $movimentacao): array
    {
        return [
            'id' => $movimentacao->id,
            'nome' => $movimentacao->nome,
            'matricula' => $movimentacao->matricula,
            'turma' => $movimentacao->turma,
            'saida' => $movimentacao->saida->toIso8601String(),
            'retorno' => $movimentacao->retorno?->toIso8601String(),
            'observacao' => $movimentacao->observacao,
        ];
    }

    /** @param array{matricula: string, turma: string, data: string, horario: string, observacao?: ?string} $dados */
    private function registrarFrequencia(array $dados, string $tipo, string $nome): void
    {
        Frequencia::create([
            'nome_aluno' => $nome,
            'matricula' => $dados['matricula'],
            'turma' => $dados['turma'],
            'tipo_registro' => $tipo,
            'data_registro' => $dados['data'],
            'hora_registro' => $dados['horario'],
            'observacao' => $dados['observacao'] ?? null,
        ]);
    }
}
