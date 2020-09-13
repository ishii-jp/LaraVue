<template>
<div class="photo-list">
    <div class="grid">
        ユーザー情報
        <!-- なぜか下記のように書くと値を取得できるがvue.jsでエラーになる -->
        <!-- Error in render: "TypeError: Cannot read property 'name' of null" -->
        <table v-if="users">
            <tr>
                <td>ユーザーネーム：</td>
                <td><strong>{{ users.name }}</strong></td>
            </tr>
            <tr>
                <td>メールアドレス：</td>
                <td><strong>{{ users.email }}</strong></td>
            </tr>
            <tr>
                <td>写真投稿数：</td>
                <td><strong>{{ users.photos_count }}</strong></td>
            </tr>
            <tr>
                <td>いいねした数：</td>
                <td><strong>{{ users.likes_count }}</strong></td>
            </tr>
            <!-- 2020/07/19
            フォロー機能を実装した際には、下記の様に追加予定 -->
            <!-- <tr>
                <td>フォローした数：</td>
                <td><strong>{{ users.likes_count }}</strong></td>
            </tr>
            <tr>
                <td>フォローされた数：</td>
                <td><strong>{{ users.likes_count }}</strong></td>
            </tr> -->
        </table>

        <!-- TODO ここでコメントや投稿写真を表示する場合はv-forで回して表示させる。 -->
        <!-- <div v-for="(user, index) in users" :key="index"> -->
    </div>
</div>
</template>

<script>
import { OK, CREATED, UNPROCESSABLE_ENTITY } from '../util'

export default {
    props: {
        id: {
            type: String,
            required: true
        }
    },
    data () {
        return {
            users: null,
        }
    },
    methods: {
        async getUser () {
            const response = await axios.get(`/api/profile/${this.id}`)

            if (response.status !== OK){
                this.$store.commit('error/setCode', response.status)
                return false
            }

            this.users = response.data
        }
    },
    watch: {
        $route: {
            async handler () {
                // ログインしていたらユーザー情報を取得
                // 未ログインだったらログイン画面へ
                if (this.isLogin){
                    await this.getUser()
                } else {
                    this.$router.push({path: '/login'})
                }
            },
            immediate: true
        },
    },
    computed: {
        isLogin () {
            return this.$store.getters['auth/check']
        }
    }
}
</script>