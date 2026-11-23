<?php

namespace App\Http\Controllers;

use App\Events\FriendAdded;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FriendshipController extends Controller
{
    public function store(Request $request, User $user): RedirectResponse
    {
        $member = $request->user();
        abort_if($member->is($user), 422, 'Нельзя добавить себя в друзья.');

        DB::transaction(function () use ($member, $user) {
            $inserted = DB::table('friendships')->insertOrIgnore([
                'user_id' => $member->id,
                'friend_id' => $user->id,
            ]);

            if ($inserted) {
                FriendAdded::dispatch($member->id, $user->id);
            }
        }, attempts: 3);

        return redirect()->route('users.index')->with('status', 'Пользователь добавлен в друзья.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        $member = $request->user();
        abort_if($member->is($user), 422, 'Нельзя удалить себя из друзей.');

        DB::transaction(function () use ($member, $user) {
            $member->friends()->detach($user->id);
            $user->friends()->detach($member->id);
        }, attempts: 3);

        return redirect()->route('users.index')->with('status', 'Дружба удалена.');
    }
}
