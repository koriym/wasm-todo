import { build } from 'esbuild';
import { cpSync, mkdirSync, readFileSync, rmSync } from 'node:fs';
import { dirname, join } from 'node:path';
import { fileURLToPath } from 'node:url';

const here = dirname(fileURLToPath(import.meta.url));
const dist = join(here, 'dist');

rmSync(dist, { recursive: true, force: true });
mkdirSync(dist, { recursive: true });

await build({
    entryPoints: [join(here, 'sw.js')],
    bundle: true,
    format: 'esm',
    outfile: join(dist, 'sw.js'),
    target: ['es2022'],
});

// The 8.5 worker runtime references its wasm by content hash; resolve it from
// the module source rather than hardcoding.
const workerSrc = readFileSync(join(here, 'node_modules/php-cgi-wasm/php8.5-cgi-worker.mjs'), 'utf8');
const wasmName = workerSrc.match(/[a-f0-9]{40}\.wasm/)[0];

const copies = [
    [join(here, 'index.html'), join(dist, 'index.html')],
    // A static host has no file for an app route, so the same bootstrap answers
    // its 404 and hands the URL to the worker.
    [join(here, 'index.html'), join(dist, '404.html')],
    [join(here, '..', 'app.phar'), join(dist, 'app.phar')],
    [join(here, 'node_modules/php-cgi-wasm', wasmName), join(dist, wasmName)],
    [join(here, 'node_modules/php-wasm-phar/php8.5-phar.so'), join(dist, 'php8.5-phar.so')],
    [join(here, 'node_modules/php-wasm-sqlite/php8.5-sqlite.so'), join(dist, 'php8.5-sqlite.so')],
    [join(here, 'node_modules/php-wasm-sqlite/php8.5-pdo-sqlite.so'), join(dist, 'php8.5-pdo-sqlite.so')],
    [join(here, 'node_modules/php-wasm-sqlite/libsqlite3.so'), join(dist, 'libsqlite3.so')],
];

for (const [from, to] of copies) {
    cpSync(from, to);
}

console.log('built dist/');
