<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;

class PhotoDestroyApiTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = factory(\App\User::class)->create();

        Storage::fake('s3'); // テスト用ストレージを使用

        // 実際にAPIヘリクエストを送りダミーファイルを作成して送信している
        $this->actingAs($this->user)
            ->json('POST', route('photo.create'), [
                'photo' => UploadedFile::fake()->image('photo.jpg'),
            ]);

        $this->photo = \App\Photo::first(); // 作成された写真のデータをDBから取得
    }

    /**
     * @test
     */
    public function destroy_正常に画像をS3から削除しDBから関連レコードを削除できる事()
    {
        $this->actingAs($this->user)
        ->json('delete', route('photo.destroy', [
            'photo_id' => $this->photo->id,
        ]));

        $this->assertDatabaseMissing('photos', [
            'filename' => $this->photo->filename
        ]);

        Storage::cloud()->assertMissing($this->photo->filename);
    }

    /**
     * @test
     */
    public function destroy_存在しないphoto_idを送信された場合404になる事()
    {
        $response = $this->actingAs($this->user)
        ->json('delete', route('photo.destroy', [
            'photo_id' => 'hogehoge.jpg',
        ]));

        $response->assertStatus(404);
    }

    /**
     * @test
     */
    public function destroy_写真に紐づくコメントといいねがあった場合は連動して削除されること()
    {
        $commentsData = [
            'photo_id' => $this->photo->id,
            'user_id' => $this->user->id,
            'content' => 'テストテスト'
        ];

        // コメントを作成
        \App\Comment::insert($commentsData);

        $likesData = [
            'photo_id' => $this->photo->id,
            'user_id' => $this->user->id
        ];

        // いいねを作成
        \App\Like::insert($likesData);

        $this->assertDatabaseHas('comments', $commentsData); // commentsテーブルに作成したレコードがあるか検証
        $this->assertDatabaseHas('likes', $likesData); // likesテーブルに作成したコードがあるか検証

        // APIへリクエストし削除を実行
        $this->actingAs($this->user)
        ->json('delete', route('photo.destroy', [
            'photo_id' => $this->photo->id,
        ]));

        Storage::cloud()->assertMissing($this->photo->filename); // S3から写真が削除されている事を検証

        // 作成した写真データが削除されている事を検証
        $this->assertDatabaseMissing('photos', ['filename' => $this->photo->filename]);
        // 削除した写真データに紐づいたcommentsテーブルのレコードが削除されていることを検証
        $this->assertDatabaseMissing('comments', $commentsData);
        // 削除した写真データに紐づいたlikesテーブルのレコードが削除されていることを検証
        $this->assertDatabaseMissing('likes', $likesData);
    }
}
