/**
 * Backwards-compatible wrapper – compiles all locale .po files.
 * Run: node scripts/compile-mo.js
 */
const path = require('path');

require('child_process').execFileSync(process.execPath, ['scripts/compile-all-mo.js'], {
  stdio: 'inherit',
  cwd: path.join(__dirname, '..'),
});
