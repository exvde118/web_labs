<?php
namespace App\Http\Controllers;

use App\Models\Album;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AlbumController extends Controller
{
    public function index()
    {
        return view('albums.index', ['albums' => Album::orderBy('id')->get()]);
    }

    public function create()
    {
        return view('albums.create', ['album' => new Album]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['image_path'] = $this->saveImage($request->file('image'));
        $album = Album::create($data);
        return redirect()->route('albums.show', $album)->with('status', 'Альбом добавлен.');
    }

    public function show(Album $album)
    {
        return view('albums.show', compact('album'));
    }

    public function edit(Album $album)
    {
        return view('albums.edit', compact('album'));
    }

    public function update(Request $request, Album $album)
    {
        $data = $this->validated($request, true);
        $oldImage = $album->image_path;
        if ($request->hasFile('image')) {
            $data['image_path'] = $this->saveImage($request->file('image'));
        }
        $album->update($data);
        if (isset($data['image_path']) && str_starts_with($oldImage, 'albums/uploads/')) {
            Storage::disk('public')->delete($oldImage);
        }
        return redirect()->route('albums.show', $album)->with('status', 'Альбом изменён.');
    }

    public function destroy(Album $album)
    {
        $album->delete();
        return redirect()->route('albums.index')->with('status', 'Альбом удалён.');
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
        imagedestroy($source);
        imagedestroy($target);
        $path = 'albums/uploads/'.Str::uuid().'.jpg';
        Storage::disk('public')->put($path, $contents);
        return $path;
    }
}
