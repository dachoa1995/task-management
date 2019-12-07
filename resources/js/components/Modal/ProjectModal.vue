<template>
    <div>
        <modal name="project">
            <form novalidate class="md-layout" @submit.prevent="validateProject">
                <md-card class="md-layout-item md-size-100 md-small-size-100">
                    <md-card-header>
                        <div class="md-title">プロジェクト</div>
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

                            <div class="md-layout-item md-medium-size-100">
                                <md-field :class="getValidationClass('description')">
                                    <label for="description">説明文</label>
                                    <md-input name="description" id="description" v-model="form.description"/>
                                    <span class="md-error" v-if="!$v.form.description.required">説明文が必要です。</span>
                                </md-field>
                            </div>
                        </div>
                    </md-card-content>
                    <md-progress-bar md-mode="indeterminate" v-if="config.sending"/>

                    <md-card-actions>
                        <md-button type="submit" class="md-primary" :disabled="config.sending"
                                   v-if="!config.isChange">
                            作成
                        </md-button>
                        <md-button type="submit" class="md-primary" :disabled="config.sending"
                                   v-if="config.isChange">
                            編集
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
    name: "ProjectModal",
    components: {
      Message
    },
    data: () => ({
      form: {
        project_id: '',
        name: '',
        description: '',
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
        description: {
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
      validateProject() {
        this.$v.$touch();

        if (!this.$v.$invalid) {
          this.saveProject()
        }
      },
      processAfterSave() {
        this.config.sending = false;

        //close modal
        this.$modal.hide('project');
      },
      saveProject() {
        //processing
        this.config.sending = true;
        const actionURL = this.config.isChange ? 'project/changeProject' : 'project/createProject';
        this.$store.dispatch(actionURL, this.form).then(() => {
          this.processAfterSave();

          this.$refs.Message.setValue({
            active: true,
            title: 'Success!',
            content: 'プロジェクトを保存しました。'
          });
        }).catch((e) => {
          console.log(e);
          this.processAfterSave();

          this.$refs.Message.setValue({
            active: true,
            title: 'Error!',
            content: 'エラーでプロジェクトが保存出来ませんでした。'
          });
        });
      },
    }

  }
</script>