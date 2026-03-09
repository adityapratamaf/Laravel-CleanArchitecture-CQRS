<h1>Edit Product</h1>

@include('partials.flash')

<form method="POST" action="/products/{{ $product->id }}" enctype="multipart/form-data">
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

  <p>
    <label>Image</label><br/>
    <input type="file" name="image" id="image" accept="image/*" />
    @error('image') <div style="color:red">{{ $message }}</div> @enderror
  </p>

  <p>
    <img
      id="image-preview"
      src="{{ $product->imageUrl ?? '' }}"
      alt="{{ $product->name }}"
      width="120"
      style="{{ $product->imageUrl ? '' : 'display:none;' }}"
    >
  </p>

  <button type="submit">Update</button>
  <a href="/products/{{ $product->id }}">Cancel</a>
</form>

<script>
  document.getElementById('image').addEventListener('change', function (event) {
    const file = event.target.files[0];
    const preview = document.getElementById('image-preview');

    if (!file) {
      return;
    }

    const reader = new FileReader();

    reader.onload = function (e) {
      preview.src = e.target.result;
      preview.style.display = 'block';
    };

    reader.readAsDataURL(file);
  });
</script>