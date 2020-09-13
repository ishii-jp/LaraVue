import Axios from "axios"
import { OK, CREATED, UNPROCESSABLE_ENTITY } from '../util'

const state = {
    user: null,
    apiStatus: null,
    loginErrorMessages: null,
    registerErrorMessages: null
}

const getters = {
    check: state => !! state.user,
    username: state => state.user ? state.user.name : '',
    userId: state => state.user ? state.user.id : ''
}

const mutations = {
    setUser (state, user) {
        state.user = user
    },
    setApiStatus (state, status) {
        state.apiStatus = status
    },
    setLoginErrorMessages (state, messages) {
        state.loginErrorMessages = messages
    },
    setRegisterErrorMessages (state, messages) {
        state.registerErrorMessages = messages
    }
}

const actions = {
    // 会員登録
    async register (context, data) {
        context.commit('setApiStatus', null)
        const response = await axios.post('/api/register', data)

        if (response.status === CREATED) {
          context.commit('setApiStatus', true)
          context.commit('setUser', response.data)
          return false
        }

        context.commit('setApiStatus', false)
        if (response.status === UNPROCESSABLE_ENTITY) {
          context.commit('setRegisterErrorMessages', response.data.errors)
        } else {
          context.commit('error/setCode', response.status, { root: true })
        }
      },

    //   ログイン
    async login (context, data) {
        context.commit('setApiStatus', null)
        const response = await Axios.post('api/login', data)

        if (response.status === OK){
            context.commit('setApiStatus', true)
            context.commit('setUser', response.data)
            return false
        }

        context.commit('setApiStatus', false)

        if (response.status === UNPROCESSABLE_ENTITY) {
            context.commit('setLoginErrorMessages', response.data.errors)
        } else {
            context.commit('error/setCode', response.status, { root: true })
        }
    },

    // ログアウト
    async logout (context) {
        const response = await axios.post('/api/logout')
        context.commit('setUser', null)
    },

    // ログインユーザチェック
    async currentUser (context) {
        const response = await axios.get('/api/user')
        const user = response.data || null
        context.commit('setUser', user)
    }
}

export default {
  namespaced: true,
  state,
  getters,
  mutations,
  actions
}