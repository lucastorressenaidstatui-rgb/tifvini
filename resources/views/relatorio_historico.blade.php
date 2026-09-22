<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatório de movimentações | SESI</title>
    <style>
        :root { color-scheme: light; --orange: #c64a0b; --ink: #292b31; --muted: #73757e; --line: #e7e7e7; }
        * { box-sizing: border-box; }
        body { margin: 0; padding: 32px; background: #f7f8fa; color: var(--ink); font: 13px "Segoe UI", Arial, sans-serif; }
        .page { max-width: 1120px; margin: auto; padding: 40px; background: #fff; border: 1px solid var(--line); border-radius: 14px; }
        .actions { max-width: 1120px; margin: 0 auto 20px; display: flex; align-items: center; justify-content: space-between; gap: 16px; }
        .actions a, .actions button { padding: 11px 16px; border: 1px solid var(--orange); border-radius: 8px; background: var(--orange); color: white; font: inherit; font-weight: 600; text-decoration: none; cursor: pointer; }
        .actions a { background: white; color: var(--orange); }
        .actions small { color: var(--muted); }
        header { display: flex; justify-content: space-between; align-items: start; gap: 20px; padding-bottom: 22px; border-bottom: 2px solid var(--orange); }
        .brand { font-size: 30px; font-weight: 900; font-style: italic; letter-spacing: -2px; color: #e42230; }
        h1 { margin: 12px 0 6px; font-size: 25px; letter-spacing: -.5px; }
        .muted { color: var(--muted); }
        .issued { text-align: right; white-space: nowrap; }
        .filters { display: flex; flex-wrap: wrap; gap: 16px 28px; padding: 22px 0; }
        .filters span { display: block; margin-bottom: 5px; color: var(--muted); font-size: 11px; }
        .filters strong { font-size: 13px; }
        .stats { display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; margin-bottom: 28px; }
        .stat { padding: 17px; border: 1px solid var(--line); border-radius: 10px; }
        .stat span { display: block; color: var(--muted); font-size: 11px; }
        .stat strong { display: block; margin-top: 8px; font-size: 23px; }
        h2 { margin: 0 0 12px; font-size: 16px; }
        .table-scroll { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; text-align: left; }
        th { padding: 11px 9px; border-bottom: 2px solid var(--line); color: var(--muted); font-size: 11px; white-space: nowrap; }
        td { padding: 12px 9px; border-bottom: 1px solid var(--line); vertical-align: top; overflow-wrap: anywhere; }
        td strong { display: block; }
        td small { color: var(--muted); }
        .pending { color: var(--orange); font-weight: 600; }
        .empty { padding: 28px; color: var(--muted); text-align: center; }
        @media (max-width: 700px) { body { padding: 14px; } .page { padding: 22px 16px; } .stats { grid-template-columns: repeat(2, 1fr); } header { flex-direction: column; } .issued { text-align: left; } .actions { flex-wrap: wrap; } }
        @media print {
            @page { size: A4 landscape; margin: 12mm; }
            body { padding: 0; background: white; print-color-adjust: exact; -webkit-print-color-adjust: exact; }
            .page { max-width: none; padding: 0; border: 0; border-radius: 0; }
            .actions { display: none; }
            .table-scroll { overflow: visible; }
            thead { display: table-header-group; }
            tr, .stat { break-inside: avoid; }
        }
    </style>
</head>
<body>
    <div class="actions">
        <a href="{{ route('historico') }}">Voltar ao Histórico</a>
        <small>Na janela de impressão, escolha “Salvar como PDF”.</small>
        <button type="button" onclick="window.print()">Salvar em PDF</button>
    </div>
    <main class="page">
        <header>
            <div>
                <div class="brand">SESI</div>
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
</body>
</html>
