<template>
    <div>
        <modal name="taskModal">
            <form novalidate class="md-layout" @submit.prevent="validateTask">
                <md-card class="md-layout-item md-size-100 md-small-size-100">
                    <md-card-header>
                        <div class="md-title">タスク</div>
                    </md-card-header>

                    <md-card-content>
                        <div class="md-layout md-gutter">
                            <div class="md-layout-item md-medium-size-100">
                                <md-field :class="getValidationClass('name')">
                                    <label for="name">名前（必要）</label>
                                    <md-input name="name" id="name" v-model="form.name"/>
                                    <span class="md-error" v-if="!$v.form.name.required">名前が必要です。</span>
                                </md-field>
                            </div>

                            <div class="md-layout-item md-medium-size-100">
                                <md-field>
                                    <label>説明文</label>
                                    <md-textarea v-model="form.description"></md-textarea>
                                </md-field>
                            </div>

                            <div class="md-layout-item md-medium-size-100">
                                <md-datepicker v-model="form.deadline" md-immediately>
                                    <label>期限日</label>
                                </md-datepicker>
                            </div>
                        </div>
                    </md-card-content>
                    <md-progress-bar md-mode="indeterminate" v-if="config.sending"/>

                    <md-card-actions>
                        <md-button type="submit" class="md-primary" :disabled="config.sending" v-if="config.isChange">
                            修正
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
  import {formatDate} from '../../util';

  export default {
    name: "TaskModal",
    components: {
      Message
    },
    data: () => ({
      form: {
        project_id: '',
        status_id: '',
        task_id: '',
        name: '',
        description: '',
        deadline: formatDate(new Date())
      },
      config: {
        isChange: false,
        sending: false,
      }
    }),
    validations: {
      form: {
        name: {
          required,
        }
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
      validateTask() {
        this.$v.$touch();

        if (!this.$v.$invalid) {
          this.saveTask()
        }
      },
      processAfterSave() {
        this.config.sending = false;

        //close modal
        this.$modal.hide('taskModal');
      },
      saveTask() {
        //processing
        this.config.sending = true;
        const actionURL = this.config.isChange ? 'task/changeTask' : 'status/createTask';
        this.$store.dispatch(actionURL, this.form).then(() => {
          this.processAfterSave();

          this.$refs.Message.setValue({
            active: true,
            title: 'Success!',
            content: 'タスクを保存しました。'
          });
        }).catch((e) => {
          console.log(e);
          this.processAfterSave();

          this.$refs.Message.setValue({
            active: true,
            title: 'Error!',
            content: 'エラーでタスクが保存出来ませんでした。'
          });
        });
      },
    }

  }
</script>