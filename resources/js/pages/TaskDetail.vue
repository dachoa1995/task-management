<template>
    <div>
        <div :disabled="loading">
            <div class="task-name">
                <div class="task-name-header">
                    <div>
                        <h1>{{ task.name }}</h1>
                    </div>
                    <div class="task-action-button">
                        <md-button class="md-icon-button md-list-action"
                                   @click="changeTask(task)">
                            <md-icon>edit</md-icon>
                        </md-button>
                        <md-button class="md-icon-button md-list-action">
                            <md-icon>delete_forever</md-icon>
                        </md-button>
                    </div>
                </div>

                <Assign ref="Assign"></Assign>
            </div>
            <div v-if="!loading">
                <md-list class="task-content">
                    <md-subheader class="task-content-header">
                        <div>内容</div>
                        <div>期限日：{{ task.deadline }}</div>
                    </md-subheader>

                    <md-list-item>
                        <span class="md-list-item-text description">
                            {{ task.description}}
                        </span>
                    </md-list-item>

                </md-list>
            </div>
        </div>

        <TaskModal ref="TaskModal"></TaskModal>
        <md-progress-spinner class="md-accent" md-mode="indeterminate" v-if="loading"></md-progress-spinner>
    </div>
</template>

<script>
  import Assign from '../components/Assign.vue'
  import TaskModal from '../components/Modal/TaskModal.vue'

  export default {
    name: "TaskDetail",

    components: {
      Assign,
      TaskModal
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
        deadline: new Date(),
        tasks_users: [],
      },
      comments: [],
      loading: true,
    }),
    methods: {
      changeTask(task) {
        this.$refs.TaskModal.setForm({
          project_id: this.project_id,
          status_id: task.id,
          name: task.name,
          description: task.description,
          deadline: new Date(task.deadline)
        });
        this.$refs.TaskModal.setConfigIsChange(true);
        this.$modal.show('taskModal');
      }
    },
  }
</script>