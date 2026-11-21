<?php

use App\Http\Controllers\AlbumController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/albums');

Route::middleware('auth')->group(function () {
    Route::get('users', [UserController::class, 'index'])->name('users.index');
    Route::get('users/{id}', [UserController::class, 'byId'])->whereNumber('id')->name('users.by-id');
    Route::get('users/{user:username}', [UserController::class, 'albums'])->name('users.albums');
    Route::get('users/{user:username}/trash', [UserController::class, 'trash'])
        ->can('view-album-trash')->name('users.trash');

    Route::patch('albums/{album}/restore', [AlbumController::class, 'restore'])
        ->withTrashed()->can('restore-album', 'album')->name('albums.restore');
    Route::delete('albums/{album}/force', [AlbumController::class, 'forceDelete'])
        ->withTrashed()->can('force-delete-album', 'album')->name('albums.force-delete');
    Route::resource('albums', AlbumController::class);
});

require __DIR__.'/auth.php';
