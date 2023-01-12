<template>
	<div class="add-ticket-thread">
		<div>
			<editor :init="mce" v-model="content"/>
		</div>
		<div v-show="content.length" class="add-ticket-thread__settings p-4">
			<div class="mb-8">
				<label class="flex mb-2" for="thread_attachments"><strong>Attachments</strong></label>
				<input type="file" id="thread_attachments" class="thread-attachments" multiple>
			</div>
			<div class="mb-4 flex flex-col">
				<div class="mb-2 w-full" v-if="cbPushNotification">
					<shapla-checkbox v-model="send_push_notification">Send Push Notification</shapla-checkbox>
				</div>
				<div class="mb-2 w-full">
					<shapla-checkbox v-model="send_email_notification">Send Email Notification</shapla-checkbox>
				</div>
			</div>
			<div class="mb-4 flex space-x-2">
				<div class="flex-grow"></div>
				<shapla-button theme="primary" @click="addThread('reply')">Submit Reply</shapla-button>
			</div>
		</div>
	</div>
</template>

<script>
import Editor from '@tinymce/tinymce-vue'
import {shaplaButton,shaplaCheckbox} from 'shapla-vue-components'
import axios from "axios";

export default {
	name: "AddTicketThread",
	components: {Editor, shaplaButton, shaplaCheckbox},
	props: {
		ticket_id: {type: Number, default: 0},
		cbPushNotification: {type: Boolean, default: false},
	},
	data() {
		return {
			content: '',
			send_push_notification: false,
			send_email_notification: false
		}
	},
	computed: {
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
	methods: {
		submit(data) {
			this.$emit('added', data);
		},
		addThread(thread_type) {
			this.$store.commit('SET_LOADING_STATUS', true);
			let headers = {'Content-Type': 'multipart/form-data'};
			let formData = new FormData();
			formData.append('thread_type', thread_type);
			formData.append('thread_content', this.content);
			formData.append('send_push_notification', this.send_push_notification);
			formData.append('send_email_notification', this.send_email_notification);

			let fileList = this.$el.querySelector('.thread-attachments').files;
			for (let i = 0, numFiles = fileList.length; i < numFiles; i++) {
				formData.append("files[]", fileList[i]);
			}
			axios.post(StackonetSupportTicket.restRoot + '/tickets/' + this.ticket_id + '/thread/', formData, {headers: headers}).then(response => {
				this.$store.commit('SET_LOADING_STATUS', false);
				this.content = '';
				this.submit(response);
			}).catch(error => {
				console.log(error);
				this.$store.commit('SET_LOADING_STATUS', false);
			});
		},
	}
}
</script>

<style lang="scss">
.add-ticket-thread {
	.mce-tinymce {
		box-shadow: none !important;
		border-bottom: 1px solid rgba(#000, 0.12) !important;
	}

	&__settings {
		background-color: #ffffff;
	}
}
</style>
