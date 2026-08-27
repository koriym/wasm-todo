<?php

declare(strict_types=1);

namespace WasmTodo\App\Module;

use BEAR\Resource\RenderInterface;
use Ray\Di\AbstractModule;
use WasmTodo\App\Provide\HtmlRenderer;
use WasmTodo\App\Provide\TodoRepository;

final class HtmlModule extends AbstractModule
{
    protected function configure(): void
    {
        $this->bind(RenderInterface::class)->to(HtmlRenderer::class);
        $this->bind(TodoRepository::class);
    }
}
