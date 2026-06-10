/**
 * Generate translation seed JSON files for additional locales from English seeds.
 *
 * Usage:
 *   node scripts/generate-locale-seeds.js de_DE
 *   node scripts/generate-locale-seeds.js all
 */
const fs = require('fs');
const path = require('path');
const translate = require('google-translate-api-x');

const LANG_DIR = path.join(__dirname, '..', 'lang');

const TARGET_LOCALES = {
  de_DE: 'de',
  pl_PL: 'pl',
  fr_FR: 'fr',
  es_ES: 'es',
};

const EN_TRANSLATIONS = path.join(LANG_DIR, 'translations-en_US.json');
const EN_BLOCK_EDITOR = path.join(LANG_DIR, 'block-editor-en_US.json');

const BATCH_SIZE = 8;
const DELAY_MS = 200;
const MAX_RETRIES = 5;

function sleep(ms) {
  return new Promise((resolve) => setTimeout(resolve, ms));
}

const PRESERVE_TERMS = [
  'Kursagenten',
  'WordPress',
  'Wordpress',
  'HMS',
  'SEO',
  'srcset',
];

function protectTerms(text) {
  const terms = [];
  let protectedText = text;
  for (const term of PRESERVE_TERMS) {
    const re = new RegExp(term, 'g');
    protectedText = protectedText.replace(re, (match) => {
      const token = `__TERM_${terms.length}__`;
      terms.push({ token, match });
      return token;
    });
  }
  return { protectedText, terms };
}

function restoreTerms(text, terms) {
  let restored = text;
  for (const { token, match } of terms) {
    restored = restored.replace(new RegExp(token, 'g'), match);
  }
  return restored;
}

function protectPlaceholders(text) {
  const tokens = [];
  const protectedText = text.replace(/%(\d+\$)?[sd]/g, (match) => {
    const token = `__PH_${tokens.length}__`;
    tokens.push({ token, match });
    return token;
  });
  return { protectedText, tokens };
}

function restorePlaceholders(text, tokens) {
  let restored = text;
  for (const { token, match } of tokens) {
    restored = restored.replace(new RegExp(token, 'g'), match);
  }
  return restored;
}

async function translateText(text, targetCode) {
  if (!text || !text.trim()) return text;

  const { protectedText: withTerms, terms } = protectTerms(text);
  const { protectedText, tokens } = protectPlaceholders(withTerms);

  for (let attempt = 1; attempt <= MAX_RETRIES; attempt++) {
    try {
      const result = await translate(protectedText, { from: 'en', to: targetCode });
      return restoreTerms(restorePlaceholders(result.text, tokens), terms);
    } catch (error) {
      if (attempt === MAX_RETRIES) {
        console.warn(
          `Failed to translate: ${text.slice(0, 60)}... (${error.message})`
        );
        return text;
      }
      await sleep(DELAY_MS * attempt * 4);
    }
  }
  return text;
}

async function translateMap(sourceMap, targetCode, cachePath) {
  const cache = fs.existsSync(cachePath)
    ? JSON.parse(fs.readFileSync(cachePath, 'utf8'))
    : {};
  const output = { ...cache };
  const keys = Object.keys(sourceMap)
    .sort((a, b) => a.localeCompare(b, 'nb'))
    .filter((key) => !output[key]);
  let done = Object.keys(output).length;
  const total = Object.keys(sourceMap).length;

  for (let index = 0; index < keys.length; index += BATCH_SIZE) {
    const batchKeys = keys.slice(index, index + BATCH_SIZE);
    const translated = await Promise.all(
      batchKeys.map((key) => translateText(sourceMap[key], targetCode))
    );
    batchKeys.forEach((key, batchIndex) => {
      output[key] = translated[batchIndex];
    });
    done += batchKeys.length;
    fs.writeFileSync(cachePath, JSON.stringify(output, null, 2) + '\n', 'utf8');
    console.log(`  cached ${done}/${total}`);
    await sleep(DELAY_MS);
  }

  return output;
}

