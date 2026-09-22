<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatório de movimentações | Controle de frequência</title>
    <link rel="stylesheet" href="{{ asset('css/relatorio_historico.css') }}">
    <link rel="stylesheet" href="{{ asset('css/auth-theme.css') }}">
</head>
<body>
    <div class="actions">
        <a href="{{ route('historico') }}">Voltar ao Histórico</a>
        <small>Na janela de impressão, escolha “Salvar como PDF”.</small>
        <button type="button" id="print-report">Salvar em PDF</button>
    </div>
    <main class="page">
        <header>
            <div>
                <h1>Relatório de movimentações</h1>
                <div class="muted">Controle de saídas e retornos dos alunos</div>
            </div>
            <div class="issued"><strong>Emitido em</strong><br>{{ now()->setTimezone('America/Sao_Paulo')->format('d/m/Y H:i') }}</div>
        </header>

        <section class="filters" aria-label="Filtros do relatório">
            <div><span>Turma</span><strong>{{ $filtros['turma'] ?? 'Todas as turmas' }}</strong></div>
            <div><span>Data inicial</span><strong>{{ isset($filtros['inicio']) ? \Carbon\Carbon::createFromFormat('Y-m-d', $filtros['inicio'])->format('d/m/Y') : 'Todas' }}</strong></div>
            <div><span>Data final</span><strong>{{ isset($filtros['fim']) ? \Carbon\Carbon::createFromFormat('Y-m-d', $filtros['fim'])->format('d/m/Y') : 'Todas' }}</strong></div>
        </section>

        <section class="stats" aria-label="Resumo do relatório">
            <div class="stat"><span>Saídas</span><strong>{{ $movimentacoes->count() }}</strong></div>
            <div class="stat"><span>Alunos</span><strong>{{ $totalAlunos }}</strong></div>
            <div class="stat"><span>Sem retorno</span><strong>{{ $pendentes }}</strong></div>
            <div class="stat"><span>Tempo médio fora</span><strong>{{ $tempoMedio === null ? '—' : $tempoMedio . ' min' }}</strong></div>
        </section>

        <section aria-labelledby="details-title">
            <h2 id="details-title">Registros do período</h2>
            @if ($movimentacoes->isEmpty())
                <p class="empty">Nenhuma movimentação encontrada para os filtros selecionados.</p>
            @else
                <div class="table-scroll">
                    <table>
                        <thead><tr><th scope="col">Aluno</th><th scope="col">Turma</th><th scope="col">Saída</th><th scope="col">Retorno</th><th scope="col">Tempo fora</th><th scope="col">Situação</th><th scope="col">Observação</th></tr></thead>
                        <tbody>
                            @foreach ($movimentacoes as $movimentacao)
                                <tr>
                                    <td><strong>{{ $movimentacao->nome }}</strong><small>{{ $movimentacao->matricula }}</small></td>
                                    <td>{{ $movimentacao->turma }}</td>
                                    <td>{{ $movimentacao->saida->copy()->setTimezone('America/Sao_Paulo')->format('d/m/Y H:i') }}</td>
                                    <td>{{ $movimentacao->retorno?->copy()->setTimezone('America/Sao_Paulo')->format('d/m/Y H:i') ?? '—' }}</td>
                                    <td>{{ $movimentacao->retorno ? round($movimentacao->saida->diffInMinutes($movimentacao->retorno)) . ' min' : '—' }}</td>
                                    <td class="{{ $movimentacao->retorno ? '' : 'pending' }}">{{ $movimentacao->retorno ? 'Concluído' : 'Sem retorno' }}</td>
                                    <td>{{ $movimentacao->observacao ?: '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>
    </main>
    <script src="{{ asset('js/relatorio_historico.js') }}"></script>
</body>
</html>
