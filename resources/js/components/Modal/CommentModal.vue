<template>
    <div>
        <form novalidate @submit.prevent="validateComment">
            <md-progress-bar md-mode="indeterminate" v-if="config.sending"/>
            <div class="comment">
                <md-avatar class="md-avatar-icon comment_avatar">
                    <img v-if="user.avatarURL !== null" :src="user.avatarURL" alt="Avatar">
                    <md-icon v-if="user.avatarURL === null">account_circle</md-icon>
                </md-avatar>
                <div class="md-layout-item md-medium-size-80">
                    <md-field :class="getValidationClass('content')">
                        <label>コメント（必要）</label>
                        <md-textarea v-model="form.content"></md-textarea>
                        <span class="md-error" v-if="!$v.form.content.required">説明文が必要です。</span>
                    </md-field>
                </div>
                <md-button type="submit" class="md-primary" :disabled="config.sending">
                    送信
                </md-button>
            </div>
        </form>
    </div>
</template>

<script>
  import {required} from 'vuelidate/lib/validators'

  export default {
    name: "CommentModal",
    data: () => ({
      form: {
        project_id: '',
        task_id: '',
        content: ''
      },
      user: {
        avatarURL: '',
        name: ''
      },
      config: {
        sending: false
      }
    }),
    validations: {
      form: {
        content: {
          required,
        },
      },
    },
    methods: {
      setForm(value) {
        this.form = value;
      },
      setUser(value) {
        this.user = value;
      },
      getValidationClass(fieldName) {
        const field = this.$v.form[fieldName];

        if (field) {
          return {
            'md-invalid': field.$invalid && field.$dirty
          }
        }
      },
      validateComment() {
        this.$v.$touch();

        if (!this.$v.$invalid) {
          this.saveComment()
        }
      },
      processAfterSave() {
        this.config.sending = false;
        this.form.content = ' ';
      },
      saveComment() {
        this.config.sending = true;
        this.$store.dispatch('comment/createComment', {
          'form': this.form,
          'user': this.user
        }).then(() => {
          this.processAfterSave();
          this.$refs.Message.setValue({
            active: true,
            title: 'Success!',
            content: 'コメントを保存しました。'
          });
        }).catch((e) => {
          console.log(e);
          this.processAfterSave();
          this.$refs.Message.setValue({
            active: true,
            title: 'Error!',
            content: 'エラーでコメントが保存出来ませんでした。'
          });
        });
      },
    }
  }
</script>