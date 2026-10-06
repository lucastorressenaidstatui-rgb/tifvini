const assert = require('node:assert/strict');
const { test } = require('node:test');
const config = require('../app.json');

test('configura o splash com a imagem imgsesi', () => {
  const plugins = config.expo.plugins ?? [];
  const splashPlugin = plugins.find(
    (plugin) => Array.isArray(plugin) && plugin[0] === 'expo-splash-screen',
  );

  assert.ok(splashPlugin, 'o plugin expo-splash-screen deve estar configurado');
  assert.equal(splashPlugin[1].image, './assets/imgsesi.jpg');
  assert.equal(splashPlugin[1].backgroundColor, '#ffffff');
  assert.equal(splashPlugin[1].resizeMode, 'contain');
});
