import { useEffect } from 'react';
import { Pressable, StyleSheet, Text, View } from 'react-native';
import colors from '../theme/colors';

const ICONS = {
  success: '✓',
  error: '!',
  warning: 'i',
};

export default function AlertBanner({ visible, type = 'success', title, message, onClose }) {
  useEffect(() => {
    if (!visible) return undefined;

    const timer = setTimeout(() => {
      onClose && onClose();
    }, 2600);

    return () => clearTimeout(timer);
  }, [visible, onClose]);

  if (!visible) return null;

  const palette = {
    success: {
      box: colors.successSoft,
      border: '#A9DCC2',
      icon: colors.success,
      title: colors.text,
      text: colors.textMuted,
    },
    error: {
      box: colors.errorSoft,
      border: '#F1B8B8',
      icon: colors.error,
      title: colors.text,
      text: colors.textMuted,
    },
    warning: {
      box: colors.warningSoft,
      border: '#F5D7A4',
      icon: colors.warning,
      title: colors.text,
      text: colors.textMuted,
    },
  }[type] || palette.success;

  return (
    <View pointerEvents="box-none" style={styles.container}>
      <Pressable
        onPress={onClose}
        style={[styles.banner, { backgroundColor: palette.box, borderColor: palette.border }]}
      >
        <View style={[styles.iconWrap, { backgroundColor: palette.icon }]}>
          <Text style={styles.iconText}>{ICONS[type] || '✓'}</Text>
        </View>

        <View style={styles.textWrap}>
          <Text style={[styles.title, { color: palette.title }]}>{title}</Text>
          {message ? <Text style={[styles.message, { color: palette.text }]}>{message}</Text> : null}
        </View>
      </Pressable>
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    position: 'absolute',
    top: 10,
    left: 16,
    right: 16,
    zIndex: 50,
  },
  banner: {
    alignItems: 'center',
    borderRadius: 16,
    borderWidth: 1,
    flexDirection: 'row',
    paddingHorizontal: 14,
    paddingVertical: 12,
    shadowColor: colors.shadow,
    shadowOffset: { width: 0, height: 8 },
    shadowOpacity: 0.12,
    shadowRadius: 14,
    elevation: 8,
  },
  iconWrap: {
    alignItems: 'center',
    borderRadius: 999,
    height: 28,
    justifyContent: 'center',
    marginRight: 12,
    width: 28,
  },
  iconText: {
    color: colors.white,
    fontSize: 16,
    fontWeight: '800',
  },
  textWrap: {
    flex: 1,
  },
  title: {
    fontSize: 15,
    fontWeight: '800',
  },
  message: {
    fontSize: 13,
    marginTop: 2,
    lineHeight: 18,
  },
});
