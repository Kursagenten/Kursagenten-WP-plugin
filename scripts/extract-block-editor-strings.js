/**
 * Extract user-visible strings from taxonomy-grid block editor source.
 */
const fs = require('fs');
const path = require('path');

const file = path.join(__dirname, '..', 'src', 'blocks', 'taxonomy-grid', 'index.js');
const content = fs.readFileSync(file, 'utf8');
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
	while ((match = pattern.exec(content)) !== null) {
		const value = match[1].trim();
		if (/[æøåÆØÅa-zA-Z]/.test(value) && !value.includes('${')) {
			set.add(value);
		}
	}
}

const sorted = [...set].sort((a, b) => a.localeCompare(b, 'nb'));
console.log(sorted.join('\n'));
console.error(`\nTotal: ${sorted.length}`);
