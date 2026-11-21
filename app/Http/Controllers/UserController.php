<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(): View
    {
        return view('users.index', [
            'users' => User::withCount('albums')->orderBy('username')->get(),
        ]);
    }

    public function byId(string $id): RedirectResponse
    {
        return redirect()->route('users.albums', User::findOrFail($id)->username);
    }

    public function albums(User $user): View
    {
        return view('albums.index', [
            'albums' => $user->albums()->with('user')->orderBy('id')->get(),
            'owner' => $user,
        ]);
    }

    public function trash(User $user): View
    {
        return view('albums.index', [
            'albums' => $user->albums()->withTrashed()->with('user')->orderBy('id')->get(),
            'owner' => $user,
            'withTrash' => true,
        ]);
    }
}
