<template>
    <nav class="navbar">
        <RouterLink class="navbar__brand" to="/">
            Task Management
        </RouterLink>
        <div class="navbar__menu">
            <span v-if="isLogin" class="navbar__item">
                {{ username }}
            </span>
            <div v-else class="navbar__item">
                <RouterLink class="button button--link" to="/login">
                    Login / Register
                </RouterLink>
            </div>
        </div>
    </nav>
</template>
<script>
  export default {
    computed: {
      isLogin () {
        return this.$store.getters['auth/check']
      },
      username () {
        return this.$store.getters['auth/name']
      }
    }
  }
</script>
