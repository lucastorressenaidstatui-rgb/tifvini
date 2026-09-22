(() => {
    // Each item represents a departure, with an optional matching return.
    const historyData = document.getElementById('history-data');
    let records = JSON.parse(historyData.textContent);
    const get = (id) => document.getElementById(id);
    const number = new Intl.NumberFormat('pt-BR', { maximumFractionDigits: 1 });
    const dateTime = (value) => value ? new Date(value).toLocaleString('pt-BR', { dateStyle: 'short', timeStyle: 'short', timeZone: 'America/Sao_Paulo' }) : '—';
    const localDate = (value) => {
        const parts = new Intl.DateTimeFormat('en-US', {
            timeZone: 'America/Sao_Paulo', year: 'numeric', month: '2-digit', day: '2-digit',
        }).formatToParts(new Date(value));
        const date = Object.fromEntries(parts.map((part) => [part.type, part.value]));
        return `${date.year}-${date.month}-${date.day}`;
    };
    const duration = (record) => {
        if (!record.retorno) return null;
        const minutes = (new Date(record.retorno) - new Date(record.saida)) / 60000;
        return Number.isFinite(minutes) && minutes >= 0 ? minutes : null;
    };
    const averageDuration = (items) => {
        const values = items.map(duration).filter((value) => value !== null);
        return values.length ? `${number.format(values.reduce((sum, value) => sum + value, 0) / values.length)} min` : '—';
    };
    const element = (tag, text, className) => {
        const node = document.createElement(tag);
        if (text !== undefined) node.textContent = text;
        if (className) node.className = className;
        return node;
    };
    function syncClasses() {
        const classes = [...new Set(records.map((item) => item.turma))].sort();
        const selected = get('class-filter').value;
        get('class-filter').replaceChildren(element('option', 'Todas as turmas'));
        classes.forEach((turma) => {
            const option = element('option', turma);
            option.value = turma;
            get('class-filter').append(option);
        });
        get('class-filter').value = classes.includes(selected) ? selected : '';
    }
    syncClasses();
    function showStudent(items) {
        get('student-title').textContent = items[0].nome;
        get('student-subtitle').textContent = `Matrícula ${items[0].matricula} · ${items.length} saída(s) no período · Média: ${averageDuration(items)}`;
        get('student-history').replaceChildren();
        items.forEach((record) => {
            const row = element('tr');
            [dateTime(record.saida), record.retorno ? dateTime(record.retorno) : 'Sem retorno', duration(record) === null ? '—' : `${number.format(duration(record))} min`, record.observacao || '—'].forEach((value) => row.append(element('td', value)));
            get('student-history').append(row);
        });
        get('student-dialog').showModal();
    }
    function render() {
        const query = get('search').value.trim().toLocaleLowerCase('pt-BR');
        const start = get('start-date').value;
        const end = get('end-date').value;
        const invalid = start && end && start > end;
        get('filter-error').hidden = !invalid;
        const reportLink = get('report-link');
        const reportUrl = new URL(reportLink.dataset.baseUrl);
        if (get('class-filter').value) reportUrl.searchParams.set('turma', get('class-filter').value);
        if (start) reportUrl.searchParams.set('inicio', start);
        if (end) reportUrl.searchParams.set('fim', end);
        if (invalid) reportLink.removeAttribute('href');
        else reportLink.href = reportUrl.toString();
        const filtered = records.filter((record) => {
            const day = localDate(record.saida);
            return !invalid && (!start || day >= start) && (!end || day <= end)
                && (!get('class-filter').value || record.turma === get('class-filter').value)
                && `${record.nome} ${record.matricula}`.toLocaleLowerCase('pt-BR').includes(query);
        }).sort((a, b) => new Date(b.saida) - new Date(a.saida));
        const groups = new Map();
        filtered.forEach((record) => {
            const key = String(record.matricula);
            if (!groups.has(key)) groups.set(key, []);
            groups.get(key).push(record);
        });
        get('total').textContent = filtered.length;
        get('students').textContent = groups.size;
        get('average').textContent = groups.size ? number.format(filtered.length / groups.size) : '—';
        get('duration').textContent = averageDuration(filtered);
        const pending = filtered.filter((record) => !record.retorno);
        get('pending').textContent = pending.length;
        get('pending-list').replaceChildren();
        pending.slice(0, 3).forEach((record) => {
            const row = element('div');
            row.append(element('strong', record.nome), element('span', record.turma));
            get('pending-list').append(row);
        });
        get('result-count').textContent = `${groups.size} aluno(s)`;
        get('student-rows').replaceChildren();
        [...groups.values()].sort((a, b) => b.length - a.length).forEach((items) => {
            const record = items[0];
            const row = element('tr');
            const name = element('td');
            name.append(element('strong', record.nome), element('small', record.matricula));
            row.append(name);
            [record.turma, items.length, averageDuration(items), dateTime(record.saida), items.filter((item) => !item.retorno).length].forEach((value) => row.append(element('td', value)));
            const action = element('td');
            const button = element('button', 'Ver histórico →', 'details');
            button.type = 'button';
            button.setAttribute('aria-label', `Ver histórico de ${record.nome}`);
            button.addEventListener('click', () => showStudent(items));
            action.append(button);
            row.append(action);
            get('student-rows').append(row);
        });
        if (!groups.size) {
            const row = element('tr');
            const cell = element('td');
            cell.colSpan = 7;
            const empty = element('div', undefined, 'empty-state');
            empty.append(element('span', '⇄'), element('h3', 'Nenhuma movimentação encontrada'), element('p', 'Os alunos aparecerão aqui quando houver registros no período.'));
            cell.append(empty); row.append(cell); get('student-rows').append(row);
        }
        const days = new Map();
        filtered.forEach((record) => { const day = localDate(record.saida); days.set(day, (days.get(day) || 0) + 1); });
        get('chart').replaceChildren();
        get('chart-total').textContent = `${filtered.length} saída(s)`;
        if (!days.size) get('chart').append(element('p', 'Nenhuma saída no período.', 'empty-chart'));
        const maximum = Math.max(1, ...days.values());
        [...days].sort(([a], [b]) => a.localeCompare(b)).forEach(([day, count]) => {
            const column = element('div', undefined, 'bar-column');
            const bar = element('div', undefined, 'bar');
            bar.style.height = `${count / maximum * 135}px`;
            column.title = `${day.split('-').reverse().join('/')}: ${count} saída(s)`;
            column.append(element('span', count), bar, element('small', day.slice(5).split('-').reverse().join('/')));
            get('chart').append(column);
        });
    }
    get('filters').addEventListener('submit', (event) => event.preventDefault());
    get('filters').addEventListener('input', render);
    get('filters').addEventListener('change', render);
    get('filters').addEventListener('reset', () => setTimeout(render, 0));
    render();
    setInterval(async () => {
        try {
            const response = await fetch(historyData.dataset.refreshUrl, { headers: { Accept: 'application/json' }, cache: 'no-store' });
            if (!response.ok) return;
            records = (await response.json()).movimentacoes;
            syncClasses();
            render();
        } catch {
            // Keep the last loaded records when the connection is unavailable.
        }
    }, 10000);
})();
