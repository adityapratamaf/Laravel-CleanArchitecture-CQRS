<h1>Login</h1>

@if(session('success'))
  <p style="color: green">{{ session('success') }}</p>
@endif

@if($errors->any())
  <p style="color:red">{{ $errors->first() }}</p>
@endif

<form method="POST" action="/login">
  @csrf

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

  <p>
    <label><input type="checkbox" name="remember" /> Remember me</label>
  </p>

  <button type="submit">Login</button>
</form>