const state = {
  projects: []
};

const getters = {
  projects: state => state.projects,
};

const mutations = {
  initProject(state, projects) {
    state.projects = projects;
  },
  addProject(state, project) {
    state.projects.push(project);
  },
  deleteProject(state, project_id) {
    const index = state.projects.findIndex(project => project.id === project_id);
    state.projects.splice(index, 1);
  },
  changeProject(state, project) {
    state.projects = state.projects.map((record) => {
      if (record.id === project.project_id) {
        record.name = project.name;
        record.description = project.description;
      }
      return record;
    })
  }
};

const actions = {
  async getProjects(context) {
    const response = await axios.get('/api/projects');
    context.commit('initProject', response.data.data)
  },
  async createProject(context, data) {
    const response = await axios.post('/api/projects', data);
    context.commit('addProject', response.data.data)
  },
  async deleteProject(context, data) {
    await axios.delete('/api/projects/' + data.params.project_id, []);
    context.commit('deleteProject', data.params.project_id)
  },
  async changeProject(context, data) {
    await axios.put('/api/projects/' + data.project_id, data);
    context.commit('changeProject', data)
  },
  async getProjectDetail(context, data) {
    const response = await axios.get('/api/projects/' + data.params.project_id, []);
    context.commit('initProject', response.data.data)
  },
  async assign(context, data) {
    await axios.post('/api/assign_project/' + data.project_id, data);
  },

};

export default {
  namespaced: true,
  state,
  getters,
  mutations,
  actions
}
