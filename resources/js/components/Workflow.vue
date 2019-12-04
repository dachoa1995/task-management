<template>
    <div>
        <div class="viewport" v-for="(workflow, index) in status">
            <md-toolbar :md-elevation="1">
                <span class="md-title">{{workflow.name}}</span>
                <md-menu md-size="medium" :md-offset-x="127" :md-offset-y="-36">
                    <md-button md-menu-trigger class="md-icon-button"
                               @click="changeStatus(workflow.id, workflow.name)">
                        <md-icon>edit</md-icon>
                    </md-button>
                    <md-button md-menu-trigger class="md-icon-button"
                               @click='deleteStatus(workflow.id, index)'>
                        <md-icon>delete_forever</md-icon>
                    </md-button>
                </md-menu>
            </md-toolbar>
            <draggable class="list-group" :list="workflow.task" group="people" @change="moveTaskToAnotherWorkflow">
                <div class="list-group-item" v-for="task in workflow.task" :key="task.id">
                    <div class="md-list-item-text">
                        <div>
                            <h3>{{ task.name }}</h3>
                            <span>{{ task.description }}</span>
                        </div>
                        <md-button :href="'/project/' + project_id + '/' + task.id" target="_blank" md-menu-trigger class="md-icon-button">
                            <md-icon>launch</md-icon>
                        </md-button>
                    </div>
                </div>
            </draggable>
            <div class="add-task">
                <md-button class="md-fab md-mini md-primary add-button"
                           @click="addTask(workflow.id)">
                    <md-icon>add</md-icon>
                </md-button>
            </div>
        </div>
        <div class="add-more" v-if="project_id !== ''">
            <md-button class="md-fab md-mini md-primary add-button" @click="addStatus()">
                <md-icon :disabled="loading">add</md-icon>
            </md-button>
        </div>
        <StatusModal ref="StatusModal"></StatusModal>
        <TaskModal ref="TaskModal"></TaskModal>
        <Delete ref="Delete"></Delete>
        <Message ref="Message"></Message>
    </div>
</template>

<script>
  import draggable from "vuedraggable";
  import Delete from '../components/Delete.vue'
  import StatusModal from '../components/Modal/StatusModal.vue'
  import TaskModal from '../components/Modal/TaskModal.vue'
  import Message from '../components/Message.vue'

  export default {
    name: "Workflow",
    components: {
      draggable,
      Delete,
      StatusModal,
      TaskModal,
      Message,
    },
    data: () => ({
      project_id: '',
      status: [],
      loading: true,
    }),
    methods: {
      setStatus(value) {
        this.status = value;
      },
      setProjectID(value) {
        this.project_id = value;
      },
      addStatus() {
        this.$refs.StatusModal.setForm({
          project_id: this.project_id,
          name: '',
        });
        this.$refs.StatusModal.setConfigIsChange(false);
        this.$modal.show('status');
      },
      changeStatus(status_id, status_name) {
        this.$refs.StatusModal.setForm({
          project_id: this.project_id,
          status_id: status_id,
          name: status_name,
        });
        this.$refs.StatusModal.setConfigIsChange(true);
        this.$modal.show('status');
      },
      deleteStatus(status_id, index) {
        this.$refs.Delete.setValue({
          active: true,
          project_id: this.project_id,
          status_id: status_id,
          action: 'delete_status',
          index: index
        });
      },
      addTask(status_id) {
        this.$refs.TaskModal.setForm({
          project_id: this.project_id,
          status_id: status_id,
          name: '',
          description: '',
          deadline: new Date()
        });
        this.$refs.TaskModal.setConfigIsChange(false);
        this.$modal.show('taskModal');
      },
      moveTaskToAnotherWorkflow(log) {
        if (log.added !== undefined) {
          const status = log.added.element;

          //何のワークフローに移行されたか、取得
          const statusHaveBeenChanged = this.status.filter((eachStatus) => {
            return eachStatus.task.filter((eachTask) => {
              return eachTask.id === status.id
            }).length > 0
          })[0] || false;

          if (statusHaveBeenChanged) {
            this.$store.dispatch('status/moveTask', {
              project_id: this.project_id,
              task_id: status.id,
              change_to_status_id: statusHaveBeenChanged.id
            }).then(() => {
              this.$refs.Message.setValue({
                active: true,
                title: 'Success!',
                content: statusHaveBeenChanged.name + 'に移動したのが保存されました。'
              });
            }).catch((e) => {
              console.log(e);
              this.$refs.Message.setValue({
                active: true,
                title: 'Error!',
                content: 'エラーで移動が保存出来ませんでした。'
              });
            });
          }
        }
      }
    },
    computed: {
      dragOptions() {
        return {
          group: "description",
        };
      },
    },
  }
</script>
