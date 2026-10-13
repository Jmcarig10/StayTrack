<x-app-layout>
<section class="screen is-active" aria-labelledby="tenant-notifications-title">
    <div class="page-heading heading-with-actions"><div><span class="eyebrow">My rental</span><h1 id="tenant-notifications-title">Notifications</h1><p>Updates about your lease, billing, payments, and maintenance.</p></div>@if($notifications->whereNull('read_at')->isNotEmpty())<form method="POST" action="{{ route('notifications.read') }}">@csrf @method('PATCH')<button class="button button-secondary" type="submit">Mark all as read</button></form>@endif</div>
    <article class="panel"><div class="notification-list">@forelse($notifications as $notification)<a class="notification-item {{ $notification->read_at ? '' : 'is-unread' }}" href="{{ $notification->url ?: '#' }}"><span class="notification-icon info"><svg class="icon"><use href="#icon-bell"/></svg></span><span><strong>{{ $notification->title }}</strong><small>{{ $notification->body }}</small><time>{{ $notification->created_at->diffForHumans() }}</time></span></a>@empty <div class="empty-state"><p>No notifications yet.</p></div>@endforelse</div></article>
</section>
</x-app-layout>
