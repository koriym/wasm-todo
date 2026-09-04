# wasm-todo

A todo app built with [BEAR.Sunday](https://bearsunday.github.io/) that runs entirely in the browser via [php-wasm](https://github.com/seanmorris/php-wasm). No server, no PHP runtime installed — a single `app.phar` boots inside WebAssembly, served by a service worker.

## What it demonstrates

- A BEAR.Sunday application compiled to a single immutable `app.phar`.
- The phar boots in `php-cgi-wasm` inside a service worker and serves HTTP from the browser.
- Resources render HTML through Qiq templates; `#[Link]` declares the affordances, the templates own the markup.
- State persists in SQLite (`pdo_sqlite`) inside the wasm virtual filesystem, backed by IndexedDB.
- The same resources answer as HAL from the terminal (`cli.phar`), and with a static PHP they are one executable.

The point is not the todo app. It is that a PHP developer can ship a working web app as one file, with no JavaScript to write and no server to run.

## Requirements

- PHP 8.5 (to build the phar)
- Composer
- Node.js (to bundle the wasm host)

## Build

```bash
composer install
composer compile          # runs bin/compile.php, produces app.phar and cli.phar
```

`bin/compile.php` compiles the app twice and packs each build into its own archive, since one archive carries one build: `prod-html-app` into `app.phar` for the browser, `prod-cli-hal-app` into `cli.phar` for the terminal.

## Run in the browser

```bash
cd wasm
npm install
npm run build             # bundles sw.js and copies assets into dist/
npx serve dist            # or any static server
```

Open the served URL. `index.html` registers the service worker, which mounts `app.phar` into the wasm virtual filesystem and serves every request through PHP.

## Run natively (optional)

The same app runs under PHP's built-in server:

```bash
composer serve            # php -S 127.0.0.1:8080 -t public
```

## Run from the terminal

`cli.phar` carries the same resources with the CLI router and HAL rendering; its entry is `bin/cli.php`.

```bash
php cli.phar get /todos
php cli.phar post '/todos?title=milk'
php cli.phar get '/todo?id=1'
```

With `micro.sfx` from [static-php-cli](https://static-php.dev), PHP and the archive become one executable that runs on a machine with no PHP installed ([manual](https://bearsunday.github.io/manuals/1.0/en/phar.html#one-executable)):

```bash
cat micro.sfx cli.phar > todo && chmod +x todo
./todo get /todos
```

## Test

```bash
vendor/bin/phpunit        # PHP resource tests
cd wasm && npm run smoke  # end-to-end wasm smoke test (Node host)
```

## How it works

```
browser ──fetch──> service worker ──> PhpCgiWorker ──> app.phar (BEAR.Sunday)
                    (wasm/sw.js)       (php-cgi-wasm)   └─ pdo_sqlite
```

- `app.phar` is written to the wasm virtual filesystem as `/index.php` during the worker's install event.
- `CONTEXT=prod-html-app` selects `HtmlModule`, which installs `QiqModule` over `var/qiq/template`.
- `QiqProdModule` compiles the templates at build time into `var/build/prod-html-app/qiq`, and production renders from there — nothing writes at runtime, so the read-only phar serves them as they were packed.
- Page resources implement `onGet` and `onPost` only. State transitions are their own resources (`todo-toggle`, `todo-delete`), so no form needs a `_method` override.
- `TodoRepository` stores todos in `/persist/todo.db` via `pdo_sqlite`; `/persist` is the wasm filesystem mount backed by IndexedDB, so state survives reloads.
- The worker derives its base path from its own URL, so the same bundle works at any subpath (e.g. `/wasm-todo/` on GitHub Pages).

## Structure

```
src/
  Module/            AppModule, ProdModule, HtmlModule, App
  Repository/        TodoRepository
  Resource/Page/     Todos, Todo, TodoToggle, TodoDelete
var/qiq/template/    Page/Todos, Page/Todo, Page/TodoToggle, Page/TodoDelete, layout/base
public/index.php     browser entry point
bin/cli.php          terminal entry point
bin/compile.php      phar build (app.phar and cli.phar)
wasm/
  sw.js              service worker source
  index.html         registration page
  build.mjs          esbuild bundle + asset copy
  server.mjs         Node host (alternative to the service worker)
  smoke.mjs          end-to-end smoke test
```

## Notes

- The `phar` and `sqlite` extensions are not in php-cgi-wasm's default build; they are loaded via `php-wasm-phar` and `php-wasm-sqlite` in `sharedLibs`.
- Packing the phar requires native PHP (`phar.readonly=0` is a system-level ini setting); wasm only boots the already-built phar.
- MySQL is not available in browser wasm (no raw TCP sockets). SQLite, PGlite, and Cloudflare D1 are the supported options.
