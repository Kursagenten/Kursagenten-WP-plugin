/**
 * Compile all kursagenten-*.po files to .mo for WordPress.
 *
 * Run: node scripts/compile-all-mo.js
 */
const fs = require('fs');
const path = require('path');
const gettextParser = require('gettext-parser');

const LANG_DIR = path.join(__dirname, '..', 'lang');
const poFiles = fs
  .readdirSync(LANG_DIR)
  .filter((name) => /^kursagenten-[a-z]{2}_[A-Z]{2}\.po$/.test(name));

if (poFiles.length === 0) {
  console.error('No kursagenten-*.po files found in lang/.');
  process.exit(1);
}

const LOCALE_ALIASES = {
  en_US: ['en'],
  de_DE: ['de'],
  fr_FR: ['fr'],
  es_ES: ['es'],
  pl_PL: ['pl'],
};

for (const poFile of poFiles.sort()) {
  const poPath = path.join(LANG_DIR, poFile);
  const moPath = poPath.replace(/\.po$/, '.mo');
  const parsed = gettextParser.po.parse(fs.readFileSync(poPath));
  const moBuffer = gettextParser.mo.compile(parsed);
  fs.writeFileSync(moPath, moBuffer);
  console.log(`Wrote ${moPath}`);

  const locale = poFile.replace(/^kursagenten-|\.po$/g, '');
  const aliases = LOCALE_ALIASES[locale] || [];
  for (const alias of aliases) {
    const aliasPath = path.join(LANG_DIR, `kursagenten-${alias}.mo`);
    fs.writeFileSync(aliasPath, moBuffer);
    console.log(`Wrote ${aliasPath}`);
  }
}
