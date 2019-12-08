<template>
    <div>
        <CommentModal ref="CommentModal"></CommentModal>
        <div>
            <div class="comment" v-for="comment in comments">
                <md-avatar class="md-avatar-icon comment_avatar comment_avatar_list">
                    <img v-if="comment.user.avatarURL !== null" :src="comment.user.avatarURL" alt="Avatar">
                    <md-icon v-if="comment.user.avatarURL === null">account_circle</md-icon>
                    <md-tooltip md-direction="top">{{comment.user.name || 'anonymous'}}</md-tooltip>
                </md-avatar>
                <div class="md-layout-item md-medium-size-90">
                    <md-list class="task-content">
                        <md-subheader class="task-content-header">
                            <div>
                                {{comment.user.name}}
                            </div>
                            <div>{{comment.created_at}}</div>
                        </md-subheader>

                        <md-list-item>
                            <span class="md-list-item-text description">{{comment.content}}</span>
                        </md-list-item>

                    </md-list>
                </div>
            </div>
        </div>
        <Message ref="Message"></Message>
    </div>
</template>

<script>
  import Message from '../components/Message.vue'
  import CommentModal from '../components/Modal/CommentModal.vue'

  export default {
    name: "Comment",
    components: {
      Message,
      CommentModal
    },
    mounted() {
      this.form = {
        project_id: this.$route.params.id,
        task_id: this.$route.params.taskID,
        content: ''
      };
      this.$refs.CommentModal.setForm(this.form);
      this.$refs.CommentModal.setUser({
        avatarURL: this.$store.getters['auth/avatarURL'],
        name: this.$store.getters['auth/name']
      });
      this.getComments();
    },
    data: () => ({
      form: {
        project_id: '',
        task_id: '',
        content: ''
      },
      comments: [],
    }),
    methods: {
      async getComments() {
        await this.$store.dispatch('comment/getComments', {
          params: this.form
        });
        this.comments = this.$store.getters['comment/comments'];
      },
    }
  }
</script>
