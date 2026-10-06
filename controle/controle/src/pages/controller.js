import { StatusBar } from 'expo-status-bar';
import { useMemo, useState } from 'react';
import { Pressable, SafeAreaView, ScrollView, StyleSheet, Text, TextInput, View } from 'react-native';
import AlertBanner from '../components/alert_banner';
import AppHeader from '../components/app_header';
import { saveAttendanceToLaravel, buildAttendancePayload } from '../services/laravelService';

const turmaOptions = ['Selecione a turma', '1º Ano A', '1º Ano B', '2º Ano A', '2º Ano B', '3º Ano A', '3º Ano B'];
const tipoOptions = ['Entrada', 'Saída'];

const Field = ({ label, children, compact = false }) => (
  <View style={[styles.field, compact && styles.fieldCompact]}>
    <Text style={styles.label}>{label}</Text>
    {children}
  </View>
);

const SelectField = ({ label, value, options, onChange, compact = false }) => {
  const index = options.indexOf(value);
  const next = () => {
    const nextIndex = (index + 1) % options.length;
    onChange(options[nextIndex]);
  };

  return (
    <Field label={label} compact={compact}>
      <Pressable onPress={next} style={styles.select}>
        <Text style={styles.selectText}>{value}</Text>
        <Text style={styles.caret}>▾</Text>
      </Pressable>
    </Field>
  );
};

