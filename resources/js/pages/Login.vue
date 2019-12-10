<template>
    <div class="container--small">

        <div class="panel">
            <form class="form">
                <div class="form__button">
                    <md-button @click="login()" class="md-raised md-accent">
                        Sign in with Google
                    </md-button>
                </div>
            </form>
        </div>

    </div>
</template>
<script>
  export default {
    // Waiting for the callback.blade.php message (token and username).
    mounted() {
      window.addEventListener('message', this.onMessage, false)
    },

    data() {
      return {}
    },
    methods: {
      async login() {
        await this.$auth.authenticate('google');
      },
      async onMessage(e) {
        if (e.origin !== window.origin || !e.data.api_token) {
          return
        }
        // save token and username to Vuex
        await this.$store.dispatch('auth/login', e.data);

        // トップページに移動する
        setTimeout(() =>
          this.$router.push('/'),
          500
        );
      }

    }
  }
</script>
