const state = {
  status: []
};

const getters = {
  status: state => state.status,
};

const mutations = {
  initStatus(state, status) {
    state.status = status;
  },
  addStatus(state, status) {
    status.task = [];
    state.status.push(status);
  },
  deleteStatus(state, status_id) {
    const index = state.status.findIndex(status => status.id === status_id);
    state.status.splice(index, 1);
  },
  changeStatus(state, status) {
    state.status = state.status.map((record) => {
      if (record.id === status.id) {
        record.name = status.name;
      }
      return record;
    })
  },
  addTask(state, task) {
    state.status = state.status.map((record) => {
      if (record.id === task.status_id) {
        record.task.push(task);
      }
      return record;
    })
  }
};

const actions = {
  async getStatus(context, data) {
    const response = await axios.get('/api/status_list', data);
    context.commit('initStatus', response.data.data)
  },
  async createStatus(context, data) {
    const response = await axios.post('/api/status', data);
    context.commit('addStatus', response.data.data)
  },
  async changeStatus(context, data) {
    const response = await axios.put('/api/status', data);
    context.commit('changeStatus', response.data.data)
  },
  async deleteStatus(context, data) {
    await axios.delete('/api/status', data);
    context.commit('deleteStatus', data.params.status_id)
  },
  async createTask(context, data) {
    const response = await axios.post('/api/task', data);
    context.commit('addTask', response.data.data)
  },
  async moveTask(context, data) {
    await axios.post('/api/moveTask', data);
  },
};

export default {
  namespaced: true,
  state,
  getters,
  mutations,
  actions
}
