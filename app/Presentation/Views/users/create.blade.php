<h1>Create User</h1>

<form method="POST" action="/users">
  @csrf
  <p>
    <label>Name</label><br/>
    <input name="name" value="{{ old('name') }}" />
    @error('name') <div style="color:red">{{ $message }}</div> @enderror
  </p>

  <p>
    <label>Email</label><br/>
    <input name="email" value="{{ old('email') }}" />
    @error('email') <div style="color:red">{{ $message }}</div> @enderror
  </p>

  <p>
    <label>Password</label><br/>
    <input type="password" name="password" />
    @error('password') <div style="color:red">{{ $message }}</div> @enderror
  </p>

  <button type="submit">Save</button>
  <a href="/users">Back</a>
</form>