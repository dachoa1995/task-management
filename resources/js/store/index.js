import Vue from 'vue'
import Vuex from 'vuex'

import auth from './auth'
import project from './project'
import status from './status'
import task from './task'
import comment from './comment'

Vue.use(Vuex);

const store = new Vuex.Store({
  modules: {
    auth,
    project,
    status,
    task,
    comment
  }
});

export default store
