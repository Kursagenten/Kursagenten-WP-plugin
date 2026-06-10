/**
 * Sync kursagenten-{locale}.po with kursagenten.pot.
 *
 * Usage: node scripts/merge-locale.js en_US
 *        node scripts/merge-locale.js de_DE
 */
const fs = require('fs');
const path = require('path');

const LANG_DIR = path.join(__dirname, '..', 'lang');
const POT = path.join(LANG_DIR, 'kursagenten.pot');

const LOCALE_META = {
  en_US: {
    languageTeam: 'English',
    pluralForms: 'nplurals=2; plural=(n != 1);',
    pluralCount: 2,
  },
  de_DE: {
    languageTeam: 'German',
    pluralForms: 'nplurals=2; plural=(n != 1);',
    pluralCount: 2,
  },
  pl_PL: {
    languageTeam: 'Polish',
    pluralForms:
      'nplurals=3; plural=(n==1 ? 0 : n%10>=2 && n%10<=4 && (n%100<10 || n%100>=20) ? 1 : 2);',
    pluralCount: 3,
  },
  fr_FR: {
    languageTeam: 'French',
    pluralForms: 'nplurals=2; plural=(n > 1);',
    pluralCount: 2,
  },
  es_ES: {
    languageTeam: 'Spanish',
    pluralForms: 'nplurals=2; plural=(n != 1);',
    pluralCount: 2,
  },
};

const locale = process.argv[2];
if (!locale || !LOCALE_META[locale]) {
  console.error(
    `Usage: node scripts/merge-locale.js <locale>\nSupported: ${Object.keys(LOCALE_META).join(', ')}`
  );
  process.exit(1);
}

const meta = LOCALE_META[locale];
const PO = path.join(LANG_DIR, `kursagenten-${locale}.po`);
const SEED = path.join(LANG_DIR, `translations-${locale}.json`);
const BLOCK_EDITOR_SEED = path.join(LANG_DIR, `block-editor-${locale}.json`);
const PLURALS_SEED = path.join(LANG_DIR, `plurals-${locale}.json`);

const KNOWN = {
  ...(fs.existsSync(SEED) ? JSON.parse(fs.readFileSync(SEED, 'utf8')) : {}),
  ...(fs.existsSync(BLOCK_EDITOR_SEED)
    ? JSON.parse(fs.readFileSync(BLOCK_EDITOR_SEED, 'utf8'))
    : {}),
};
const PLURALS_KNOWN = fs.existsSync(PLURALS_SEED)
  ? JSON.parse(fs.readFileSync(PLURALS_SEED, 'utf8'))
  : {};

function unescapePo(str) {
  return str.replace(/\\n/g, '\n').replace(/\\"/g, '"').replace(/\\\\/g, '\\');
}

function escPo(str) {
  return str.replace(/\\/g, '\\\\').replace(/"/g, '\\"').replace(/\n/g, '\\n');
}

function parsePot(content) {
  const entries = [];
  const blocks = content.split(/\n\n+/);
  for (const block of blocks) {
    if (!block.trim()) continue;
    const pluralMatch = block.match(
      /^msgid "((?:\\.|[^"])*)"\nmsgid_plural "((?:\\.|[^"])*)"\nmsgstr\[0\]/m
    );
    if (pluralMatch) {
      entries.push({
        type: 'plural',
        msgid: unescapePo(pluralMatch[1]),
        msgid_plural: unescapePo(pluralMatch[2]),
      });
      continue;
    }
    const m = block.match(/^msgid "((?:\\.|[^"])*)"\nmsgstr/m);
    if (!m || m[1] === '') continue;
    entries.push({
      type: 'singular',
      msgid: unescapePo(m[1]),
    });
  }
  return entries;
}