export default function Controller({ onMenu }) {
  const [nome, setNome] = useState('');
  const [matricula, setMatricula] = useState('');
  const [turma, setTurma] = useState('Selecione a turma');
  const [tipoRegistro, setTipoRegistro] = useState('Entrada');
  const [dataRegistro, setDataRegistro] = useState('');
  const [horaRegistro, setHoraRegistro] = useState('');
  const [observacao, setObservacao] = useState('');
  const [registros, setRegistros] = useState([]);
  const [banner, setBanner] = useState({ visible: false, type: 'success', title: '', message: '' });

  const showBanner = (type, title, message) => setBanner({ visible: true, type, title, message });

  const stats = useMemo(() => {
    const entradas = registros.filter((item) => item.tipoRegistro === 'Entrada').length;
    const saidas = registros.filter((item) => item.tipoRegistro === 'Saída').length;

    return {
      total: registros.length,
      entradas,
      saidas,
    };
  }, [registros]);

  const handleSubmit = async () => {
    if (!nome.trim() || !matricula.trim() || turma === 'Selecione a turma' || !dataRegistro || !horaRegistro) {
      showBanner('error', 'Dados incompletos', 'Preencha nome, matrícula, turma, data e horário para registrar a frequência.');
      return;
    }

    const novoRegistro = {
      id: String(Date.now()),
      nome: nome.trim(),
      matricula: matricula.trim(),
      turma,
      tipoRegistro,
      dataRegistro,
      horaRegistro,
      observacao: observacao.trim(),
    };

    try {
      const payload = buildAttendancePayload(novoRegistro);
      const response = await saveAttendanceToLaravel(payload);
      const mensagem = response?.mensagem || response?.message || 'Registro de frequência salvo com sucesso.';
      const sucesso = response?.erro === 'n' || response?.error === 'n' || response?.success === true || response?.ok === true;

      showBanner(sucesso ? 'success' : 'error', sucesso ? 'Registro salvo' : 'Registro não concluído', mensagem);

      if (sucesso) {
        setRegistros((atual) => [novoRegistro, ...atual]);
        setNome('');
        setMatricula('');
        setTurma('Selecione a turma');
        setTipoRegistro('Entrada');
        setDataRegistro('');
        setHoraRegistro('');
        setObservacao('');
      }
    } catch (error) {
      showBanner('error', 'Registro não concluído', error.message || 'Não foi possível salvar a frequência no Laravel.');
    }
  };

  return (
    <SafeAreaView style={styles.safe}>
      <StatusBar style="dark" />
      <AppHeader title="Controle de frequência" onMenu={onMenu} />
      <AlertBanner
        visible={banner.visible}
        type={banner.type}
        title={banner.title}
        message={banner.message}
        onClose={() => setBanner((atual) => ({ ...atual, visible: false }))}
      />

      <ScrollView contentContainerStyle={styles.content} showsVerticalScrollIndicator={false}>
        <Text style={styles.eyebrow}>Sistema escolar</Text>
        <Text style={styles.title}>Controle de Frequência</Text>
        <Text style={styles.description}>Registre a entrada e a saída dos alunos de forma rápida e organizada.</Text>

        <View style={styles.topPanel}>
          <View style={styles.cards}>
            <View style={styles.card}>
              <Text style={styles.cardLabel}>Registros hoje</Text>
              <Text style={styles.cardValue}>{stats.total}</Text>
            </View>
            <View style={styles.card}>
              <Text style={styles.cardLabel}>Entradas</Text>
              <Text style={styles.cardValue}>{stats.entradas}</Text>
            </View>
            <View style={styles.card}>
              <Text style={styles.cardLabel}>Saídas</Text>
              <Text style={styles.cardValue}>{stats.saidas}</Text>
            </View>
          </View>
        </View>

        <View style={styles.formSection}>
          <Text style={styles.sectionTitle}>Registrar movimentação</Text>
          <Text style={styles.sectionSubtitle}>Preencha os dados do aluno e confirme o registro.</Text>

          <View style={styles.formGrid}>
            <View style={styles.inputRow}>
              <Field label="Nome completo do aluno">
                <TextInput
                  value={nome}
                  onChangeText={setNome}
                  placeholder="Ex.: Ana Clara Silva"
                  autoCapitalize="words"
                  placeholderTextColor="#8190A8"
                  style={styles.input}
                />
              </Field>
            </View>

            <View style={styles.inputRowSplit}>
              <Field label="Matrícula">
                <TextInput
                  value={matricula}
                  onChangeText={setMatricula}
                  placeholder="Ex.: 202600123"
                  keyboardType="number-pad"
                  placeholderTextColor="#8190A8"
                  style={styles.input}
                />
              </Field>

              <SelectField label="Turma" value={turma} options={turmaOptions} onChange={setTurma} />
            </View>

            <View style={styles.inputRowSplit}>
              <SelectField label="Tipo de registro" value={tipoRegistro} options={tipoOptions} onChange={setTipoRegistro} />

              <Field label="Data">
                <TextInput
                  value={dataRegistro}
                  onChangeText={setDataRegistro}
                  placeholder="dd/mm/aaaa"
                  keyboardType="numeric"
                  placeholderTextColor="#8190A8"
                  style={styles.input}
                />
              </Field>
            </View>

            <View style={styles.inputRowSplit}>
              <Field label="Horário">
                <TextInput
                  value={horaRegistro}
                  onChangeText={setHoraRegistro}
                  placeholder="--:--"
                  keyboardType="numeric"
                  placeholderTextColor="#8190A8"
                  style={styles.input}
                />
              </Field>

              <View style={styles.spacer} />
            </View>

            <Field label="Observação (opcional)">
              <TextInput
                value={observacao}
                onChangeText={setObservacao}
                placeholder="Ex.: aluno liberado mais cedo pela coordenação."
                multiline
                placeholderTextColor="#8190A8"
                style={[styles.input, styles.textarea]}
                textAlignVertical="top"
              />
            </Field>
          </View>

          <View style={styles.actions}>
            <Pressable onPress={handleSubmit} style={styles.submitButton}>
              <Text style={styles.submitText}>Registrar frequência</Text>
            </Pressable>
          </View>
        </View>
      </ScrollView>
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  safe: { backgroundColor: '#F3F6FB', flex: 1 },
  content: { paddingHorizontal: 22, paddingTop: 20, paddingBottom: 42 },
  eyebrow: { color: '#1AA7A1', fontSize: 14, fontWeight: '700', letterSpacing: 1.2 },
  title: { color: '#1D2B32', fontSize: 34, fontWeight: '800', marginTop: 8 },
  description: { color: '#5F6F75', fontSize: 16, lineHeight: 24, marginTop: 8 },
  topPanel: { marginTop: 24 },
  cards: { flexDirection: 'row', gap: 12 },
  card: { backgroundColor: '#FFFFFF', borderColor: '#DDE5F0', borderRadius: 14, borderWidth: 1, flex: 1, minHeight: 96, paddingHorizontal: 18, paddingVertical: 16, justifyContent: 'center' },
  cardLabel: { color: '#4B5563', fontSize: 12, fontWeight: '700', letterSpacing: 0.8, marginBottom: 8, textTransform: 'uppercase' },
  cardValue: { color: '#1D2B32', fontSize: 28, fontWeight: '800' },
  formSection: { backgroundColor: '#FFFFFF', borderColor: '#E7ECF3', borderRadius: 20, borderWidth: 1, marginTop: 24, paddingHorizontal: 18, paddingVertical: 20 },
  sectionTitle: { color: '#1F2F36', fontSize: 24, fontWeight: '800' },
  sectionSubtitle: { color: '#66777D', fontSize: 15, lineHeight: 22, marginTop: 6 },
  formGrid: { marginTop: 18, gap: 14 },
  inputRow: { width: '100%' },
  inputRowSplit: { flexDirection: 'row', gap: 12 },
  spacer: { flex: 1 },
  field: { flex: 1 },
  fieldCompact: { flex: 1 },
  label: { color: '#1F2937', fontSize: 14, fontWeight: '700', marginBottom: 8 },
  input: { backgroundColor: '#F9FBFF', borderColor: '#D9DEE7', borderRadius: 12, borderWidth: 1, color: '#20242A', fontSize: 16, minHeight: 50, paddingHorizontal: 14, paddingVertical: 12 },
  select: { alignItems: 'center', backgroundColor: '#F9FBFF', borderColor: '#D9DEE7', borderRadius: 12, borderWidth: 1, flex: 1, flexDirection: 'row', justifyContent: 'space-between', minHeight: 50, paddingHorizontal: 14 },
  selectText: { color: '#20242A', fontSize: 16 },
  caret: { color: '#303743', fontSize: 18 },
  textarea: { minHeight: 92, paddingTop: 12 },
  actions: { alignItems: 'flex-end', marginTop: 20 },
  submitButton: { alignItems: 'center', backgroundColor: '#1AA7A1', borderRadius: 12, justifyContent: 'center', minHeight: 50, paddingHorizontal: 22 },
  submitText: { color: '#FFFFFF', fontSize: 16, fontWeight: '700' },
});
