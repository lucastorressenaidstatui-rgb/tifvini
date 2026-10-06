import { REMOTE_DATA_ENDPOINT, REMOTE_FULL_DUMP_ENDPOINT } from '../config';

// Lazy-load expo-sqlite to avoid bundler crash when the package isn't installed.
let SQLite = null;
let db = null;
function ensureSQLite() {
  if (SQLite) return true;
  try {
    // require at runtime so Metro doesn't try to resolve at bundle time
    // when the package may be absent in some environments.
    // eslint-disable-next-line global-require, import/no-dynamic-require
    SQLite = require('expo-sqlite');
    return true;
  } catch (err) {
    SQLite = null;
    return false;
  }
}

function getDb() {
  if (!ensureSQLite()) throw new Error('expo-sqlite não está instalado. Rode `expo install expo-sqlite`.');
  if (!db) db = SQLite.openDatabase('controle.db');
  return db;
}

function runSql(sql, params = []) {
  return new Promise((resolve, reject) => {
    try {
      const _db = getDb();
      _db.transaction((tx) => {
        tx.executeSql(sql, params, (_, result) => resolve(result), (_, err) => reject(err));
      });
    } catch (err) {
      reject(err);
    }
  });
}

export async function initDb() {
  // table stores remote items as JSON by id
  await runSql(
    `CREATE TABLE IF NOT EXISTS remote_data (
      id TEXT PRIMARY KEY NOT NULL,
      payload TEXT NOT NULL
    );`
  );
}

export async function saveItem(item) {
  const id = item.id ? String(item.id) : Math.random().toString(36).slice(2);
  const payload = JSON.stringify(item);
  await runSql('INSERT OR REPLACE INTO remote_data (id, payload) VALUES (?, ?);', [id, payload]);
}

export async function clearRemoteData() {
  await runSql('DELETE FROM remote_data;');
}

export async function getAllLocalData() {
  const res = await runSql('SELECT * FROM remote_data;');
  const rows = [];
  for (let i = 0; i < res.rows.length; i++) rows.push(JSON.parse(res.rows.item(i).payload));
  return rows;
}

export async function syncAll() {
  // Fetch remote data and save to local DB
  const resp = await fetch(REMOTE_DATA_ENDPOINT);
  if (!resp.ok) throw new Error(`Failed to fetch remote data: ${resp.status}`);
  const data = await resp.json();
  if (!Array.isArray(data)) throw new Error('Remote endpoint must return a JSON array of items');

  await initDb();
  // optional: clear existing remote_data before inserting
  await clearRemoteData();

  for (const item of data) {
    await saveItem(item);
  }
  return { imported: data.length };
}

// --- Structured sync helpers -------------------------------------------------
function sqliteTypeForValue(v) {
  if (v === null || v === undefined) return 'TEXT';
  if (typeof v === 'number') return Number.isInteger(v) ? 'INTEGER' : 'REAL';
  if (typeof v === 'boolean') return 'INTEGER';
  return 'TEXT';
}

async function createTableFromSample(tableName, sample) {
  const cols = [];
  for (const key of Object.keys(sample)) {
    const col = `${key.replace(/[^a-z0-9_]/gi, '_')}`;
    const type = sqliteTypeForValue(sample[key]);
    cols.push(`${col} ${type}`);
  }
  // keep original payload too
  cols.push('payload TEXT');
  const sql = `CREATE TABLE IF NOT EXISTS ${tableName} (id TEXT PRIMARY KEY, ${cols.join(', ')});`;
  await runSql(sql);
}

async function clearTable(tableName) {
  await runSql(`DELETE FROM ${tableName};`);
}

async function saveStructuredItem(tableName, item) {
  const id = item.id ? String(item.id) : Math.random().toString(36).slice(2);
  const keys = Object.keys(item).map((k) => k.replace(/[^a-z0-9_]/gi, '_'));
  const cols = keys.join(', ');
  const placeholders = keys.map(() => '?').join(', ');
  const values = keys.map((k) => item[k]);
  // append payload
  const insertSql = `INSERT OR REPLACE INTO ${tableName} (id, ${cols}, payload) VALUES (?, ${placeholders}, ?);`;
  await runSql(insertSql, [id, ...values, JSON.stringify(item)]);
}

export async function syncAllStructured(tableName = 'remote_items') {
  const resp = await fetch(REMOTE_DATA_ENDPOINT);
  if (!resp.ok) throw new Error(`Failed to fetch remote data: ${resp.status}`);
  const data = await resp.json();
  if (!Array.isArray(data)) throw new Error('Remote endpoint must return a JSON array of items');

  if (data.length === 0) return { imported: 0 };

  // create table based on first item's keys
  await createTableFromSample(tableName, data[0]);
  await clearTable(tableName);

  for (const item of data) {
    await saveStructuredItem(tableName, item);
  }
  return { imported: data.length };
}

export default { initDb, saveItem, getAllLocalData, syncAll, syncAllStructured };


// --- Full DB dump sync ------------------------------------------------------
export async function syncFullDump(url) {
  const resp = await fetch(url);
  if (!resp.ok) throw new Error(`Failed to fetch full dump: ${resp.status}`);
  const data = await resp.json();
  if (typeof data !== 'object' || Array.isArray(data)) throw new Error('Full dump must be an object with arrays per table');

  // For each key (table) infer schema and insert rows
  const tables = Object.keys(data);
  for (const tableName of tables) {
    const rows = data[tableName];
    if (!Array.isArray(rows)) continue;
    if (rows.length === 0) {
      // ensure table exists
      await createTableFromSample(tableName, {});
      await clearTable(tableName);
      continue;
    }
    // create table from first row
    await createTableFromSample(tableName, rows[0]);
    await clearTable(tableName);
    for (const row of rows) {
      await saveStructuredItem(tableName, row);
    }
  }
  return { tables: tables.length };
}
