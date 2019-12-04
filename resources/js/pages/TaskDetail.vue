<template>
    <div>
        <div :disabled="loading">
            <div class="task-name">
                <h1>{{ task.name }}</h1>

                <Assign ref="Assign"></Assign>
            </div>
            <div>
                <md-list class="task-content">
                    <md-subheader>内容</md-subheader>

                    <md-list-item>
                        <span class="md-list-item-text">
                            {{ task.description}}
                        </span>
                    </md-list-item>

                </md-list>
                <md-list class="task-deadline">
                    <md-subheader>期限日</md-subheader>

                    <md-list-item>
                        <span class="md-list-item-text">
                            {{ task.description}}
                        </span>
                    </md-list-item>

                </md-list>
            </div>
        </div>

        <md-progress-spinner class="md-accent" md-mode="indeterminate" v-if="loading"></md-progress-spinner>
    </div>
</template>

<script>
  import Assign from '../components/Assign.vue'

  export default {
    name: "TaskDetail",

    components: {
      Assign
    },
    async mounted() {
      const project_id = this.$route.params.id;
      const task_id = this.$route.params.taskID;
      this.$store.dispatch('task/getTask', {
        params: {
          'project_id': project_id,
          'task_id': task_id
        }
      }).catch((e) => {
        if (e.response.status === 401 || e.response.status === 403) {
          this.$router.push('/');
        }

      }).then(async () => {
        this.task = this.$store.getters['task/task'];
        this.$refs.Assign.setTaskID(this.task.id);
        this.$refs.Assign.setValue({
          id: project_id,
          projects_users: this.task.tasks_users
        });
        this.loading = false;
      });
    },
    data: () => ({
      project_id: '',
      task: {
        id: '',
        name: '',
        description: '',
        tasks_users: [],
      },
      comments: [],
      loading: true,
    }),
    methods: {},
  }
</script>