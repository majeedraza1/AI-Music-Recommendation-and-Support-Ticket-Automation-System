<template>
  <div class="stackonet-support-ticket-settings">
    <h1 class="wp-heading-inline">Settings</h1>
    <hr class="wp-header-end">
    <tabs>
      <tab v-for="(panel,index) in panels" :key="panel.id" :name="panel.title" :selected="index === 0">
        <template v-for="section in sections">
          <template v-if="panel.id === section.panel">
            <h2 class="title" v-if="section.title">{{ section.title }}</h2>
            <p class="description" v-if="section.description" v-html="section.description"></p>

            <table class="form-table">
              <template v-for="field in fields">
                <template v-if="field.section === section.id">
                  <tr>
                    <th scope="row">
                      <label :for="field.id" v-text="field.title"></label>
                    </th>
                    <td>
                      <template v-if="field.type === 'textarea'">
										<textarea class="regular-text" :id="field.id" :rows="field.rows"
                              v-model="options[field.id]"></textarea>
                      </template>
                      <template v-else-if="field.type === 'select'">
                        <select class="regular-text" v-model="options[field.id]"
                                :multiple="field.multiple">
                          <option value="">-- Choose --</option>
                          <option v-for="(label, value) in field.options" :value="value"
                                  v-text="label"></option>
                        </select>
                      </template>
                      <template v-else>
                        <input type="text" class="regular-text" :id="field.id"
                               v-model="options[field.id]">
                      </template>
                      <p class="description" v-if="field.description" v-html="field.description"></p>
                    </td>
                  </tr>
                </template>
              </template>
            </table>

          </template>
        </template>
        <div class="button-save-settings-container">
          <shapla-button theme="primary" size="medium" :fab="true" @click="saveOptions">
            <icon-container>
              <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                <path d="M0 0h24v24H0z" fill="none"/>
                <path
                    d="M17 3H5c-1.11 0-2 .9-2 2v14c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V7l-4-4zm-5 16c-1.66 0-3-1.34-3-3s1.34-3 3-3 3 1.34 3 3-1.34 3-3 3zm3-10H5V5h10v4z"/>
              </svg>
            </icon-container>
          </shapla-button>
        </div>
      </tab>

      <tab name="Categories">
        <ticket-categories/>
      </tab>

      <tab name="Priorities">
        <ticket-priorities/>
      </tab>

      <tab name="Statuses">
        <ticket-statuses/>
      </tab>

      <tab name="Roles & Capabilities">
        <roles/>
      </tab>

      <tab name="Agents">
        <agents/>
      </tab>

      <tab name="Extra Fields">
        <extra-fields :labels="fields_label" :user-fields="user_fields"/>
      </tab>
    </tabs>
  </div>
</template>

<script>

import {
  ShaplaButton as shaplaButton,
  ShaplaIcon as iconContainer,
  ShaplaTab as tab,
  ShaplaTabs as tabs
} from '@shapla/vue-components';
import TicketCategories from "../../admin/categories/TicketCategories";
import TicketPriorities from "../../admin/priorities/TicketPriorities";
import TicketStatuses from "../../admin/statuses/TicketStatuses";
import ExtraFields from "../../admin/settings/ExtraFields.vue";
import Agents from "../agents/Agents";
import Roles from "../agents/Roles";
import {useRouter} from 'vue-router'
import {useStore} from "vuex";
import {onMounted, reactive, toRefs} from "vue";

export default {
  name: "Settings",
  components: {
    ExtraFields,
    Roles, Agents, TicketStatuses, TicketPriorities, TicketCategories, shaplaButton, tabs, tab, iconContainer
  },
  setup() {
    const store = useStore();
    const router = useRouter();
    const state = reactive({
      options: {},
      panels: [],
      sections: [],
      fields: [],
      fields_label: {},
      user_fields: {},
    })

    const saveOptions = () => {
      store.dispatch('saveSettingsFields', state.options);
    }

    const backToTicketList = () => {
      router.push({name: 'SupportTicketList'})
    }

    onMounted(() => {
      store.dispatch('getSettingsFields').then(data => {
        state.panels = data.panels;
        state.sections = data.sections;
        state.fields = data.fields;
        state.options = data.options;
        state.fields_label = data.fields_label;
        state.user_fields = data.user_fields;
      })
    })

    return {
      ...toRefs(state),
      saveOptions,
      backToTicketList
    }
  }
}
</script>

<style lang="scss">
.stackonet-support-ticket-settings {

  table.form-table {
    th {
      vertical-align: top;
    }
  }
}
</style>