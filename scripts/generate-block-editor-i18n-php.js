/**
 * Generate includes/misc/block-editor-i18n.php from extracted editor strings.
 */
const fs = require('fs');
const path = require('path');

const strings = fs.readFileSync(path.join(__dirname, 'extract-block-editor-strings.js'), 'utf8');
// Re-run extraction inline
const index = fs.readFileSync(path.join(__dirname, '..', 'src', 'blocks', 'taxonomy-grid', 'index.js'), 'utf8');
const set = new Set();
const patterns = [
	/label:\s*'([^']+)'/g,
	/title:\s*'([^']+)'/g,
	/title="([^"]+)"/g,
	/label="([^"]+)"/g,
	/help="([^"]+)"/g,
	/emptyText="([^"]+)"/g,
	/buttonLabel="([^"]+)"/g,
];
for (const pattern of patterns) {
	let match;
	while ((match = pattern.exec(index)) !== null) {
		const value = match[1].trim();
		if (/[æøåÆØÅa-zA-Z]/.test(value) && !value.includes('${')) {
			set.add(value);
		}
	}
}

const extra = [
	'Kursagenten Taxonomi',
	'Vis kurskategorier, kurssteder eller instruktører med preset-stiler.',
	'Vis kurskategorier med preset-stiler',
	'Vis kurssteder med preset-stiler',
	'Vis instruktører med preset-stiler',
	'Start direkte med visuelle stilvalg',
	'mer informasjon',
	'Rammeinnstillinger',
	'Rediger i Element-kort',
	'Bildeformer',
	'Bildestørrelse',
	'Velg region for filtrering. Kan kombineres med stedvalg (\'x eller y\'-logikk).',
	'Region er deaktivert i Synkronisering → Regioner.',
	'Vis kun %s',
	'Skjul %s',
	'Kursagenten',
];

for (const item of extra) set.add(item);

const sorted = [...set].sort((a, b) => a.localeCompare(b, 'nb'));

const phpItems = sorted.map((s) => {
	const esc = s.replace(/\\/g, '\\\\').replace(/'/g, "\\'");
	return `\t\t'${esc}'`;
}).join(',\n');

const php = `<?php
/**
 * Gutenberg block editor i18n for Kursagenten.
 *
 * @package Kursagenten
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Editor UI strings for taxonomy-grid block (passed to JS via wp_localize_script).
 *
 * @return array{strings: array<string, string>}
 */
function kursagenten_get_taxonomy_grid_editor_i18n(): array {
    $keys = [
${phpItems},
    ];

    $strings = [];
    foreach ($keys as $key) {
        $strings[$key] = __($key, 'kursagenten');
    }

    return [
        'strings' => $strings,
    ];
}
`;

const out = path.join(__dirname, '..', 'includes', 'misc', 'block-editor-i18n.php');
fs.writeFileSync(out, php, 'utf8');
console.log(`Wrote ${sorted.length} strings to ${out}`);
