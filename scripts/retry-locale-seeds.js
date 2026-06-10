/**
 * Retry failed locale translations (values still identical to English).
 *
 * Usage:
 *   node scripts/retry-locale-seeds.js pl_PL
 *   node scripts/retry-locale-seeds.js all
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

const BATCH_SIZE = 2;
const DELAY_MS = 900;
const MAX_RETRIES = 8;

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
      const translated = restoreTerms(restorePlaceholders(result.text, tokens), terms);
      if (translated && translated !== text) {
        return translated;
      }
      throw new Error('Translation unchanged');
    } catch (error) {
      if (attempt === MAX_RETRIES) {
        console.warn(`  skip: ${text.slice(0, 50)}... (${error.message})`);
        return text;
      }
      await sleep(DELAY_MS * attempt * 3);
    }
  }
  return text;
}

function loadEnglish() {
  return {
    ...JSON.parse(fs.readFileSync(EN_TRANSLATIONS, 'utf8')),
    ...JSON.parse(fs.readFileSync(EN_BLOCK_EDITOR, 'utf8')),
  };
}

function loadLocale(locale) {
  const translationsPath = path.join(LANG_DIR, `translations-${locale}.json`);
  const blockEditorPath = path.join(LANG_DIR, `block-editor-${locale}.json`);
  if (!fs.existsSync(translationsPath)) {
    return null;
  }
  return {
    translations: JSON.parse(fs.readFileSync(translationsPath, 'utf8')),
    blockEditor: fs.existsSync(blockEditorPath)
      ? JSON.parse(fs.readFileSync(blockEditorPath, 'utf8'))
      : {},
  };
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

function writeLocaleFiles(locale, fullMap) {
  const enTranslations = JSON.parse(fs.readFileSync(EN_TRANSLATIONS, 'utf8'));
  const enBlockEditor = JSON.parse(fs.readFileSync(EN_BLOCK_EDITOR, 'utf8'));
  const { translations, blockEditor } = splitSeeds(
    fullMap,
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
  fs.writeFileSync(
    path.join(LANG_DIR, `.cache-translate-${locale}.json`),
    JSON.stringify(fullMap, null, 2) + '\n',
    'utf8'
  );
}

async function retryLocale(locale) {
  const targetCode = TARGET_LOCALES[locale];
  const english = loadEnglish();
  const localeData = loadLocale(locale);

  if (!localeData) {
    console.log(`Skipping ${locale}: no translation files yet.`);
    return;
  }

  const current = { ...localeData.translations, ...localeData.blockEditor };
  const pendingKeys = Object.keys(english).filter(
    (key) => current[key] === english[key]
  );

  console.log(`\nRetry ${locale}: ${pendingKeys.length} English fallbacks`);

  if (pendingKeys.length === 0) {
    return;
  }

  let fixed = 0;
  for (let index = 0; index < pendingKeys.length; index += BATCH_SIZE) {
    const batchKeys = pendingKeys.slice(index, index + BATCH_SIZE);
    const translated = await Promise.all(
      batchKeys.map((key) => translateText(english[key], targetCode))
    );

    batchKeys.forEach((key, batchIndex) => {
      const value = translated[batchIndex];
      current[key] = value;
      if (value !== english[key]) {
        fixed++;
      }
    });

    if ((index + BATCH_SIZE) % 20 === 0 || index + BATCH_SIZE >= pendingKeys.length) {
      writeLocaleFiles(locale, current);
      const remaining = pendingKeys.length - Math.min(index + BATCH_SIZE, pendingKeys.length);
      console.log(`  progress ${Math.min(index + BATCH_SIZE, pendingKeys.length)}/${pendingKeys.length}, fixed ${fixed}, remaining ${remaining}`);
    }

    await sleep(DELAY_MS);
  }

  writeLocaleFiles(locale, current);
  const stillEnglish = Object.keys(english).filter((key) => current[key] === english[key]).length;
  console.log(`Done ${locale}: fixed ${fixed}, still English: ${stillEnglish}`);
}

async function main() {
  const arg = process.argv[2];
  if (!arg) {
    console.error('Usage: node scripts/retry-locale-seeds.js <locale|all>');
    process.exit(1);
  }

  const locales =
    arg === 'all' ? Object.keys(TARGET_LOCALES) : [arg];

  for (const locale of locales) {
    if (!TARGET_LOCALES[locale]) {
      console.error(`Unsupported locale: ${locale}`);
      process.exit(1);
    }
    await retryLocale(locale);
  }
}

main().catch((error) => {
  console.error(error);
  process.exit(1);
});
