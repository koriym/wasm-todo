{{ setLayout('layout/base') }}
<h2>Todo</h2>
{{ if (isset($message)): }}
<p>{{h $message }}</p>
{{ endif }}
{{ if (isset($id)): }}
<p>{{h $title }}</p>
<p><strong>status</strong> {{h $status }}</p>
<nav>
<a href="todos">Back to list</a>
<form action="todo-toggle" method="post">
<input type="hidden" name="id" value="{{h $id }}">
<button type="submit">Toggle done</button>
</form>
<form action="todo-delete" method="post">
<input type="hidden" name="id" value="{{h $id }}">
<button class="danger" type="submit">Delete</button>
</form>
</nav>
{{ endif }}
