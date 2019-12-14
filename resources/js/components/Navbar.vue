<template>
    <nav class="navbar">
        <a href="/" class="navbar__brand">
            Task Management
        </a>

        <div class="navbar__menu">
            <div v-if="isLogin">
                <a class="logout-button" @click="logout">ログアウト</a>
            </div>
        </div>
    </nav>
</template>
<script>
  export default {
    computed: {
      isLogin() {
        return this.$store.getters['auth/isLogin']
      },
      username() {
        return this.$store.getters['auth/name']
      }
    },
    methods: {
      async logout() {
        await this.$store.dispatch('auth/logout');

        window.location.href = '/login';
        //this.$router.push('/login');
      }
    }
  }
</script>
