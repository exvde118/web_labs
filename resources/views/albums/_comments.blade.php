<section id="comments" class="border-top mt-4 pt-4">
    <h2 class="h4">Комментарии</h2>

    @forelse ($album->comments as $comment)
        <article @class(['border rounded p-3 mb-3', 'border-primary bg-primary-subtle' => $friendIds->has($comment->user_id)])>
            <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                <a class="fw-semibold" href="{{ route('users.albums', $comment->user->username) }}">{{ $comment->user->name }}</a>
                @if ($friendIds->has($comment->user_id))
                    <span class="badge text-bg-primary">Друг</span>
                @endif
                <time class="small text-body-secondary" datetime="{{ $comment->created_at->toIso8601String() }}">
                    {{ $comment->created_at->format('d.m.Y H:i') }}
                </time>
            </div>
            <p class="mb-0 text-break" style="white-space: pre-wrap">{{ $comment->body }}</p>
        </article>
    @empty
        <p class="text-body-secondary">Комментариев пока нет.</p>
    @endforelse

    <form method="POST" action="{{ route('albums.comments.store', $album) }}">
        @csrf
        <label class="form-label" for="comment-body">Оставить комментарий</label>
        <textarea id="comment-body" name="body" class="form-control @error('body') is-invalid @enderror"
            rows="3" maxlength="2000" required>{{ old('body') }}</textarea>
        @error('body')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        <button class="btn btn-primary mt-3" type="submit">Отправить</button>
    </form>
</section>