function parsePo(content, pluralCount) {
  const singular = new Map();
  const plural = new Map();
  const blocks = content.split(/\n\n+/);
  for (const block of blocks) {
    const pluralMatch = block.match(
      new RegExp(
        `^msgid "((?:\\\\.|[^"])*)"\\nmsgid_plural "((?:\\\\.|[^"])*)"\\n((?:msgstr\\[\\d+\\] "((?:\\\\.|[^"])*)"\n?)+)`,
        'm'
      )
    );
    if (pluralMatch) {
      const msgid = unescapePo(pluralMatch[1]);
      const forms = [];
      const formRegex = /msgstr\[(\d+)\] "((?:\\.|[^"])*)"/g;
      let formMatch;
      while ((formMatch = formRegex.exec(block)) !== null) {
        forms[Number(formMatch[1])] = unescapePo(formMatch[2]);
      }
      plural.set(
        msgid,
        Array.from({ length: pluralCount }, (_, index) => forms[index] || '')
      );
      continue;
    }
    const idMatch = block.match(/^msgid "((?:\\.|[^"])*)"\nmsgstr "((?:\\.|[^"])*)"/m);
    if (!idMatch || idMatch[1] === '') continue;
    const msgid = unescapePo(idMatch[1]);
    const msgstr = unescapePo(idMatch[2]);
    if (msgstr) singular.set(msgid, msgstr);
  }
  return { singular, plural };
}

function normalizePluralForms(seedForms, pluralCount) {
  const forms = Array.isArray(seedForms) ? [...seedForms] : [];
  while (forms.length < pluralCount) {
    forms.push(forms[forms.length - 1] || '');
  }
  return forms.slice(0, pluralCount);
}

const potEntries = parsePot(fs.readFileSync(POT, 'utf8'));
const existing = fs.existsSync(PO)
  ? parsePo(fs.readFileSync(PO, 'utf8'), meta.pluralCount)
  : { singular: new Map(), plural: new Map() };

const header = `msgid ""
msgstr ""
"Project-Id-Version: Kursagenten 1.1.24\\n"
"Report-Msgid-Bugs-To: https://kursagenten.no\\n"
"POT-Creation-Date: ${new Date().toISOString().slice(0, 19).replace('T', ' ')}+0000\\n"
"PO-Revision-Date: ${new Date().toISOString().slice(0, 10)} 00:00+0000\\n"
"Last-Translator: Kursagenten Team\\n"
"Language-Team: ${meta.languageTeam}\\n"
"Language: ${locale}\\n"
"MIME-Version: 1.0\\n"
"Content-Type: text/plain; charset=UTF-8\\n"
"Content-Transfer-Encoding: 8bit\\n"
"Plural-Forms: ${meta.pluralForms}\\n"
"X-Generator: scripts/merge-locale.js\\n"
"X-Domain: kursagenten\\n"

`;

let body = '';
let translated = 0;
for (const entry of potEntries) {
  if (entry.type === 'plural') {
    const seedForms = PLURALS_KNOWN[entry.msgid]?.forms;
    const existingForms = existing.plural.get(entry.msgid);
    const forms = normalizePluralForms(
      seedForms || existingForms || [],
      meta.pluralCount
    );
    if (forms.every((form) => form)) translated++;
    body += `msgid "${escPo(entry.msgid)}"\n`;
    body += `msgid_plural "${escPo(entry.msgid_plural)}"\n`;
    for (let i = 0; i < meta.pluralCount; i++) {
      body += `msgstr[${i}] "${escPo(forms[i] || '')}"\n`;
    }
    body += '\n';
    continue;
  }

  const msgstr = KNOWN[entry.msgid] || existing.singular.get(entry.msgid) || '';
  if (msgstr) translated++;
  body += `msgid "${escPo(entry.msgid)}"\nmsgstr "${escPo(msgstr)}"\n\n`;
}

fs.writeFileSync(PO, header + body, 'utf8');
console.log(`Wrote ${potEntries.length} entries to ${PO} (${translated} translated)`);
