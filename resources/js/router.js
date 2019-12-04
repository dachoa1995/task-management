import Vue from 'vue'
import VueRouter from 'vue-router'

// ページコンポーネントをインポートする
import Projects from './pages/Projects.vue'
import Login from './pages/Login.vue'
import ProjectDetail from './pages/ProjectDetail.vue'
import taskDetail from './pages/TaskDetail.vue'
import store from './store'

// VueRouterプラグインを使用する
// これによって<RouterView />コンポーネントなどを使うことができる
Vue.use(VueRouter);

// パスとコンポーネントのマッピング
const routes = [
  {
    path: '/',
    component: Projects,
    beforeEnter(to, from, next) {
      if (!store.getters['auth/check']) {
        next('/login')
      }

      next();
    }
  },
  {
    path: '/login',
    component: Login,
    beforeEnter(to, from, next) {
      if (store.getters['auth/check']) {
        next('/')
      }

      next()
    }
  },
  {
    path: '/project/:id',
    component: ProjectDetail,
    beforeEnter(to, from, next) {
      if (!store.getters['auth/check']) {
        next('/login')
      }

      next()
    }
  },
  {
    path: '/project/:id/:taskID',
    component: taskDetail,
    beforeEnter(to, from, next) {
      if (!store.getters['auth/check']) {
        next('/login')
      }

      next()
    }
  }
];

// VueRouterインスタンスを作成する
const router = new VueRouter({
  mode: 'history',
  routes
});

// VueRouterインスタンスをエクスポートする
// app.jsでインポートするため
export default router
