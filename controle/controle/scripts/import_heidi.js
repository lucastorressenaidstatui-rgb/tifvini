// Script: import_heidi.js
// Usage: create a file `heidi_config.json` in project root with:
// { "host": "127.0.0.1", "port": 3306, "user": "root", "password": "pwd", "database": "dbname" }
// Then run: `npm install mysql2` and `node scripts/import_heidi.js`

const fs = require('fs');
const path = require('path');
async function main() {
  const cfgPath = path.resolve(process.cwd(), 'heidi_config.json');
  if (!fs.existsSync(cfgPath)) {
    console.error('Missing heidi_config.json in project root. See scripts/import_heidi.js header for format.');
    process.exit(1);
  }
  const cfg = JSON.parse(fs.readFileSync(cfgPath, 'utf8'));
  const mysql = require('mysql2/promise');
  const conn = await mysql.createConnection({
    host: cfg.host || '127.0.0.1', port: cfg.port || 3306, user: cfg.user, password: cfg.password, database: cfg.database,
  });
  try {
    const [tables] = await conn.query("SHOW TABLES");
    const tableNames = tables.map((r) => Object.values(r)[0]);
    const out = {};
    for (const t of tableNames) {
      console.log('Exporting table', t);
      const [rows] = await conn.query(`SELECT * FROM \`${t}\``);
      out[t] = rows;
    }
    const outPath = path.resolve(process.cwd(), 'heidi_dump.json');
    fs.writeFileSync(outPath, JSON.stringify(out, null, 2), 'utf8');
    console.log('Wrote', outPath);
  } finally {
    await conn.end();
  }
}

main().catch((err) => { console.error(err); process.exit(1); });
