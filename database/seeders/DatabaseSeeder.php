<?php

namespace Database\Seeders;

use App\Models\Album;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $albums = json_decode(file_get_contents(database_path('seeders/albums.json')), true, 512, JSON_THROW_ON_ERROR);

        DB::transaction(function () use ($albums) {
            $owners = [
                User::firstOrCreate(['email' => 'anna@example.test'], [
                    'name' => 'Анна',
                    'username' => 'anna',
                    'password' => Str::random(48),
                ]),
                User::firstOrCreate(['email' => 'editor@example.test'], [
                    'name' => 'Редактор',
                    'username' => 'editor',
                    'password' => Str::random(48),
                ]),
            ];

            Album::withoutEvents(function () use ($albums, $owners) {
                foreach ($albums as $index => $album) {
                    if (Album::withTrashed()->where('image_path', $album['image_path'])->exists()) {
                        continue;
                    }

                    $owners[$index % count($owners)]->albums()->create($album);
                }
            });
        });
    }
}
