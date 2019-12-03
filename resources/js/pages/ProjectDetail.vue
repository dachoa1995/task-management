<template>
    <div>
        <div :disabled="loading">
            <div class="project-name">
                <h1>{{ project.name }}</h1>

                <Assign ref="Assign"></Assign>
            </div>
            <Workflow ref="Workflow"></Workflow>
        </div>

        <md-progress-spinner class="md-accent" md-mode="indeterminate" v-if="loading"></md-progress-spinner>
    </div>
</template>

<script>
  import Assign from '../components/Assign.vue'
  import Workflow from '../components/Workflow.vue'

  export default {
    name: "projectDetail",

    components: {
      Assign,
      Workflow
    },

    async mounted() {
      const project_id = this.$route.params.id;
      this.$store.dispatch('project/getProjectDetail', {
        params: {
          'project_id': project_id
        }
      }).catch((e) => {
        if (e.response.status === 401 || e.response.status === 403) {
          this.$router.push('/');
        }

      }).then(async () => {
        await this.$store.dispatch('status/getStatus', {
          params: {
            'project_id': project_id
          }
        });
        this.project = this.$store.getters['project/projects'];
        this.status = this.$store.getters['status/status'];
        this.$refs.Assign.setValue(this.project);
        this.$refs.Workflow.setProjectID(project_id);
        this.$refs.Workflow.setStatus(this.status);
        this.loading = false;
      });
    },
    data: () => ({
      project: {
        name: '',
        projects_users: []
      },
      status: [],
      loading: true,
    }),
    methods: {

    },
  };
</script>
