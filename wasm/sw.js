import { PhpCgiWebBase } from 'php-cgi-wasm/PhpCgiWebBase.mjs';
import Php85CgiWorker from 'php-cgi-wasm/php8.5-cgi-worker.mjs';
import phar from 'php-wasm-phar';
import sqlite from 'php-wasm-sqlite';

// Only the 8.5 runtime is needed; importing the full PhpCgiWorker would pull
// in all six versioned runtimes.
class PhpCgiWorker85 extends PhpCgiWebBase {
    constructor(args = {}) {
        super(Promise.resolve({ default: Php85CgiWorker }), { version: '8.5', ...args });
    }
}

// Derive the deployment base path from this worker's own URL, so the same
// bundle works at any subpath (e.g. /wasm-todo/ on GitHub Pages).
const basePath = self.location.pathname.replace(/\/sw\.js$/, '');
const prefix = basePath || '/';
const at = p => (prefix === '/' ? '' : prefix) + p;

const php = new PhpCgiWorker85({
    docroot: '/',
    prefix,
    exclude: [at('/index.html'), at('/app.phar')],
    sharedLibs: [phar, sqlite],
    // A service worker process is disposable: a fetch event may wake a fresh
    // one whose filesystem is empty. Preloading with the binary repopulates
    // /index.php on every start, and request() awaits the binary, so no
    // request can run before the phar is there.
    files: [{ parent: '/', name: 'index.php', url: 'app.phar' }],
    ini: 'sys_temp_dir=/tmp\ndisplay_errors=0\n',
    env: {
        CONTEXT: 'prod-html-app',
        BASE_PATH: basePath,
        TODO_DB: '/persist/todo.db',
    },
});

self.addEventListener('install', event => event.waitUntil(self.skipWaiting()));
self.addEventListener('activate', event => event.waitUntil(self.clients.claim()));
self.addEventListener('fetch', event => php.handleFetchEvent(event));
self.addEventListener('message', event => php.handleMessageEvent(event));
