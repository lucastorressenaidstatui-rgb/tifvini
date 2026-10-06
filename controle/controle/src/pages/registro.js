import { StatusBar } from 'expo-status-bar';
import { useState } from 'react';
import { Pressable, SafeAreaView, ScrollView, StyleSheet, Text, View } from 'react-native';
import AppHeader from '../components/app_header';

const columns = ['Aluno', 'Matrícula', 'Turma', 'Movimentação', 'Data e horário', 'Observação'];

export default function Registro({ onMenu }) {
  const [registros, setRegistros] = useState([]);

  const stats = {
    total: registros.length,
    entradas: registros.filter((item) => item?.tipo === 'entrada').length,
    saidas: registros.filter((item) => item?.tipo === 'saida').length,
  };

  return (
    <SafeAreaView style={styles.safeArea}>
      <StatusBar style="dark" />
      <AppHeader title="Registros do dia" onMenu={onMenu} />
      <ScrollView contentContainerStyle={styles.content} showsVerticalScrollIndicator={false}>
        <View style={styles.heroCard}>
          <Text style={styles.eyebrow}>Histórico</Text>
          <Text style={styles.title}>Registros do dia</Text>
          <Text style={styles.description}>Movimentações registradas nesta sessão e no acompanhamento diário.</Text>
        </View>

        <View style={styles.statsRow}>
          <View style={styles.statCard}>
            <Text style={styles.statLabel}>Total</Text>
            <Text style={styles.statValue}>{stats.total}</Text>
          </View>
          <View style={styles.statCard}>
            <Text style={styles.statLabel}>Entradas</Text>
            <Text style={styles.statValue}>{stats.entradas}</Text>
          </View>
          <View style={styles.statCard}>
            <Text style={styles.statLabel}>Saídas</Text>
            <Text style={styles.statValue}>{stats.saidas}</Text>
          </View>
        </View>

        <View style={styles.tableCard}>
          <View style={styles.tableHeader}>
            <Text style={styles.tableTitle}>Movimentações</Text>
            <Pressable onPress={() => setRegistros([])} style={({ pressed }) => [styles.clearButton, pressed && styles.pressed]}>
              <Text style={styles.clearButtonText}>Limpar</Text>
            </Pressable>
          </View>

          <ScrollView horizontal showsHorizontalScrollIndicator={false} style={styles.tableScroll}>
            <View style={styles.table}>
              <View style={styles.headerRow}>
                {columns.map((column) => (
                  <Text key={column} style={styles.headerCell}>{column}</Text>
                ))}
              </View>

              {registros.length === 0 ? (
                <View style={styles.emptyState}>
                  <Text style={styles.emptyIcon}>📋</Text>
                  <Text style={styles.emptyTitle}>Nenhum registro realizado ainda.</Text>
                  <Text style={styles.emptyText}>As entradas e saídas aparecem aqui assim que forem cadastradas.</Text>
                </View>
              ) : null}
            </View>
          </ScrollView>
        </View>
      </ScrollView>
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  safeArea: { backgroundColor: '#F3F6FB', flex: 1 },
  content: { paddingBottom: 40, paddingHorizontal: 22, paddingTop: 20 },
  heroCard: {
    backgroundColor: '#FFFFFF',
    borderColor: '#E5EAF4',
    borderRadius: 18,
    borderWidth: 1,
    paddingHorizontal: 20,
    paddingVertical: 18,
  },
  eyebrow: { color: '#1AA7A1', fontSize: 12, fontWeight: '800', letterSpacing: 1.3, textTransform: 'uppercase' },
  title: { color: '#1D2B32', fontSize: 32, fontWeight: '800', marginTop: 8 },
  description: { color: '#5F6F75', fontSize: 15, lineHeight: 22, marginTop: 8 },
  statsRow: { flexDirection: 'row', gap: 12, marginTop: 20 },
  statCard: {
    backgroundColor: '#FFFFFF',
    borderColor: '#E5EAF4',
    borderRadius: 14,
    borderWidth: 1,
    flex: 1,
    paddingHorizontal: 16,
    paddingVertical: 16,
  },
  statLabel: { color: '#6B7280', fontSize: 12, fontWeight: '700', letterSpacing: 0.8, textTransform: 'uppercase' },
  statValue: { color: '#1D2B32', fontSize: 28, fontWeight: '800', marginTop: 8 },
  tableCard: {
    backgroundColor: '#FFFFFF',
    borderColor: '#E5EAF4',
    borderRadius: 20,
    borderWidth: 1,
    marginTop: 20,
    paddingHorizontal: 16,
    paddingVertical: 16,
  },
  tableHeader: { alignItems: 'center', flexDirection: 'row', justifyContent: 'space-between', marginBottom: 12 },
  tableTitle: { color: '#1F2F36', fontSize: 22, fontWeight: '800' },
  clearButton: {
    alignItems: 'center',
    backgroundColor: '#F5F8FF',
    borderColor: '#DDE7F5',
    borderRadius: 10,
    borderWidth: 1,
    justifyContent: 'center',
    minHeight: 38,
    paddingHorizontal: 12,
  },
  clearButtonText: { color: '#146CFF', fontSize: 14, fontWeight: '700' },
  tableScroll: { marginTop: 6 },
  table: { minWidth: 790 },
  headerRow: { backgroundColor: '#F5F7FB', borderBottomColor: '#D9DEE7', borderBottomWidth: 1, borderRadius: 12, flexDirection: 'row', paddingBottom: 12, paddingHorizontal: 10, paddingTop: 12 },
  headerCell: { color: '#111827', fontSize: 12, fontWeight: '800', textTransform: 'uppercase', width: 145 },
  emptyState: {
    alignItems: 'center',
    backgroundColor: '#F8FBFF',
    borderBottomColor: '#E7ECF3',
    borderBottomWidth: 1,
    justifyContent: 'center',
    minHeight: 180,
    paddingHorizontal: 18,
    paddingVertical: 26,
  },
  emptyIcon: { fontSize: 28, marginBottom: 10 },
  emptyTitle: { color: '#1F2937', fontSize: 18, fontWeight: '700' },
  emptyText: { color: '#5F6F75', fontSize: 14, lineHeight: 20, marginTop: 8, textAlign: 'center' },
  pressed: { opacity: 0.72 },
});