function splitSeeds(fullMap, enTranslations, enBlockEditor) {
  const translationKeys = new Set(Object.keys(enTranslations));
  const blockEditorKeys = new Set(Object.keys(enBlockEditor));

  const translations = {};
  const blockEditor = {};

  for (const [key, value] of Object.entries(fullMap)) {
    if (blockEditorKeys.has(key)) {
      blockEditor[key] = value;
    } else if (translationKeys.has(key)) {
      translations[key] = value;
    } else {
      translations[key] = value;
    }
  }

  return { translations, blockEditor };
}

function writePlurals() {
  const pluralTemplates = {
    de_DE: {
      '%d dag': { msgid_plural: '%d dager', forms: ['%d Tag', '%d Tage'] },
      '%d kurs': { msgid_plural: '%d kurs', forms: ['%d Kurs', '%d Kurse'] },
    },
    pl_PL: {
      '%d dag': { msgid_plural: '%d dager', forms: ['%d dzień', '%d dni', '%d dni'] },
      '%d kurs': { msgid_plural: '%d kurs', forms: ['%d kurs', '%d kursy', '%d kursów'] },
    },
    fr_FR: {
      '%d dag': { msgid_plural: '%d dager', forms: ['%d jour', '%d jours'] },
      '%d kurs': { msgid_plural: '%d kurs', forms: ['%d cours', '%d cours'] },
    },
    es_ES: {
      '%d dag': { msgid_plural: '%d dager', forms: ['%d día', '%d días'] },
      '%d kurs': { msgid_plural: '%d kurs', forms: ['%d curso', '%d cursos'] },
    },
  };

  for (const [locale, plurals] of Object.entries(pluralTemplates)) {
    fs.writeFileSync(
      path.join(LANG_DIR, `plurals-${locale}.json`),
      JSON.stringify(plurals, null, 2) + '\n',
      'utf8'
    );
    console.log(`Wrote plurals-${locale}.json`);
  }
}

async function generateLocale(locale) {
  const targetCode = TARGET_LOCALES[locale];
  if (!targetCode) {
    throw new Error(`Unsupported locale: ${locale}`);
  }

  console.log(`\nGenerating seeds for ${locale} (${targetCode})...`);

  const enTranslations = JSON.parse(fs.readFileSync(EN_TRANSLATIONS, 'utf8'));
  const enBlockEditor = JSON.parse(fs.readFileSync(EN_BLOCK_EDITOR, 'utf8'));
  const sourceMap = { ...enTranslations, ...enBlockEditor };

  const cachePath = path.join(LANG_DIR, `.cache-translate-${locale}.json`);
  const translated = await translateMap(sourceMap, targetCode, cachePath);
  const { translations, blockEditor } = splitSeeds(
    translated,
    enTranslations,
    enBlockEditor
  );

  fs.writeFileSync(
    path.join(LANG_DIR, `translations-${locale}.json`),
    JSON.stringify(translations, null, 2) + '\n',
    'utf8'
  );
  fs.writeFileSync(
    path.join(LANG_DIR, `block-editor-${locale}.json`),
    JSON.stringify(blockEditor, null, 2) + '\n',
    'utf8'
  );

  console.log(
    `Wrote translations-${locale}.json (${Object.keys(translations).length}) and block-editor-${locale}.json (${Object.keys(blockEditor).length})`
  );
}

async function main() {
  const arg = process.argv[2];
  if (!arg) {
    console.error('Usage: node scripts/generate-locale-seeds.js <locale|all>');
    process.exit(1);
  }

  writePlurals();

  if (arg === 'all') {
    for (const locale of Object.keys(TARGET_LOCALES)) {
      await generateLocale(locale);
    }
    return;
  }

  if (!TARGET_LOCALES[arg]) {
    console.error(`Unsupported locale: ${arg}`);
    process.exit(1);
  }

  await generateLocale(arg);
}

main().catch((error) => {
  console.error(error);
  process.exit(1);
});
