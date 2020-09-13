<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Photo;
use App\Comment;

class PhotoDetailApiTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function should_正しい構造のJSONを返却する()
    {
        factory(Photo::class)->create();
        $photo = Photo::first();

        $response = $this->json('GET', route('photo.show', ['id' => $photo->id]));

        $response->assertStatus(200)
            ->assertJsonFragment([
                'id'       => $photo->id,
                'url'      => $photo->url,
                'owner'    => [
                    'id' => $photo->owner->id,
                    'name' => $photo->owner->name,
                    'email' => $photo->owner->email,
                    'created_at' => $photo->owner->created_at,
                    'photos_count' => $photo->owner->photos_count,
                    'comments_count' => $photo->owner->comments_count,
                    'likes_count' => $photo->owner->likes_count,
                    'photos' => $photo->owner->photos
                    ->map(function ($photo){
                        return [
                            'id' => $photo->id,
                            'url' => $photo->url,
                            'likes_count' => $photo->likes_count,
                            'liked_by_user' => $photo->liked_by_user,
                        ];
                    })->all(),
                    'comments' => [],
                    'likes' => [],
                ],
                'comments' => $photo->comments
                    ->sortByDesc('id')
                    ->map(function ($comment) {
                        return [
                            'author' => [
                                'name' => $comment->author->name
                            ],
                            'content' => $comment->content
                        ];
                    })->all(),
                'liked_by_user' => false,
                'likes_count' => 0
            ]);
    }
}
