<h1>Create Product</h1>

@include('partials.flash')

<form method="POST" action="/products">
  @csrf

  <p>
    <label>Name</label><br/>
    <input name="name" value="{{ old('name') }}" />
    @error('name') <div style="color:red">{{ $message }}</div> @enderror
  </p>

  <p>
    <label>SKU</label><br/>
    <input name="sku" value="{{ old('sku') }}" />
    @error('sku') <div style="color:red">{{ $message }}</div> @enderror
  </p>

  <p>
    <label>Price</label><br/>
    <input name="price" value="{{ old('price') }}" />
    @error('price') <div style="color:red">{{ $message }}</div> @enderror
  </p>

  <p>
    <label>Stock</label><br/>
    <input name="stock" value="{{ old('stock', 0) }}" />
    @error('stock') <div style="color:red">{{ $message }}</div> @enderror
  </p>

  <p>
    <label>Description</label><br/>
    <textarea name="description">{{ old('description') }}</textarea>
    @error('description') <div style="color:red">{{ $message }}</div> @enderror
  </p>

  <button type="submit">Save</button>
  <a href="/products">Back</a>
</form>