<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

class Album extends Model
{
    use SoftDeletes;

    protected $fillable = ['title', 'description', 'genre', 'genre_description', 'release_date', 'image_path'];

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
