<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Gate;

class Album extends Model
{
    use SoftDeletes;

    protected $fillable = ['title', 'description', 'genre', 'genre_description', 'release_date', 'image_path'];

    protected static function booted(): void
    {
        static::creating(function (Album $album) {
            Gate::authorize('create-album');
            abort_unless(auth()->id() === $album->user_id && ! $album->trashed(), 403);
        });

        static::updating(function (Album $album) {
            Gate::authorize('update-album', $album);
            abort_if($album->isDirty('user_id'), 403);
            if ($album->isDirty('deleted_at')) {
                // SoftDeletes restores a record by saving deleted_at = null.
                Gate::authorize('view-album-trash');
                abort_if($album->trashed() || $album->getRawOriginal('deleted_at') === null, 403);
            }
        });

        static::deleting(function (Album $album) {
            if (! $album->isForceDeleting()) {
                Gate::authorize('delete-album', $album);
            }
        });

        static::restoring(fn (Album $album) => Gate::authorize('restore-album', $album));
        static::forceDeleting(fn (Album $album) => Gate::authorize('force-delete-album', $album));
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    protected function title(): Attribute
    {
        return Attribute::make(set: fn (string $value) => trim($value));
    }

    protected function releaseDate(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => $value ? Carbon::parse($value) : null,
            set: fn ($value) => $value ? Carbon::parse($value)->format('Y-m-d') : null,
        );
    }
}
