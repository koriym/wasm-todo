<?php

declare(strict_types=1);

namespace WasmTodo\App\Module;

use BEAR\Package\AbstractAppModule;
use BEAR\QiqModule\QiqModule;

final class HtmlModule extends AbstractAppModule
{
    protected function configure(): void
    {
        $this->install(new QiqModule($this->appMeta->appDir . '/var/qiq/template'));
    }
}
