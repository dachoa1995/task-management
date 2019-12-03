<template>
    <div>
        <div>
            <ProjectModal ref="ProjectModal"></ProjectModal>

            <md-button @click="createProject()" class="md-fab md-mini md-primary">
                <md-icon>add</md-icon>
            </md-button>
        </div>

        <md-list class="md-double-line" :disabled="loading">

            <md-list-item v-for="(project, index) in projects" v-bind:key="project.id" :href="'project/' + project.id"
                          class="md-inset">
                <div class="md-list-item-text">
                    <span>{{ project.name }}</span>
                    <span>{{ project.description }}</span>
                </div>
                <md-button class="md-icon-button md-list-action" onclick='return false;'
                           @click='change_project(project.id, project.name, project.description)'>
                    <md-icon>edit</md-icon>
                </md-button>
                <md-button class="md-icon-button md-list-action" onclick='return false;'
                           @click='delete_project(project.id, index)'>
                    <md-icon>delete_forever</md-icon>
                </md-button>
            </md-list-item>

            <md-divider></md-divider>
        </md-list>

        <Delete ref="Delete"></Delete>

        <md-progress-spinner class="md-accent" md-mode="indeterminate" v-if="loading"></md-progress-spinner>

    </div>

</template>

<script>
  import Delete from '../components/Delete.vue'
  import ProjectModal from '../components/Modal/ProjectModal.vue'

  export default {
    name: 'Project_list',

    components: {
      Delete,
      ProjectModal
    },

    //init
    async mounted() {
      //プロジェクト一覧を取得
      await this.$store.dispatch('project/getProjects');
      this.projects = this.$store.getters['project/projects'];
      this.loading = false;
    },

    data: () => ({
      projects: [],
      loading: true,
    }),

    methods: {
      createProject() {
        this.$refs.ProjectModal.setForm({
          name: '',
          description: '',
        });
        this.$refs.ProjectModal.setConfigIsChange(false);
        this.$modal.show('project');
      },
      change_project(id, name, description) {
        this.$refs.ProjectModal.setForm({
          project_id: id,
          name: name,
          description: description,
        });
        this.$refs.ProjectModal.setConfigIsChange(true);
        this.$modal.show('project');
      },
      delete_project(project_id, index) {
        this.$refs.Delete.setValue({
          active: true,
          project_id: project_id,
          action: 'delete_project',
          index: index
        });
      },
    }
  }
</script>
