<template>
    <nav class="navbar">
        <RouterLink class="navbar__brand" to="/">
            Task Management
        </RouterLink>
        <div class="navbar__menu">
            <md-menu v-if="isLogin" md-size="medium" md-align-trigger md-direction="top-end">
                <md-button md-menu-trigger>{{ username }}</md-button>

                <md-menu-content>
                    <md-menu-item @click="logout">ログアウト</md-menu-item>
                </md-menu-content>
            </md-menu>
        </div>
    </nav>
</template>
<script>
  export default {
    computed: {
      isLogin() {
        return this.$store.getters['auth/check']
      },
      username() {
        return this.$store.getters['auth/name']
      }
    },
    methods: {
      async logout() {
        await this.$store.dispatch('auth/logout');

        this.$router.push('/login');
      }
    }
  }
</script>
