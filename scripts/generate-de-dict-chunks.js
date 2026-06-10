/**
 * Generate de-dict chunk files (EN key -> DE value) from es-dict key order.
 * Run: node scripts/generate-de-dict-chunks.js
 */
const fs = require('fs');
const path = require('path');

const LANG_DIR = path.join(__dirname, '..', 'lang');
const ES_DICT_DIR = path.join(LANG_DIR, 'es-dict');
const DE_DICT_DIR = path.join(LANG_DIR, 'de-dict');
const MAP_PATH = path.join(LANG_DIR, 'de-en-translations.json');

const PRESERVE_TERMS = [
  'Kursagenten',
  'WordPress',
  'Wordpress',
  'Kursadmin',
  'Kursdesign',
  'HMS',
  'SEO',
  'srcset',
  'Gravatar',
  'Gutenberg',
  'Elementor',
  'Kadence',
  'Trello',
  'Google Maps',
  'Google',
  'LinkedIn',
  'Instagram',
  'Facebook',
  'YouTube',
  'Rank Math',
  'AIOSEO',
  'SEOPress',
  'Cloudflare',
  'Apache',
  'XML-RPC',
  'JSON',
  'AJAX',
  'HTML',
  'CSS',
  'HTTP',
  'ZIP',
  'GUID',
  'OR',
  'Div',
  'Span',
  'Hooks',
  'Shortcodes',
  'Webhook',
  'Webhooks',
  'Provider ID',
  'Provider GUID',
  'CourseCreated',
  'CourseUpdated',
  'Site Reviews',
  'Slim SEO',
  'Open Graph',
  'Twitter Cards',
  'Course schema',
  'ka-meny',
  'ka_course',
  'ka_coursedate',
  'ka_coursecategory',
  'ka_course_location',
  'ka_instructors',
  'kursliste',
  'kurskategorier',
  'kurssteder',
  'instruktorer',
  'kilde=ikon',
  'kilde=ka-bilde',
  'List_type',
  'List type',
  'profile image',
  'KA course',
  'Desktop',
  'Tablet',
  'Mobile',
  'Hover',
  'TOP',
  'BOTTOM',
  'LEFT',
  'RIGHT',
  'H2',
  'H3',
  'H4',
  'H5',
  'H6',
  'P',
  'St',
  'URLs',
  'Plugin',
  'Menu',
  'Menus',
  'Div',
];

function loadEsDictKeysByChunk() {
  const chunks = [];
  for (let i = 1; i <= 7; i++) {
    const chunk = JSON.parse(
      fs.readFileSync(path.join(ES_DICT_DIR, `chunk-${i}.json`), 'utf8')
    );
    chunks.push({ num: i, keys: Object.keys(chunk) });
  }
  return chunks;
}

function main() {
  if (!fs.existsSync(MAP_PATH)) {
    console.error(`Missing translation map: ${MAP_PATH}`);
    console.error('Run: node scripts/build-de-en-translations-map.js first');
    process.exit(1);
  }

  const translations = JSON.parse(fs.readFileSync(MAP_PATH, 'utf8'));
  const chunks = loadEsDictKeysByChunk();
  const missing = [];

  if (!fs.existsSync(DE_DICT_DIR)) {
    fs.mkdirSync(DE_DICT_DIR, { recursive: true });
  }

  for (const { num, keys } of chunks) {
    const chunk = {};
    for (const key of keys) {
      if (Object.prototype.hasOwnProperty.call(translations, key)) {
        chunk[key] = translations[key];
      } else {
        missing.push(key);
        chunk[key] = key;
      }
    }
    fs.writeFileSync(
      path.join(DE_DICT_DIR, `chunk-${num}.json`),
      `${JSON.stringify(chunk, null, 2)}\n`
    );
  }

  console.log(`Wrote de-dict chunks (${Object.keys(translations).length} map entries)`);
  console.log(`Missing translations: ${missing.length}`);
  if (missing.length > 0) {
    console.error('First missing:', missing.slice(0, 5).map((k) => k.slice(0, 80)));
    process.exit(1);
  }
}

main();
