<?php

declare(strict_types=1);

namespace WasmTodo\App;

use BEAR\Package\Injector;
use BEAR\Resource\Method;
use BEAR\Sunday\Extension\Application\AppInterface;
use BEAR\Sunday\Extension\Router\NullMatch;
use WasmTodo\App\Module\App;

use function dirname;
use function getenv;
use function header;
use function parse_url;
use function str_starts_with;
use function strlen;
use function substr;

use const PHP_URL_PATH;

require dirname(__DIR__) . '/vendor/autoload.php';

$context = getenv('CONTEXT') ?: (PHP_SAPI === 'cli' ? 'cli-hal-app' : 'hal-app');

// GitHub Pages serves the app under a subpath (e.g. /wasm-todo). Strip it so
// BEAR's router sees the resource path, not the deployment prefix.
$basePath = getenv('BASE_PATH') ?: '';
if ($basePath !== '' && ($_SERVER['REQUEST_URI'] === $basePath || str_starts_with($_SERVER['REQUEST_URI'], $basePath . '/'))) {
    $_SERVER['REQUEST_URI'] = substr($_SERVER['REQUEST_URI'], strlen($basePath));
}

// The root has no resource; send it to the todo list.
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
if ($path === '/' || $path === '') {
    header('Location: todos', true, 303);
    exit;
}

$app = Injector::getInstance('WasmTodo\App', $context, dirname(__DIR__))->getInstance(AppInterface::class);
assert($app instanceof App);
// match() throws BadRequestException on client input it cannot read.
$request = new NullMatch();
try {
    $request = $app->router->match($GLOBALS, $_SERVER);
    $app->resource->newRequest(
        Method::from($request->method), $request->path, $request->query
    )()->transfer($app->responder, $_SERVER);
    exit(0);
} catch (\Throwable $e) {
    $app->throwableHandler->handle($e, $request)->transfer();
    exit(1);
}
