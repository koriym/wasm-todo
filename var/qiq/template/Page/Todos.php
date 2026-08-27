{{ setLayout('layout/base') }}
<h2>Todos</h2>
<ul>
{{ foreach ($todos ?? [] as $todo): }}
<li><a href="todo?id={{h $todo['id'] }}"{{= $todo['done'] ? ' class="done"' : '' }}>{{h $todo['title'] }}</a></li>
{{ endforeach }}
</ul>
<nav>
<form action="todos" method="post">
<input name="title" placeholder="title">
<button type="submit">Add</button>
</form>
</nav>
