@extends('layouts.app')
@section('title', $album->title)
@section('content')
<div class="album-detail">
    <h1>{{ $album->title }}</h1>
    <img class="album-cover d-block mx-auto mb-4" src="{{ asset('storage/'.$album->image_path) }}" alt="Обложка {{ $album->title }}">
    <p>{{ $album->description }}</p>
    <p>Жанр:
        @if ($album->genre_description)
        <button type="button" class="genre-hint" data-bs-toggle="popover" data-bs-trigger="hover focus" data-bs-content="{{ $album->genre_description }}">{{ $album->genre }}</button>
        @else {{ $album->genre }} @endif
    </p>
    @if ($album->release_date)<p>Дата выхода: {{ $album->release_date->format('d.m.Y') }}</p>@endif
    <div class="d-flex flex-wrap gap-2">
        <a class="btn btn-outline-secondary" href="{{ route('albums.index') }}">Назад</a>
        <a class="btn btn-primary" href="{{ route('albums.edit', $album) }}">Редактировать</a>
        <form method="POST" action="{{ route('albums.destroy', $album) }}">
            @csrf @method('DELETE')
            <button class="btn btn-danger" type="submit">Удалить</button>
        </form>
    </div>
</div>
@endsection
