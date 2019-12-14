import {getCookieValue, setCookie, deleteCookie} from '../util'

const state = {
  user: null
};

const getters = {
  isLogin: state => getCookieValue('api_token') !== '',
  name: state => state.user ? state.user.name : '',
  avatarURL: state => state.user ? state.user.avatarURL : '',
  api_token: state => state.user ? state.user.api_token : '',
};

const mutations = {
  setUser (state, user) {
    state.user = user;
  }
};

const actions = {
  async login (context, data) {
    context.commit('setUser', data);
    setCookie('api_token', data.api_token, 1);
  },
  async currentUser (context) {
    const response = await axios.get('/api/user');
    const user = response.data || null;
    context.commit('setUser', user)
  },
  async logout (context) {
    await axios.get('/logout');
    deleteCookie('api_token');
    context.commit('setUser', null)
  }
};

export default {
  namespaced: true,
  state,
  getters,
  mutations,
  actions
}
