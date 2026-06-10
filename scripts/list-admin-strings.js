const fs = require('fs');
const path = require('path');

const pot = fs.readFileSync(path.join(__dirname, '..', 'lang', 'kursagenten.pot'), 'utf8');
const seed = JSON.parse(fs.readFileSync(path.join(__dirname, '..', 'lang', 'translations-en_US.json'), 'utf8'));
const adminPrefixes = [
  'includes/options/',
  'includes/misc/',
  'includes/plugin_update/',
  'includes/post_types/',
  'public/menus/',
];

const blocks = pot.split(/\n\n+/);
const missing = [];

for (const block of blocks) {
  const idMatch = block.match(/^msgid "((?:\\.|[^"])*)"/m);
  if (!idMatch || idMatch[1] === '') continue;
  const msgid = idMatch[1]
    .replace(/\\n/g, '\n')
    .replace(/\\"/g, '"')
    .replace(/\\\\/g, '\\');
  const refs = [...block.matchAll(/^#: (.+)$/gm)].map((m) => m[1]);
  const isAdmin = refs.some((r) => adminPrefixes.some((p) => r.startsWith(p)));
  if (!isAdmin || seed[msgid]) continue;
  missing.push({ msgid, ref: refs[0] });
}

console.log(JSON.stringify(missing, null, 2));
console.error(`Missing admin translations: ${missing.length}`);
