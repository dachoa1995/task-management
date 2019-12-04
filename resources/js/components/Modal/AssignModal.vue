<template>
    <div>
        <modal name="assign">
            <form novalidate class="md-layout" @submit.prevent="validateEmail">
                <md-card class="md-layout-item md-size-100 md-small-size-100">
                    <md-card-header>
                        <div class="md-title">担当者をアサイン</div>
                    </md-card-header>

                    <md-card-content>
                        <div class="md-layout md-gutter">
                            <div class="md-layout-item md-medium-size-100">
                                <md-field :class="getValidationClass('email')">
                                    <label for="email">メールアドレス</label>
                                    <md-input name="email" id="email" v-model="form.email"/>
                                    <span class="md-error" v-if="!$v.form.email.required">メールアドレスが必要です。</span>
                                    <span class="md-error" v-if="!$v.form.email.email">メールアドレスを正しく入力してください。</span>
                                </md-field>
                            </div>
                        </div>
                    </md-card-content>
                    <md-progress-bar md-mode="indeterminate" v-if="config.sending"/>

                    <md-card-actions>
                        <md-button type="submit" class="md-primary" :disabled="config.sending">
                            招待を送る
                        </md-button>
                    </md-card-actions>
                </md-card>
            </form>
        </modal>
        <Message ref="Message"></Message>
    </div>
</template>

<script>
  import {required, email} from 'vuelidate/lib/validators'
  import Message from '../../components/Message.vue'

  export default {
    name: "AssignModal",
    components: {
      Message
    },
    data: () => ({
      form: {
        project_id: '',
        task_id: '',
        email: '',
      },
      config: {
        sending: false
      }
    }),
    validations: {
      form: {
        email: {
          required,
          email
        },
      },
    },
    methods: {
      setForm(value) {
        this.form = value;
      },
      getValidationClass(fieldName) {
        const field = this.$v.form[fieldName];

        if (field) {
          return {
            'md-invalid': field.$invalid && field.$dirty
          }
        }
      },
      validateEmail() {
        this.$v.$touch();

        if (!this.$v.$invalid) {
          this.sendInvite()
        }
      },
      processAfterSave() {
        //reset data
        this.config.sending = false;
        this.form.email = '';

        //close modal
        this.$modal.hide('assign');
      },
      sendInvite() {
        //processing
        this.config.sending = true;
        const actionURL = this.form.task_id === '' ? 'project/assign' : 'task/assign';
        this.$store.dispatch(actionURL, this.form).then(() => {
          this.processAfterSave();

          this.$refs.Message.setValue({
            active: true,
            title: 'Success!',
            content: 'メールアドレスに招待を送りました。'
          });
        }).catch((e) => {
          console.log(e.response);
          this.processAfterSave();

          this.$refs.Message.setValue({
            active: true,
            title: 'Error!',
            content: 'エラーでメールアドレスに招待が送れませんでした。'
          });
        });
      },
    }

  }
</script>
