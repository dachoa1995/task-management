<template>
    <div v-if="project.id !== ''">
        <md-avatar class="md-avatar-icon" v-for="projects_user in project.projects_users"
                   :key="projects_user.id">
            <img v-if="projects_user.user.avatarURL !== null" :src="projects_user.user.avatarURL" alt="Avatar">
            <md-icon v-if="projects_user.user.avatarURL === null">account_circle</md-icon>
            <md-tooltip md-direction="top">{{projects_user.user.name || 'anonymous'}}</md-tooltip>
        </md-avatar>

        <md-button class="md-fab md-mini md-primary assign-button" @click="assign()">
            <md-avatar class="md-avatar-icon md-primary">
                <md-icon>person_add</md-icon>
            </md-avatar>
        </md-button>

        <AssignModal ref="AssignModal"></AssignModal>
    </div>
</template>

<script>
  import AssignModal from '../components/Modal/AssignModal.vue'

  export default {
    name: "Assign",
    components: {
      AssignModal
    },
    data: () => ({
      project: {
        id: '',
        projects_users: []
      },
      task_id: ''
    }),
    methods: {
      setProject(value) {
        this.project = value;
      },
      setTaskID(value) {
        this.task_id = value;
      },
      assign() {
        this.$refs.AssignModal.setForm({
          project_id: this.project.id,
          task_id: this.task_id,
          email: '',
        });
        this.$modal.show('assign');
      }
    }
  }
</script>
