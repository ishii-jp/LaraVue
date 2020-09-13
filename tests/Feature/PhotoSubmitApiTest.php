<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\User;
use App\Photo;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class PhotoSubmitApiTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = factory(User::class)->create();
    }

    // ※現在下記エラーがテスト時に発生しています。
    // 1) Tests\Feature\PhotoSubmitApiTest::should_ファイルをアップロードできる
    // Error: Call to undefined function Illuminate\Http\Testing\imagecreatetruecolor()

    // /var/www/html/vuesplash/vendor/laravel/framework/src/Illuminate/Http/Testing/FileFactory.php:75
    // /var/www/html/vuesplash/vendor/laravel/framework/src/Illuminate/Support/helpers.php:433
    // /var/www/html/vuesplash/vendor/laravel/framework/src/Illuminate/Http/Testing/FileFactory.php:87
    // /var/www/html/vuesplash/vendor/laravel/framework/src/Illuminate/Http/Testing/FileFactory.php:58
    // /var/www/html/vuesplash/tests/Feature/PhotoSubmitApiTest.php:37
    // これはどうやらpngやgiffイメージをアップした際には起こらないようですが、
    // JPEGイメージをアップした際に発生するようです。
    // PHP拡張モジュールGDにて、JPEGサポートが有効になっていないことが原因のようです。
    // 解決すには、Dockerfileを修正してgdをインストールする必要がありそうです。
    
    /**
     * @test
     */
    public function should_ファイルをアップロードできる()
    {
        // S3ではなくテスト用のストレージを使用する
        // → storage/framework/testing
        Storage::fake('s3');

        $response = $this->actingAs($this->user)
            ->json('POST', route('photo.create'), [
                // ダミーファイルを作成して送信している
                'photo' => UploadedFile::fake()->image('photo.jpg'),
            ]);

        // レスポンスが201(CREATED)であること
        $response->assertStatus(201);

        $photo = Photo::first();

        // 写真のIDが12桁のランダムな文字列であること
        $this->assertRegExp('/^[0-9a-zA-Z-_]{12}$/', $photo->id);

        // DBに挿入されたファイル名のファイルがストレージに保存されていること
        Storage::cloud()->assertExists($photo->filename);
    }

    /**
     * @test
     */
    public function should_データベースエラーの場合はファイルを保存しない()
    {
        // 乱暴だがこれでDBエラーを起こす
        Schema::drop('photos');

        Storage::fake('s3');

        $response = $this->actingAs($this->user)
            ->json('POST', route('photo.create'), [
                'photo' => UploadedFile::fake()->image('photo.jpg'),
            ]);

        // レスポンスが500(INTERNAL SERVER ERROR)であること
        $response->assertStatus(500);

        // ストレージにファイルが保存されていないこと
        $this->assertEquals(0, count(Storage::cloud()->files()));
    }

    /**
     * @test
     */
    public function should_ファイル保存エラーの場合はDBへの挿入はしない()
    {
        // ストレージをモックして保存時にエラーを起こさせる
        Storage::shouldReceive('cloud')
            ->once()
            ->andReturnNull();

        $response = $this->actingAs($this->user)
            ->json('POST', route('photo.create'), [
                'photo' => UploadedFile::fake()->image('photo.jpg'),
            ]);

        // レスポンスが500(INTERNAL SERVER ERROR)であること
        $response->assertStatus(500);

        // データベースに何も挿入されていないこと
        $this->assertEmpty(Photo::all());
    }
}
