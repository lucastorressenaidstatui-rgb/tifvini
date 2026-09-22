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
