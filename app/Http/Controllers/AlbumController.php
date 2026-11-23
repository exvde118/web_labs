<?php

namespace App\Http\Controllers;

use App\Models\Album;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

class AlbumController extends Controller
{
    public function index()
    {
        return view('albums.index', ['albums' => Album::with('user')->orderBy('id')->get()]);
    }

    public function create()
    {
        Gate::authorize('create-album');

        return view('albums.create', ['album' => new Album]);
    }

    public function store(Request $request)
    {
        Gate::authorize('create-album');

        $data = $this->validated($request);
        $data['image_path'] = $this->saveImage($request->file('image'));

        try {
            $album = $request->user()->albums()->create($data);
        } catch (Throwable $exception) {
            $this->deleteUploadedImage($data['image_path']);
            throw $exception;
        }

        return redirect()->route('albums.show', $album)->with('status', 'Альбом добавлен.');
    }

    public function show(Request $request, Album $album)
    {
        $album->load(['user', 'comments' => fn ($query) => $query->with('user')->oldest()->orderBy('id')]);

        return view('albums.show', [
            'album' => $album,
            'friendIds' => $request->user()->friends()->allRelatedIds()->flip(),
        ]);
    }

    public function edit(Album $album)
    {
        Gate::authorize('update-album', $album);

        return view('albums.edit', compact('album'));
    }

    public function update(Request $request, Album $album)
    {
        Gate::authorize('update-album', $album);

        $data = $this->validated($request, true);
        if ($request->hasFile('image')) {
            $data['image_path'] = $this->saveImage($request->file('image'));
        }

        try {
            $oldImage = DB::transaction(function () use ($album, $data) {
                $current = Album::query()->lockForUpdate()->findOrFail($album->id);
                $oldImage = $current->image_path;
                $current->update($data);

                return $oldImage;
            });
        } catch (Throwable $exception) {
            if (isset($data['image_path'])) {
                $this->deleteUploadedImage($data['image_path']);
            }
            throw $exception;
        }

        if (isset($data['image_path'])) {
            $this->deleteUploadedImage($oldImage);
        }

        return redirect()->route('albums.show', $album)->with('status', 'Альбом изменён.');
    }

    public function destroy(Album $album)
    {
        Gate::authorize('delete-album', $album);
        DB::transaction(function () use ($album) {
            Album::query()->lockForUpdate()->findOrFail($album->id)->delete();
        });

        return redirect()->route('albums.index')->with('status', 'Альбом мягко удалён.');
    }

    public function restore(Album $album)
    {
        DB::transaction(function () use ($album) {
            Album::withTrashed()->lockForUpdate()->findOrFail($album->id)->restore();
        });

        return redirect()->route('users.trash', $album->user->username)->with('status', 'Альбом восстановлен.');
    }

    public function forceDelete(Album $album)
    {
        $owner = $album->user->username;
        $image = DB::transaction(function () use ($album) {
            $current = Album::withTrashed()->lockForUpdate()->findOrFail($album->id);
            $current->forceDelete();

            return $current->image_path;
        });

        $this->deleteUploadedImage($image);

        return redirect()->route('users.trash', $owner)->with('status', 'Альбом удалён навсегда.');
    }

    private function deleteUploadedImage(string $path): void
    {
        if (str_starts_with($path, 'albums/uploads/')) {
            rescue(fn () => Storage::disk('public')->delete($path));
        }
    }


    private function validated(Request $request, bool $editing = false): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:5000'],
            'genre' => ['required', 'string', 'max:100'],
            'genre_description' => ['nullable', 'string', 'max:1000'],
            'release_date' => ['nullable', 'date_format:Y-m-d'],
            'image' => [$editing ? 'nullable' : 'required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120', 'dimensions:max_width=5000,max_height=5000'],
        ], [
            'required' => 'Заполните поле «:attribute».',
            'max.string' => 'Поле «:attribute» слишком длинное (не более :max символов).',
            'image' => 'Выберите изображение.',
            'mimes' => 'Допустимы JPG, PNG и WebP.',
            'max.file' => 'Размер файла не должен превышать 5 МБ.',
            'dimensions' => 'Размер изображения не должен превышать 5000 × 5000 пикселей.',
            'date_format' => 'Укажите корректную дату.',
        ], [
            'title' => 'Название', 'description' => 'Описание', 'genre' => 'Жанр',
            'genre_description' => 'Описание жанра', 'release_date' => 'Дата выпуска', 'image' => 'Обложка',
        ]);
        unset($data['image']);
        return $data;
    }

    private function saveImage(UploadedFile $file): string
    {
        $source = @imagecreatefromstring(file_get_contents($file->getRealPath()));
        if ($source === false) {
            throw ValidationException::withMessages(['image' => 'Не удалось прочитать изображение.']);
        }
        // Квадратные карточки исходной верстки: центральная обрезка и размер 600 × 600.
        $width = imagesx($source);
        $height = imagesy($source);
        $side = min($width, $height);
        $target = imagecreatetruecolor(600, 600);
        imagefill($target, 0, 0, imagecolorallocate($target, 255, 255, 255));
        imagecopyresampled($target, $source, 0, 0, (int) (($width - $side) / 2), (int) (($height - $side) / 2), 600, 600, $side, $side);
        ob_start();
        imagejpeg($target, null, 90);
        $contents = ob_get_clean();
        $path = 'albums/uploads/'.Str::uuid().'.jpg';
        if (! Storage::disk('public')->put($path, $contents)) {
            throw ValidationException::withMessages(['image' => 'Не удалось сохранить обложку.']);
        }
        return $path;
    }
}
