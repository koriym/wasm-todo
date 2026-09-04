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

/**
 * Terminal entry: the same resources, as HAL.
 *
 * Usage:  php bin/cli.php get /todos
 *         php bin/cli.php post '/todos?title=milk'
 *         CONTEXT picks the context (default prod-cli-hal-app, the one cli.phar carries).
 */

require dirname(__DIR__) . '/vendor/autoload.php';

$context = getenv('CONTEXT') ?: 'prod-cli-hal-app';

$app = Injector::getInstance('WasmTodo\App', $context, dirname(__DIR__))->getInstance(AppInterface::class);
assert($app instanceof App);
// match() throws BadRequestException on input it cannot read.
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
