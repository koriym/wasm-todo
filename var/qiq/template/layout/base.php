{{ /* Inlined, not linked: the phar serves every request through PHP and has no route for a static file. */ }}
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Todos</title>
<style>
:root {
    color-scheme: light dark;
    --bg: #f5f5f2;
    --surface: #fdfdfc;
    --ink: #23272d;
    --muted: #71787f;
    --line: #e4e4de;
    --accent: #0c756a;
    --on-accent: #f4fbfa;
    --danger: #a8433b;
}

@media (prefers-color-scheme: dark) {
    :root {
        --bg: #16181a;
        --surface: #1e2124;
        --ink: #e7e9ea;
        --muted: #969ea5;
        --line: #2c3034;
        --accent: #4cc4b2;
        --on-accent: #0b2420;
        --danger: #e18a81;
    }
}

* {
    box-sizing: border-box;
}

body {
    margin: 0;
    background: var(--bg);
    color: var(--ink);
    font-family: system-ui, -apple-system, 'Segoe UI', sans-serif;
    line-height: 1.6;
    -webkit-font-smoothing: antialiased;
}

main {
    max-width: 33rem;
    margin: 0 auto;
    padding: 4rem 1.25rem 5rem;
}

::selection {
    background: color-mix(in srgb, var(--accent) 25%, transparent);
}

h2 {
    margin: 0 0 1.5rem;
    font-size: 1.6rem;
    font-weight: 650;
    letter-spacing: -0.02em;
    text-transform: capitalize;
}

h2::after {
    content: '';
    display: block;
    width: 2.25rem;
    height: 0.2rem;
    margin-top: 0.5rem;
    border-radius: 0.1rem;
    background: var(--accent);
}

ul {
    margin: 0;
    padding: 0;
    list-style: none;
    background: var(--surface);
    border: 1px solid var(--line);
    border-radius: 0.75rem;
    overflow: hidden;
}

li + li {
    border-top: 1px solid var(--line);
}

li a {
    display: block;
    padding: 0.75rem 1rem;
}

li a:hover {
    background: color-mix(in srgb, var(--accent) 6%, transparent);
}

ul:empty::before {
    content: 'Nothing here yet — add your first todo below.';
    display: block;
    padding: 1.5rem 1rem;
    color: var(--muted);
}

li a.done {
    color: var(--muted);
    text-decoration-line: line-through;
}

p {
    margin: 0 0 1.25rem;
}

p strong {
    display: block;
    margin-bottom: 0.1rem;
    font-size: 0.72rem;
    font-weight: 600;
    letter-spacing: 0.09em;
    text-transform: uppercase;
    color: var(--muted);
}

p:first-of-type {
    font-size: 1.35rem;
    font-weight: 600;
    letter-spacing: -0.01em;
    line-height: 1.35;
}

a {
    color: var(--accent);
    text-decoration: underline;
    text-decoration-thickness: 1px;
    text-decoration-color: color-mix(in srgb, var(--accent) 40%, transparent);
    text-underline-offset: 0.2em;
}

a:hover {
    text-decoration-color: currentColor;
}

a:focus-visible,
button:focus-visible,
input:focus-visible {
    outline: 2px solid var(--accent);
    outline-offset: 2px;
}

nav {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 0.75rem;
    margin-top: 2rem;
    padding-top: 1.5rem;
    border-top: 1px solid var(--line);
}

nav form {
    display: flex;
    gap: 0.6rem;
}

nav form:has(input:not([type=hidden])) {
    flex: 1 1 16rem;
}

input:not([type=hidden]) {
    flex: 1;
    min-width: 8rem;
    padding: 0.55rem 0.9rem;
    font: inherit;
    color: inherit;
    background: var(--surface);
    border: 1px solid var(--line);
    border-radius: 0.5rem;
}

input::placeholder {
    color: var(--muted);
    text-transform: capitalize;
}

button {
    padding: 0.55rem 1.2rem;
    font: inherit;
    font-weight: 600;
    color: var(--on-accent);
    background: var(--accent);
    border: 0;
    border-radius: 0.5rem;
    cursor: pointer;
}

button:hover {
    background: color-mix(in srgb, var(--accent) 88%, var(--ink));
}

button:active {
    transform: translateY(1px);
}

button.danger {
    color: var(--danger);
    background: transparent;
    box-shadow: inset 0 0 0 1px color-mix(in srgb, var(--danger) 45%, transparent);
}

button.danger:hover {
    background: color-mix(in srgb, var(--danger) 8%, transparent);
}

footer {
    max-width: 33rem;
    margin: -3rem auto 0;
    padding: 1rem 1.25rem 0;
    border-top: 1px solid var(--line);
    text-align: center;
    font-size: 0.8rem;
    color: var(--muted);
}

footer a {
    color: var(--muted);
}
</style>
</head>
<body>
<main>
{{= getContent() }}
</main>
<footer><a href="https://bearsunday.github.io/" target="_blank" rel="noopener">BEAR.Sunday</a> running in your browser via WebAssembly &mdash; <a href="https://github.com/koriym/wasm-todo" target="_blank" rel="noopener">source</a></footer>
</body>
</html>
