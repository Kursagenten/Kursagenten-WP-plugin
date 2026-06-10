/**
 * Generate fr_FR-translations-map.js from English sources.
 * Run: node scripts/generate-fr-map-content.js > scripts/fr_FR-translations-map.js
 */
const fs = require('fs');
const path = require('path');

const LANG = path.join(__dirname, '..', 'lang');
const enT = require(path.join(LANG, 'translations-en_US.json'));
const enB = require(path.join(LANG, 'block-editor-en_US.json'));
const all = { ...enT, ...enB };

/** English value -> French translation */
const EN_TO_FR = require('./fr-en-glossary.json');

const out = {};
const missing = [];

for (const [key, en] of Object.entries(all)) {
  if (EN_TO_FR[en] !== undefined) {
    out[key] = EN_TO_FR[en];
  } else if (EN_TO_FR[key] !== undefined) {
    out[key] = EN_TO_FR[key];
  } else {
    missing.push({ key, en: en.slice(0, 100) });
    out[key] = en;
  }
}

if (missing.length) {
  fs.writeFileSync(
    path.join(LANG, '_fr-missing.json'),
    JSON.stringify(missing, null, 2) + '\n'
  );
  console.error(`Missing ${missing.length} translations – see lang/_fr-missing.json`);
  process.exit(1);
}

process.stdout.write('module.exports = ' + JSON.stringify(out, null, 2) + ';\n');
