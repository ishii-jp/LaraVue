<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Photo;
use App\User;

class AddComentApiTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = factory(User::class)->create();
    }

    /**
     * @test
     */
    public function should_コメントを追加できる()
    {
        factory(Photo::class)->create();
        $photo = Photo::first();
        $content = 'sample content';

        $response = $this->actingAs($this->user)
            ->json('POST', route('photo.comment', [
                'photo' => $photo->id
            ]), compact('content'));

        $comments = $photo->comments()->get();

        // dd(json_decode($response->content(), true));
        $response->assertStatus(201)
                // JSONフォーマットが期待通りであること
                ->assertJsonFragment([
                    "author" => [
                        'id' => $this->user->id,
                        "name" => $this->user->name,
                        'email' => $this->user->email,
                        'created_at' => $this->user->created_at,
                        'photos_count' => $this->user->photos_count,
                        'comments_count' => $this->user->comments_count,
                        'likes_count' => $this->user->likes_count,
                        'likes' => [],
                        'comments' => $this->user->comments->sortByDesc('id')->map(function ($comment){
                            return [
                                'content' => $comment->content
                            ];
                        })->all(),
                        'photos' => [],
                    ],
                    "content" => $content,
                ]);

        // DBにコメントが1件登録されていること
        $this->assertEquals(1, $comments->count());
        // 内容がAPIでリクエストしたものであること
        $this->assertEquals($content, $comments[0]->content);

    }
}
