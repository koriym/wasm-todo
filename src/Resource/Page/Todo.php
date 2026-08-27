<?php

declare(strict_types=1);

namespace WasmTodo\App\Resource\Page;

use BEAR\Resource\ResourceObject;
use WasmTodo\App\Provide\TodoRepository;

class Todo extends ResourceObject
{
    public function __construct(
        private TodoRepository $todos,
    ) {
    }

    public function onGet(int $id): static
    {
        $todo = $this->todos->find($id);
        if ($todo === null) {
            $this->code = 404;
            $this->body = ['message' => 'Not Found'];

            return $this;
        }

        $this->body = [
            'title' => $todo['title'],
            'status' => $todo['done'] ? 'done' : 'pending',
            '_links' => [
                'self' => ['href' => 'todo?id=' . $id],
                'todos' => ['href' => 'todos', 'title' => 'Back to list'],
                'toggle' => ['href' => 'todo?id=' . $id, 'method' => 'put', 'title' => 'Toggle done'],
                'delete' => ['href' => 'todo?id=' . $id, 'method' => 'delete', 'title' => 'Delete'],
            ],
        ];

        return $this;
    }

    public function onPut(int $id): static
    {
        $this->todos->toggle($id);
        $this->code = 303;
        $this->headers['Location'] = 'todo?id=' . $id;

        return $this;
    }

    public function onDelete(int $id): static
    {
        $this->todos->delete($id);
        $this->code = 303;
        $this->headers['Location'] = 'todos';

        return $this;
    }
}
