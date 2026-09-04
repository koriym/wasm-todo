<?php

declare(strict_types=1);

/**
 * Compile the app, then pack it twice: cli.phar for the terminal, app.phar for the browser.
 *
 * Usage:  php bin/compile.php
 */

use BEAR\Package\Compiler;

require dirname(__DIR__) . '/vendor/autoload.php';

ini_set('memory_limit', '-1');

$appDir = dirname(__DIR__);

// One archive carries one build, so each context is packed on its own.
// phar() always writes app.phar: the browser build comes last, so that name stays its own.
$builds = [
    ['prod-cli-hal-app', 'bin/cli.php', 'cli.phar'],
    ['prod-html-app', 'public/index.php', 'app.phar'],
];

foreach ($builds as [$context, $entry, $archive]) {
    $compiler = new Compiler('WasmTodo\App', $context, $appDir);
    $code = $compiler();
    if ($code === 0) {
        $code = $compiler->phar($entry);
    }

    if ($code !== 0) {
        exit($code);
    }

    if ($archive !== 'app.phar' && ! rename($appDir . '/app.phar', $appDir . '/' . $archive)) {
        exit(1);
    }
}

exit(0);
