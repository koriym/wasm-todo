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
        $this->assertSame('text/html; charset=utf-8', $ro->headers['Content-Type']);
        $this->assertStringContainsString('<form action="todos" method="post">', $html);
        $this->assertStringContainsString('name="_method" value="post"', $html);
    }

    public function testCreateThenListShowsLink(): void
    {
        $created = $this->resource->post('page://self/todos', ['title' => 'Buy milk']);
        $this->assertSame(303, $created->code);
        $this->assertSame('todo?id=1', $created->headers['Location']);

        $ro = $this->resource->get('page://self/todos');
        $this->assertStringContainsString('<a href="todo?id=1">Buy milk</a>', (string) $ro);
    }

    public function testDetailRendersToggleAndDelete(): void
    {
        $this->resource->post('page://self/todos', ['title' => 'Buy milk']);

        $ro = $this->resource->get('page://self/todo', ['id' => 1]);
        $this->assertSame(200, $ro->code);
        $this->assertStringContainsString('name="_method" value="put"', (string) $ro);
        $this->assertStringContainsString('name="_method" value="delete"', (string) $ro);
        $this->assertStringContainsString('status:</strong> pending', (string) $ro);
    }

    public function testToggleFlipsStatus(): void
    {
        $this->resource->post('page://self/todos', ['title' => 'Buy milk']);
        $this->resource->put('page://self/todo', ['id' => 1]);

        $ro = $this->resource->get('page://self/todo', ['id' => 1]);
        $this->assertStringContainsString('status:</strong> done', (string) $ro);
    }

    public function testDeleteEmptiesList(): void
    {
        $this->resource->post('page://self/todos', ['title' => 'Buy milk']);
        $this->resource->delete('page://self/todo', ['id' => 1]);

        $ro = $this->resource->get('page://self/todos');
        $this->assertStringNotContainsString('Buy milk', (string) $ro);
    }
}
