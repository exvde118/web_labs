<?php
use App\Http\Controllers\AlbumController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/albums');
Route::resource('albums', AlbumController::class);
