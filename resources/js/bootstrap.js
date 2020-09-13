window._ = require('lodash');

/**
 * We'll load jQuery and the Bootstrap jQuery plugin which provides support
 * for JavaScript based Bootstrap features such as modals and tabs. This
 * code may be modified to fit the specific needs of your application.
 */

try {
    window.Popper = require('popper.js').default;
    window.$ = window.jQuery = require('jquery');

    require('bootstrap');
} catch (e) {}

// ヘッダーを見てCSRFトークンチェックを行う追加設定
import { getCookieValue } from './util'
window.axios = require('axios')

/**
 * We'll load the axios HTTP library which allows us to easily issue requests
 * to our Laravel back-end. This library automatically handles sending the
 * CSRF token as a header based on the value of the "XSRF" token cookie.
 */

window.axios = require('axios');

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// ヘッダーを見てCSRFトークンチェックを行う追加設定
window.axios.interceptors.request.use(config => {
    // クッキーからトークンを取り出してヘッダーに添付する
    config.headers['X-XSRF-TOKEN'] = getCookieValue('XSRF-TOKEN')
    return config
})

// axiosのresponseインターセプタはレスポンスを受けた後の処理を上書きします。
// 成功時の処理は変更しませんが、失敗時にエラーレスポンスが返ってきた場合、
// エラーそのものではなくレスポンスオブジェクトを返すという処理です。
// 同じような処理がAPI呼び出しのところで重複してしまうため、
// 下記にまとめました。
window.axios.interceptors.response.use(
    response => response,
    error => error.response || error
)

/**
 * Echo exposes an expressive API for subscribing to channels and listening
 * for events that are broadcast by Laravel. Echo and event broadcasting
 * allows your team to easily build robust real-time web applications.
 */

// import Echo from 'laravel-echo';

// window.Pusher = require('pusher-js');

// window.Echo = new Echo({
//     broadcaster: 'pusher',
//     key: process.env.MIX_PUSHER_APP_KEY,
//     cluster: process.env.MIX_PUSHER_APP_CLUSTER,
//     forceTLS: true
// });
