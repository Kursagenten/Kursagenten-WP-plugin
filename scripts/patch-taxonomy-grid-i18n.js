/**
 * Patch taxonomy-grid index.js to use t() helper for editor strings.
 */
const fs = require('fs');
const path = require('path');

const file = path.join(__dirname, '..', 'src', 'blocks', 'taxonomy-grid', 'index.js');
let content = fs.readFileSync(file, 'utf8');

if (!content.includes('function t( text )')) {
	const helper = `
const blockEditorStrings = window?.kursagentenTaxonomyGridEditorI18n?.strings || {};
function t( text ) {
	return blockEditorStrings[ text ] || text;
}
function translateOptions( options ) {
	return options.map( ( option ) => ( {
		...option,
		label: t( option.label ),
	} ) );
}
`;
	content = content.replace(
		"import './editor.css';\n",
		`import './editor.css';\n${helper}\n`
	);
}

// Rename option constants to *_RAW and add translated getters.
const optionConsts = [
	'SOURCE_OPTIONS',
	'FILTER_OPTIONS',
	'CATEGORY_IMAGE_SOURCE_OPTIONS',
	'NAME_MODE_OPTIONS',
	'INSTRUCTOR_IMAGE_SOURCE_OPTIONS',
	'IMAGE_ASPECT_OPTIONS',
	'IMAGE_RESOLUTION_OPTIONS',
	'SHADOW_OPTIONS',
	'TITLE_TAG_OPTIONS',
	'FONT_WEIGHT_OPTIONS',
	'IMAGE_BORDER_STYLES',
	'RESPONSIVE_TABS',
	'BORDER_STATE_TABS',
];

for (const name of optionConsts) {
	content = content.replace(
		new RegExp(`const ${name} = `),
		`const ${name}_RAW = `
	);
	if (!content.includes(`const ${name} = translateOptions`)) {
		content = content.replace(
			new RegExp(`const ${name}_RAW = `),
			`const ${name}_RAW = `
		);
		const rawMatch = content.match(new RegExp(`const ${name}_RAW = ([\\s\\S]*?);\\n`));
		if (rawMatch) {
			const insert = `const ${name} = translateOptions( ${name}_RAW );\n`;
			const idx = content.indexOf(rawMatch[0]) + rawMatch[0].length;
			content = content.slice(0, idx) + insert + content.slice(idx);
		}
	}
}

// PRESETS special case
content = content.replace('const PRESETS = ', 'const PRESETS_RAW = ');
if (!content.includes('const PRESETS = PRESETS_RAW.map')) {
	content = content.replace(
		/const PRESETS_RAW = (\[[\s\S]*?\]);/,
		(match) => `${match}\nconst PRESETS = PRESETS_RAW.map( ( preset ) => ( { ...preset, label: t( preset.label ) } ) );`
	);
}

// REGION_OPTIONS uses spread - patch manually
content = content.replace(
	/const REGION_OPTIONS = \[/,
	'const REGION_OPTIONS_RAW = ['
);
if (!content.includes('const REGION_OPTIONS = translateOptions')) {
	content = content.replace(
		/const REGION_OPTIONS_RAW = (\[[\s\S]*?\]);/,
		(match) => `${match}\nconst REGION_OPTIONS = translateOptions( REGION_OPTIONS_RAW );`
	);
}

// JSX / prop string replacements
content = content.replace(/title="([^"]+)"/g, (m, s) => `title={ t( '${s.replace(/'/g, "\\'")}' ) }`);
content = content.replace(/label="([^"]+)"/g, (m, s) => `label={ t( '${s.replace(/'/g, "\\'")}' ) }`);
content = content.replace(/help="([^"]+)"/g, (m, s) => `help={ t( '${s.replace(/'/g, "\\'")}' ) }`);
content = content.replace(/emptyText="([^"]+)"/g, (m, s) => `emptyText={ t( '${s.replace(/'/g, "\\'")}' ) }`);
content = content.replace(/buttonLabel="([^"]+)"/g, (m, s) => `buttonLabel={ t( '${s.replace(/'/g, "\\'")}' ) }`);

// Inline option arrays with label keys
content = content.replace(
	/\{ label: '([^']+)', value: '([^']+)' \}/g,
	(_, label, value) => `{ label: t( '${label}' ), value: '${value}' }`
);

// SETTINGS_TABS inside edit function
content = content.replace(
	/\{ name: 'general', title: 'Generelt' \}/,
	"{ name: 'general', title: t( 'Generelt' ) }"
);
content = content.replace(
	/\{ name: 'adjustments', title: 'Justeringer' \}/,
	"{ name: 'adjustments', title: t( 'Justeringer' ) }"
);

// registerBlockType title/description
content = content.replace(
	/registerBlockType\( metadata\.name, \{/,
	`registerBlockType( metadata.name, {
\ttitle: t( 'Kursagenten Taxonomi' ),
\tdescription: t( 'Vis kurskategorier, kurssteder eller instruktører med preset-stiler.' ),`
);

// Variations
const variations = [
	['Kurskategorier', 'Vis kurskategorier med preset-stiler'],
	['Kurssteder', 'Vis kurssteder med preset-stiler'],
	['Instruktører', 'Vis instruktører med preset-stiler'],
	['Preset-stiler', 'Start direkte med visuelle stilvalg'],
];
for (const [title, desc] of variations) {
	content = content.replace(
		`title: '${title}',\n\tdescription: '${desc}'`,
		`title: t( '${title}' ),\n\tdescription: t( '${desc}' )`
	);
}

// mer informasjon button text
content = content.replace(
	/>\s*mer informasjon\s*</,
	`>{ t( 'mer informasjon' ) }<`
);

// Modal title
content = content.replace(
	/Mer informasjon om dataflyt fra Kursagenten/,
	"{ t( 'Mer informasjon om dataflyt fra Kursagenten' ) }"
);

fs.writeFileSync(file, content, 'utf8');
console.log('Patched', file);
