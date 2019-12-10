import Vue from 'vue'
import VueRouter from 'vue-router'

// ページコンポーネントをインポートする
import Projects from './pages/Projects.vue'
import Login from './pages/Login.vue'
import ProjectDetail from './pages/ProjectDetail.vue'
import taskDetail from './pages/TaskDetail.vue'
import {getCookieValue} from './util'

// VueRouterプラグインを使用する
// これによって<RouterView />コンポーネントなどを使うことができる
Vue.use(VueRouter);

const hasToken = getCookieValue('api_token') !== '';

// パスとコンポーネントのマッピング
const routes = [
  {
    path: '/',
    component: Projects,
    beforeEnter(to, from, next) {
      if (!hasToken) {
        next('/login')
      }

      next();
    }
  },
  {
    path: '/login',
    component: Login,
    beforeEnter(to, from, next) {
      if (hasToken) {
        next('/')
      }

      next()
    }
  },
  {
    path: '/project/:id',
    component: ProjectDetail,
    beforeEnter(to, from, next) {
      if (!hasToken) {
        next('/login')
      }

      next()
    }
  },
  {
    path: '/project/:id/:taskID',
    component: taskDetail,
    beforeEnter(to, from, next) {
      if (!hasToken) {
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
