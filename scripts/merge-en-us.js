/**
 * Backwards-compatible wrapper for English locale merge.
 * Run: node scripts/merge-en-us.js
 */
const path = require('path');

require('child_process').execFileSync(process.execPath, ['scripts/merge-locale.js', 'en_US'], {
  stdio: 'inherit',
  cwd: path.join(__dirname, '..'),
});
