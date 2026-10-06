import { Modal, Pressable, StyleSheet, Text, View } from 'react-native';

export default function SideMenu({ visible, activePage, onClose, onSelect }) {
  const items = [
    { id: 'controle', label: 'Controle de frequência' },
    { id: 'cadastro', label: 'Cadastro de usuário' },
    { id: 'registro', label: 'Registros do dia' },
  ];

  return (
    <Modal animationType="fade" transparent visible={visible} onRequestClose={onClose}>
      <View style={styles.layer}>
        <View style={styles.panel}>
          <View style={styles.top}>
            <Text style={styles.brand}>Sistema escolar</Text>
            <Pressable onPress={onClose}>
              <Text style={styles.close}>x</Text>
            </Pressable>
          </View>

          <Text style={styles.caption}>NAVEGAÇÃO</Text>

          {items.map((item) => (
            <Pressable
              key={item.id}
              onPress={() => onSelect(item.id)}
              style={[styles.item, activePage === item.id && styles.itemActive]}
            >
              <Text style={[styles.itemText, activePage === item.id && styles.itemTextActive]}>{item.label}</Text>
            </Pressable>
          ))}

          <View style={styles.futureArea}>
            <Text style={styles.futureText} />
          </View>
        </View>
        <Pressable style={styles.backdrop} onPress={onClose} />
      </View>
    </Modal>
  );
}

const styles = StyleSheet.create({
  layer: { flex: 1, flexDirection: 'row' },
  backdrop: { backgroundColor: 'rgba(16, 24, 40, 0.36)', flex: 1 },
  panel: { backgroundColor: '#FFFFFF', elevation: 10, minHeight: '100%', paddingHorizontal: 20, paddingTop: 56, width: 290 },
  top: { alignItems: 'center', flexDirection: 'row', justifyContent: 'space-between', marginBottom: 38 },
  brand: { color: '#20242A', fontSize: 18, fontWeight: '700' },
  close: { color: '#20242A', fontSize: 26 },
  caption: { color: '#687386', fontSize: 11, fontWeight: '700', letterSpacing: 1, marginBottom: 10 },
  item: { borderRadius: 8, marginBottom: 6, paddingHorizontal: 14, paddingVertical: 14 },
  itemActive: { backgroundColor: '#EAF2FF' },
  itemText: { color: '#303743', fontSize: 16 },
  itemTextActive: { color: '#146CFF', fontWeight: '700' },
  futureArea: { borderTopColor: '#E8EBF0', borderTopWidth: 1, marginTop: 24, paddingTop: 18 },
  futureText: { color: '#687386', fontSize: 14, lineHeight: 20 },
});
