<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Registro de movimentação | SESI</title>
    <style>
        :root { color-scheme: light; --orange: #c64a0b; --ink: #292b31; --muted: #73757e; --line: #eae9e6; }
        * { box-sizing: border-box; }
        body { margin: 0; background: #f7f8fa; color: var(--ink); font-family: "Segoe UI", sans-serif; font-size: 14px; }
        button, input, textarea { font: inherit; }
        a { color: inherit; text-decoration: none; }
        button, a, input, textarea { -webkit-tap-highlight-color: transparent; }
        :focus-visible { outline: 3px solid #f5a56d; outline-offset: 4px; }
        .sidebar { width: 242px; position: fixed; inset: 0 auto 0 0; padding: 36px 24px; background: white; border-right: 1px solid var(--line); display: flex; flex-direction: column; }
        .brand { font-size: 35px; font-weight: 900; font-style: italic; letter-spacing: -2px; color: #e42230; }
        .brand-caption { margin: 5px 0 46px; color: var(--muted); font-size: 12px; }
        .nav-label, .eyebrow { text-transform: uppercase; letter-spacing: 1.8px; font-size: 10px; font-weight: 700; color: var(--muted); }
        nav { display: grid; gap: 10px; margin-top: 32px; }
        nav a { padding: 14px 12px; display: flex; align-items: center; gap: 12px; border-radius: 9px; font-weight: 600; font-size: 13px; }
        nav a:hover { background: #f8f8f8; }
        nav .active { background: #fff0e5; color: var(--orange); }
        .nav-icon { font-size: 20px; line-height: 1; }
        .sidebar-bottom { margin-top: auto; padding-top: 28px; color: var(--muted); font-size: 12px; line-height: 1.7; }
        .sidebar-bottom strong { display: block; color: var(--ink); }
        .workspace { margin-left: 242px; }
        .topbar { min-height: 82px; padding: 20px 42px; background: white; border-bottom: 1px solid var(--line); display: flex; justify-content: space-between; align-items: center; gap: 16px; }
        .topbar-title { font-weight: 600; }
        .today { color: var(--muted); font-size: 12px; text-transform: capitalize; }
        main { max-width: 1260px; margin: auto; padding: 40px 42px; }
        .breadcrumb { color: var(--muted); font-size: 12px; margin-bottom: 26px; }
        .breadcrumb span { padding: 0 10px; color: #b9bbc1; }
        .heading { display: flex; justify-content: space-between; align-items: center; gap: 20px; margin-bottom: 30px; }
        h1 { font-size: clamp(25px, 2.5vw, 34px); margin: 0 0 10px; letter-spacing: -1px; font-weight: 700; }
        .subtitle { margin: 0; color: var(--muted); line-height: 1.7; }
        .tag { white-space: nowrap; padding: 9px 13px; border: 1px solid #ebdfd2; border-radius: 30px; color: #945022; background: #fff8f0; font-size: 11px; font-weight: 600; }
        .layout { display: grid; grid-template-columns: minmax(0, 1fr) 290px; gap: 24px; align-items: start; }
        .card { border: 1px solid var(--line); border-radius: 16px; background: white; box-shadow: 0 4px 16px #19233303; overflow: hidden; }
        .card-header { display: flex; align-items: center; gap: 14px; padding: 24px 28px; border-bottom: 1px solid var(--line); }
        .header-icon { width: 42px; height: 42px; display: grid; place-items: center; background: #fff1e8; color: var(--orange); border-radius: 12px; font-size: 23px; }
        h2 { font-size: 16px; margin: 0 0 5px; }
        .small { font-size: 12px; color: var(--muted); line-height: 1.6; margin: 0; }
        .form-body { padding: 28px; }
        fieldset { border: 0; padding: 0; margin: 0; min-width: 0; }
        fieldset + fieldset { border-top: 1px solid var(--line); margin-top: 28px; padding-top: 26px; }
        legend { padding: 0; margin-bottom: 22px; font-weight: 650; font-size: 13px; }
        .step { color: var(--orange); font-size: 11px; margin-right: 9px; }
        .fields { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 20px; }
        .full { grid-column: 1 / -1; }
        label, .field-label { display: block; font-size: 12px; font-weight: 600; margin-bottom: 9px; }
        .required { color: var(--orange); }
        input:not([type=radio]), textarea { width: 100%; min-width: 0; padding: 13px 14px; border: 1px solid #dedfe3; border-radius: 8px; color: var(--ink); background: #fff; transition: border-color .15s; }
        input::placeholder, textarea::placeholder { color: #90929b; }
        input:focus, textarea:focus { border-color: var(--orange); outline: 3px solid #ffeddc; }
        textarea { resize: vertical; min-height: 100px; line-height: 1.6; }
        .types { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
        .type { cursor: pointer; margin: 0; position: relative; }
        .type input { position: absolute; top: 20px; right: 15px; accent-color: var(--orange); }
        .type-content { display: block; border: 1px solid #dedfe3; border-radius: 10px; padding: 16px; padding-right: 38px; }
        .type input:checked + .type-content { border-color: var(--orange); background: #fff7f1; box-shadow: 0 0 0 1px var(--orange); }
        .type input:focus-visible + .type-content { outline: 3px solid #f5a56d; outline-offset: 3px; }
        .type-title { display: block; font-size: 14px; margin-bottom: 5px; }
        .type-description { color: var(--muted); font-weight: 400; font-size: 11px; }
        .optional { color: var(--muted); font-weight: 400; }
        .counter { text-align: right; font-size: 11px; color: var(--muted); margin-top: 7px; }
        .form-footer { border-top: 1px solid var(--line); padding: 20px 28px; background: #fdfdfd; display: flex; justify-content: space-between; align-items: center; gap: 14px; }
        .button { cursor: pointer; border: 1px solid #dedfe3; border-radius: 8px; padding: 12px 17px; background: white; font-weight: 600; font-size: 12px; }
        .button:hover { background: #f4f4f5; }
        .primary { border-color: var(--orange); background: var(--orange); color: white; }
        .primary:hover { background: #a83c06; }
        .summary { padding: 25px; }
        .summary h2 { margin: 12px 0 20px; font-size: 18px; }
        .movement { display: flex; gap: 12px; align-items: center; background: #fff4ec; padding: 15px; border-radius: 10px; margin-bottom: 24px; }
        .movement-icon { font-size: 26px; color: var(--orange); }
        .movement strong { display: block; font-size: 13px; margin-bottom: 3px; }
        dl { margin: 0; }
        .summary-row { margin-bottom: 19px; }
        dt { font-size: 11px; color: var(--muted); margin-bottom: 6px; }
        dd { margin: 0; font-size: 13px; font-weight: 600; overflow-wrap: anywhere; white-space: pre-wrap; }
        .summary-dates { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; border-top: 1px dashed #dedfe3; padding-top: 20px; }
        .hint { padding: 22px 5px; color: var(--muted); font-size: 12px; line-height: 1.8; }
        .hint strong { color: var(--ink); display: block; margin-bottom: 5px; }
        .page-footer { color: var(--muted); font-size: 11px; margin-top: 28px; text-align: center; }
        dialog { border: 1px solid var(--line); border-radius: 16px; padding: 28px; width: min(480px, calc(100% - 32px)); color: var(--ink); box-shadow: 0 24px 80px #0002; }
        dialog::backdrop { background: #19233380; }
        dialog h2 { font-size: 22px; margin-bottom: 12px; }
        dialog dl { margin: 22px 0; display: grid; gap: 14px; }
        @media (min-width: 1100px) { .summary-column { position: sticky; top: 24px; } }
        @media (max-width: 1100px) { .sidebar { width: 205px; padding: 28px 16px; } .workspace { margin-left: 205px; } main { padding: 30px 24px; } .topbar { padding: 20px 24px; } .layout { grid-template-columns: minmax(0, 1fr); } .summary-column { display: grid; grid-template-columns: 1fr 1fr; gap: 24px; } .heading .tag { display: none; } }
        @media (max-width: 700px) { .sidebar { position: static; width: auto; padding: 14px 20px; border-right: 0; border-bottom: 1px solid var(--line); flex-direction: row; align-items: center; justify-content: space-between; gap: 12px; } .brand { font-size: 28px; } .brand-caption, .nav-label, .sidebar-bottom, .nav-icon { display: none; } nav { display: flex; gap: 4px; margin: 0; } nav a { font-size: 11px; padding: 10px; } .workspace { margin: 0; } .topbar { min-height: 60px; padding: 16px 20px; } .topbar-title { font-size: 12px; } .today { font-size: 10px; } main { padding: 26px 16px; } .breadcrumb { margin-bottom: 22px; } .heading { margin-bottom: 22px; } .subtitle { font-size: 13px; } .card-header, .form-body { padding: 22px 20px; } .summary-column { grid-template-columns: 1fr; gap: 0; } .form-footer { padding: 18px 20px; } }
        @media (max-width: 390px) { .fields { grid-template-columns: 1fr; } .type-content { padding: 14px 30px 14px 10px; } .type input { right: 9px; } .form-footer { align-items: stretch; flex-direction: column-reverse; } }
    </style>
</head>
<body>
    <aside class="sidebar">
        <div><div class="brand" aria-label="SESI">SESI</div><p class="brand-caption">Controle de frequência</p></div>
        <div>
            <nav aria-label="Navegação principal">
                <a class="active" href="{{ route('registro') }}" aria-current="page"><span class="nav-icon" aria-hidden="true">⇄</span> Movimentações</a>
                <a href="{{ route('meu-historico') }}"><span class="nav-icon" aria-hidden="true">▥</span> Meu histórico</a>
                <a href="{{ url('/cadastro_usuario') }}"><span class="nav-icon" aria-hidden="true">＋</span> Cadastrar usuário</a>
            </nav>
        </div>
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
                                <div class="full"><label for="nome">Nome completo <span class="required">*</span></label><input id="nome" name="nome" placeholder="Digite o nome completo do aluno" autocomplete="name" maxlength="255" required></div>
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
                    <p id="save-success" role="status" style="color:#18794e;padding:0 28px" hidden></p>
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
        <p id="save-error" role="alert" style="color:#b42318" hidden></p>
        <div style="display:flex;gap:12px;justify-content:flex-end;margin-top:24px">
            <form method="dialog"><button class="button">Voltar ao formulário</button></form>
            <button type="button" id="save-movement" class="button primary">Confirmar registro</button>
        </div>
    </dialog>
    <script>
        const form = document.getElementById('movement-form');
        const field = (name) => form.elements.namedItem(name);
        const setText = (id, text) => { document.getElementById(id).textContent = text; };
        const formatDate = (value) => value ? value.split('-').reverse().join('/') : '—';

        function fillDateTime() {
            const now = new Date();
            const pad = (value) => String(value).padStart(2, '0');
            field('data').value = `${now.getFullYear()}-${pad(now.getMonth() + 1)}-${pad(now.getDate())}`;
            field('horario').value = `${pad(now.getHours())}:${pad(now.getMinutes())}`;
        }

        function updateSummary() {
            setText('summary-name', field('nome').value.trim() || 'Não informado');
            setText('summary-enrollment', field('matricula').value.trim() || 'Não informada');
            setText('summary-class', field('turma').value.trim() || 'Não informada');
            setText('summary-type', field('tipo').value);
            setText('summary-icon', field('tipo').value === 'Entrada' ? '↘' : '↗');
            setText('summary-date', formatDate(field('data').value));
            setText('summary-time', field('horario').value || '—');
            setText('counter', `${field('observacao').value.length} / 500 caracteres`);
        }

        form.addEventListener('input', updateSummary);
        form.addEventListener('change', updateSummary);
        form.addEventListener('reset', () => setTimeout(() => { fillDateTime(); updateSummary(); }, 0));
        form.addEventListener('submit', (event) => {
            event.preventDefault();
            document.getElementById('save-error').hidden = true;
            document.getElementById('save-success').hidden = true;
            const details = document.getElementById('review-details');
            details.replaceChildren();
            const entries = [
                ['Aluno', field('nome').value], ['Matrícula', field('matricula').value],
                ['Turma', field('turma').value], ['Tipo', field('tipo').value],
                ['Data e horário', `${formatDate(field('data').value)} às ${field('horario').value}`],
                ['Observação', field('observacao').value.trim() || 'Sem observação'],
            ];
            entries.forEach(([label, value]) => {
                const row = document.createElement('div');
                const term = document.createElement('dt');
                const description = document.createElement('dd');
                term.textContent = label;
                description.textContent = value;
                row.append(term, description);
                details.append(row);
            });
            document.getElementById('review').showModal();
        });
        document.getElementById('save-movement').addEventListener('click', async () => {
            const button = document.getElementById('save-movement');
            const error = document.getElementById('save-error');
            button.disabled = true;
            error.hidden = true;

            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        Accept: 'application/json',
                    },
                    body: new FormData(form),
                });
                const result = await response.json();

                if (!response.ok) {
                    error.textContent = result.mensagem || (result.errors ? Object.values(result.errors).flat().join(' ') : 'Não foi possível salvar o registro.');
                    error.hidden = false;
                    return;
                }

                document.getElementById('review').close();
                form.reset();
                const success = document.getElementById('save-success');
                success.textContent = result.mensagem;
                success.hidden = false;
            } catch {
                error.textContent = 'Não foi possível conectar ao servidor. Tente novamente.';
                error.hidden = false;
            } finally {
                button.disabled = false;
            }
        });
        fillDateTime();
        updateSummary();
    </script>
</body>
</html>
