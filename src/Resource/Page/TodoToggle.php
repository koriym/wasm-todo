<?php

declare(strict_types=1);

namespace WasmTodo\App\Resource\Page;

use BEAR\Resource\ResourceObject;
use WasmTodo\App\Repository\TodoRepository;

class TodoToggle extends ResourceObject
{
    public function __construct(
        private TodoRepository $todos,
    ) {
    }

    public function onPost(int $id): static
    {
        $this->todos->toggle($id);
        $this->code = 303;
        $this->headers['Location'] = 'todo?id=' . $id;
        $this->body = ['id' => $id];

        return $this;
    }
}
