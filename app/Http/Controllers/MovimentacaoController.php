<?php

namespace App\Http\Controllers;

use App\Models\Frequencia;
use App\Models\Movimentacao;
use App\Models\Usuario;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class MovimentacaoController extends Controller
{
    public function registro(Request $request): View|RedirectResponse
    {
        $usuario = Usuario::find($request->session()->get('usuario_id'));

        if (! $usuario) {
            return redirect()->route('login');
        }

        if ($usuario->is_professor) {
            return redirect()->route('historico');
        }

        return view('registro', ['usuario' => $usuario]);
    }

    public function store(Request $request): JsonResponse
    {
        $usuario = Usuario::find($request->session()->get('usuario_id'));

        if (! $usuario || $usuario->is_professor) {
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
            DB::transaction(function () use ($usuario, $dados, $momento): void {
                Movimentacao::create([
                    'usuario_id' => $usuario->id,
                    'nome' => $usuario->nome,
                    'matricula' => $dados['matricula'],
                    'turma' => $dados['turma'],
                    'saida' => $momento,
                    'observacao' => $dados['observacao'] ?? null,
                ]);

                $this->registrarFrequencia($dados, 'saida', $usuario->nome);
            });

            return response()->json(['mensagem' => 'Saída registrada com sucesso.'], 201);
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

        return response()->json(['mensagem' => 'Entrada registrada com sucesso.']);
    }

    public function historico(): View
    {
        return view('historico', ['movimentacoes' => $this->movimentacoes()]);
    }

    public function meuHistorico(Request $request): View|RedirectResponse
    {
        $usuario = Usuario::find($request->session()->get('usuario_id'));

        if (! $usuario) {
            return redirect()->route('login');
        }

        if ($usuario->is_professor) {
            return redirect()->route('historico');
        }

        $movimentacoes = Movimentacao::where('usuario_id', $usuario->id)
            ->orderByDesc('saida')
            ->get();

        return view('meu_historico', [
            'usuario' => $usuario,
            'movimentacoes' => $movimentacoes,
            'retornos' => $movimentacoes->whereNotNull('retorno')->count(),
        ]);
    }

    public function listar(): JsonResponse
    {
        return response()->json(['movimentacoes' => $this->movimentacoes()]);
    }

    public function relatorio(Request $request): View
    {
        $filtros = $request->validate([
            'turma' => ['nullable', 'string', 'max:100'],
            'inicio' => ['nullable', 'date_format:Y-m-d'],
            'fim' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:inicio'],
        ]);

        $consulta = Movimentacao::query();

        if (! empty($filtros['turma'])) {
            $consulta->where('turma', $filtros['turma']);
        }

        if (! empty($filtros['inicio'])) {
            $inicio = Carbon::createFromFormat('Y-m-d', $filtros['inicio'], 'America/Sao_Paulo')->startOfDay()->utc();
            $consulta->where('saida', '>=', $inicio);
        }

        if (! empty($filtros['fim'])) {
            $fim = Carbon::createFromFormat('Y-m-d', $filtros['fim'], 'America/Sao_Paulo')->endOfDay()->utc();
            $consulta->where('saida', '<=', $fim);
        }

        $movimentacoes = $consulta->orderByDesc('saida')->get();
        $duracoes = $movimentacoes->filter(fn (Movimentacao $movimentacao): bool => $movimentacao->retorno !== null)
            ->map(fn (Movimentacao $movimentacao): float => $movimentacao->saida->diffInMinutes($movimentacao->retorno));

        return view('relatorio_historico', [
            'filtros' => $filtros,
            'movimentacoes' => $movimentacoes,
            'totalAlunos' => $movimentacoes->unique('usuario_id')->count(),
            'pendentes' => $movimentacoes->whereNull('retorno')->count(),
            'tempoMedio' => $duracoes->isNotEmpty() ? round($duracoes->avg()) : null,
        ]);
    }

    private function movimentacoes(): array
    {
        return Movimentacao::orderByDesc('saida')->get()
            ->map(fn (Movimentacao $movimentacao): array => [
                'nome' => $movimentacao->nome,
                'matricula' => $movimentacao->matricula,
                'turma' => $movimentacao->turma,
                'saida' => $movimentacao->saida->toIso8601String(),
                'retorno' => $movimentacao->retorno?->toIso8601String(),
                'observacao' => $movimentacao->observacao,
            ])->all();
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
