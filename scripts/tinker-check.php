<?php

use App\Models\User;
use Illuminate\Support\Facades\DB;

DB::beginTransaction();
try {
    $user = User::query()->firstOrFail();
    auth()->setUser($user);
    $album = $user->albums()->create([
        'title' => ' Проверка Tinker ',
        'description' => 'Тест создания объекта через Eloquent.',
        'genre' => 'Ambient',
        'release_date' => '2024-03-12 18:00:00',
        'image_path' => 'albums/seed/1.jpg',
    ]);
    $album->refresh();
    if ($album->title !== 'Проверка Tinker' || $album->release_date->format('Y-m-d') !== '2024-03-12') {
        throw new RuntimeException('Ошибка проверки мутаторов.');
    }
    dump(['created' => $album->exists, 'title' => $album->title, 'release_date' => $album->release_date->format('d.m.Y')]);
} finally {
    DB::rollBack();
}
