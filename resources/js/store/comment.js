const state = {
  comments: [],
};

const getters = {
  comments: state => state.comments,
};

const mutations = {
  initComments(state, comments) {
    state.comments = comments;
  },
  createComments(state, comment) {
    state.comments.push(comment);
  }

};

const actions = {
  async getComments(context, data) {
    const response = await axios.get('/api/comments', data);
    context.commit('initComments', response.data.data)
  },
  async createComment(context, data) {
    const response = await axios.post('/api/comment', data.form);
    const comment = response.data.data;
    comment.user = data.user;
    context.commit('createComments', comment)
  },
};

export default {
  namespaced: true,
  state,
  getters,
  mutations,
  actions
}
