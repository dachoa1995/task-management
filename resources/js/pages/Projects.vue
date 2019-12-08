<template>
    <div>
        <div>
            <ProjectModal ref="ProjectModal" v-on:updateProject="updateProject"></ProjectModal>

            <md-button @click="createProject()" class="md-fab md-mini md-primary">
                <md-icon>add</md-icon>
            </md-button>
        </div>

        <md-list class="md-double-line" :disabled="loading">

            <md-list-item v-for="project in projects" v-bind:key="project.id" :href="'project/' + project.id"
                          class="md-inset">
                <div class="md-list-item-text project-item-text">
                    <span>{{ project.name }}</span>
                    <span class="description">{{ project.description }}</span>
                </div>
                <md-button class="md-icon-button md-list-action" onclick='return false;'
                           @click='changeProject(project)'>
                    <md-icon>edit</md-icon>
                </md-button>
                <md-button class="md-icon-button md-list-action" onclick='return false;'
                           @click='deleteProject(project.id)'>
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
      changeProject(project) {
        project.project_id = project.id;
        this.$refs.ProjectModal.setForm(project);
        this.$refs.ProjectModal.setConfigIsChange(true);
        this.$modal.show('project');
      },
      deleteProject(project_id) {
        this.$refs.Delete.setValue({
          active: true,
          project_id: project_id,
          action: 'deleteProject',
        });
      },
      updateProject() {
        this.projects = this.$store.getters['project/projects'];
      }
    }
  }
</script>
