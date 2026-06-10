/**
 * One-time composer: reads EN+DE reference and writes pl_PL-patch-trans.js
 * Run: node scripts/_compose-pl-trans-patch.js
 */
const fs = require('fs');
const path = require('path');

const ref = JSON.parse(
  fs.readFileSync(path.join(__dirname, '..', 'lang', '.pl-translate-with-de.json'), 'utf8')
);

/** @type {Record<string, string>} Norwegian key -> Polish value */
const PL = require('./pl_PL-patch-trans-data.js');

const missing = ref.filter(({ k }) => PL[k] === undefined);
if (missing.length) {
  console.error('Missing PL entries:', missing.length);
  missing.slice(0, 20).forEach(({ k }) => console.error(' -', k.slice(0, 80)));
  process.exit(1);
}

const lines = ['/** Polish translations for admin/frontend keys still in English */', 'module.exports = {'];
for (const { k } of ref) {
  lines.push(`${JSON.stringify(k)}: ${JSON.stringify(PL[k])},`);
}
lines.push('};', '');
fs.writeFileSync(path.join(__dirname, 'pl_PL-patch-trans.js'), lines.join('\n'), 'utf8');
console.log('Wrote pl_PL-patch-trans.js', ref.length, 'entries');
