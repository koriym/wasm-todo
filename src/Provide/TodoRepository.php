<?php

declare(strict_types=1);

namespace WasmTodo\App\Provide;

use PDO;

use function array_map;
use function getenv;

final class TodoRepository
{
    private PDO $pdo;

    public function __construct()
    {
        $db = getenv('TODO_DB') ?: '/tmp/todo.db';
        $this->pdo = new PDO('sqlite:' . $db);
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->pdo->exec('CREATE TABLE IF NOT EXISTS todos (id INTEGER PRIMARY KEY AUTOINCREMENT, title TEXT NOT NULL, done INTEGER NOT NULL DEFAULT 0)');
    }

    /** @return list<array{id: int, title: string, done: int}> */
    public function findAll(): array
    {
        $rows = $this->pdo->query('SELECT id, title, done FROM todos ORDER BY id')->fetchAll(PDO::FETCH_ASSOC);

        return array_map(self::hydrate(...), $rows);
    }

    /** @return array{id: int, title: string, done: int}|null */
    public function find(int $id): array|null
    {
        $stmt = $this->pdo->prepare('SELECT id, title, done FROM todos WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row === false ? null : self::hydrate($row);
    }

    public function create(string $title): int
    {
        $stmt = $this->pdo->prepare('INSERT INTO todos (title) VALUES (?)');
        $stmt->execute([$title]);

        return (int) $this->pdo->lastInsertId();
    }

    public function toggle(int $id): void
    {
        $stmt = $this->pdo->prepare('UPDATE todos SET done = 1 - done WHERE id = ?');
        $stmt->execute([$id]);
    }

    public function delete(int $id): void
    {
        $stmt = $this->pdo->prepare('DELETE FROM todos WHERE id = ?');
        $stmt->execute([$id]);
    }

    /** @param array<string, mixed> $row */
    private static function hydrate(array $row): array
    {
        return [
            'id' => (int) $row['id'],
            'title' => (string) $row['title'],
            'done' => (int) $row['done'],
        ];
    }
}
