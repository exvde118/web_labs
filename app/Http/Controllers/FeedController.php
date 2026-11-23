<?php

namespace App\Http\Controllers;

use App\Models\Album;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FeedController extends Controller
{
    public function __invoke(Request $request): View
    {
        return view('albums.index', [
            'albums' => Album::with('user')
                ->whereIn('user_id', $request->user()->friends()->select('users.id'))
                ->latest()
                ->orderByDesc('id')
                ->get(),
            'isFriendsFeed' => true,
        ]);
    }
}
