<h1>Edit Product</h1>

<form method="POST" action="/products/{{ $product->id }}">
  @csrf
  @method('PUT')

  <p>
    <label>Name</label><br/>
    <input name="name" value="{{ old('name', $product->name) }}" />
    @error('name') <div style="color:red">{{ $message }}</div> @enderror
  </p>

  <p>
    <label>SKU</label><br/>
    <input name="sku" value="{{ old('sku', $product->sku) }}" />
    @error('sku') <div style="color:red">{{ $message }}</div> @enderror
  </p>

  <p>
    <label>Price</label><br/>
    <input name="price" value="{{ old('price', $product->price) }}" />
    @error('price') <div style="color:red">{{ $message }}</div> @enderror
  </p>

  <p>
    <label>Stock</label><br/>
    <input name="stock" value="{{ old('stock', $product->stock) }}" />
    @error('stock') <div style="color:red">{{ $message }}</div> @enderror
  </p>

  <p>
    <label>Description</label><br/>
    <textarea name="description">{{ old('description', $product->description) }}</textarea>
    @error('description') <div style="color:red">{{ $message }}</div> @enderror
  </p>

  <button type="submit">Update</button>
  <a href="/products/{{ $product->id }}">Cancel</a>
</form>