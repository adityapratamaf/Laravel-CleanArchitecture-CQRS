<h1>Users</h1>

@if(session('success'))
    <p style="color: green">{{ session('success') }}</p>
@endif

<!-- Helper -->
@php
    $baseQuery = [
    'search' => $filters['search'] ?? '',
    'per_page' => $filters['per_page'] ?? 20,
    ];

    $currentSortBy = $filters['sort_by'] ?? 'id';
    $currentSortDir = $filters['sort_dir'] ?? 'desc';

    $sortUrl = function(string $column) use ($baseQuery, $currentSortBy, $currentSortDir) {
    $dir = 'asc';
    if ($currentSortBy === $column) {
    $dir = $currentSortDir === 'asc' ? 'desc' : 'asc';
    }
    $q = array_merge($baseQuery, ['sort_by' => $column, 'sort_dir' => $dir, 'page' => 1]);
    return '/users?' . http_build_query($q);
    };

    $sortIcon = function(string $column) use ($currentSortBy, $currentSortDir) {
    if ($currentSortBy !== $column) return '';
    return $currentSortDir === 'asc' ? ' ▲' : ' ▼';
    };
@endphp

@php
  $offset = (($meta['current_page'] ?? 1) - 1) * ($meta['per_page'] ?? 20);
@endphp

<form method="GET" action="/users" style="margin-bottom: 12px">
    <input name="search" value="{{ $filters['search'] }}" placeholder="Search name/email" />
    <button type="submit">Search</button>
    <a href="/users/create">Create</a>
</form>

<table border="1" cellpadding="8" cellspacing="0">
    <thead>
        <tr>
            <th>No</th>
            <th><a href="{{ $sortUrl('id') }}">ID{!! $sortIcon('id') !!}</a></th>
            <th><a href="{{ $sortUrl('name') }}">Name{!! $sortIcon('name') !!}</a></th>
            <th><a href="{{ $sortUrl('email') }}">Email{!! $sortIcon('email') !!}</a></th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        @foreach($users as $u)
            <tr>
                <td>{{ $offset + $loop->iteration }}</td>
                <td>{{ $u->id }}</td>
                <td><a href="/users/{{ $u->id }}">{{ $u->name }}</a></td>
                <td>{{ $u->email }}</td>
                <td>
                    <a href="/users/{{ $u->id }}/edit">Edit</a>
                    <form method="POST" action="/users/{{ $u->id }}" style="display:inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" onclick="return confirm('Delete?')">Delete</button>
                    </form>
                </td>
            </tr>
        @endforeach
    </tbody>
</table>

<p style="margin-top: 12px">
    Page {{ $meta['current_page'] }} of {{ $meta['last_page'] }} |
    Total {{ $meta['total'] }}
</p>

@if($meta['last_page'] > 1)
    <div style="margin-top: 12px; display:flex; gap:6px; align-items:center; flex-wrap:wrap;">
        @foreach($paginationLinks as $item)
            @if($item['label'] === '...')
                <span style="padding:6px 10px;">...</span>
            @elseif($item['disabled'])
                <span
                    style="padding:6px 10px; color:#999; border:1px solid #ddd;">{{ $item['label'] }}</span>
            @elseif($item['active'])
                <span
                    style="padding:6px 10px; font-weight:bold; border:1px solid #000;">{{ $item['label'] }}</span>
            @else
                <a href="{{ $item['url'] }}"
                    style="padding:6px 10px; border:1px solid #ddd; text-decoration:none;">
                    {{ $item['label'] }}
                </a>
            @endif
        @endforeach
    </div>
@endif
