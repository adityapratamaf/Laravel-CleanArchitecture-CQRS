<h1>Product Detail</h1>

<p>ID: {{ $product->id }}</p>
<p>Name: {{ $product->name }}</p>
<p>SKU: {{ $product->sku }}</p>
<p>Price: {{ number_format($product->price, 2) }}</p>
<p>Stock: {{ $product->stock }}</p>
<p>Description: {{ $product->description ?? '-' }}</p>
@if($product->imageUrl)
  <p>
    <img src="{{ $product->imageUrl }}" alt="{{ $product->name }}" width="180">
  </p>
@endif

<p>
  <a href="/products/{{ $product->id }}/edit">Edit</a> |
  <a href="/products">Back</a>
</p>