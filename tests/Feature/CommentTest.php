<?php

namespace Tests\Feature;

use App\Models\Album;
use App\Models\Comment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommentTest extends TestCase
{
    use RefreshDatabase;

    public function test_members_can_comment_on_their_own_and_other_members_albums(): void
    {
        $this->seed();
        $dan = User::whereUsername('dan')->firstOrFail();
        $admin = User::whereUsername('admin')->firstOrFail();
        $own = $dan->albums()->firstOrFail();
        $other = $admin->albums()->firstOrFail();

        $this->actingAs($dan);
        $this->post(route('albums.comments.store', $own), ['body' => 'Свой трек'])
            ->assertRedirect(route('albums.show', $own).'#comments');
        $this->post(route('albums.comments.store', $other), [
            'body' => '<b>Чужой трек</b>',
            'user_id' => $admin->id,
            'album_id' => $own->id,
        ])->assertRedirect(route('albums.show', $other).'#comments');

        $comment = $other->comments()->sole();
        $this->assertTrue($comment->album->is($other));
        $this->assertTrue($comment->user->is($dan));
        $this->assertSame(2, $dan->comments()->count());
        $this->get(route('albums.show', $other))->assertOk()
            ->assertSee('&lt;b&gt;Чужой трек&lt;/b&gt;', false)->assertDontSee('Свой трек');
    }

    public function test_comments_require_authentication_valid_content_and_an_active_album(): void
    {
        $this->seed();
        $album = Album::firstOrFail();
        $action = route('albums.comments.store', $album);

        $this->post($action, ['body' => 'Текст'])->assertRedirect(route('login'));
        $this->actingAs($album->user)->post($action, ['body' => '   '])->assertSessionHasErrors('body');
        $this->post($action, ['body' => str_repeat('а', 2001)])->assertSessionHasErrors('body');
        $this->delete(route('albums.destroy', $album))->assertRedirect();
        $this->post($action, ['body' => 'Текст'])->assertNotFound();
        $this->assertDatabaseCount('comments', 0);
    }

    public function test_soft_delete_keeps_comments_and_permanent_delete_removes_them(): void
    {
        $this->seed();
        $album = Album::firstOrFail();
        $this->actingAs($album->user)->post(route('albums.comments.store', $album), ['body' => 'Останется после восстановления'])
            ->assertSessionHasNoErrors();
        $comment = Comment::sole();

        $this->delete(route('albums.destroy', $album))->assertRedirect();
        $this->assertModelExists($comment);

        $this->actingAs(User::factory()->create(['is_admin' => true]));
        $this->patch(route('albums.restore', $album))->assertRedirect();
        $this->get(route('albums.show', $album))->assertSee($comment->body);
        $this->delete(route('albums.destroy', $album))->assertRedirect();
        $this->delete(route('albums.force-delete', $album))->assertRedirect();
        $this->assertModelMissing($comment);
    }
}
