<?php
namespace Database\Seeders;
use App\Models\Album;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $albums = json_decode(file_get_contents(database_path('seeders/albums.json')), true, 512, JSON_THROW_ON_ERROR);
        Album::withTrashed()->forceDelete();
        foreach ($albums as $album) {
            Album::create($album);
        }
    }
}
