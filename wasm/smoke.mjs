import { PhpCgiNode } from 'php-cgi-wasm/PhpCgiNode';
import phar from 'php-wasm-phar';
import sqlite from 'php-wasm-sqlite';
import fs from 'node:fs';

const PHAR = new URL('../app.phar', import.meta.url).pathname;

const php = new PhpCgiNode({
    version: '8.5',
    docroot: '/',
    prefix: '/',
    sharedLibs: [phar, sqlite],
    ini: 'sys_temp_dir=/tmp\ndisplay_errors=0\n',
    env: {
        CONTEXT: 'prod-html-app',
    },
});

await php.binary;
const bytes = fs.readFileSync(PHAR);
await php.writeFile('/index.php', bytes);

async function req(method, path, body) {
    const res = await php.request({
        url: path,
        method,
        headers: { host: 'localhost', 'content-type': 'application/x-www-form-urlencoded' },
        body: body ? new ReadableStream({
            start(c) { c.enqueue(new TextEncoder().encode(body)); c.close(); }
        }) : undefined,
    });
    const text = await res.text();
    console.log(`${method} ${path}${body ? ' [' + body + ']' : ''} -> ${res.status}${res.headers.get('location') ? ' Location: ' + res.headers.get('location') : ''}`);
    return { res, text };
}

let r = await req('GET', '/todos');
if (!r.text.includes('<form action="todos" method="post">')) throw new Error('create form missing');

r = await req('POST', '/todos', 'title=Buy+milk');
if (r.res.status !== 303 || r.res.headers.get('location') !== 'todo?id=1') throw new Error('create failed');

r = await req('GET', '/todos');
if (!r.text.includes('<a href="todo?id=1">Buy milk</a>')) throw new Error('list link missing');

r = await req('GET', '/todo?id=1');
if (!r.text.includes('status</strong> pending')) throw new Error('detail pending missing');
if (r.text.includes('_method')) throw new Error('detail page still tunnels a method');

r = await req('POST', '/todo/toggle', 'id=1');
if (r.res.status !== 303) throw new Error('toggle failed');

r = await req('GET', '/todo?id=1');
if (!r.text.includes('status</strong> done')) throw new Error('detail done missing');

r = await req('POST', '/todo/delete', 'id=1');
if (r.res.status !== 303) throw new Error('delete failed');

r = await req('GET', '/todos');
if (r.text.includes('Buy milk')) throw new Error('delete did not empty list');

console.log('smoke test passed');
