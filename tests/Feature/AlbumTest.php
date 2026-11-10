<?php
namespace Tests\Feature;

use App\Models\Album;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AlbumTest extends TestCase
{
    use RefreshDatabase;

    public function test_all_five_albums_can_be_read_edited_and_soft_deleted(): void
    {
        $this->seed();
        $this->assertDatabaseCount('albums', 5);
        $this->get('/')->assertRedirect('/albums');
        $this->get(route('albums.create'))->assertOk();
        foreach (Album::all() as $album) {
            $this->get(route('albums.index'))->assertOk()->assertSee($album->title);
            $this->get(route('albums.show', $album))->assertOk()->assertSee($album->genre);
            $this->get(route('albums.edit', $album))->assertOk()->assertSee($album->title);
            $this->put(route('albums.update', $album), [
                'title' => $album->title.' — изменён', 'description' => $album->description,
                'genre' => $album->genre, 'release_date' => '2024-03-12',
            ])->assertSessionHasNoErrors()->assertRedirect(route('albums.show', $album));
            $this->assertEquals('2024-03-12', $album->fresh()->release_date->format('Y-m-d'));
            $this->delete(route('albums.destroy', $album))->assertRedirect(route('albums.index'));
            $this->assertSoftDeleted($album);
            $this->get(route('albums.show', $album))->assertNotFound();
        }
        $this->get(route('albums.index'))->assertOk()->assertSee('Альбомов пока нет.');
    }

    public function test_image_upload_resize_and_replacement(): void
    {
        Storage::fake('public');
        $data = ['title' => ' Новый альбом ', 'description' => 'Описание', 'genre' => 'Ambient', 'release_date' => '2024-03-12'];
        $this->post(route('albums.store'), $data + ['image' => UploadedFile::fake()->image('cover.png', 1000, 700)])
            ->assertSessionHasNoErrors()->assertRedirect();
        $album = Album::firstOrFail();
        $this->assertSame('Новый альбом', $album->title);
        $oldPath = $album->image_path;
        Storage::disk('public')->assertExists($oldPath);
        $size = getimagesize(Storage::disk('public')->path($oldPath));
        $this->assertSame([600, 600], [$size[0], $size[1]]);
        $this->put(route('albums.update', $album), $data + ['image' => UploadedFile::fake()->image('new.jpg', 800, 1000)])
            ->assertSessionHasNoErrors()->assertRedirect();
        Storage::disk('public')->assertMissing($oldPath);
        Storage::disk('public')->assertExists($album->fresh()->image_path);
    }

    public function test_validation_rejects_missing_fields_invalid_dates_and_non_images(): void
    {
        $this->post(route('albums.store'), [])->assertSessionHasErrors(['title', 'description', 'genre', 'image']);
        $this->post(route('albums.store'), [
            'title' => 'Альбом', 'description' => 'Описание', 'genre' => 'Ambient',
            'release_date' => '2024-02-31',
            'image' => UploadedFile::fake()->create('cover.txt', 10, 'text/plain'),
        ])->assertSessionHasErrors(['release_date', 'image']);
        $this->assertDatabaseCount('albums', 0);
    }

    public function test_date_mutator_normalizes_date(): void
    {
        $album = new Album(['release_date' => '2024-03-12 16:45:00']);
        $this->assertSame('2024-03-12', $album->getAttributes()['release_date']);
        $this->assertSame('12.03.2024', $album->release_date->format('d.m.Y'));
    }
}
