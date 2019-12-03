const state = {
  task: [],
  comments: []
};

const getters = {
  task: state => state.task,
};

const mutations = {
  initTask(state, task) {
    state.task = task;
  },
  initComments(state, comments) {
    state.comments = comments;
  },
};

const actions = {
  async getTask(context, data) {
    const response = await axios.get('/api/task', data);
    context.commit('initTask', response.data.data)
  },
  async getComments(context, data) {
    const response = await axios.get('/api/comments', data);
    context.commit('initComments', response.data.data)
  },
};

export default {
  namespaced: true,
  state,
  getters,
  mutations,
  actions
}
