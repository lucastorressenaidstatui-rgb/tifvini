<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meu histórico | Controle de frequência</title>
    <link rel="stylesheet" href="{{ asset('css/meu_historico.css') }}">
    <link rel="stylesheet" href="{{ asset('css/auth-theme.css') }}">
</head>
<body>
    <aside class="sidebar">
        <p class="sidebar-title">Controle de frequência</p>
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
