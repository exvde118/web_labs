<div class="d-flex flex-wrap gap-2 mt-3">
    @can('restore-album', $album)
        <form method="POST" action="{{ route('albums.restore', $album) }}">
            @csrf @method('PATCH')
            <button class="btn btn-sm btn-outline-primary" type="submit">Восстановить</button>
        </form>
    @endcan
    @can('force-delete-album', $album)
        <form method="POST" action="{{ route('albums.force-delete', $album) }}" onsubmit="return confirm('Удалить запись без возможности восстановления?')">
            @csrf @method('DELETE')
            <button class="btn btn-sm btn-outline-danger" type="submit">Удалить навсегда</button>
        </form>
    @endcan
</div>
