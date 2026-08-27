<?php

declare(strict_types=1);

namespace WasmTodo\App\Module;

use BEAR\Package\PackageModule;
use Ray\Di\AbstractModule;

class AppModule extends AbstractModule
{
    protected function configure(): void
    {
        $this->install(new PackageModule);
    }
}
