/**
 * Build complete fr_FR translation seed files from en_US sources.
 * Manual French translations – no external APIs.
 */
const fs = require('fs');
const path = require('path');

const LANG = path.join(__dirname, '..', 'lang');
const enT = JSON.parse(fs.readFileSync(path.join(LANG, 'translations-en_US.json'), 'utf8'));
const enB = JSON.parse(fs.readFileSync(path.join(LANG, 'block-editor-en_US.json'), 'utf8'));

/** @type {Record<string, string>} Norwegian key -> French value */
const FR = require('./fr_FR-translations-map.js');

function buildOutput(enSource, filename) {
  const out = {};
  const missing = [];
  for (const key of Object.keys(enSource)) {
    if (FR[key] !== undefined) {
      out[key] = FR[key];
    } else {
      missing.push(key);
      out[key] = enSource[key];
    }
  }
  if (missing.length) {
    console.error(`Missing ${missing.length} translations for ${filename}:`);
    missing.slice(0, 20).forEach((k) => console.error('  -', k.slice(0, 80)));
    process.exit(1);
  }
  fs.writeFileSync(path.join(LANG, filename), JSON.stringify(out, null, 2) + '\n', 'utf8');
  console.log(`Wrote ${filename} (${Object.keys(out).length} entries)`);
}

buildOutput(enT, 'translations-fr_FR.json');
buildOutput(enB, 'block-editor-fr_FR.json');
console.log('Done. Total:', Object.keys(enT).length + Object.keys(enB).length);
