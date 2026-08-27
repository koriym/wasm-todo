<?php

declare(strict_types=1);

namespace WasmTodo\App;

$loader = require dirname(__DIR__) . '/vendor/autoload.php';
$loader->addPsr4(__NAMESPACE__ . '\\', dirname(__DIR__) . '/src');
