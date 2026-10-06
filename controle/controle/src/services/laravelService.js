function getDefaultBase() {
  if (typeof process !== 'undefined' && process.env && process.env.EXPO_PUBLIC_LARAVEL_API_BASE) {
    return String(process.env.EXPO_PUBLIC_LARAVEL_API_BASE).replace(/\/$/, '');
  }

  return 'http://10.0.2.2:8000/api';
}

const DEFAULT_BASE = getDefaultBase();

function getLaravelBase() {
  if (typeof process !== 'undefined' && process.env && process.env.EXPO_PUBLIC_LARAVEL_API_BASE) {
    return String(process.env.EXPO_PUBLIC_LARAVEL_API_BASE).replace(/\/$/, '');
  }

  return DEFAULT_BASE;
}

function buildUserPayload(data) {
  return {
    nome: data.nome,
    email: data.email,
    senha: data.senha,
    data_nascimento: data.dataNascimento,
    cpf: data.cpf,
  };
}

function buildAttendancePayload(data) {
  return {
    nome: data.nome,
    matricula: data.matricula,
    turma: data.turma,
    tipo_registro: data.tipoRegistro,
    data_registro: data.dataRegistro,
    hora_registro: data.horaRegistro,
    observacao: data.observacao || '',
  };
}

async function postJson(url, payload) {
  const response = await fetch(url, {
    method: 'POST',
    mode: 'cors',
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
    },
    body: JSON.stringify(payload),
  });

  const text = await response.text();
  let body = null;

  try {
    body = text ? JSON.parse(text) : null;
  } catch (err) {
    body = text;
  }

  const LaravelErrorMessage = (value) => {
    if (!value || typeof value !== 'object') return value || `Erro ao salvar no Laravel (${response.status})`;
    if (value.mensagem) return value.mensagem;
    if (value.message) return value.message;
    if (value.error) return value.error;
    if (value.erro) return value.erro;
    return `Erro ao salvar no Laravel (${response.status})`;
  };

  if (body && typeof body === 'object') {
    if (body.erro === 's' || body.error === 's' || body.status === 'error') {
      throw new Error(LaravelErrorMessage(body));
    }
    if (body.erro === 'n' || body.success === true || body.ok === true) {
      return body;
    }
  }

  if (!response.ok) {
    throw new Error(LaravelErrorMessage(body));
  }

  return body || { ok: true };
}

async function saveWithFallback(endpoints, payload) {
  const errors = [];

  for (const endpoint of endpoints) {
    try {
      return await postJson(endpoint, payload);
    } catch (error) {
      errors.push(`${endpoint}: ${error.message}`);
    }
  }

  throw new Error(errors.join(' | ') || 'Não foi possível salvar no Laravel.');
}

async function saveUserToLaravel(payload, endpointsOverride) {
  const base = getLaravelBase();
  const normalized = payload && payload.nome ? payload : buildUserPayload(payload);
  const endpoints = endpointsOverride || [`${base}/cadastro_usuario`];

  return saveWithFallback(endpoints, normalized);
}

async function saveAttendanceToLaravel(payload, endpointsOverride) {
  const base = getLaravelBase();
  const normalized = payload && payload.tipo_registro ? payload : buildAttendancePayload(payload);
  const endpoints = endpointsOverride || [`${base}/frequencias`];

  return saveWithFallback(endpoints, normalized);
}

module.exports = {
  DEFAULT_BASE,
  getLaravelBase,
  buildUserPayload,
  buildAttendancePayload,
  saveUserToLaravel,
  saveAttendanceToLaravel,
};
