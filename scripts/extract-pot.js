/**
 * Extract translatable strings from PHP files and write lang/kursagenten.pot.
 * Run: node scripts/extract-pot.js
 */
const fs = require('fs');
const path = require('path');

const ROOT = path.resolve(__dirname, '..');
const OUT = path.join(ROOT, 'lang', 'kursagenten.pot');
const EXCLUDE = new Set(['node_modules', 'build', 'slettes', '.git', 'vendor']);

const FN_PATTERN = /(?:__|_e|esc_html__|esc_attr__|esc_html_e|esc_attr_e|_x)\(\s*(['"])((?:\\.|(?!\1).)*)\1/g;
const N_PATTERN = /_n\(\s*(['"])((?:\\.|(?!\1).)*)\1\s*,\s*(['"])((?:\\.|(?!\3).)*)\3/g;

function walk(dir, files = []) {
  for (const entry of fs.readdirSync(dir, { withFileTypes: true })) {
    if (EXCLUDE.has(entry.name)) continue;
    const full = path.join(dir, entry.name);
    if (entry.isDirectory()) walk(full, files);
    else if (entry.name.endsWith('.php')) files.push(full);
  }
  return files;
}

function unescape(str) {
  return str.replace(/\\'/g, "'").replace(/\\"/g, '"').replace(/\\n/g, '\n');
}

function hasDomain(tail) {
  return /['"]kursagenten['"]\s*\)/.test(tail);
}

const strings = new Map();
const plurals = new Map();

for (const file of walk(ROOT)) {
  const rel = path.relative(ROOT, file).replace(/\\/g, '/');
  const content = fs.readFileSync(file, 'utf8');
  let match;

  while ((match = N_PATTERN.exec(content)) !== null) {
    const tail = content.slice(match.index, match.index + 8000);
    if (!hasDomain(tail)) continue;
    const singular = unescape(match[2]);
    const plural = unescape(match[4]);
    if (!singular || !plural) continue;
    if (!plurals.has(singular)) {
      plurals.set(singular, { plural, refs: new Set() });
    }
    plurals.get(singular).refs.add(rel);
  }

  FN_PATTERN.lastIndex = 0;
  while ((match = FN_PATTERN.exec(content)) !== null) {
    const tail = content.slice(match.index, match.index + 8000);
    if (!hasDomain(tail)) continue;
    const msgid = unescape(match[2]);
    if (!msgid) continue;
    if (plurals.has(msgid)) continue;
    if (!strings.has(msgid)) strings.set(msgid, new Set());
    strings.get(msgid).add(rel);
  }
}

// block-editor-i18n.php uses __($key) with variables; extract literal keys from the list.
const blockEditorI18n = path.join(ROOT, 'includes', 'misc', 'block-editor-i18n.php');
if (fs.existsSync(blockEditorI18n)) {
  const rel = 'includes/misc/block-editor-i18n.php';
  const content = fs.readFileSync(blockEditorI18n, 'utf8');
  const keyPattern = /^\s*'((?:\\'|[^'])*)',/gm;
  let blockMatch;
  while ((blockMatch = keyPattern.exec(content)) !== null) {
    const msgid = blockMatch[1].replace(/\\'/g, "'");
    if (!msgid || plurals.has(msgid)) continue;
    if (!strings.has(msgid)) strings.set(msgid, new Set());
    strings.get(msgid).add(rel);
  }
}

const sorted = [...strings.keys()].sort((a, b) => a.localeCompare(b, 'nb'));
const sortedPlurals = [...plurals.keys()].sort((a, b) => a.localeCompare(b, 'nb'));

const header = `# Copyright (C) 2026 Kursagenten Team
# This file is distributed under the same license as the Kursagenten plugin.
msgid ""
msgstr ""
"Project-Id-Version: Kursagenten 1.1.24\\n"
"Report-Msgid-Bugs-To: https://kursagenten.no\\n"
"POT-Creation-Date: ${new Date().toISOString().slice(0, 19).replace('T', ' ')}+0000\\n"
"PO-Revision-Date: YEAR-MO-DA HO:MI+ZONE\\n"
"Last-Translator: FULL NAME <EMAIL@ADDRESS>\\n"
"Language-Team: LANGUAGE <LL@li.org>\\n"
"Language: \\n"
"MIME-Version: 1.0\\n"
"Content-Type: text/plain; charset=UTF-8\\n"
"Content-Transfer-Encoding: 8bit\\n"
"X-Generator: scripts/extract-pot.js\\n"
"X-Domain: kursagenten\\n"

`;

let body = '';
for (const msgid of sorted) {
  const refs = [...strings.get(msgid)].sort();
  for (const ref of refs.slice(0, 3)) {
    body += `#: ${ref}\n`;
  }
  const escaped = msgid.replace(/\\/g, '\\\\').replace(/"/g, '\\"');
  body += `msgid "${escaped}"\nmsgstr ""\n\n`;
}

for (const singular of sortedPlurals) {
  const entry = plurals.get(singular);
  const refs = [...entry.refs].sort();
  for (const ref of refs.slice(0, 3)) {
    body += `#: ${ref}\n`;
  }
  const escSingular = singular.replace(/\\/g, '\\\\').replace(/"/g, '\\"');
  const escPlural = entry.plural.replace(/\\/g, '\\\\').replace(/"/g, '\\"');
  body += `msgid "${escSingular}"\nmsgid_plural "${escPlural}"\nmsgstr[0] ""\nmsgstr[1] ""\n\n`;
}

fs.mkdirSync(path.dirname(OUT), { recursive: true });
fs.writeFileSync(OUT, header + body, 'utf8');
console.log(`Wrote ${sorted.length} strings and ${sortedPlurals.length} plurals to ${OUT}`);
