<template>
	<widget-box :title="title" class="support-ticket-widget-box--sms">
		<columns multiline>
			<column :tablet="12">
				<shapla-checkbox v-model="send_to_customer" :disabled="!customer_phone">
					<strong>Customer Phone: </strong> {{ customer_phone }}
				</shapla-checkbox>
			</column>
			<column :tablet="12">
				<shapla-checkbox v-model="send_to_custom_number">
					<strong>Custom Phone: </strong>
				</shapla-checkbox>
				<text-field v-if="send_to_custom_number" label="Phone Number" v-model="custom_number"/>
			</column>
			<column :tablet="12">
				<div class="flex">
					<shapla-checkbox v-model="send_to_agents">
						<strong>Support Agent(s): </strong>
					</shapla-checkbox>
					<icon-container v-if="send_to_agents" size="medium" hoverable @click="$emit('edit:agent')">
						<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20">
							<title>Edit Agents</title>
							<use xlink:href="#icon-pen"/>
						</svg>
					</icon-container>
				</div>
				<shapla-chip v-for="_agent in selectedAgents" :key="_agent.display_name" :image_src="_agent.avatar_url">
					{{ _agent.display_name }}
				</shapla-chip>
			</column>
			<column :tablet="12">
				<text-field type="textarea" label="SMS Content" v-model="sms_content" :rows="3"/>
			</column>
			<column :tablet="12">
				<shapla-button theme="primary" size="small" @click="sendSms">Send SMS</shapla-button>

				<span class="sms_content_length">{{ sms_content.length }}</span>
			</column>
		</columns>
	</widget-box>
</template>

<script>
import WidgetBox from "./WidgetBox";
import shaplaCheckbox from "shapla-checkbox";
import shaplaButton from 'shapla-button'
import shaplaChip from 'shapla-chip';
import iconContainer from 'shapla-icon-container';
import textField from 'shapla-text-field';
import {column, columns} from 'shapla-columns'

export default {
	name: "SmsWidgetBox",
	components: {WidgetBox, shaplaCheckbox, shaplaButton, shaplaChip, iconContainer, textField, column, columns},
	props: {
		title: {type: String, default: 'SMS Messages'},
		customer_phone: {type: String, default: ''},
		agents: {type: Array},
	},
	data() {
		return {
			send_to_customer: false,
			send_to_custom_number: false,
			send_to_agents: false,
			custom_number: '',
			selectedAgents: [],
			sms_content: '',
		}
	},
	methods: {
		sendSms() {

		}
	}
}
</script>

<style lang="scss">
@import "~shapla-color-system/src/variables";

.support-ticket-widget-box--sms {
	.shapla-text-field {
		margin-bottom: 0;
	}

	.sms_content_length {
		color: $primary;
		float: right;
		font-size: 16px;
	}
}
</style>
