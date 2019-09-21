<template>
	<div class="admin-support-tickets-container">
		<router-view></router-view>
		<spinner :active="loading"></spinner>
		<notification v-model="snackbar"></notification>
	</div>
</template>

<script>
	import {mapState} from 'vuex';
	import notification from 'shapla-notifications';
	import spinner from "shapla-spinner";

	export default {
		name: "SupportTicket",
		components: {notification, spinner},
		computed: {
			...mapState(['snackbar', 'loading']),
		}
	}
</script>

<style lang="scss">
	.admin-support-tickets-container {
		position: relative;
		box-sizing: border-box;

		* {
			box-sizing: border-box;
		}
	}
</style>
