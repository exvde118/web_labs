<?php

namespace Tests\Feature;

use App\Models\Album;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class AlbumAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_members_can_browse_catalogs_by_username_and_id(): void
    {
        $this->seed();
        $dan = User::whereUsername('dan')->firstOrFail();
        $admin = User::whereUsername('admin')->firstOrFail();
        $danAlbum = $dan->albums()->firstOrFail();
        $adminAlbum = $admin->albums()->firstOrFail();

        $this->get('/users/dan')->assertRedirect(route('login'));
        $this->actingAs($admin)->get('/users')->assertOk()->assertSee('@dan')->assertSee('@admin');
        $this->get('/users/'.$dan->id)->assertRedirect('/users/dan');
        $this->get('/users/dan')->assertOk()->assertSee($danAlbum->title)->assertDontSee($adminAlbum->title);
        $this->get('/users/admin')->assertOk()->assertSee($adminAlbum->title)->assertDontSee($danAlbum->title);
    }

    public function test_guests_and_non_owners_cannot_change_albums(): void
    {
        $this->seed();
        $album = Album::firstOrFail();
        $this->get(route('albums.create'))->assertRedirect(route('login'));
        $this->post(route('albums.store'))->assertRedirect(route('login'));
        $this->get(route('albums.edit', $album))->assertRedirect(route('login'));
        $this->put(route('albums.update', $album))->assertRedirect(route('login'));
        $this->delete(route('albums.destroy', $album))->assertRedirect(route('login'));

        $this->actingAs(User::factory()->create());
        $this->get(route('albums.show', $album))->assertOk()->assertDontSee('Редактировать')->assertDontSee('Удалить');
        $this->get(route('albums.edit', $album))->assertForbidden();
        $this->put(route('albums.update', $album))->assertForbidden();
        $this->delete(route('albums.destroy', $album))->assertForbidden();

        $this->assertThrows(fn () => $album->update(['title' => 'Другая запись']), AuthorizationException::class);
        $this->assertThrows(fn () => $album->delete(), AuthorizationException::class);
        $this->assertThrows(fn () => $album->user->albums()->create($album->getAttributes()), HttpException::class);
    }

    public function test_admin_can_edit_soft_delete_restore_and_permanently_delete_another_users_album(): void
    {
        Storage::fake('public');
        $owner = User::factory()->create();
        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($owner)->post(route('albums.store'), [
            'title' => 'Трек владельца',
            'genre' => 'Ambient',
            'description' => 'Описание',
            'image' => UploadedFile::fake()->image('cover.png'),
        ])->assertSessionHasNoErrors()->assertRedirect();
        $album = $owner->albums()->firstOrFail();
        $trash = route('users.trash', $owner->username);

        $this->actingAs($admin)->get(route('albums.edit', $album))->assertOk();
        $this->put(route('albums.update', $album), [
            'title' => 'Исправленный трек', 'genre' => 'Ambient', 'description' => 'Описание',
        ])->assertSessionHasNoErrors()->assertRedirect(route('albums.show', $album));
        $this->assertDatabaseHas('albums', ['id' => $album->id, 'title' => 'Исправленный трек', 'user_id' => $owner->id]);
        $this->delete(route('albums.force-delete', $album))->assertForbidden();
        $this->delete(route('albums.destroy', $album))->assertRedirect();
        $this->assertSoftDeleted($album);
        Storage::disk('public')->assertExists($album->image_path);
        $this->get($trash)->assertOk()->assertSee('Исправленный трек')
            ->assertSee('Восстановить')->assertSee('Удалить навсегда');

        $this->actingAs($owner)->get($trash)->assertForbidden();
        $this->get(route('users.albums', $owner->username))->assertOk()->assertDontSee('Исправленный трек');
        $this->patch(route('albums.restore', $album))->assertForbidden();
        $this->delete(route('albums.force-delete', $album))->assertForbidden();
        $archived = Album::withTrashed()->findOrFail($album->id);
        $this->assertThrows(fn () => $archived->restore(), AuthorizationException::class);
        $this->assertThrows(fn () => $archived->forceDelete(), AuthorizationException::class);

        $this->actingAs($admin)->patch(route('albums.restore', $album))->assertRedirect($trash);
        $this->assertNotSoftDeleted($album);
        $this->delete(route('albums.destroy', $album))->assertRedirect();
        $this->delete(route('albums.force-delete', $album))->assertRedirect($trash);
        $this->assertModelMissing($album);
        Storage::disk('public')->assertMissing($album->image_path);
    }
}
