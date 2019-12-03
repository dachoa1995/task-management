<template>
    <div>
        <AssignTask ref="AssignTask"></AssignTask>
        <modal name="taskDetail">
            <form novalidate class="md-layout" @submit.prevent="">
                <md-card class="md-layout-item md-size-100 md-small-size-100">
                    <md-card-header>
                        <div class="md-title">{{task.name}}</div>
                    </md-card-header>
                </md-card>
            </form>
        </modal>
    </div>
</template>

<script>
  import AssignTask from '../components/AssignTask.vue'

  export default {
    name: "TaskDetail",

    components: {
      AssignTask
    },

    data: () => ({
      project_id: '',
      task: {
        id: '',
        name: '',
        description: '',
        deadline: new Date(),
        tasks_users: []
      },
      comments: []
    }),
    methods: {
      setProjectID(value) {
        this.project_id = value;
      },
      setTask(value) {
        this.task = value;
        this.$refs.AssignTask.setValue(this.task);
      },
      setComments(value) {
        this.comments = value;
      },
    },
  }
</script>