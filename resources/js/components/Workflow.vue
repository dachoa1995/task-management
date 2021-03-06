<template>
    <div>
        <div class="viewport" v-for="workflow in status">
            <md-toolbar :md-elevation="1">
                <span class="md-title">{{workflow.name}}</span>
                <md-menu md-size="medium" :md-offset-x="127" :md-offset-y="-36">
                    <md-button md-menu-trigger class="md-icon-button"
                               @click="changeStatus(workflow)">
                        <md-icon>edit</md-icon>
                    </md-button>
                    <md-button md-menu-trigger class="md-icon-button"
                               @click='deleteStatus(workflow.id)'>
                        <md-icon>delete_forever</md-icon>
                    </md-button>
                </md-menu>
            </md-toolbar>
            <draggable class="list-group" :list="workflow.task" group="people" @change="moveTaskToAnotherWorkflow">
                <div class="list-group-item" v-for="task in workflow.task" :key="task.id">
                    <div class="md-list-item-text">
                        <div>
                            <h3>{{ task.name }}</h3>
                            <span class="description">{{ task.description }}</span>
                        </div>
                        <md-button :href="'/project/' + project_id + '/' + task.id" md-menu-trigger class="md-icon-button">
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
        <StatusModal ref="StatusModal" v-on:updateStatus="updateStatus"></StatusModal>
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
  import {formatDate} from '../util';

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
      updateStatus() {
        this.status = this.$store.getters['status/status'];
      },
      changeStatus(workflow) {
        workflow.project_id = this.project_id;
        workflow.status_id = workflow.id;
        this.$refs.StatusModal.setForm(workflow);
        this.$refs.StatusModal.setConfigIsChange(true);
        this.$modal.show('status');
      },
      deleteStatus(status_id) {
        this.$refs.Delete.setValue({
          active: true,
          project_id: this.project_id,
          status_id: status_id,
          action: 'deleteStatus',
        });
      },
      addTask(status_id) {
        this.$refs.TaskModal.setForm({
          project_id: this.project_id,
          status_id: status_id,
          name: '',
          description: '',
          deadline: formatDate(new Date())
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
