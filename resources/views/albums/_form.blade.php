<form method="POST" action="{{ $album->exists ? route('albums.update', $album) : route('albums.store') }}" enctype="multipart/form-data" class="needs-validation" novalidate>
    @csrf
    @if ($album->exists) @method('PUT') @endif
    @foreach (['title' => ['Название', 255], 'description' => ['Описание', 5000], 'genre' => ['Жанр', 100], 'genre_description' => ['Описание жанра', 1000]] as $field => [$label, $limit])
    <div class="mb-3">
        <label class="form-label" for="{{ $field }}">{{ $label }}</label>
        @if (in_array($field, ['description', 'genre_description']))
        <textarea id="{{ $field }}" name="{{ $field }}" class="form-control @error($field) is-invalid @enderror" rows="3" maxlength="{{ $limit }}" @required($field !== 'genre_description')>{{ old($field, $album->$field) }}</textarea>
        @else
        <input id="{{ $field }}" name="{{ $field }}" class="form-control @error($field) is-invalid @enderror" value="{{ old($field, $album->$field) }}" maxlength="{{ $limit }}" required>
        @endif
        <div class="invalid-feedback">@error($field) {{ $message }} @else Проверьте поле «{{ $label }}». @enderror</div>
    </div>
    @endforeach
    <div class="mb-3">
        <label for="release_date" class="form-label">Дата выхода</label>
        <input type="date" id="release_date" name="release_date" class="form-control @error('release_date') is-invalid @enderror" value="{{ old('release_date', $album->release_date?->format('Y-m-d')) }}">
        <div class="invalid-feedback">@error('release_date') {{ $message }} @else Укажите корректную дату. @enderror</div>
    </div>
    <div class="mb-3">
        <label for="image" class="form-label">Обложка</label>
        @if ($album->exists)<img class="album-cover d-block mb-2" src="{{ asset('storage/'.$album->image_path) }}" alt="Текущая обложка">@endif
        <input type="file" id="image" name="image" class="form-control @error('image') is-invalid @enderror" accept="image/jpeg,image/png,image/webp" @required(!$album->exists)>
        <div class="invalid-feedback">@error('image') {{ $message }} @else Выберите обложку. @enderror</div>
        <div class="form-text">JPG, PNG или WebP, до 5 МБ и 5000 × 5000 пикселей. Обложка будет обрезана до квадрата 600 × 600.@if ($album->exists) Оставьте поле пустым, чтобы сохранить текущую обложку.@endif</div>
    </div>
    <button type="submit" class="btn btn-primary">Сохранить</button>
    <a href="{{ route('albums.index') }}" class="btn btn-outline-secondary">Отмена</a>
</form>
