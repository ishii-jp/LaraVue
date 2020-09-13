<?php

namespace Tests\Feature\app\Http;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileControllerTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        $this->photo = factory(\App\Photo::class)->create();

        // PhotoFactoryでユーザーのファクトリーも作成しているのでここで取得します
        $this->user = \App\User::first();
    }

    /**
     * @test
     */
    public function invoke_ユーザー情報が取得できること()
    {
        $response = $this->actingAs($this->user)->json('get', route('profile', ['user_id' => $this->user->id]));

        // dd(json_decode($response->content(), true));
        $response->assertStatus(200)->assertJsonFragment([
            'id' => $this->user->id,
            'name' => $this->user->name,
            'email' => $this->user->email,
            'created_at' => $this->user->created_at,
            'photos_count' => $this->user->photos_count,
            'comments_count' => $this->user->comments_count,
            'likes_count' => $this->user->likes_count,
            'photos' => $this->user->photos->sortByDesc('id')->map(function ($photo){
                return [
                    'id' => $photo->id,
                    'liked_by_user' => false,
                    'likes_count' => 0,
                    'url' => $photo->url
                ];
            })->all(),
            'comments' => [],
            'likes' => []
        ]);
    }

    /**
     * @test
     */
    public function invoke_存在しないユーザーIDがリクエストされたら404をレスポンスすること()
    {
        $this->actingAs($this->user)
            ->json('get', route('profile', ['user_id' => 10]))
            ->assertStatus(404);
    }
}
