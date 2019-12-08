<template>
    <div v-if="task.id !== ''">
        <md-avatar class="md-avatar-icon" v-for="tasks_user in task.tasks_users"
                   :key="tasks_user.id">
            <img v-if="tasks_user.user.avatarURL !== null" :src="tasks_user.user.avatarURL" alt="Avatar">
            <md-icon v-if="tasks_user.user.avatarURL === null">account_circle</md-icon>
            <md-tooltip md-direction="top">{{tasks_user.user.name || 'anonymous'}}</md-tooltip>
        </md-avatar>

        <md-button class="md-fab md-mini md-primary assign-button" @click="assign()">
            <md-avatar class="md-avatar-icon md-primary">
                <md-icon>add</md-icon>
            </md-avatar>
        </md-button>

        <AssignModal ref="AssignModal"></AssignModal>
    </div>
</template>

<script>
  import AssignModal from '../components/Modal/AssignModal.vue'

  export default {
    name: "AssignTask",
    components: {
      AssignModal
    },
    data: () => ({
      task: {
        id: '',
        tasks_users: []
      },
    }),
    methods: {
      setValue(value) {
        this.task = value;
      },
      assign() {
        this.$refs.AssignModal.setForm({
          project_id: this.task.id,
          task_id: this.task_id,
          email: '',
        });
        this.$modal.show('assign');
      }
    }
  }
</script>
