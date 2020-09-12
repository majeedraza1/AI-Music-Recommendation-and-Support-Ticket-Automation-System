<template>
	<div class="stackont-single-support-ticket-container">
		<div class="flex mb-4">
			<div class="flex-grow"></div>
			<shapla-button theme="primary" outline size="small" @click="backToTicketList">Back to Ticket</shapla-button>
		</div>

		<columns>
			<column :desktop="8">
				<div class="stackont-single-ticket-content">

					<div class="stackont-single-ticket__heading flex">
						<h4 class="stackont-single-ticket__title">[Ticket #{{ item.id }}] {{ item.ticket_subject }}</h4>
						<icon-container size="medium" hoverable @click="openTitleModal">
							<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20">
								<title>Edit Title</title>
								<use xlink:href="#icon-pen"/>
							</svg>
						</icon-container>
					</div>

					<div>
						<add-ticket-thread :ticket_id="id" @added="getItem"/>
					</div>

					<div class="shapla-thread-container">
						<template v-for="thread in threads">

							<template v-if="thread.thread_type === 'log'">
								<div class="shapla-thread shapla-thread--log">
									<div class="shapla-thread__content">
										<span v-html="thread.thread_content"></span>
										<small class="shapla-thread__time">reported {{ thread.human_time }} ago</small>
									</div>
								</div>
							</template>

							<ticket-thread
								v-if="thread.thread_type !== 'log'"
								:thread="thread"
								@edit="openThreadEditor"
								@delete="deleteThread"
							/>

						</template>
					</div>
				</div>

			</column>

			<column :desktop="4">
				<widget-box title="Status" :show-edit-icon="true" @edit="openStatusModal">
					<list-item label="Status">{{ item.status.name }}</list-item>
					<list-item label="Category">{{ item.category.name }}</list-item>
					<list-item label="Priority">{{ item.priority.name }}</list-item>
				</widget-box>
				<widget-box title="Assign Agent(s)" :show-edit-icon="true" @edit="openAssignAgentModal">
					<shapla-chip v-for="_agent in item.assigned_agents" :key="_agent.display_name"
					             :image_src="_agent.avatar_url"> {{ _agent.display_name }}
					</shapla-chip>
				</widget-box>
				<widget-box title="Raised By">
					<shapla-chip :image_src="item.customer_url">{{ item.customer_name }}</shapla-chip>
				</widget-box>
				<widget-box title="SMS Messages">
					<sms-widget-box :agents="agents" @edit:agent="openTwilioAssignAgentModal" @submit="sendSms"/>
				</widget-box>
			</column>

		</columns>
		<modal :active="activeThreadModal" title="Edit this Thread" @close="closeThreadEditor">
			<editor :init="mce" v-model="activeThreadContent"/>
			<template slot="foot">
				<shapla-button theme="primary" @click="updateThread">Save</shapla-button>
			</template>
		</modal>

		<modal :active="activeStatusModal" title="Change Ticket Status" @close="activeStatusModal = false">
			<list-item label="Status">
				<select v-model="ticket_status">
					<option :value="_category.term_id" v-for="_category in statuses">{{ _category.name }}</option>
				</select>
			</list-item>
			<list-item label="Category">
				<select v-model="ticket_category">
					<option :value="_category.term_id" v-for="_category in categories">{{ _category.name }}</option>
				</select>
			</list-item>
			<list-item label="Priority">
				<select v-model="ticket_priority">
					<option :value="_category.term_id" v-for="_category in priorities">{{ _category.name }}</option>
				</select>
			</list-item>
			<template slot="foot">
				<shapla-button theme="primary" @click="updateTicketStatus">Save</shapla-button>
			</template>
		</modal>

		<modal :active="activeAgentModal" title="Change Assign Agent(s)" @close="activeAgentModal = false">
			<template v-for="_agent in agents">
				<div class="support_agents-chip">
					<div class="shapla-chip shapla-chip--contact" @click="updateAgent(_agent)"
					     :class="{'is-active':support_agents_ids.indexOf(_agent.id) !== -1}">
						<div class="shapla-chip__contact">
							<image-container>
								<img :src="_agent.avatar_url" width="32" height="32" alt="">
							</image-container>
						</div>
						<span class="shapla-chip__text">{{ _agent.display_name }} - {{ _agent.role_label }}</span>
					</div>
				</div>
			</template>
			<template slot="foot">
				<shapla-button theme="primary" @click="updateAssignAgents">Save</shapla-button>
			</template>
		</modal>

		<modal :active="activeTwilioAgentModal" title="Choose Assign Agent(s)" @close="activeTwilioAgentModal = false">
			<template v-for="_agent in agents">
				<div class="support_agents-chip">
					<div class="shapla-chip shapla-chip--contact" @click="updateTwilioAgent(_agent)"
					     :class="{'is-active':twilio_support_agents_ids.indexOf(_agent.id) !== -1}">
						<div class="shapla-chip__contact">
							<image-container>
								<img :src="_agent.avatar_url" alt="" width="32" height="32">
							</image-container>
						</div>
						<span class="shapla-chip__text">{{ _agent.display_name }} - {{ _agent.role_label }}</span>
					</div>
				</div>
			</template>
			<template slot="foot">
				<shapla-button theme="primary" @click="activeTwilioAgentModal = false">Confirm</shapla-button>
			</template>
		</modal>

		<modal :active="activeTitleModal" title="Change Ticket Subject" @close="activeTitleModal = false">
			<textarea v-model="ticket_subject" style="width: 100%;"></textarea>
			<template slot="foot">
				<shapla-button theme="primary" @click="updateSubject">Save</shapla-button>
			</template>
		</modal>
	</div>
</template>

<script>
import axios from 'axios';
import {mapState} from 'vuex';
import {column, columns} from 'shapla-columns'
import shaplaButton from 'shapla-button'
import modal from 'shapla-modal'
import Editor from '@tinymce/tinymce-vue'
import ListItem from '../../shapla/shapla-list-item/ListItem'
import ImageContainer from "../../shapla/image/image";
import Icon from "../../shapla/icon/icon";
import shaplaCheckbox from "shapla-checkbox";
import TicketThread from "../components/TicketThread";
import WidgetBox from "../components/WidgetBox";
import shaplaChip from 'shapla-chip';
import SmsWidgetBox from "../components/SmsWidgetBox";
import iconContainer from 'shapla-icon-container';
import AddTicketThread from "../components/AddTicketThread";

export default {
	name: "SingleSupportTicket",
	components: {
		AddTicketThread,
		SmsWidgetBox, iconContainer, WidgetBox, shaplaChip, TicketThread,
		shaplaCheckbox, Icon, ImageContainer, shaplaButton, columns, column, ListItem, Editor, modal
	},
	data() {
		return {
			loading: false,
			activeStatusModal: false,
			activeAgentModal: false,
			activeThreadModal: false,
			activeTitleModal: false,
			activeThread: {},
			activeTwilioAgentModal: false,
			ticket_twilio_sms_customer_phone: true,
			ticket_twilio_sms_enable_custom_phone: false,
			ticket_twilio_sms_custom_phone: '',
			ticket_twilio_sms_content: '',
			twilio_support_agents_ids: [],
			activeThreadContent: '',
			ticket_subject: '',
			ticket_category: '',
			ticket_priority: '',
			ticket_status: '',
			support_agents_ids: [],
			threadType: '',
			id: 0,
			content: '',
			item: {},
			threads: []
		}
	},
	computed: {
		...mapState(['categories', 'priorities', 'statuses', 'agents']),
		mce() {
			return {
				branding: false,
				plugins: 'lists link paste wpemoji',
				toolbar: 'undo redo bold italic underline strikethrough bullist numlist link unlink table inserttable',
				min_height: 150,
				inline: false,
				menubar: false,
				statusbar: true
			}
		},
	},
	mounted() {
		let id = this.$route.params.id;
		this.$store.commit('SET_LOADING_STATUS', false);
		this.$store.commit('SET_SHOW_SIDE_NAVE', false);
		if (id) {
			this.id = parseInt(id);
			this.getItem();
		}
		if (!this.categories.length) {
			this.$store.dispatch('getCategories');
		}
		if (!this.priorities.length) {
			this.$store.dispatch('getPriorities');
		}
		if (!this.statuses.length) {
			this.$store.dispatch('getStatuses');
		}
		if (!this.agents.length) {
			this.$store.dispatch('getAgents');
		}
	},
	methods: {
		backToTicketList() {
			this.$router.push({name: 'SupportTicketList'})
		},
		sendSms() {
			if (this.ticket_twilio_sms_content.length < 5) {
				alert('Please add some content first.');
				return;
			}
			this.$store.commit('SET_LOADING_STATUS', true);
			axios.post(StackonetSupportTicket.restRoot + '/tickets/' + this.id + '/sms', {
				content: this.ticket_twilio_sms_content,
				send_to_customer: this.ticket_twilio_sms_customer_phone,
				send_to_custom_number: this.ticket_twilio_sms_enable_custom_phone,
				custom_phone: this.ticket_twilio_sms_custom_phone,
				agents_ids: this.twilio_support_agents_ids,
			}).then(() => {
				this.$store.commit('SET_LOADING_STATUS', false);
				this.ticket_twilio_sms_content = '';
				this.ticket_twilio_sms_customer_phone = true;
				this.ticket_twilio_sms_enable_custom_phone = false;
				this.ticket_twilio_sms_custom_phone = '';
				this.twilio_support_agents_ids = [];
				alert('Message has been sent.');
			}).catch(error => {
				console.log(error);
				this.$store.commit('SET_LOADING_STATUS', false);
			});
		},
		openNewTicket() {
			this.$router.push({name: 'NewSupportTicket'});
		},
		addNote() {
			this.addThread('note', this.content);
		},
		submitReply() {
			this.$dialog.confirm('Are you sure?').then(confirmed => {
				if (confirmed) {
					this.addThread('reply', this.content);
				}
			});
		},
		openTitleModal() {
			this.ticket_subject = this.item.ticket_subject;
			this.activeTitleModal = true;
		},
		openStatusModal() {
			this.activeStatusModal = true;
			this.ticket_category = this.item.ticket_category;
			this.ticket_priority = this.item.ticket_priority;
			this.ticket_status = this.item.ticket_status;
		},
		openAssignAgentModal() {
			let ids = this.assigned_agents_ids();
			this.activeAgentModal = true;
			this.support_agents_ids = ids;
		},
		openTwilioAssignAgentModal() {
			this.activeTwilioAgentModal = true;
		},
		assigned_agents_ids() {
			if (this.item.assigned_agents.length < 1) {
				return [];
			}

			return this.item.assigned_agents.map(item => {
				return item.id;
			});
		},
		updateAgent(agent) {
			let index = this.support_agents_ids.indexOf(agent.id);
			if (-1 !== index) {
				this.support_agents_ids.splice(index, 1);
			} else {
				this.support_agents_ids.push(agent.id);
			}
		},
		updateTwilioAgent(agent) {
			let index = this.twilio_support_agents_ids.indexOf(agent.id);
			if (-1 !== index) {
				this.twilio_support_agents_ids.splice(index, 1);
			} else {
				this.twilio_support_agents_ids.push(agent.id);
			}
		},
		updateAssignAgents() {
			this.$store.commit('SET_LOADING_STATUS', true);
			axios.post(StackonetSupportTicket.restRoot + '/tickets/' + this.id + '/agent', {agents_ids: this.support_agents_ids}).then(() => {
				this.$store.commit('SET_LOADING_STATUS', false);
				this.activeAgentModal = false;
				this.support_agents_ids = [];
				this.getItem();
			}).catch(error => {
				console.log(error);
				this.$store.commit('SET_LOADING_STATUS', false);
			});
		},
		openThreadEditor(thread) {
			this.activeThreadModal = true;
			this.activeThread = thread;
			this.activeThreadContent = thread.thread_content;
		},
		closeThreadEditor() {
			this.activeThreadModal = false;
			this.activeThread = {};
			this.activeThreadContent = '';
		},
		updateTicketStatus() {
			this.$store.commit('SET_LOADING_STATUS', true);
			axios.put(StackonetSupportTicket.restRoot + '/tickets/' + this.id, {
				ticket_category: this.ticket_category,
				ticket_priority: this.ticket_priority,
				ticket_status: this.ticket_status,
			}).then(() => {
				this.$store.commit('SET_LOADING_STATUS', false);
				this.activeStatusModal = false;
				this.ticket_subject = '';
				this.ticket_status = '';
				this.ticket_priority = '';
				this.getItem();
			}).catch(error => {
				console.log(error);
				this.$store.commit('SET_LOADING_STATUS', false);
			});
		},
		updateSubject() {
			this.$store.commit('SET_LOADING_STATUS', true);
			axios.put(StackonetSupportTicket.restRoot + '/tickets/' + this.id, {
				ticket_subject: this.ticket_subject,
			}).then(() => {
				this.$store.commit('SET_LOADING_STATUS', false);
				this.activeTitleModal = false;
				this.ticket_subject = '';
				this.getItem();
			}).catch(error => {
				console.log(error);
				this.$store.commit('SET_LOADING_STATUS', false);
			});
		},
		addThread(thread_type, thread_content) {
			this.$store.commit('SET_LOADING_STATUS', true);
			axios.post(StackonetSupportTicket.restRoot + '/tickets/' + this.id + '/thread/', {
				thread_type: thread_type,
				thread_content: thread_content,
			}).then(() => {
				this.$store.commit('SET_LOADING_STATUS', false);
				this.content = '';
				this.getItem();
			}).catch(error => {
				console.log(error);
				this.$store.commit('SET_LOADING_STATUS', false);
			});
		},
		updateThread() {
			this.$store.commit('SET_LOADING_STATUS', true);
			axios.put(StackonetSupportTicket.restRoot + '/tickets/' + this.id + '/thread/' + this.activeThread.thread_id, {
				post_content: this.activeThreadContent,
			}).then(() => {
				this.$store.commit('SET_LOADING_STATUS', false);
				this.activeThreadModal = false;
				this.activeThread = {};
				this.activeThreadContent = '';
				this.getItem();
			}).catch(error => {
				console.log(error);
				this.$store.commit('SET_LOADING_STATUS', false);
			});
		},
		deleteThread(thread) {
			if (confirm('Are you sure to delete this thread?')) {
				this.$store.commit('SET_LOADING_STATUS', true);
				axios.delete(StackonetSupportTicket.restRoot + '/tickets/' + this.id + '/thread/' + thread.thread_id).then(() => {
					this.$store.commit('SET_LOADING_STATUS', false);
					this.getItem();
				}).catch(error => {
					console.log(error);
					this.$store.commit('SET_LOADING_STATUS', false);
				});
			}
		},
		threadClass(thread_type) {
			return [
				'shapla-thread',
				`shapla-thread--${thread_type}`
			]
		},
		ticketList() {
			this.$router.push({name: 'SupportTicketList'});
		},
		getItem() {
			this.$store.commit('SET_LOADING_STATUS', true);
			axios.get(StackonetSupportTicket.restRoot + '/tickets/' + this.id).then(response => {
				this.$store.commit('SET_LOADING_STATUS', false);
				this.item = response.data.data.ticket;
				this.threads = response.data.data.threads;
			}).catch(error => {
				console.log(error);
				this.$store.commit('SET_LOADING_STATUS', false);
			});
		}
	}
}
</script>

<style lang="scss">
.stackont-single-support-ticket-container {
	margin-top: 50px;
	margin-bottom: 50px;

	.stackont-single-support-ticket-actions-bar {
		border-bottom: 1px solid rgba(#000, 0.1);
		padding-bottom: 1.5rem;
		margin-bottom: 1.5rem;
	}

	.stackont-single-support-ticket-actions {
		display: flex;
		margin-bottom: 20px;

		.left {
			> *:not(:last-child) {
				margin-right: 5px;
			}
		}

		> *:not(:last-child) {
			margin-right: 5px;
		}
	}

	.stackont-single-ticket__heading {
		display: flex;
		align-items: center;
		margin: 0 0 1.5rem 0;
		justify-content: space-between;

		h4, i {
			padding: 0;
			font-size: 20px;
			margin: 0 0 1em;
		}
	}

	.support_agents-chip {
		padding: 10px;
		cursor: pointer;

		.shapla-chip.is-active {
			background: #f68638;
			color: #fff;
		}
	}
}

.support-ticket-log {

}

.shapla-widget-box {
	margin-bottom: 1.5rem;
	box-shadow: 0 4px 6px rgba(50, 50, 93, .11), 0 1px 3px rgba(0, 0, 0, .08);

	&__heading {
		align-items: center;
		border-bottom: 1px solid rgba(#000, 0.2);
		display: flex;
		justify-content: space-between;
		padding-bottom: 10px;
		margin-bottom: 10px;
	}

	&__title {
		color: currentColor;
		margin: 0;
		padding: 0;
	}

	&__customer-phone {
		padding-bottom: 10px;
	}

}

.shapla-chip {
	height: 32px;
	line-height: 32px;
	border: 0;
	border-radius: 16px;
	background-color: #f1f1f1;
	display: inline-block;
	color: rgba(0, 0, 0, .87);
	margin: 2px 0;
	font-size: 0;
	white-space: nowrap;
	padding: 0 12px 0 0;

	&:not(:last-child) {
		margin-right: 10px;
	}

	&__contact {
		height: 32px;
		width: 32px;
		border-radius: 16px;
		margin-right: 8px;
		font-size: 18px;
		line-height: 32px;
		display: inline-block;
		vertical-align: middle;
		overflow: hidden;
		text-align: center;
	}

	&__text {
		font-size: 13px;
		vertical-align: middle;
		display: inline-block;
	}
}

.shapla-thread {
	&--log {
		background-color: rgba(#f68638, 0.2);
		border: 1px solid rgba(#000, 0.2);
		border-radius: 6px;
		margin: 1.5rem auto;
		padding: 1.5rem;
		text-align: center;
		max-width: 600px;
	}

	&--report {
		background-color: #fff;
	}

	&--note {
		background-color: #fffdf5;
	}

	&--reply {
		background-color: #f5fffd;
	}

	&--sms {
		background-color: rgba(255, 56, 96, 0.1);
	}

	&--sms,
	&--report,
	&--note,
	&--reply {
		box-shadow: 0 4px 6px rgba(50, 50, 93, .11), 0 1px 3px rgba(0, 0, 0, .08);
		//border: 1px solid rgba(#000, 0.2);
		border-radius: 6px;
		display: flex;
		margin: 1.5rem 0;
		padding: 1.5rem;

		> * {
			flex-grow: 1;
		}

		.shapla-thread__avatar {
			width: 50px;
			margin-right: 10px;
		}

		.shapla-thread__actions {
			width: 62px;
		}

		.shapla-thread__customer_name {
			font-weight: bold;
		}

		.shapla-thread__content {
			width: calc(100% - 100px);

			table {
				margin-bottom: 0;
			}

			&-top {
				border-bottom: 1px solid rgba(#000, 0.2);
				display: flex;
				margin-bottom: 10px;
				padding-bottom: 10px;
			}

			&-align-left,
			&-align-right {
			}

			&-align-left {
				display: flex;
				flex-grow: 1;
			}
		}
	}

	&__time {
		display: block;
	}

}

.table--support-order {
	td {
		padding: 8px;
	}

	img {
		max-width: 200px;
		height: auto;
	}
}

.mce-panel {
	border: none !important;
}

.mce-tinymce {
	box-shadow: 0 4px 6px rgba(50, 50, 93, .11), 0 1px 3px rgba(0, 0, 0, .08) !important;
}
</style>
