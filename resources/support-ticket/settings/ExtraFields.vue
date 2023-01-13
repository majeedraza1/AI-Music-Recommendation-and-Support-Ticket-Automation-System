<template>
  <div>
    <table class="form-table" role="presentation">
      <thead>
      <tr>
        <th>Key</th>
        <th>Label</th>
        <th>Enabled for user</th>
      </tr>
      </thead>
      <tbody v-if="Object.keys(fields_label).length">
      <tr v-for="(value, key) in fields_label" :key="key">
        <th>{{ key }}</th>
        <td>
          <input type="text" class="regular-text" v-model="fields_label[key]">
        </td>
        <td>
          <input type="checkbox" v-model="user_fields[key]">
        </td>
      </tr>
      </tbody>
    </table>
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
  </div>
</template>

<script>
import {iconContainer, shaplaButton} from "shapla-vue-components";
import axios from "axios";

export default {
  name: "ExtraFields",
  components: {shaplaButton, iconContainer},
  props: {
    labels: {type: Object, default: () => ({})},
    userFields: {type: Object, default: () => ({})},
  },
  data() {
    return {
      fields_label: {},
      user_fields: {},
    }
  },
  watch: {
    labels: {
      handler: function (val) {
        this.fields_label = val;
      },
      deep: true
    },
    userFields: {
      handler: function (val) {
        this.user_fields = val;
      },
      deep: true
    }
  },
  methods: {
    saveOptions() {
      axios.post(StackonetSupportTicket.restRoot + '/settings/fields_labels', {
        fields_labels: this.fields_label,
        user_fields: this.user_fields
      }).then(response => {
        window.console.log(response.data.data);
      })
    }
  },
  mounted() {
    this.fields_label = this.labels;
    this.user_fields = this.userFields;
  }
}
</script>

<style scoped>

</style>