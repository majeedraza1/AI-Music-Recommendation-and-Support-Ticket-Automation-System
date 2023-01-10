<template>
	<div class="support-ticket-thread" :class="threadClass">
		<div class="support-ticket-thread__content">
			<div class="support-ticket-thread__header">

				<div class="support-ticket-thread__avatar">
					<image-container container-width="48px" container-height="48px" is-rounded>
						<img :src="thread.customer_avatar_url" width="48" height="48" alt="">
					</image-container>
				</div>

				<div class="support-ticket-thread__user-info">
					<span class="support-ticket-thread__customer_name">{{ thread.customer_name }}</span>
					<small class="support-ticket-thread__time">&nbsp;
						<span v-if="thread.thread_type === 'note'">added note</span>
						<span v-else-if="thread.thread_type === 'reply'">replied</span>
						<span v-else-if="thread.thread_type === 'sms'">sent sms</span>
						<span v-else>reported</span>
						{{ to_human_time(thread.thread_date) }} ago
					</small>
					<div class="support-ticket-thread__customer_email">{{ thread.customer_email }}</div>
					<div class="support-ticket-thread__user_type">
						{{ thread.user_type === 'agent' ? "Agent" : "User" }}
					</div>
				</div>
				<div class="support-ticket-thread__spacer"></div>
				<div class="support-ticket-thread__content-align-right support-ticket-thread__actions">
					<icon-container size="medium" hoverable @click="$emit('edit',thread)">
						<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20">
							<title>Edit Thread</title>
							<use xlink:href="#icon-pen"/>
						</svg>
					</icon-container>
					<icon-container size="medium" hoverable @click="$emit('delete',thread)">
						<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20">
							<title>Delete Thread</title>
							<use xlink:href="#icon-delete_outline"/>
						</svg>
					</icon-container>
				</div>
			</div>
			<div v-html="thread.thread_content"></div>
			<div class="support-ticket-thread__attachment" v-if="thread.attachments.length">
				<strong>Attachments :</strong>
				<table>
					<tr v-for="attachment in thread.attachments">
						<td>
							<a target="_blank" :href="attachment.download_url">{{ attachment.title }}</a>
						</td>
					</tr>
				</table>
			</div>
		</div>
	</div>
</template>

<script>
import imageContainer from 'shapla-image-container';
import iconContainer from 'shapla-icon-container';
import human_time_diff from "../human_time_diff";

export default {
	name: "TicketThread",
	components: {imageContainer, iconContainer},
	props: {
		thread: {type: Object},
		thread_type: {type: String},
		thread_content: {type: String},
		attachments: {type: Array},
	},
	computed: {
		threadClass() {
			return [
				`support-ticket-thread--${this.thread.thread_type}`
			]
		},
	},
  methods:{
    to_human_time(date) {
      return human_time_diff(date);
    }
  }
}
</script>

<style lang="scss">
.support-ticket-thread {
	background-color: #fff;
	box-shadow: 0 4px 6px rgba(50, 50, 93, 0.11), 0 1px 3px rgba(0, 0, 0, 0.08);
	border-radius: 6px;
	display: flex;
	margin: 1.5rem 0;
	padding: 1.5rem;

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

	> * {
		flex-grow: 1;
	}

	&__header {
		border-bottom: 1px solid rgba(#000, 0.12);
		display: flex;
		margin-bottom: 0.5rem;
		padding-bottom: 0.5rem;
	}

	&__avatar {
		padding-right: 1rem;
	}

	&__customer_name {
		font-weight: bold;
	}

	&__spacer {
		flex-grow: 1;
	}

	&__attachment {
		margin-top: 1rem;
	}
}
</style>