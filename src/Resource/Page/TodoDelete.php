<?php

declare(strict_types=1);

namespace WasmTodo\App\Resource\Page;

use BEAR\Resource\ResourceObject;
use WasmTodo\App\Repository\TodoRepository;

class TodoDelete extends ResourceObject
{
    public function __construct(
        private TodoRepository $todos,
    ) {
    }

    public function onPost(int $id): static
    {
        $this->todos->delete($id);
        $this->code = 303;
        $this->headers['Location'] = 'todos';

        return $this;
    }
}
