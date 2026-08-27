<?php

declare(strict_types=1);

namespace WasmTodo\App\Resource\Page;

use BEAR\Package\Injector;
use BEAR\Resource\ResourceInterface;
use PHPUnit\Framework\TestCase;

use function dirname;
use function file_exists;
use function unlink;

class TodosTest extends TestCase
{
    private ResourceInterface $resource;

    protected function setUp(): void
    {
        parent::setUp();
        if (file_exists('/tmp/todo.db')) {
            unlink('/tmp/todo.db');
        }

        $injector = Injector::getInstance('WasmTodo\App', 'html-app', dirname(__DIR__, 3));
        $this->resource = $injector->getInstance(ResourceInterface::class);
    }

    public function testEmptyListRendersCreateForm(): void
    {
        $ro = $this->resource->get('page://self/todos');
        $this->assertSame(200, $ro->code);
        $html = (string) $ro;
        $this->assertStringContainsString('<form action="todos" method="post">', $html);
        $this->assertStringContainsString('<input name="title" placeholder="title">', $html);
    }

    public function testCreateThenListShowsLink(): void
    {
        $created = $this->resource->post('page://self/todos', ['title' => 'Buy milk']);
        $this->assertSame(303, $created->code);
        $this->assertSame('todo?id=1', $created->headers['Location']);

        $ro = $this->resource->get('page://self/todos');
        $this->assertStringContainsString('<a href="todo?id=1">Buy milk</a>', (string) $ro);
    }

    public function testDetailRendersToggleAndDeleteForms(): void
    {
        $this->resource->post('page://self/todos', ['title' => 'Buy milk']);

        $ro = $this->resource->get('page://self/todo', ['id' => 1]);
        $this->assertSame(200, $ro->code);
        $html = (string) $ro;
        $this->assertStringContainsString('<form action="todo-toggle" method="post">', $html);
        $this->assertStringContainsString('<form action="todo-delete" method="post">', $html);
        $this->assertStringContainsString('status</strong> pending', $html);
    }

    public function testNoMethodOverrideInAnyPage(): void
    {
        $this->resource->post('page://self/todos', ['title' => 'Buy milk']);

        $this->assertStringNotContainsString('_method', (string) $this->resource->get('page://self/todos'));
        $this->assertStringNotContainsString('_method', (string) $this->resource->get('page://self/todo', ['id' => 1]));
    }

    /**
     * A 303 carries no representation, yet its page template still runs.
     *
     * @see \BEAR\Sunday\Provide\Transfer\HttpResponder::getOutput() renders whatever the code
     */
    public function testEveryRedirectRendersItsPage(): void
    {
        $created = $this->resource->post('page://self/todos', ['title' => 'Buy milk']);
        $this->assertStringContainsString('<h2>Todos</h2>', (string) $created);

        $toggled = $this->resource->post('page://self/todo-toggle', ['id' => 1]);
        $this->assertStringContainsString('<a href="todo?id=1">Back to the todo</a>', (string) $toggled);

        $deleted = $this->resource->post('page://self/todo-delete', ['id' => 1]);
        $this->assertStringContainsString('<a href="todos">Back to list</a>', (string) $deleted);
    }

    public function testToggleFlipsStatus(): void
    {
        $this->resource->post('page://self/todos', ['title' => 'Buy milk']);
        $toggled = $this->resource->post('page://self/todo-toggle', ['id' => 1]);
        $this->assertSame(303, $toggled->code);
        $this->assertSame('todo?id=1', $toggled->headers['Location']);

        $ro = $this->resource->get('page://self/todo', ['id' => 1]);
        $this->assertStringContainsString('status</strong> done', (string) $ro);
    }

    public function testDeleteEmptiesList(): void
    {
        $this->resource->post('page://self/todos', ['title' => 'Buy milk']);
        $deleted = $this->resource->post('page://self/todo-delete', ['id' => 1]);
        $this->assertSame(303, $deleted->code);
        $this->assertSame('todos', $deleted->headers['Location']);

        $ro = $this->resource->get('page://self/todos');
        $this->assertStringNotContainsString('Buy milk', (string) $ro);
    }
}
