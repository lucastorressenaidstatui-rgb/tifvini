import { Platform, Pressable, StyleSheet, Text, View } from 'react-native';

export default function AppHeader({ title, onMenu }) {
  return <View style={styles.header}><Pressable accessibilityLabel="Abrir menu" accessibilityRole="button" hitSlop={10} onPress={onMenu} style={({ pressed }) => [styles.menuButton, pressed && styles.pressed]}><Text style={styles.menuLines}>{'\u2261'}</Text></Pressable><Text numberOfLines={1} style={styles.headerTitle}>{title}</Text><View style={styles.balance} /></View>;
}

const styles = StyleSheet.create({
  header: { alignItems: 'center', borderBottomColor: '#E8EBF0', borderBottomWidth: 1, flexDirection: 'row', height: Platform.OS === 'android' ? 82 : 58, justifyContent: 'space-between', paddingHorizontal: 16, paddingTop: Platform.OS === 'android' ? 24 : 0 }, menuButton: { alignItems: 'center', borderRadius: 18, height: 44, justifyContent: 'center', transform: [{ translateY: 26 }], width: 44, zIndex: 1 }, menuLines: { color: '#20242A', fontSize: 30, fontWeight: '700', lineHeight: 30 }, headerTitle: { color: '#20242A', fontSize: 17, fontWeight: '700' }, balance: { width: 44 }, pressed: { backgroundColor: '#EEF4FF' },
});
