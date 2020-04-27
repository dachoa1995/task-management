const state = {
  user: null
};

const getters = {
  check: state => !! state.user,
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
    context.commit('setUser', data)
  },
  async currentUser (context) {
    const response = await axios.get('/user');
    const user = response.data || null;
    context.commit('setUser', user)
  },
  async logout (context) {
    await axios.get('/logout');
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
