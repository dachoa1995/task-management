<template>
    <div>
        <md-dialog-confirm
                :md-active.sync="delete_modal.active"
                md-title="削除"
                md-content="削除したら、元に戻れないですが、本当に削除したいですか。"
                md-confirm-text="削除"
                md-cancel-text="戻る"
                @md-confirm="onConfirm"/>
        <Message ref="Message"></Message>
    </div>
</template>

<script>
  import Message from '../components/Message.vue'

  export default {
    name: "delete",
    components: {
      Message
    },
    data: () => ({
      delete_modal: {
        active: false,
        project_id: '',
        status_id: '',
        task_id: '',
        action: ''
      },
    }),
    methods: {
      setValue(value) {
        this.delete_modal = value;
      },
      onConfirm() {
        switch (this.delete_modal.action) {
          case 'deleteProject':
            this.$store.dispatch('project/deleteProject', {
              params: {
                'project_id': this.delete_modal.project_id
              },
            }).then(() => {
              this.$refs.Message.setValue({
                active: true,
                title: 'Success!',
                content: 'プロジェクトを削除しました。'
              });
            }).catch((e) => {
              console.log(e);
              this.$refs.Message.setValue({
                active: true,
                title: 'Error!',
                content: 'エラーでプロジェクトが削除出来ませんでした。'
              });
            });
            break;
          case 'deleteStatus':
            this.$store.dispatch('status/deleteStatus', {
              params: {
                'project_id': this.delete_modal.project_id,
                'status_id': this.delete_modal.status_id
              },
            }).then(() => {
              this.$refs.Message.setValue({
                active: true,
                title: 'Success!',
                content: 'ワークフローを削除しました。'
              });
            }).catch((e) => {
              this.$refs.Message.setValue({
                active: true,
                title: 'Error!',
                content: 'エラーでワークフローが削除出来ませんでした。'
              });
            });
            break;
          case 'deleteTask':
            this.$store.dispatch('task/deleteTask', {
              params: {
                'project_id': this.delete_modal.project_id,
                'task_id': this.delete_modal.task_id
              },
            }).then(() => {
              this.$refs.Message.setValue({
                active: true,
                title: 'Success!',
                content: 'タスクを削除しました。'
              });
              this.$router.push('/project/' + this.delete_modal.project_id);
            }).catch((e) => {
              this.$refs.Message.setValue({
                active: true,
                title: 'Error!',
                content: 'エラーでタスクが削除出来ませんでした。'
              });
            });
            break;
        }
      }
    }

  }
</script>
