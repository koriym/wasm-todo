# wasm-todo

A todo app built with [BEAR.Sunday](https://bearsunday.github.io/) that runs entirely in the browser via [php-wasm](https://github.com/seanmorris/php-wasm). No server, no PHP runtime installed — a single `app.phar` boots inside WebAssembly.

## What it demonstrates

- A BEAR.Sunday application compiled to a single immutable `app.phar`.
- The phar boots in `php-cgi-wasm` and serves HTTP from inside the browser.
- Resources render HTML with real hyperlinks: `_links` become `<a>` and `<form>`.
- State persists in SQLite (`pdo_sqlite`) inside the wasm virtual filesystem.

The point is not the todo app. It is that a PHP developer can ship a working web app as one file, with no JavaScript to write and no server to run.

## Requirements

- PHP 8.5 (to build the phar)
- Composer
- Node.js (to run the wasm host)

## Build

```bash
composer install
composer compile          # runs bin/compile.php, produces app.phar
```

`bin/compile.php` compiles the app for the `prod-html-app` context and packs it into `app.phar`.

## Run in the browser

```bash
cd wasm
npm install
npm start                 # http://localhost:8080/todos
```

`wasm/server.mjs` mounts `app.phar` into the wasm virtual filesystem and serves it through `PhpCgiNode`. Open `http://localhost:8080/todos`.

## Run natively (optional)

The same app runs under PHP's built-in server:

```bash
composer serve            # php -S 127.0.0.1:8080 -t public
```

## Test

```bash
vendor/bin/phpunit        # PHP resource tests
cd wasm && npm run smoke  # end-to-end wasm smoke test
```

## How it works

```
browser ──HTTP──> Node http.Server ──> PhpCgiNode ──> app.phar (BEAR.Sunday)
                    (wasm/server.mjs)   (php-cgi-wasm)   └─ pdo_sqlite
```

- `app.phar` is written to the wasm virtual filesystem as `/index.php`.
- `CONTEXT=prod-html-app` selects the `HtmlModule`, which binds `RenderInterface` to `HtmlRenderer`.
- `HtmlRenderer` turns HAL `_links` into HTML: `get` links become `<a>`, `post`/`put`/`delete` links become `<form>` with a `_method` override field.
- `TodoRepository` stores todos in `/tmp/todo.db` via `pdo_sqlite`.

## Structure

```
src/
  Module/            AppModule, ProdModule, HtmlModule, App
  Provide/           HtmlRenderer, TodoRepository
  Resource/Page/     Todos, Todo
public/index.php     entry point
bin/compile.php      phar build
wasm/                Node host (server.mjs, smoke.mjs)
```

## Notes

- The `phar` and `sqlite` extensions are not in php-cgi-wasm's default build; they are loaded via `php-wasm-phar` and `php-wasm-sqlite` in `sharedLibs`.
- Packing the phar requires native PHP (`phar.readonly=0` is a system-level ini setting); wasm only boots the already-built phar.
- MySQL is not available in browser wasm (no raw TCP sockets). SQLite, PGlite, and Cloudflare D1 are the supported options.
