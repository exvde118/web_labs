<?php

namespace App\Providers;

use App\Models\Album;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
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
