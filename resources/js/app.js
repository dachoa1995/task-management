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
import Vuelidate from 'vuelidate'

Vue.use(VueAxios, axios);
Vue.use(VueAuthenticate, {
  baseUrl: 'https://task-management-oss.000webhostapp.com/',

  providers: {
    google: {
      clientId: '723569059770-uluu31hlfll07sil7meqa75up3ip0upr.apps.googleusercontent.com',
      redirectUri: 'https://task-management-oss.000webhostapp.com/auth/google/callback'
    }
  }
});
Vue.use(VueMaterial);
Vue.use(VModal);
Vue.use(Vuelidate);

const createApp = async () => {
  // ログインチェックしてからアプリを生成する
  new Vue({
    el: '#app',
    router,
    store,
    components: {App},
    template: '<App />'
  })
};

createApp();
