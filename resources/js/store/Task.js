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
    const response = await axios.get('/api/tasks/' + data.params.task_id, data);
    context.commit('initTask', response.data.data)
  },
  async assign(context, data) {
    await axios.post('/api/assign_task/' + data.task_id, data);
  },
  async changeTask(context, data) {
    const response = await axios.put('/api/tasks/' + data.task_id, data);
    context.commit('changeTask', response.data.data)
  },
  async deleteTask(context, data) {
    await axios.delete('/api/tasks/' + data.params.task_id, data);
  },
};

export default {
  namespaced: true,
  state,
  getters,
  mutations,
  actions
}
