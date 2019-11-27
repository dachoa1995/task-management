<template>
    <div>
        <div>
            <modal name="create-project">
                <form novalidate class="md-layout" @submit.prevent="saveProject">
                    <md-card class="md-layout-item md-size-100 md-small-size-100">
                        <md-card-header>
                            <div class="md-title">プロジェクト</div>
                        </md-card-header>

                        <md-card-content>
                            <div class="md-layout md-gutter">
                                <div class="md-layout-item md-medium-size-100">
                                    <md-field>
                                        <label for="name">名前</label>
                                        <md-input name="name" id="name" v-model="form.name"/>
                                    </md-field>
                                </div>

                                <div class="md-layout-item md-medium-size-100">
                                    <md-field>
                                        <label for="description">内容</label>
                                        <md-input name="description" id="description" v-model="form.description"/>
                                    </md-field>
                                </div>
                            </div>
                        </md-card-content>
                        <md-progress-bar md-mode="indeterminate" v-if="status.sending"/>

                        <md-card-actions>
                            <md-button type="submit" class="md-primary" :disabled="status.sending" v-if="!status.isChange">
                                作成
                            </md-button>
                            <md-button type="submit" class="md-primary" :disabled="status.sending" v-if="status.isChange">
                                編集
                            </md-button>
                        </md-card-actions>
                    </md-card>
                </form>
            </modal>

            <md-button @click="showModal()" class="md-fab md-mini md-primary">
                <md-icon>add</md-icon>
            </md-button>
        </div>

        <md-list class="md-double-line" :disabled="status.loading">

            <md-list-item v-for="project in projects" v-bind:key="project.id" :href="'project/' + project.id" class="md-inset">
                <div class="md-list-item-text">
                    <span>{{ project.name }}</span>
                    <span>{{ project.description }}</span>
                </div>
                <md-button class="md-icon-button md-list-action" onclick='return false;'
                           @click='change_project(project.id, project.name, project.description)'>
                    <md-icon>edit</md-icon>
                </md-button>
                <md-button class="md-icon-button md-list-action" onclick='return false;'
                           @click='delete_project(project.id)'>
                    <md-icon>delete_forever</md-icon>
                </md-button>
            </md-list-item>

            <md-divider></md-divider>
        </md-list>

        <md-dialog-confirm
                :md-active.sync="delete_modal.active"
                md-title="削除"
                md-content="削除したら、元に戻れないですが、本当に削除したいですか。"
                md-confirm-text="削除"
                md-cancel-text="戻る"
                @md-confirm="onConfirm"/>

        <md-progress-spinner class="md-accent" md-mode="indeterminate" v-if="status.loading"></md-progress-spinner>

    </div>

</template>

<script>
  export default {
    name: 'Project_list',

    //init
    async mounted() {
      //プロジェクト一覧を取得
      await this.$store.dispatch('project/getProjects');
      this.projects = this.$store.getters['project/projects'];
      this.status.loading = false;
    },

    data: () => ({
      projects: [],
      form: {
        project_id: '',
        name: '',
        description: '',
      },
      delete_modal: {
        active: false,
        value: null
      },
      status: {
        sending: false,
        loading: true,
        isChange: false
      }
    }),

    methods: {
      showModal() {
        this.status.isChange = false;
        this.$modal.show('create-project');
      },
      saveProject() {
        //validation
        if (this.form.name === '' || this.form.description === '') {
          alert('名前、または説明文を入力してください。');
          return;
        }

        //processing
        this.status.sending = true;
        const actionURL = this.status.isChange ? 'project/changeProject' : 'project/createProject';
        this.$store.dispatch(actionURL, this.form).then(() => {
          //reset data
          this.status.sending = false;
          this.form.project_id = '';
          this.form.name = '';
          this.form.description = '';

          //close modal
          this.$modal.hide('create-project');

          alert('プロジェクトを保存しました。');
        });
      },
      delete_project(project_id) {
        this.delete_modal.active = true;
        this.delete_modal.value = project_id;
      },
      onConfirm() {
        this.$store.dispatch('project/deleteProject', {
          params: {
            'project_id': this.delete_modal.value
          }
        }).then(() => {
          this.projects = this.$store.getters['project/projects'];
          alert('プロジェクトを削除しました');
        });
      },
      change_project(id, name, description) {
        this.status.isChange = true;
        this.form.project_id = id;
        this.form.name = name;
        this.form.description = description;

        this.$modal.show('create-project');
      }


    }


  }
</script>
