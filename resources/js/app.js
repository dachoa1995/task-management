import Vue from 'vue'
import router from './router'
import store from './store'
import App from './App.vue'
import './bootstrap'

import VueAxios from 'vue-axios'
import VueAuthenticate from 'vue-authenticate'
import axios from 'axios';

import VueMaterial from 'vue-material'
import 'vue-material/dist/vue-material.min.css'
import 'vue-material/dist/theme/default.css'
import VModal from 'vue-js-modal'

Vue.use(VueAxios, axios);
Vue.use(VueAuthenticate, {
  baseUrl: 'http://local.task.com/',

  providers: {
    google: {
      clientId: 'your google client ID',
      redirectUri: 'http://local.task.com/auth/google/callback'
    }
  }
});


Vue.use(VueMaterial);
Vue.use(VModal);
const createApp = async () => {
  // ログインチェックしてからアプリを生成する
  await store.dispatch('auth/currentUser');

  new Vue({
    el: '#app',
    router,
    store,
    components: {App},
    template: '<App />'
  })
};

createApp();
