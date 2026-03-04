<h1>Edit User</h1>

<form method="POST" action="/users/{{ $user->id }}">
  @csrf
  @method('PUT')

  <p>
    <label>Name</label><br/>
    <input name="name" value="{{ old('name', $user->name) }}" />
    @error('name') <div style="color:red">{{ $message }}</div> @enderror
  </p>

  <p>
    <label>Email</label><br/>
    <input name="email" value="{{ old('email', $user->email) }}" />
    @error('email') <div style="color:red">{{ $message }}</div> @enderror
  </p>

  <p>
    <label>Password (optional)</label><br/>
    <input type="password" name="password" />
    @error('password') <div style="color:red">{{ $message }}</div> @enderror
  </p>

  <button type="submit">Update</button>
  <a href="/users/{{ $user->id }}">Cancel</a>
</form>