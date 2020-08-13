<template>
	<div class="stackont-support-ticket-container stackont-support-ticket-container--new">
		<p>
			<shapla-button theme="primary" @click="backToTicketList">Back to Ticket</shapla-button>
		</p>

		<columns multiline>

			<column :desktop="6">
				<div class="form-field">
					<text-field label="Name" id="customer_name" v-model="customer_name"/>
				</div>
			</column>

			<column :desktop="6">
				<div class="form-field">
					<text-field label="Email Address" id="email_address" v-model="customer_email"/>
				</div>
			</column>

			<column :desktop="12">
				<div class="form-field">
					<text-field type="textarea" :rows="2" label="Subject" id="ticket_subject" v-model="ticket_subject"/>
				</div>
			</column>

			<column :desktop="12">
				<div class="form-field">
					<label for="ticket_description">Description</label>
					<editor id="ticket_description" :init="mce" v-model="ticket_content"/>
				</div>
			</column>

			<column :desktop="6">
				<div class="form-field">
					<select-field label="Category *" v-model="ticket_category" :options="categories"
					              label-key="name" value-key="term_id"/>
				</div>
			</column>

			<column :desktop="6">
				<div class="form-field">
					<select-field label="Priority *" v-model="ticket_priority" :options="priorities"
					              label-key="name" value-key="term_id"/>
				</div>
			</column>

			<column :desktop="12">
				<div class="form-field">
					<shapla-button theme="primary" :disabled="!canSubmit" @click="submitTicket">
						Submit Ticket
					</shapla-button>
				</div>
			</column>
		</columns>

	</div>
</template>

<script>
import axios from 'axios'
import {mapGetters, mapState} from 'vuex';
import Editor from '@tinymce/tinymce-vue'
import shaplaButton from "shapla-button";
import {column, columns} from "shapla-columns";
import selectField from 'shapla-select-field'
import textField from 'shapla-text-field';
import Icon from "../../shapla/icon/icon";

export default {
	name: "NewSupportTicket",
	components: {column, columns, Icon, shaplaButton, Editor, selectField, textField},
	data() {
		return {
			customer_name: '',
			customer_email: '',
			ticket_subject: '',
			ticket_content: '',
			ticket_category: '',
			ticket_priority: '',
		}
	},
	computed: {
		...mapState(['categories', 'priorities', 'statuses', 'agents']),
		...mapGetters(['display_name', 'user_email']),
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
		isValidEmail() {
			return !!(this.customer_email.length && this.validateEmail(this.customer_email));
		},
		canSubmit() {
			return !!(this.customer_name.length > 2 && this.isValidEmail &&
				this.ticket_subject.length > 3 && this.ticket_content.length > 5);
		}
	},
	mounted() {
		this.$store.commit('SET_LOADING_STATUS', false);
		this.$store.commit('SET_SHOW_SIDE_NAVE', false);
		this.customer_name = this.display_name;
		this.customer_email = this.user_email;
		if (!this.categories.length) {
			this.$store.dispatch('getCategories');
		}
		if (!this.priorities.length) {
			this.$store.dispatch('getPriorities');
		}
	},
	methods: {
		backToTicketList() {
			this.$router.push({name: 'SupportTicketList'})
		},
		validateEmail(email) {
			let re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
			return re.test(String(email).toLowerCase());
		},
		ticketList() {
			this.$router.push({name: 'SupportTicketList'});
		},
		submitTicket() {
			let self = this;
			axios.post(StackonetSupportTicket.restRoot + '/tickets', {
				name: self.customer_name,
				email: self.customer_email,
				subject: self.ticket_subject,
				content: self.ticket_content,
				category: self.ticket_category,
				priority: self.ticket_priority,
			}).then((response) => {
				self.$store.commit('SET_LOADING_STATUS', false);
				let id = response.data.data.ticket_id;
				this.$router.push({name: 'SingleSupportTicket', params: {id: id}});
			}).catch(error => {
				console.log(error);
				self.$store.commit('SET_LOADING_STATUS', false);
			});
		}
	}
}
</script>

<style lang="scss">
.stackont-support-ticket-container--new {
	margin-top: 50px;
}

.display-flex.justify-space-between {
	margin-bottom: 20px;
}

.stackont-support-ticket-container {
	&--new {
		max-width: 800px;
		margin-left: auto;
		margin-right: auto;
	}

	.shapla-text-field__input {
		width: 100% !important;
	}

	.form-field {
		display: block;

		label {
			display: block;
		}

		input, select {
			display: block;
			width: 100%;
		}
	}
}
</style>
