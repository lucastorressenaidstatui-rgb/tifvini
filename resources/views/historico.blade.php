<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Histórico de movimentações | SESI</title>
    <link rel="stylesheet" href="{{ asset('css/historico.css') }}">
</head>
<body>
    <aside class="sidebar">
        <div class="brand" aria-label="SESI">SESI</div>
        <nav aria-label="Navegação principal">
            <a href="{{ route('registro') }}"><span aria-hidden="true">⇄</span> Movimentações</a>
            <a class="active" href="{{ url('/historico') }}" aria-current="page"><span aria-hidden="true">▥</span> Histórico</a>
            <a href="{{ url('/cadastro_usuario') }}"><span aria-hidden="true">＋</span> Cadastrar usuário</a>
        </nav>
        <form action="{{ route('logout') }}" method="POST" class="logout-form">@csrf<button type="submit" class="button">Sair da conta</button></form>
    </aside>
    <main>
        <header class="heading"><h1>Histórico de movimentações</h1><span class="teacher-badge">Área do professor</span></header>
        <form class="filters card" id="filters">
            <div class="search"><label for="search">Aluno ou matrícula</label><input type="search" id="search" placeholder="Buscar aluno..."></div>
            <div><label for="class-filter">Turma</label><select id="class-filter"><option value="">Todas as turmas</option></select></div>
            <div><label for="start-date">De</label><input type="date" id="start-date"></div>
            <div><label for="end-date">Até</label><input type="date" id="end-date"></div>
            <button class="button secondary" type="reset">Limpar</button>
            <a class="button secondary" id="report-link" href="{{ route('historico.relatorio') }}" data-base-url="{{ route('historico.relatorio') }}">Salvar relatório em PDF</a>
        </form>
        <p id="filter-error" role="alert" hidden>A data inicial deve ser anterior ou igual à data final.</p>
        <section class="stats" aria-label="Indicadores do período">
            <article class="card stat"><span class="stat-icon orange" aria-hidden="true">↗</span><span>Total de saídas</span><strong id="total">0</strong><small>No período selecionado</small></article>
            <article class="card stat"><span class="stat-icon blue" aria-hidden="true">♙</span><span>Alunos que saíram</span><strong id="students">0</strong><small>Alunos com ao menos uma saída</small></article>
            <article class="card stat"><span class="stat-icon purple" aria-hidden="true">≈</span><span>Média por aluno</span><strong id="average">—</strong><small>Saídas ÷ alunos que saíram</small></article>
            <article class="card stat"><span class="stat-icon green" aria-hidden="true">◷</span><span>Tempo médio fora</span><strong id="duration">—</strong><small>Somente saídas com retorno</small></article>
        </section>
        <div class="overview">
            <section class="card chart-card"><div class="section-heading"><h2>Saídas por dia</h2><span id="chart-total">0 saídas</span></div><div class="chart" id="chart"><p class="empty-chart">Nenhuma saída no período.</p></div></section>
            <section class="card outstanding"><div class="section-heading"><h2>Sem retorno registrado</h2><span class="status-dot" aria-hidden="true"></span></div><strong id="pending">0</strong><p>Saídas sem entrada correspondente no histórico.</p><div id="pending-list" class="pending-list"></div></section>
        </div>
        <section class="card student-table">
            <div class="section-heading"><h2>Controle por aluno</h2><span id="result-count" aria-live="polite">0 alunos</span></div>
            <div class="table-scroll"><table><thead><tr><th scope="col">Aluno</th><th scope="col">Turma</th><th scope="col">Saídas</th><th scope="col">Tempo médio</th><th scope="col">Última saída</th><th scope="col">Retornos pendentes</th><th scope="col"><span class="sr-only">Ações</span></th></tr></thead><tbody id="student-rows"><tr><td colspan="7"><div class="empty-state"><span aria-hidden="true">⇄</span><h3>Nenhuma movimentação encontrada</h3><p>Os alunos aparecerão aqui quando houver registros no período.</p></div></td></tr></tbody></table></div>
        </section>
    </main>
    <dialog id="student-dialog" aria-labelledby="student-title"><div class="section-heading"><h2 id="student-title">Histórico do aluno</h2><form method="dialog"><button class="close-button" aria-label="Fechar">×</button></form></div><p id="student-subtitle"></p><div class="table-scroll"><table><thead><tr><th>Saída</th><th>Retorno</th><th>Tempo fora</th><th>Observação</th></tr></thead><tbody id="student-history"></tbody></table></div></dialog>
    <script id="history-data" type="application/json" data-refresh-url="{{ route('historico.movimentacoes') }}">@json($movimentacoes)</script>
    <script src="{{ asset('js/historico.js') }}" defer></script>
</body>
</html>
