<template>
  <div class="support-ticket-widget-box--sms">
    <div class="mb-3 w-full">
      <shapla-checkbox v-model="send_to_customer" :disabled="!customer_phone">
        <strong>Customer Phone: </strong> {{ customer_phone }}
      </shapla-checkbox>
    </div>
    <div class="mb-3 w-full">
      <shapla-checkbox v-model="send_to_custom_number">
        <strong>Custom Phone: </strong>
      </shapla-checkbox>
      <div class="mt-2" v-if="send_to_custom_number">
        <text-field label="Phone Number" v-model="custom_number"/>
      </div>
    </div>
    <div class="mb-3 w-full">
      <div class="flex">
        <shapla-checkbox v-model="send_to_agents">
          <strong>Support Agent(s): </strong>
        </shapla-checkbox>
        <icon-container v-if="send_to_agents" size="medium" hoverable @click="showAgentsModal = true">
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20">
            <title>Edit Agents</title>
            <use xlink:href="#icon-pen"/>
          </svg>
        </icon-container>
      </div>
      <div class="mt-2">
        <shapla-chip v-for="_agent in selected_agents" :key="_agent.display_name"
                     :image_src="_agent.avatar_url"> {{ _agent.display_name }}
        </shapla-chip>
      </div>
    </div>
    <div class="mb-3 w-full">
      <text-field type="textarea" label="SMS Content" v-model="sms_content" :rows="3"/>
    </div>
    <div class="w-full">
      <shapla-button theme="primary" size="small" @click="sendSms">Send SMS</shapla-button>

      <span class="sms_content_length">{{ sms_content.length }}</span>
    </div>
    <modal :active="showAgentsModal" title="Choose Assign Agent(s)" @close="showAgentsModal = false">
      <shapla-chip v-for="_agent in agents" :key="_agent.display_name" :image_src="_agent.avatar_url"
                   :class="{'is-active':selected_agents_ids.indexOf(_agent.id) !== -1}"
                   @click="handleSelect(_agent)">
        {{ _agent.display_name }}
      </shapla-chip>
      <template slot="foot">
        <shapla-button theme="default" @click="showAgentsModal = false">Close</shapla-button>
      </template>
    </modal>
  </div>
</template>

<script>
import {
  column,
  columns,
  iconContainer,
  modal,
  shaplaButton,
  shaplaCheckbox,
  shaplaChip,
  textField
} from "shapla-vue-components";

export default {
  name: "SmsWidgetBox",
  components: {shaplaCheckbox, shaplaButton, shaplaChip, iconContainer, textField, column, columns, modal},
  props: {
    customer_phone: {type: String, default: ''},
    agents: {type: Array},
  },
  data() {
    return {
      showAgentsModal: false,
      send_to_customer: false,
      send_to_custom_number: false,
      send_to_agents: false,
      custom_number: '',
      selected_agents_ids: [],
      sms_content: '',
    }
  },
  computed: {
    selected_agents() {
      if (this.selected_agents_ids.length < 1) {
        return [];
      }

      return this.agents.filter(agent => -1 !== this.selected_agents_ids.indexOf(agent.id));
    },
  },
  methods: {
    handleSelect(agent) {
      let index = this.selected_agents_ids.indexOf(agent.id);
      if (-1 !== index) {
        this.selected_agents_ids.splice(index, 1);
      } else {
        this.selected_agents_ids.push(agent.id);
      }
    },
    sendSms() {
      window.alert('SMS message is not supported yet.')
      this.$emit('submit', this.sms_content);
    },
  }
}
</script>

<style lang="scss">
@import "shapla-css/src/colors";

.support-ticket-widget-box--sms {
  .shapla-text-field {
    margin-bottom: 0;
  }

  .sms_content_length {
    color: $primary;
    float: right;
    font-size: 16px;
  }

  .shapla-chip.is-active {
    background-color: $primary-alpha;
    color: $primary;
  }
}
</style>
