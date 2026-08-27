<?php

declare(strict_types=1);

namespace WasmTodo\App\Resource\Page\Todo;

use BEAR\Resource\ResourceObject;
use WasmTodo\App\Provide\TodoRepository;

class Toggle extends ResourceObject
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
