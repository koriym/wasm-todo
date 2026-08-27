<?php

declare(strict_types=1);

namespace WasmTodo\App\Resource\Page;

use BEAR\Resource\Annotation\Link;
use BEAR\Resource\ResourceObject;
use WasmTodo\App\Provide\TodoRepository;

class Todo extends ResourceObject
{
    public function __construct(
        private TodoRepository $todos,
    ) {
    }

    #[Link(rel: 'todos', href: 'todos', title: 'Back to list')]
    #[Link(rel: 'toggle', href: 'todo-toggle{?id}', method: 'post', title: 'Toggle done')]
    #[Link(rel: 'delete', href: 'todo-delete{?id}', method: 'post', title: 'Delete')]
    public function onGet(int $id): static
    {
        $todo = $this->todos->find($id);
        if ($todo === null) {
            $this->code = 404;
            $this->body = ['message' => 'Not Found'];

            return $this;
        }

        $this->body = [
            'id' => $id,
            'title' => $todo['title'],
            'status' => $todo['done'] ? 'done' : 'pending',
        ];

        return $this;
    }
}
