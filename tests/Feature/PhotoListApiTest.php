<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Photo;
use App\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PhotoListApiTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function should_正しい構造のJSONを返却する()
    {
        // 5つの写真データを生成する
        factory(Photo::class, 5)->create();

        $response = $this->json('GET', route('photo.index'));

        // 生成した写真データを作成日降順で取得
        $photos = Photo::with(['owner'])->orderBy('created_at', 'desc')->get();

        // data項目の期待値
        $expected_data = $photos->map(function ($photo) {
            return [
                'id' => $photo->id,
                'url' => $photo->url,
                'owner' => [
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
                'liked_by_user' => false,
                'likes_count' => 0
            ];
        })
        ->all();

        $response->assertStatus(200)
            // レスポンスJSONのdata項目に含まれる要素が5つであること
            ->assertJsonCount(5, 'data')
            // レスポンスJSONのdata項目が期待値と合致すること
            ->assertJsonFragment([
                "data" => $expected_data,
            ]);
    }
}
