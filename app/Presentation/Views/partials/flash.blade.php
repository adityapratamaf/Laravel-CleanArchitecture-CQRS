@if(session('success'))
  <div style="background:#d1fae5;color:#065f46;padding:10px;margin-bottom:12px;border:1px solid #a7f3d0;">
    {{ session('success') }}
  </div>
@endif

@if(session('error'))
  <div style="background:#fee2e2;color:#991b1b;padding:10px;margin-bottom:12px;border:1px solid #fecaca;">
    {{ session('error') }}
  </div>
@endif

@if($errors->any())
  <div style="background:#fee2e2;color:#991b1b;padding:10px;margin-bottom:12px;border:1px solid #fecaca;">
    <ul style="margin:0;padding-left:18px;">
      @foreach($errors->all() as $error)
        <li>{{ $error }}</li>
      @endforeach
    </ul>
  </div>
@endif