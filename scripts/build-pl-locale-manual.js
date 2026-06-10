/**
 * Build complete pl_PL translation seed files from en_US sources.
 * Manual Polish translations – no external APIs.
 */
const fs = require('fs');
const path = require('path');

const LANG = path.join(__dirname, '..', 'lang');
const enT = JSON.parse(fs.readFileSync(path.join(LANG, 'translations-en_US.json'), 'utf8'));
const enB = JSON.parse(fs.readFileSync(path.join(LANG, 'block-editor-en_US.json'), 'utf8'));

/** @type {Record<string, string>} Norwegian key -> Polish value */
const PL = Object.assign(
  {},
  require('./pl_PL-base.js'),
  require('./pl_PL-patch-trans-data.js'),
  require('./pl_PL-patch-block.js')
);

// Polish plural form for "%d kurs" is handled in plurals-pl_PL.json
if (PL['%d kurs']) {
  PL['%d kurs'] = '%d kurs';
}

const overlapKeys = Object.keys(enT).filter((k) => enB[k]);
const allKeys = new Set([...Object.keys(enT), ...Object.keys(enB)]);

const missing = [...allKeys].filter((k) => PL[k] === undefined);
if (missing.length) {
  console.error(`Missing ${missing.length} PL translations:`);
  missing.slice(0, 20).forEach((k) => console.error('  -', k.slice(0, 80)));
  process.exit(1);
}

function writeJson(filename, out) {
  fs.writeFileSync(path.join(LANG, filename), JSON.stringify(out, null, 2) + '\n', 'utf8');
  console.log(`Wrote ${filename} (${Object.keys(out).length} entries)`);
}

// translations-pl_PL.json: enT keys minus 18 overlap keys
const translationsOut = {};
for (const key of Object.keys(enT)) {
  if (overlapKeys.includes(key)) {
    continue;
  }
  translationsOut[key] = PL[key];
}

// block-editor-pl_PL.json: all 195 keys from enB (includes overlap)
const blockEditorOut = {};
for (const key of Object.keys(enB)) {
  blockEditorOut[key] = PL[key];
}

writeJson('translations-pl_PL.json', translationsOut);
writeJson('block-editor-pl_PL.json', blockEditorOut);

console.log('Overlap keys (block-editor only):', overlapKeys.length);
console.log('Done. Total unique keys:', Object.keys(translationsOut).length + Object.keys(blockEditorOut).length);
