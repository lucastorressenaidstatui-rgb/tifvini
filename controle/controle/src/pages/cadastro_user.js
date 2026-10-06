import { StatusBar } from 'expo-status-bar';
import { useState } from 'react';
import { Alert, KeyboardAvoidingView, Platform, Pressable, SafeAreaView, ScrollView, StyleSheet, Text, TextInput, View } from 'react-native';
import AlertBanner from '../components/alert_banner';
import AppHeader from '../components/app_header';
import { saveUserToLaravel, buildUserPayload } from '../services/laravelService';

const Field = ({ label, children }) => (
  <View style={styles.field}>
    <Text style={styles.label}>{label}</Text>
    {children}
  </View>
);

const Input = (props) => <TextInput placeholderTextColor="#758197" style={styles.input} {...props} />;

export default function CadastroUser({ onBack, onMenu }) {
  const [nome, setNome] = useState('');
  const [email, setEmail] = useState('');
  const [senha, setSenha] = useState('');
  const [dataNascimento, setDataNascimento] = useState('');
  const [cpf, setCpf] = useState('');
  const [banner, setBanner] = useState({ visible: false, type: 'success', title: '', message: '' });

  const showBanner = (type, title, message) => {
    setBanner({ visible: true, type, title, message });
    Alert.alert(title, message);
  };

  const submit = async () => {
    if (!nome.trim() || !email.trim() || !senha.trim() || !dataNascimento.trim() || !cpf.trim()) {
      showBanner('error', 'Dados incompletos', 'Preencha todos os campos para concluir o cadastro.');
      return;
    }

    const emailValido = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.trim());
    if (!emailValido) {
      showBanner('error', 'E-mail inválido', 'Informe um e-mail válido para continuar.');
      return;
    }

    if (senha.trim().length < 6) {
      showBanner('error', 'Senha inválida', 'A senha deve ter pelo menos 6 caracteres.');
      return;
    }

    if (cpf.replace(/\D/g, '').length !== 11) {
      showBanner('error', 'CPF inválido', 'Informe um CPF com 11 dígitos.');
      return;
    }

    try {
      const payload = buildUserPayload({ nome, email, senha, dataNascimento, cpf });
      const response = await saveUserToLaravel(payload);
      const mensagem = response?.mensagem || response?.message || 'Usuário cadastrado com sucesso.';
      const sucesso = response?.erro === 'n' || response?.error === 'n' || response?.success === true || response?.ok === true;

      showBanner(sucesso ? 'success' : 'error', sucesso ? 'Cadastro realizado' : 'Cadastro não concluído', mensagem);

      if (sucesso) {
        setNome('');
        setEmail('');
        setSenha('');
        setDataNascimento('');
        setCpf('');
      }
    } catch (error) {
      showBanner('error', 'Cadastro não concluído', error.message || 'Não foi possível salvar o cadastro no Laravel.');
    }
  };

  return (
    <SafeAreaView style={styles.safe}>
      <StatusBar style="dark" />
      <AppHeader title="Cadastro de usuário" onMenu={onMenu} />
      <AlertBanner
        visible={banner.visible}
        type={banner.type}
        title={banner.title}
        message={banner.message}
        onClose={() => setBanner((atual) => ({ ...atual, visible: false }))}
      />
      <KeyboardAvoidingView behavior={Platform.OS === 'ios' ? 'padding' : undefined} style={styles.keyboard}>
        <ScrollView contentContainerStyle={styles.content} keyboardShouldPersistTaps="handled">
          <Pressable onPress={onBack} style={styles.back}>
            <Text style={styles.backText}>Voltar</Text>
          </Pressable>

          <View style={styles.headerCard}>
            <Text style={styles.eyebrow}>Cadastro</Text>
            <Text style={styles.title}>Crie sua conta</Text>
            <Text style={styles.description}>Cadastre o usuário para acessar o painel administrativo.</Text>
          </View>

          <View style={styles.box}>
            <View style={styles.form}>
              <Field label="Nome completo">
                <Input
                  value={nome}
                  onChangeText={setNome}
                  autoCapitalize="words"
                  placeholder="Digite seu nome completo"
                  autoComplete="name"
                />
              </Field>

              <Field label="E-mail">
                <Input
                  value={email}
                  onChangeText={setEmail}
                  autoCapitalize="none"
                  keyboardType="email-address"
                  placeholder="Digite seu e-mail"
                  autoComplete="email"
                />
              </Field>

              <Field label="Senha">
                <Input
                  value={senha}
                  onChangeText={setSenha}
                  secureTextEntry
                  placeholder="Crie uma senha"
                  autoComplete="new-password"
                />
              </Field>

              <View style={styles.row}>
                <View style={styles.half}>
                  <Field label="Data de nascimento">
                    <Input
                      value={dataNascimento}
                      onChangeText={setDataNascimento}
                      placeholder="dd/mm/aaaa"
                      keyboardType="number-pad"
                    />
                  </Field>
                </View>

                <View style={styles.half}>
                  <Field label="CPF">
                    <Input
                      value={cpf}
                      onChangeText={setCpf}
                      placeholder="Digite seu CPF"
                      keyboardType="number-pad"
                    />
                  </Field>
                </View>
              </View>

              <View style={styles.actionRow}>
                <Pressable onPress={submit} style={styles.submit}>
                  <Text style={styles.submitText}>Cadastrar</Text>
                </Pressable>

                <Pressable onPress={onBack} style={styles.secondaryButton}>
                  <Text style={styles.secondaryText}>Ir para frequência</Text>
                </Pressable>
              </View>
            </View>
          </View>
        </ScrollView>
      </KeyboardAvoidingView>
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  safe: { backgroundColor: '#F3F6FB', flex: 1 },
  keyboard: { flex: 1 },
  content: { paddingHorizontal: 18, paddingTop: 18, paddingBottom: 38 },
  back: { alignSelf: 'flex-start', marginBottom: 14 },
  backText: { color: '#1AA7A1', fontSize: 15, fontWeight: '700' },
  headerCard: {
    backgroundColor: '#FFFFFF',
    borderColor: '#E3EAF5',
    borderRadius: 18,
    borderWidth: 1,
    marginBottom: 18,
    paddingHorizontal: 20,
    paddingVertical: 18,
  },
  eyebrow: { color: '#1AA7A1', fontSize: 12, fontWeight: '800', letterSpacing: 1.3, textTransform: 'uppercase' },
  title: { color: '#1D2B32', fontSize: 30, fontWeight: '800', marginTop: 8 },
  description: { color: '#5F6F75', fontSize: 15, lineHeight: 22, marginTop: 8 },
  box: {
    backgroundColor: '#FFFFFF',
    borderColor: '#E7ECF3',
    borderRadius: 22,
    borderWidth: 1,
    paddingHorizontal: 22,
    paddingVertical: 22,
    shadowColor: '#0F172A',
    shadowOffset: { width: 0, height: 8 },
    shadowOpacity: 0.08,
    shadowRadius: 18,
    elevation: 6,
  },
  form: { marginTop: 6 },
  field: { marginBottom: 18 },
  label: { color: '#1F2937', fontSize: 14, fontWeight: '700', marginBottom: 8 },
  input: {
    backgroundColor: '#F9FBFF',
    borderColor: '#D9DEE7',
    borderRadius: 12,
    borderWidth: 1,
    color: '#20242A',
    fontSize: 16,
    minHeight: 50,
    paddingHorizontal: 14,
    paddingVertical: 12,
  },
  row: { flexDirection: 'row', marginHorizontal: -6 },
  half: { flex: 1, paddingHorizontal: 6 },
  actionRow: { marginTop: 8 },
  submit: {
    alignItems: 'center',
    backgroundColor: '#1AA7A1',
    borderRadius: 12,
    justifyContent: 'center',
    minHeight: 52,
    marginBottom: 12,
  },
  submitText: { color: '#FFFFFF', fontSize: 16, fontWeight: '700' },
  secondaryButton: {
    alignItems: 'center',
    borderColor: '#D9DEE7',
    borderRadius: 12,
    borderWidth: 1,
    justifyContent: 'center',
    minHeight: 52,
    backgroundColor: '#F8FAFC',
  },
  secondaryText: { color: '#1F2937', fontSize: 16, fontWeight: '600' },
});
