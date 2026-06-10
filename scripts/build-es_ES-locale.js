/**
 * Build Spanish (es_ES) translation seed files from English seeds.
 */
const fs = require('fs');
const path = require('path');

const LANG_DIR = path.join(__dirname, '..', 'lang');
const DICT_DIR = path.join(LANG_DIR, 'es-dict');

const te = require(path.join(LANG_DIR, 'translations-en_US.json'));
const be = require(path.join(LANG_DIR, 'block-editor-en_US.json'));

const dictionary = {};
for (let i = 1; i <= 7; i++) {
  const chunkPath = path.join(DICT_DIR, `chunk-${i}.json`);
  if (!fs.existsSync(chunkPath)) {
    console.error(`Missing dictionary chunk: ${chunkPath}`);
    process.exit(1);
  }
  Object.assign(dictionary, JSON.parse(fs.readFileSync(chunkPath, 'utf8')));
}

const missing = new Set();

function translate(en) {
  if (Object.prototype.hasOwnProperty.call(dictionary, en)) {
    return dictionary[en];
  }
  missing.add(en);
  return en;
}

function buildFile(source, outputName) {
  const result = {};
  for (const [key, en] of Object.entries(source)) {
    result[key] = translate(en);
  }
  const outPath = path.join(LANG_DIR, outputName);
  fs.writeFileSync(outPath, `${JSON.stringify(result, null, 2)}\n`);
  return Object.keys(result).length;
}

const translationsCount = buildFile(te, 'translations-es_ES.json');
const blockEditorCount = buildFile(be, 'block-editor-es_ES.json');

console.log(`Wrote translations-es_ES.json (${translationsCount} entries)`);
console.log(`Wrote block-editor-es_ES.json (${blockEditorCount} entries)`);
console.log(`Dictionary size: ${Object.keys(dictionary).length}`);
console.log(`Missing translations: ${missing.size}`);

if (missing.size > 0) {
  console.error('First missing entries:');
  [...missing].slice(0, 10).forEach((entry) => console.error(`  - ${entry.slice(0, 100)}`));
  process.exit(1);
}
