<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meu histórico | SESI</title>
    <style>
        :root { color-scheme: light; --orange: #c64a0b; --ink: #292b31; --muted: #73757e; --line: #eae9e6; }
        * { box-sizing: border-box; }
        body { margin: 0; background: #f7f8fa; color: var(--ink); font: 14px "Segoe UI", sans-serif; }
        a { color: inherit; text-decoration: none; }
        button { font: inherit; cursor: pointer; }
        :focus-visible { outline: 3px solid #f5a56d; outline-offset: 4px; }
        .sidebar { width: 242px; position: fixed; inset: 0 auto 0 0; padding: 36px 24px; background: white; border-right: 1px solid var(--line); display: flex; flex-direction: column; }
        .brand { font-size: 35px; font-weight: 900; font-style: italic; letter-spacing: -2px; color: #e42230; }
        .brand-caption { margin: 5px 0 46px; color: var(--muted); font-size: 12px; }
        nav { display: grid; gap: 10px; }
        nav a { padding: 14px 12px; border-radius: 9px; font-weight: 600; font-size: 13px; }
        nav a:hover { background: #f8f8f8; }
        nav .active { background: #fff0e5; color: var(--orange); }
        .logout { margin-top: auto; padding-top: 24px; }
        .logout button { width: 100%; padding: 12px; background: white; border: 1px solid var(--line); border-radius: 8px; text-align: left; }
        main { margin-left: 242px; padding: 42px; max-width: 1500px; }
        .heading { margin-bottom: 28px; }
        h1 { margin: 0 0 8px; font-size: clamp(25px, 2.5vw, 34px); letter-spacing: -1px; }
        .subtitle { margin: 0; color: var(--muted); }
        .stats { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 18px; margin-bottom: 24px; }
        .card { background: white; border: 1px solid var(--line); border-radius: 14px; box-shadow: 0 3px 12px #19233303; }
        .stat { padding: 23px; }
        .stat span { display: block; color: var(--muted); font-size: 12px; }
        .stat strong { display: block; margin-top: 12px; font-size: 32px; }
        .section-heading { padding: 22px 24px; border-bottom: 1px solid var(--line); }
        h2 { margin: 0; font-size: 16px; }
        .table-scroll { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; text-align: left; }
        th { padding: 15px 22px; background: #fafbfc; color: var(--muted); font-size: 11px; font-weight: 600; white-space: nowrap; }
        td { padding: 18px 22px; border-top: 1px solid #f1f2f4; vertical-align: top; font-size: 12px; }
        .badge { display: inline-block; padding: 6px 10px; border-radius: 30px; background: #eaf6ef; color: #26734a; font-weight: 600; white-space: nowrap; }
        .badge.pending { background: #fff0e5; color: var(--orange); }
        .empty { padding: 42px 22px; color: var(--muted); text-align: center; }
        @media (max-width: 800px) { .sidebar { position: static; width: auto; padding: 16px; flex-direction: row; align-items: center; gap: 18px; } .brand-caption { display: none; } nav { display: flex; flex-wrap: wrap; } .logout { margin-left: auto; padding: 0; } main { margin: 0; padding: 26px 16px; } }
        @media (max-width: 520px) { .stats { grid-template-columns: 1fr; gap: 10px; } .stat { padding: 16px; } .stat strong { font-size: 26px; } .sidebar { flex-wrap: wrap; } .logout { margin-left: 0; } }
    </style>
</head>
<body>
    <aside class="sidebar">
        <div><div class="brand" aria-label="SESI">SESI</div><p class="brand-caption">Controle de frequência</p></div>
        <nav aria-label="Navegação principal">
            <a href="{{ route('registro') }}">Registrar movimentação</a>
            <a class="active" href="{{ route('meu-historico') }}" aria-current="page">Meu histórico</a>
        </nav>
        <form class="logout" action="{{ route('logout') }}" method="POST">@csrf<button type="submit">Sair da conta</button></form>
    </aside>
    <main>
        <header class="heading">
            <h1>Meu histórico</h1>
            <p class="subtitle">Movimentações registradas na conta de {{ $usuario->nome }}.</p>
        </header>
        <section class="stats" aria-label="Resumo das movimentações">
            <article class="card stat"><span>Saídas registradas</span><strong>{{ $movimentacoes->count() }}</strong></article>
            <article class="card stat"><span>Retornos registrados</span><strong>{{ $retornos }}</strong></article>
            <article class="card stat"><span>Sem retorno</span><strong>{{ $movimentacoes->count() - $retornos }}</strong></article>
        </section>
        <section class="card" aria-labelledby="movimentacoes-title">
            <div class="section-heading"><h2 id="movimentacoes-title">Minhas movimentações</h2></div>
            @if ($movimentacoes->isEmpty())
                <p class="empty">Você ainda não registrou nenhuma saída.</p>
            @else
                <div class="table-scroll">
                    <table>
                        <thead><tr><th scope="col">Data e saída</th><th scope="col">Turma</th><th scope="col">Retorno</th><th scope="col">Tempo fora</th><th scope="col">Situação</th><th scope="col">Observação</th></tr></thead>
                        <tbody>
                            @foreach ($movimentacoes as $movimentacao)
                                <tr>
                                    <td>{{ $movimentacao->saida->copy()->setTimezone('America/Sao_Paulo')->format('d/m/Y H:i') }}</td>
                                    <td>{{ $movimentacao->turma }}</td>
                                    <td>{{ $movimentacao->retorno?->copy()->setTimezone('America/Sao_Paulo')->format('d/m/Y H:i') ?? '—' }}</td>
                                    <td>{{ $movimentacao->retorno ? round($movimentacao->saida->diffInMinutes($movimentacao->retorno)) . ' min' : '—' }}</td>
                                    <td><span class="badge {{ $movimentacao->retorno ? '' : 'pending' }}">{{ $movimentacao->retorno ? 'Concluído' : 'Aguardando retorno' }}</span></td>
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
