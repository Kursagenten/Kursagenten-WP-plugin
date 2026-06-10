const fs = require('fs');
const path = require('path');

const pot = fs.readFileSync(path.join(__dirname, '..', 'lang', 'kursagenten.pot'), 'utf8');
const seed = JSON.parse(fs.readFileSync(path.join(__dirname, '..', 'lang', 'translations-en_US.json'), 'utf8'));

const missing = [];
for (const block of pot.split(/\n\n+/)) {
  const m = block.match(/^msgid "((?:\\.|[^"])*)"/m);
  if (!m || m[1] === '') continue;
  const msgid = m[1].replace(/\\n/g, '\n').replace(/\\"/g, '"').replace(/\\\\/g, '\\');
  if (!seed[msgid]) missing.push(msgid);
}

const out = path.join(__dirname, '..', 'lang', '_missing-keys.json');
fs.writeFileSync(out, JSON.stringify(missing, null, 2), 'utf8');
console.log(`Missing: ${missing.length} (written to ${out})`);
