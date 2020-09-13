<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;

class ProfileController extends Controller
{
    /**
     * Handle the incoming request.
     * プロフィール画面用アクション
     * URLのユーザーIDに紐づくユーザー情報を取得して返します。
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        $users = User::with([
            'photos',
            'comments',
            'likes'
        ])
        ->orderBy('created_at', 'desc')
        ->find($request->route('user_id'));

        return $users ?? abort(404);
    }
}
