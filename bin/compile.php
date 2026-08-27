<?php

declare(strict_types=1);

/**
 * Compile the app, then pack it into app.phar.
 *
 * Usage:  php bin/compile.php
 *         CONTEXT picks the context (default prod-html-app).
 */

use BEAR\Package\Compiler;

require dirname(__DIR__) . '/vendor/autoload.php';

ini_set('memory_limit', '-1');

$context = getenv('CONTEXT') ?: 'prod-html-app';

$compiler = new Compiler('WasmTodo\App', $context, dirname(__DIR__));
$code = $compiler();
exit($code === 0 ? $compiler->phar() : $code);
