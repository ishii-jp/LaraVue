<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\StorePhoto;
use App\Photo;
use App\Comment;
use App\Http\Requests\StoreComment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;


class PhotoController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth')->except(['index', 'download', 'show']);
    }

    /**
     * 写真一覧
     */
    public function index ()
    {
        return Photo::with(['owner', 'likes'])->orderBy(Photo::CREATED_AT, 'DESC')->paginate();
    }

    /**
     * 写真一覧(ユーザーごとの)
     */
    public function indexByUser (Request $request)
    {
        return Photo::with(['owner', 'likes'])->where('user_id', $request->userId)->orderBy(Photo::CREATED_AT, 'DESC')->paginate();
    }

    /**
     * 写真ダウンロード
     * 写真がなければ404にします
     * @param Photo $photo
     * @return \Illuminate\Http\Response
     */
    public function download(Photo $photo)
    {
        if (!Storage::cloud()->exists($photo->filename)) abort(404);

        $disposition = 'attachment; filename="' . $photo->filename . '"';
        $headers = [
            'Content-Type'        => 'application/octet-stream',
            'Content-Disposition' => $disposition,
        ];

        return response(Storage::cloud()->get($photo->filename), 200, $headers);
    }

    /**
     * 写真投稿
     * @param StorePhoto $request フォームリクエストバリデーションを行うため
     * @return \Illuminate\Http\Response
     */
    public function create(StorePhoto $request)
    {
        // 投稿写真の拡張子を取得する
        $extension = $request->photo->extension();

        // このインスタンスの生成方法は個人的に、なんとかしたい
        // メソッドインジェクション？サービスコンテナ？
        $photo = new Photo();

        // インスタンス生成時に割り振られたランダムなID値と
        // 本来の拡張子を組み合わせてファイル名とする
        $photo->filename = $photo->id . '.' . $extension;

        // S3にファイルを保存する
        // 第三引数の'public'はファイルを公開状態で本尊するため
        Storage::cloud()->putFileAs('', $request->photo, $photo->filename, 'public');

        // データベースエラー時にファイル削除を行うため、
        // トランザクションを行う
        DB::beginTransaction();

        try {
            Auth::user()->photos()->save($photo);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            // DBとの不整合を避けるためアップロードしたファイルを削除
            Storage::cloud()->delete($photo->filename);
            throw $e;
        }

        // リソースの新規作成のため、
        // レスポンスコードは201(CREATED)を返す
        return response($photo, 201);
    }

    /**
     * 写真詳細
     * @param string $disposition
     * @return Photo
     */
    public function show(string $id)
    {
        $photo = Photo::where('id', $id)->with(['owner', 'comments.author', 'likes'])->first();

        return $photo ?? abort(404);
    }

    /**
     * 写真削除機能
     * @param $request
     */
    public function destroy(Request $request)
    {
        $photo = Photo::with(['comments', 'like'])->find($request->route('photo_id'));

        abort_unless($photo, 404); // なかったら404へ

        DB::beginTransaction();
        try {
            $photo->delete();

            Storage::cloud()->delete($photo->filename);

            DB::commit();
        } catch(\Exception $e) {
            DB::rollBack();
            report($e);
            throw $e;
        }
        return '';
    }

    /**
     * コメント投稿
     * @param Photo $photo
     * @param StoreComment $request
     * @return \Illuminate\Http\Response
     */
    public function addComment(Photo $photo, StoreComment $request)
    {
        $comment = new Comment();
        $comment->content = $request->get('content');
        $comment->user_id = Auth::id();
        $photo->comments()->save($comment);

        // authorリレーションをロードするためにコメントを取得し直す
        $new_comment = Comment::where('id', $comment->id)->with('author')->first();

        return response($new_comment, 201);
    }

    /**
     * いいね機能
     * @param string $id
     * @return array
     */
    public function like (string $id)
    {
        $photo = Photo::where('id', $id)->with('likes')->first();

        if (! $photo) abort(404);

        // 何回実行してもいいねが一つしかつかないようにするため、
        // 紐付くいいねを削除してから追加するようにしています。
        $photo->likes()->detach(Auth::id());
        $photo->likes()->attach(Auth::id());

        return ["photo_id" => $id];
    }

    /**
     * いいね解除機能
     * @param string $id
     * @return array
     */
    public function unlike (string $id)
    {
        $photo = Photo::where('id', $id)->with('likes')->first();

        if (! $photo) abort(404);

       $photo->likes()->detach(Auth::id());

        return ["photo_id" => $id];
    }
}

