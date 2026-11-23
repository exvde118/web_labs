<?php

namespace Tests\Feature;

use App\Events\FriendAdded;
use App\Models\Album;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class FriendshipTest extends TestCase
{
    use RefreshDatabase;

    public function test_friendship_dispatches_an_event_and_is_reciprocal_and_idempotent(): void
    {
        [$dan, $admin] = User::factory()->count(2)->create();
        $events = [];
        Event::listen(FriendAdded::class, function (FriendAdded $event) use (&$events) {
            $events[] = [$event->userId, $event->friendId];
        });
        $addAdmin = route('friends.store', $admin->username);

        $this->post($addAdmin)->assertRedirect(route('login'));
        $this->get(route('feed'))->assertRedirect(route('login'));
        $this->actingAs($dan)->post(route('friends.store', $dan->username))->assertUnprocessable();
        $this->assertDatabaseCount('friendships', 0);

        $this->post($addAdmin)->assertRedirect(route('users.index'));
        $this->assertSame([$admin->id], $dan->friends()->allRelatedIds()->all());
        $this->assertSame([$dan->id], $admin->friends()->allRelatedIds()->all());
        $this->post($addAdmin)->assertRedirect(route('users.index'));
        $this->actingAs($admin)->post(route('friends.store', $dan->username))->assertRedirect(route('users.index'));
        $this->assertDatabaseCount('friendships', 2);
        $this->assertSame([[$dan->id, $admin->id]], $events);
        $this->get(route('users.index'))->assertOk()->assertSee('Удалить из друзей');

        $removeDan = route('friends.destroy', $dan->username);
        $this->delete($removeDan)->assertRedirect(route('users.index'));
        $this->delete($removeDan)->assertRedirect(route('users.index'));
        $this->assertDatabaseCount('friendships', 0);
        $this->get(route('users.index'))->assertOk()->assertDontSee('Удалить из друзей');
    }

    public function test_feed_shows_active_friend_tracks_by_creation_date_for_both_members(): void
    {
        [$dan, $friend, $stranger] = User::factory()->count(3)->create();
        $newest = $this->album($friend, 'Новая запись друга', '2026-02-03 12:00:00');
        $older = $this->album($friend, 'Старая запись друга', '2026-01-02 12:00:00');
        $archived = $this->album($friend, 'Удалённая запись друга', '2026-02-04 12:00:00');
        $archived->delete();
        $own = $this->album($dan, 'Своя запись', '2026-02-04 12:00:00');
        $this->album($stranger, 'Запись незнакомца', '2026-02-05 12:00:00');

        $this->actingAs($dan)->get(route('feed'))->assertOk()->assertSee('В ленте пока нет треков.');
        $this->post(route('friends.store', $friend->username))->assertRedirect();
        $this->get(route('feed'))->assertOk()
            ->assertViewHas('albums', fn ($albums) => $albums->modelKeys() === [$newest->id, $older->id])
            ->assertSeeInOrder([$newest->title, $older->title])
            ->assertDontSee('Удалённая запись друга')->assertDontSee('Запись незнакомца')->assertDontSee($own->title);
        $this->actingAs($friend)->get(route('feed'))->assertOk()->assertSee($own->title);
        $this->actingAs($dan)->delete(route('friends.destroy', $friend->username))->assertRedirect();
        $this->get(route('feed'))->assertOk()->assertDontSee($newest->title);
    }

    public function test_comment_highlighting_depends_on_the_viewers_friends(): void
    {
        [$viewer, $friend, $stranger] = User::factory()->count(3)->create();
        $album = $this->album($stranger, 'Чужой трек', '2026-01-02 12:00:00');
        $comment = route('albums.comments.store', $album);
        $show = route('albums.show', $album);

        $this->post($comment, ['body' => 'Комментарий владельца'])->assertRedirect();
        $this->actingAs($friend)->post($comment, ['body' => 'Комментарий друга'])->assertRedirect();
        $this->actingAs($viewer)->get($show)->assertOk()->assertDontSee('>Друг</span>', false);
        $this->post(route('friends.store', $friend->username))->assertRedirect();

        $page = $this->get($show)->assertOk()
            ->assertSeeInOrder(['Комментарий владельца', '>Друг</span>', 'Комментарий друга'], false);
        $this->assertSame(1, substr_count($page->getContent(), '>Друг</span>'));
        $this->actingAs($stranger)->get($show)->assertOk()->assertDontSee('>Друг</span>', false);
    }

    private function album(User $owner, string $title, string $createdAt): Album
    {
        $this->actingAs($owner);
        $album = $owner->albums()->make([
            'title' => $title,
            'description' => 'Описание',
            'genre' => 'Ambient',
            'image_path' => 'albums/seed/1.jpg',
        ]);
        $album->created_at = $createdAt;
        $album->saveOrFail();

        return $album;
    }
}
