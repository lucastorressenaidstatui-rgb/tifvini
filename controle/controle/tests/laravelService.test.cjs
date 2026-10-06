const test = require('node:test');
const assert = require('node:assert/strict');

const { buildUserPayload, buildAttendancePayload } = require('../src/services/laravelService');

test('buildUserPayload maps form data into Laravel-friendly keys', () => {
  const payload = buildUserPayload({
    nome: 'Ana Clara',
    email: 'ana@email.com',
    senha: '123456',
    dataNascimento: '10/05/2005',
    cpf: '12345678909',
  });

  assert.deepEqual(payload, {
    nome: 'Ana Clara',
    email: 'ana@email.com',
    senha: '123456',
    data_nascimento: '10/05/2005',
    cpf: '12345678909',
  });
});

test('buildAttendancePayload maps attendance data into Laravel-friendly keys', () => {
  const payload = buildAttendancePayload({
    nome: 'Ana Clara',
    matricula: '202600123',
    turma: '1º Ano A',
    tipoRegistro: 'Entrada',
    dataRegistro: '15/09/2026',
    horaRegistro: '08:30',
    observacao: 'Chegou cedo',
  });

  assert.deepEqual(payload, {
    nome: 'Ana Clara',
    matricula: '202600123',
    turma: '1º Ano A',
    tipo_registro: 'Entrada',
    data_registro: '15/09/2026',
    hora_registro: '08:30',
    observacao: 'Chegou cedo',
  });
});
