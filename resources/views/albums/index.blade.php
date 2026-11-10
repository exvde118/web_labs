@extends('layouts.app')
@section('content')
<h1 class="text-center mb-4">Топ 5 треков исполнителя «Королевский XVII»</h1>
<div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 row-cols-xl-5 row-cols-xxxl-5 g-4" id="cards-container">
    @forelse ($albums as $album)
    <div class="col">
        <article class="card h-100 track-card position-relative">
            <span class="card-badge">Трек {{ $loop->iteration }}</span>
            <img src="{{ asset('storage/'.$album->image_path) }}" class="card-img-top img-fluid" alt="Обложка {{ $album->title }}">
            <div class="card-body d-flex flex-column">
                <h5 class="card-title">{{ $album->title }}</h5>
                <p class="card-text">{{ $album->description }}</p>
                <div class="mt-auto">
                    <p class="card-text card-date">Дата выпуска: @if ($album->release_date)<time datetime="{{ $album->release_date->format('Y-m-d') }}">{{ $album->release_date->format('d.m.Y') }}</time>@else не указана @endif</p>
                    <a class="btn btn-primary btn-sm stretched-link" href="{{ route('albums.show', $album) }}">Подробнее</a>
                </div>
            </div>
        </article>
    </div>
    @empty
    <p class="text-center">Альбомов пока нет.</p>
    @endforelse
</div>
@endsection
