@extends('layouts.app')
@section('title', 'Пользователи')
@section('content')
<div class="album-form">
    <h1>Пользователи</h1>
    <ul class="list-group">
        @foreach ($users as $user)
            <li class="list-group-item d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div>
                    <a href="{{ route('users.albums', $user->username) }}">{{ $user->name }} ({{ '@'.$user->username }})</a>
                    <span class="text-body-secondary"> · Записей {{ $user->albums_count }}</span>
                </div>
                @can('view-album-trash')
                    <a class="btn btn-sm btn-outline-secondary" href="{{ route('users.trash', $user->username) }}">Включая удалённые</a>
                @endcan
            </li>
        @endforeach
    </ul>
</div>
@endsection
