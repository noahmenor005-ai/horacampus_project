@extends('layouts.app')

@section('title', 'Notifications')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">Notifications</h1>
    <form method="POST" action="{{ route('notifications.read-all') }}">@csrf<button class="btn btn-outline-primary btn-sm">Tout marquer comme lu</button></form>
</div>
<div class="surface p-0">
    @forelse($notifications as $n)
        <div class="p-3 border-bottom d-flex justify-content-between gap-3 {{ $n->lu_at ? '' : 'bg-light' }}">
            <div>
                <div class="fw-semibold">{{ $n->titre }}</div>
                <div class="text-muted">{{ $n->message }}</div>
                <small class="text-muted">{{ $n->created_at?->format('d/m/Y H:i') }}</small>
            </div>
            @unless($n->lu_at)
                <form method="POST" action="{{ route('notifications.read', $n) }}">@csrf @method('PATCH')<button class="btn btn-sm btn-outline-secondary">Lu</button></form>
            @endunless
        </div>
    @empty
        <div class="p-4 text-muted">Aucune notification.</div>
    @endforelse
    <div class="p-3">{{ $notifications->links() }}</div>
</div>
@endsection
