<h1>User Detail</h1>

<p>ID: {{ $user->id }}</p>
<p>Name: {{ $user->name }}</p>
<p>Email: {{ $user->email }}</p>

<p>
  <a href="/users/{{ $user->id }}/edit">Edit</a> |
  <a href="/users">Back</a>
</p>