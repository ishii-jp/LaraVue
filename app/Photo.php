<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class Photo extends Model
{
    /** プライマリーキーの型 */
    protected $keyType = 'string';

    /** JSON含めるアクセサ */
    protected $appends = ['url', 'likes_count', 'liked_by_user'];

    /** ページネーションの1ページあたりの表示件数 */
    // protected $perPage = 6;

    /** (参考)JSONに含めない属性の場合の書き方 */
    // protected $hidden = [
    //     'user_id',
    //     'filename',
    //     self::CREATED_AT,
    //     self::UPDATED_AT,
    // ];

    /** JSONに含める属性 */
    protected $visible = ['id', 'owner', 'url', 'comments', 'likes_count', 'liked_by_user', 'like'];

    /**
     * deletingイベント
     * photosテーブルのレコードを削除するときは、
     * ここでリレーションしているコメントといいねテーブルも削除しています。
     * コントローラでは$photo->delete()とだけ書けば上記テーブルは削除されます。
     */
    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($photo) {
            $photo->like()->delete();
            $photo->comments()->delete();
        });
    }
    /** IDの桁数 */
    const ID_LENGTH = 12;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        if (! Arr::get($this->attributes, 'id')) {
            $this->setId();
        }
    }

    /**
     * リレーションシップ - usersテーブル
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function owner()
    {
        return $this->belongsTo('App\User', 'user_id', 'id', 'users');
    }

    /**
     * リレーションシップ - commentsテーブル
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function comments()
    {
        return $this->hasMany('App\Comment')->orderBy('id', 'desc');
    }

    /**
     * リレーションシップ - usersテーブル
     * likesテーブルを中間テーブルとしたphotosテーブルとusersテーブルの多対多のリレーションです
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function likes()
    {
        return $this->belongsToMany('App\User', 'likes')->withTimestamps();
    }

    /**
     * リレーションシップ - likesテーブル
     * 本当は多対多の関係な気がするけど、belongsToManyにして
     * 中間テーブルを用意しても何しもDBの整合性エラーが出てまともに動かなかった。
     * したがってhasManyでリレーションしてます。
     * ただ写真消したら一緒に消えて欲しいだけなので。
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function like()
    {
        return $this->hasMany(Like::class);
    }

    /**
     * アクセサ - url
     * @return string
     */
    public function getUrlAttribute()
    {
        return Storage::cloud()->url($this->attributes['filename']);
    }

    /**
     * アクセサ - likes_count
     * @return int
     */
    public function getLikesCountAttribute()
    {
        return $this->likes->count();
    }

    /**
     * アクセサ - liked_by_user
     * @return boolean
     */
    public function getLikedByUserAttribute()
    {
        if (Auth::guest()) return false;

        return $this->likes->contains(function ($user) {
            return $user->id === Auth::id();
        });
    }

    /**
     * ランダムなID値をid属性に代入する
     */
    private function setId()
    {
        $this->attributes['id'] = $this->getRandomId();
    }

    /**
     * ランダムなID値を生成する
     */
    private function getRandomId()
    {
        $characters = array_merge(
            range(0, 9),
            range('a', 'z'),
            range('A', 'Z'),
            ['-', '_']
        );

        $length = count($characters);
        $id = "";

        for ($i = 0; $i < self::ID_LENGTH; $i++) $id .= $characters[random_int(0, $length - 1)];

        return $id;
    }
}
