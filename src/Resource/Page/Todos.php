<?php

declare(strict_types=1);

namespace WasmTodo\App\Resource\Page;

use BEAR\Resource\ResourceObject;
use WasmTodo\App\Provide\TodoRepository;

use function array_map;

class Todos extends ResourceObject
{
    public function __construct(
        private TodoRepository $todos,
    ) {
    }

    public function onGet(): static
    {
        $list = $this->todos->findAll();
        $this->body = [
            'todos' => array_map(
                static fn (array $todo): array => $todo + [
                    '_links' => ['self' => ['href' => '/todo?id=' . $todo['id']]],
                ],
                $list,
            ),
            '_links' => [
                'self' => ['href' => '/todos'],
                'create' => ['href' => '/todos', 'method' => 'post', 'fields' => ['title'], 'title' => 'Add'],
            ],
        ];

        return $this;
    }

    public function onPost(string $title): static
    {
        $id = $this->todos->create($title);
        $this->code = 303;
        $this->headers['Location'] = '/todo?id=' . $id;

        return $this;
    }
}
