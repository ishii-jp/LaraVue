<?php

namespace App;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /** JSON含めるアクセサ */
    protected $appends = [
        'photos_count',
        'comments_count',
        'likes_count'
    ];

    /** 表示する属性 */
    protected $visible = [
        'id',
        'name',
        'email',
        'created_at',
        'photos',
        'comments',
        'likes',
        'photos_count',
        'comments_count',
        'likes_count'
    ];

    /**
     * The attributes that should be hidden for arrays.
     * 上記で$visibleを定義したため$hiddenはコメントアウトしました。
     *
     * @var array
     */
    // protected $hidden = [
    //     'password', 'remember_token',
    // ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * リレーションシップ - photosテーブル
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function photos()
    {
        return $this->hasMany(Photo::class);
    }

    /**
     * リレーション - commentsテーブル
     */
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    /**
     * リレーション - likesテーブル
     */
    public function likes()
    {
        return $this->hasMany(Like::class);
    }

    /**
     * アクセサ - photos_count
     * @return int
     */
    public function getPhotosCountAttribute()
    {
        return $this->photos->count();
    }

    /**
     * アクセサ - comemnts_count
     * @return int
     */
    public function getCommentsCountAttribute()
    {
        return $this->comments->count();
    }

    /**
     * アクセサ - likes_count
     * @return int
     */
    public function getLikesCountAttribute()
    {
        return $this->likes->count();
    }
}
