<?php

declare(strict_types=1);

namespace WasmTodo\App\Provide;

use BEAR\Resource\RenderInterface;
use BEAR\Resource\ResourceObject;
use Override;

use function htmlspecialchars;
use function is_array;
use function is_scalar;
use function is_string;
use function parse_str;
use function parse_url;
use function sprintf;
use function strtolower;

use const ENT_QUOTES;
use const PHP_URL_PATH;
use const PHP_URL_QUERY;

/**
 * Renders a resource body as HTML, turning HAL `_links` into anchors and forms.
 *
 * A link with no `method` (or `get`) becomes an `<a>`. A link with `post`,
 * `put`, or `delete` becomes a form that carries the method through the
 * `_method` override BEAR's router already understands, plus any query-string
 * parameters from the href as hidden fields.
 */
final class HtmlRenderer implements RenderInterface
{
    #[Override]
    public function render(ResourceObject $ro): string
    {
        $body = $this->normalize($ro->body);
        $html = $this->renderBody($body);
        $ro->view = $html;
        $ro->headers['Content-Type'] = 'text/html; charset=utf-8';

        return $html;
    }

    /** @return array<string, mixed> */
    private function normalize(mixed $body): array
    {
        if (is_scalar($body)) {
            return ['value' => $body];
        }

        if ($body === null) {
            return [];
        }

        if (is_object($body)) {
            return (array) $body;
        }

        return $body;
    }

    /** @param array<string, mixed> $body */
    private function renderBody(array $body): string
    {
        $links = $body['_links'] ?? [];
        $data = $body;
        unset($data['_links'], $data['_embedded']);

        $html = '<!DOCTYPE html><html><head><meta charset="utf-8"><title>Todos</title></head><body>';
        $html .= $this->renderData($data);
        $html .= $this->renderLinks($links);
        $html .= '</body></html>';

        return $html;
    }

    /** @param array<string, mixed> $data */
    private function renderData(array $data): string
    {
        $html = '';
        foreach ($data as $key => $value) {
            $html .= $this->renderField((string) $key, $value);
        }

        return $html;
    }

    private function renderField(string $key, mixed $value): string
    {
        if (is_array($value)) {
            return $this->renderList($key, $value);
        }

        return sprintf('<p><strong>%s:</strong> %s</p>', $this->e($key), $this->e((string) $value));
    }

    /** @param array<mixed> $items */
    private function renderList(string $key, array $items): string
    {
        $html = sprintf('<h2>%s</h2><ul>', $this->e($key));
        foreach ($items as $item) {
            $html .= '<li>' . $this->renderItem($item) . '</li>';
        }

        return $html . '</ul>';
    }

    private function renderItem(mixed $item): string
    {
        if (! is_array($item)) {
            return $this->e((string) $item);
        }

        $links = $item['_links'] ?? [];
        $self = $links['self']['href'] ?? null;
        $title = $item['title'] ?? $item['id'] ?? 'item';
        $done = (bool) ($item['done'] ?? false);

        $label = $this->e((string) $title);
        if ($done) {
            $label = '<s>' . $label . '</s>';
        }

        if (is_string($self)) {
            return sprintf('<a href="%s">%s</a>', $this->e($self), $label);
        }

        return $label;
    }

    /** @param array<string, mixed> $links */
    private function renderLinks(array $links): string
    {
        $html = '<nav>';
        foreach ($links as $rel => $link) {
            if ($rel === 'self' || ! is_array($link)) {
                continue;
            }

            $html .= $this->renderLink((string) $rel, $link);
        }

        return $html . '</nav>';
    }

    /** @param array<string, mixed> $link */
    private function renderLink(string $rel, array $link): string
    {
        $href = (string) ($link['href'] ?? '');
        $method = strtolower((string) ($link['method'] ?? 'get'));
        $label = (string) ($link['title'] ?? $rel);

        if ($method === 'get') {
            return sprintf('<a href="%s">%s</a>', $this->e($href), $this->e($label));
        }

        /** @var array<mixed> $fields */
        $fields = $link['fields'] ?? [];

        return $this->renderForm($href, $method, $label, $fields);
    }

    /** @param array<mixed> $fields */
    private function renderForm(string $href, string $method, string $label, array $fields): string
    {
        $path = (string) parse_url($href, PHP_URL_PATH);
        $query = (string) parse_url($href, PHP_URL_QUERY);
        $params = [];
        parse_str($query, $params);

        $html = sprintf('<form action="%s" method="post">', $this->e($path));
        $html .= sprintf('<input type="hidden" name="_method" value="%s">', $this->e($method));
        foreach ($params as $name => $value) {
            $html .= sprintf('<input type="hidden" name="%s" value="%s">', $this->e((string) $name), $this->e((string) $value));
        }

        foreach ($fields as $field) {
            $html .= sprintf('<input name="%s" placeholder="%s">', $this->e((string) $field), $this->e((string) $field));
        }

        $html .= sprintf('<button type="submit">%s</button></form>', $this->e($label));

        return $html;
    }

    private function e(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}
