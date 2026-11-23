<?php

namespace App\Providers;

use App\Events\FriendAdded;
use App\Models\Album;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Event::listen(FriendAdded::class, function (FriendAdded $event) {
            DB::table('friendships')->insertOrIgnore([
                'user_id' => $event->friendId,
                'friend_id' => $event->userId,
            ]);
        });

        Gate::define('create-album', fn (User $user) => true);

        $canManageActiveAlbum = fn (User $user, Album $album) =>
            ! $album->trashed() && ($user->is_admin || $user->id === $album->user_id);

        Gate::define('update-album', $canManageActiveAlbum);
        Gate::define('delete-album', $canManageActiveAlbum);

        Gate::define('view-album-trash', fn (User $user) => $user->is_admin);

        Gate::define('restore-album', fn (User $user, Album $album) =>
            $user->is_admin && $album->trashed());

        Gate::define('force-delete-album', fn (User $user, Album $album) =>
            $user->is_admin && $album->trashed());
    }
}
