<?php

declare(strict_types=1);

namespace WasmTodo\App\Resource\Page;

use BEAR\Resource\Annotation\Link;
use BEAR\Resource\ResourceObject;
use WasmTodo\App\Provide\TodoRepository;

class Todos extends ResourceObject
{
    public function __construct(
        private TodoRepository $todos,
    ) {
    }

    #[Link(rel: 'create', href: 'todos', method: 'post', title: 'Add')]
    public function onGet(): static
    {
        $this->body = ['todos' => $this->todos->findAll()];

        return $this;
    }

    public function onPost(string $title): static
    {
        $id = $this->todos->create($title);
        $this->code = 303;
        $this->headers['Location'] = 'todo?id=' . $id;

        return $this;
    }
}
