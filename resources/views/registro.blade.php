<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Registro de movimentação | Controle de frequência</title>
    <link rel="stylesheet" href="{{ asset('css/registro.css') }}">
    <link rel="stylesheet" href="{{ asset('css/auth-theme.css') }}">
</head>
<body>
    <aside class="sidebar">
        <p class="sidebar-title">Controle de frequência</p>
        <div>
            <nav aria-label="Navegação principal">
                <a class="active" href="{{ route('registro') }}" aria-current="page"><span class="nav-icon" aria-hidden="true">⇄</span> Movimentações</a>
                <a href="{{ route('meu-historico') }}"><span class="nav-icon" aria-hidden="true">▥</span> Meu histórico</a>
                <a href="{{ url('/cadastro_usuario') }}"><span class="nav-icon" aria-hidden="true">＋</span> Cadastrar usuário</a>
            </nav>
        </div>
        <form class="sidebar-logout" action="{{ route('logout') }}" method="POST">@csrf<button type="submit">Sair da conta</button></form>
    </aside>
    <div class="workspace">
        <main>
            <div class="heading"><div><h1>Registro de movimentação</h1></div></div>
            <div class="layout">
                <form class="card" id="movement-form" action="{{ route('registro.store') }}" method="POST">
                    <div class="form-body">
                        <fieldset>
                            <legend>Identificação do aluno</legend>
                            <div class="fields">
                                <div class="full"><label for="nome">Nome completo</label><input id="nome" name="nome" value="{{ $usuario->nome }}" autocomplete="name" readonly></div>
                                <div><label for="matricula">Matrícula <span class="required">*</span></label><input id="matricula" name="matricula" placeholder="Ex.: 20260001" maxlength="50" required></div>
                                <div><label for="turma">Turma <span class="required">*</span></label><input id="turma" name="turma" placeholder="Ex.: 2º ano A" maxlength="100" required></div>
                            </div>
                        </fieldset>
                        <fieldset>
                            <legend>Detalhes da movimentação</legend>
                            <div class="fields">
                                <div class="full"><span class="field-label" id="type-label">Tipo de registro <span class="required">*</span></span><div class="types" role="radiogroup" aria-labelledby="type-label">
                                    <label class="type"><input type="radio" name="tipo" value="Entrada" required><span class="type-content"><span class="type-title">↘ &nbsp; Entrada</span></span></label>
                                    <label class="type"><input type="radio" name="tipo" value="Saída" checked><span class="type-content"><span class="type-title">↗ &nbsp; Saída</span></span></label>
                                </div></div>
                                <div><label for="data">Data <span class="required">*</span></label><input type="date" id="data" name="data" required></div>
                                <div><label for="horario">Horário <span class="required">*</span></label><input type="time" id="horario" name="horario" required></div>
                                <div class="full"><label for="observacao">Observação <span class="optional">(opcional)</span></label><textarea id="observacao" name="observacao" maxlength="500" placeholder="Adicione informações relevantes, como atraso ou saída antecipada." aria-describedby="counter"></textarea><div class="counter" id="counter">0 / 500 caracteres</div></div>
                            </div>
                        </fieldset>
                    </div>
                    <p id="save-success" role="status" hidden></p>
                    <div class="form-footer"><button type="reset" class="button">Limpar campos</button><button type="submit" class="button primary">Revisar movimentação &nbsp; →</button></div>
                </form>
                <aside class="summary-column">
                    <section class="card summary" aria-labelledby="summary-title">
                        <h2 id="summary-title">Resumo do registro</h2>
                        <div class="movement"><span class="movement-icon" id="summary-icon" aria-hidden="true">↘</span><div><strong id="summary-type">Entrada</strong><p class="small">Movimentação do aluno</p></div></div>
                        <dl><div class="summary-row"><dt>Aluno</dt><dd id="summary-name">Não informado</dd></div><div class="summary-row"><dt>Matrícula</dt><dd id="summary-enrollment">Não informada</dd></div><div class="summary-row"><dt>Turma</dt><dd id="summary-class">Não informada</dd></div></dl>
                        <dl class="summary-dates"><div><dt>Data</dt><dd id="summary-date">—</dd></div><div><dt>Horário</dt><dd id="summary-time">—</dd></div></dl>
                    </section>
                </aside>
            </div>
        </main>
    </div>
    <dialog id="review" aria-labelledby="review-title" aria-describedby="review-description">
        <h2 id="review-title">Confira a movimentação</h2>
        <p class="small" id="review-description">Esta é uma prévia. O registro ainda não foi salvo.</p>
        <dl id="review-details"></dl>
        <p id="save-error" role="alert" hidden></p>
        <div class="review-actions">
            <form method="dialog"><button class="button">Voltar ao formulário</button></form>
            <button type="button" id="save-movement" class="button primary">Confirmar registro</button>
        </div>
    </dialog>
    <script src="{{ asset('js/registro.js') }}"></script>
</body>
</html>
