@extends('layouts.app')
@section('content')
@if (isset($owner))
    <h1 class="text-center mb-3">Треки пользователя {{ '@'.$owner->username }}</h1>
    <div class="d-flex justify-content-center flex-wrap gap-2 mb-4">
        <a class="btn btn-outline-secondary" href="{{ route('users.index') }}">Все пользователи</a>
        @can('view-album-trash')
            @if ($withTrash ?? false)
                <a class="btn btn-outline-secondary" href="{{ route('users.albums', $owner->username) }}">Только активные</a>
            @else
                <a class="btn btn-outline-secondary" href="{{ route('users.trash', $owner->username) }}">Включая удалённые</a>
            @endif
        @endcan
    </div>
@else
    <h1 class="text-center mb-4">Треки исполнителя «Королевский XVII»</h1>
@endif

@if ($albums->isEmpty())
    <p class="text-center">Альбомов пока нет.</p>
@endif

<div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 row-cols-xl-5 row-cols-xxxl-5 g-4" id="cards-container">
    @foreach ($albums as $album)
        <div class="col">
            <article class="card h-100 track-card">
                <img src="{{ asset('storage/'.$album->image_path) }}" class="card-img-top img-fluid" alt="Обложка {{ $album->title }}">
                <div class="card-body d-flex flex-column">
                    <h2 class="h5 card-title">{{ $album->title }}</h2>
                    @unless (isset($owner))
                        <p class="small"><a href="{{ route('users.albums', $album->user->username) }}">{{ '@'.$album->user->username }}</a></p>
                    @endunless
                    @if ($album->trashed())
                        <p class="small text-danger">Удалён {{ $album->deleted_at->format('d.m.Y') }}</p>
                    @endif
                    <p class="card-text">{{ $album->description }}</p>
                    <div class="mt-auto">
                        @if ($album->release_date)
                            <p class="card-text card-date">Дата выпуска: <time datetime="{{ $album->release_date->format('Y-m-d') }}">{{ $album->release_date->format('d.m.Y') }}</time></p>
                        @endif
                        @if ($album->trashed())
                            @include('albums._trash-actions')
                        @else
                            <a class="btn btn-primary btn-sm" href="{{ route('albums.show', $album) }}">Подробнее</a>
                        @endif
                    </div>
                </div>
            </article>
        </div>
    @endforeach
</div>
@endsection
