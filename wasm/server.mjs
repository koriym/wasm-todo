import { PhpCgiNode } from 'php-cgi-wasm/PhpCgiNode';
import phar from 'php-wasm-phar';
import sqlite from 'php-wasm-sqlite';
import fs from 'node:fs';
import http from 'node:http';

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

const server = http.createServer(async (req, res) => {
    const chunks = [];
    for await (const chunk of req) {
        chunks.push(chunk);
    }
    const body = Buffer.concat(chunks);

    const phpRes = await php.request({
        url: req.url,
        method: req.method,
        headers: { host: req.headers.host || 'localhost', 'content-type': req.headers['content-type'] || 'application/x-www-form-urlencoded' },
        body: body.length ? new ReadableStream({
            start(c) { c.enqueue(new Uint8Array(body)); c.close(); }
        }) : undefined,
    });

    res.statusCode = phpRes.status;
    for (const [k, v] of phpRes.headers) {
        res.setHeader(k, v);
    }
    res.end(await phpRes.text());
});

const port = Number(process.env.PORT || 8080);
server.listen(port, () => {
    console.log(`listening on http://localhost:${port}`);
});
