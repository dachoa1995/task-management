const state = {
  task: {},
};

const getters = {
  task: state => state.task,
};

const mutations = {
  initTask(state, task) {
    state.task = task;
  },
  changeTask(state, task) {
    state.task.name = task.name;
    state.task.description = task.description;
    state.task.deadline = task.deadline;
  }
};

const actions = {
  async getTask(context, data) {
    const response = await axios.get('/api/task', data);
    context.commit('initTask', response.data.data)
  },
  async assign(context, data) {
    await axios.post('/api/assign_task', data);
  },
  async changeTask(context, data) {
    const response = await axios.post('/api/change_task', data);
    context.commit('changeTask', response.data.data)
  },
  async deleteTask(context, data) {
    await axios.post('/api/delete_task', data);
  },
};

export default {
  namespaced: true,
  state,
  getters,
  mutations,
  actions
}
