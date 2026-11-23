<?php

namespace App\Http\Controllers;

use App\Models\Album;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CommentController extends Controller
{
    public function store(Request $request, Album $album): RedirectResponse
    {
        $data = $request->validate([
            'body' => ['required', 'string', 'max:2000'],
        ], [], ['body' => 'Комментарий']);

        DB::transaction(function () use ($request, $album, $data) {
            $current = Album::query()->lockForUpdate()->findOrFail($album->id);
            $comment = $current->comments()->make($data);
            $comment->user()->associate($request->user());
            $comment->save();
        });

        return redirect()->to(route('albums.show', $album).'#comments')->with('status', 'Комментарий добавлен.');
    }
}
