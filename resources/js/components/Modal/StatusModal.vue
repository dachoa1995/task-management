<template>
    <div>
        <modal name="status">
            <form novalidate class="md-layout" @submit.prevent="validateStatus">
                <md-card class="md-layout-item md-size-100 md-small-size-100">
                    <md-card-header>
                        <div class="md-title">ワークフロー</div>
                    </md-card-header>

                    <md-card-content>
                        <div class="md-layout md-gutter">
                            <div class="md-layout-item md-medium-size-100">
                                <md-field :class="getValidationClass('name')">
                                    <label for="name">名前</label>
                                    <md-input name="name" id="name" v-model="form.name"/>
                                    <span class="md-error" v-if="!$v.form.name.required">名前が必要です。</span>
                                </md-field>
                            </div>
                        </div>
                    </md-card-content>
                    <md-progress-bar md-mode="indeterminate" v-if="config.sending"/>

                    <md-card-actions>
                        <md-button type="submit" class="md-primary" :disabled="config.sending" v-if="config.isChange">
                            編集
                        </md-button>
                        <md-button type="submit" class="md-primary" :disabled="config.sending" v-if="!config.isChange">
                            作成
                        </md-button>
                    </md-card-actions>
                </md-card>
            </form>
        </modal>
        <Message ref="Message"></Message>
    </div>
</template>

<script>
  import {required} from 'vuelidate/lib/validators'
  import Message from '../../components/Message.vue'

  export default {
    name: "StatusModal",
    components: {
      Message
    },
    data: () => ({
      form: {
        project_id: '',
        status_id: '',
        name: '',
      },
      config: {
        sending: false,
        isChange: false
      }
    }),
    validations: {
      form: {
        name: {
          required,
        },
      },
    },
    methods: {
      setForm(value) {
        this.form = value;
      },
      setConfigIsChange(value) {
        this.config.isChange = value;
      },
      getValidationClass(fieldName) {
        const field = this.$v.form[fieldName];

        if (field) {
          return {
            'md-invalid': field.$invalid && field.$dirty
          }
        }
      },
      validateStatus() {
        this.$v.$touch();

        if (!this.$v.$invalid) {
          this.saveStatus()
        }
      },
      processAfterSave() {
        this.config.sending = false;

        //close modal
        this.$modal.hide('status');
      },
      saveStatus() {
        //processing
        this.config.sending = true;
        const actionURL = this.config.isChange ? 'status/changeStatus' : 'status/createStatus';
        this.$store.dispatch(actionURL, this.form).then(() => {
          this.processAfterSave();

          this.$refs.Message.setValue({
            active: true,
            title: 'Success!',
            content: 'ワークフローを保存しました。'
          });
        }).catch((e) => {
          console.log(e);
          this.processAfterSave();

          this.$refs.Message.setValue({
            active: true,
            title: 'Error!',
            content: 'エラーでワークフローが保存出来ませんでした。'
          });
        });
      },
    }
  }
</script>
