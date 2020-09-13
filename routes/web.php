<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Route::get('/', function () {
//     return view('welcome');
// });

// APIのURL以外のリクエストに対してはindexテンプレートを返す
// 画面遷移はフロントエンドのVueRouterが制御する

//写真ダウンロード
Route::get('/photos/{photo}/download', 'PhotoController@download');

// Route::get('/{any?}', fn() => view('index'))->where('any', '.+');
// なんか上記だとエラーしてたので、クロージャで実装しました。
Route::get('/{any?}', function () {
    return view('index');
})->where('any', '.+');

// api.phpの方へルーティングを書くためコメントアウトしています
// Auth::routes();

// Route::get('/home', 'HomeController@index')->name('home');

// phpinfo()を確認したくなった時は、下記をコメントアウトを解除する
// Route::get('phpinfo', function () {
//     phpinfo();
// });