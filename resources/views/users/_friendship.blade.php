@if ($friendIds->has($user->id))
    <form method="POST" action="{{ route('friends.destroy', $user->username) }}">
        @csrf @method('DELETE')
        <button class="btn btn-sm btn-outline-danger" type="submit">Удалить из друзей</button>
    </form>
@else
    <form method="POST" action="{{ route('friends.store', $user->username) }}">
        @csrf
        <button class="btn btn-sm btn-primary" type="submit">Добавить в друзья</button>
    </form>
@endif
