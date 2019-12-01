<template>
    <div>
        <md-dialog-alert
                :md-active.sync="message.active"
                :md-title="message.title"
                :md-content="message.content" />
    </div>
</template>

<script>
  export default {
    name: "Message",
    data: () => ({
      message: {
        active: false,
        title: '',
        content: ''
      },
    }),
    methods: {
      setValue(value) {
        this.message = value;
      },
    }
  }
</script>

<style scoped>

</style>