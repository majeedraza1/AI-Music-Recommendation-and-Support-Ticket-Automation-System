<template>
	<div>
		<draggable :list="statuses" class="list-group" handle=".handle" @update="updateMenuOrder">
			<div v-for="_status in statuses" :key="_status.term_id">
				<div class="bg-white rounded p-4 shapla-box--role flex w-full content-center shadow--2dp">
					<div>
						<strong>{{ _status.name }}</strong>
            <span class="extra_info">ID: {{ _status.term_id }}</span>
					</div>
					<div class="flex">
						<div class="handle">
							<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 320 512">
								<path fill="currentColor"
								      d="M41 288h238c21.4 0 32.1 25.9 17 41L177 448c-9.4 9.4-24.6 9.4-33.9 0L24 329c-15.1-15.1-4.4-41 17-41zm255-105L177 64c-9.4-9.4-24.6-9.4-33.9 0L24 183c-15.1 15.1-4.4 41 17 41h238c21.4 0 32.1-25.9 17-41z"></path>
							</svg>
						</div>
						<div @click.prevent="editStatus(_status)">
							<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
								<path
									d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/>
								<path d="M0 0h24v24H0z" fill="none"/>
							</svg>
						</div>
						<div @click.prevent="deleteStatus(_status)">
							<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
								<path
									d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM8 9h8v10H8V9zm7.5-5l-1-1h-5l-1 1H5v2h14V4z"/>
								<path fill="none" d="M0 0h24v24H0V0z"/>
							</svg>
						</div>
					</div>
				</div>
			</div>
		</draggable>

		<modal :active="showAddStatusModal" @close="showAddStatusModal = false" title="Add Status">
			<text-field v-model="statusName" label="Status"/>
			<div class="help has-error" v-if="statusNameError.length">{{ statusNameError }}</div>
			<text-field label="Color Hex code" v-model="statusColor"/>
			<template slot="foot">
				<shapla-button theme="primary" :disabled="statusName.length < 3" @click="createStatus">
					Create
				</shapla-button>
			</template>
		</modal>

		<modal :active="showEditStatusModal" @close="showEditStatusModal = false" title="Edit Status">
			<template v-if="Object.keys(editActiveStatus).length">
				<text-field v-model="editActiveStatus.name" label="Status Name"/>
				<text-field v-model="editActiveStatus.slug" label="Status Slug"/>
				<text-field v-model="editActiveStatus.color" label="Color Hex code"/>
				<p class="help has-error" v-if="editError.length" v-html="editError"/>
			</template>
			<template slot="foot">
				<shapla-button theme="primary" @click="updateStatus">Update</shapla-button>
			</template>
		</modal>

		<div class="button-add-status-container" title="Add Status">
			<shapla-button theme="primary" :fab="true" size="medium" @click="showAddStatusModal = true">+
			</shapla-button>
		</div>
	</div>
</template>

<script>
import modal from 'shapla-modal'
import draggable from 'vuedraggable'
import {CrudMixin} from "../../mixins/CrudMixin";
import textField from "shapla-text-field";
import shaplaButton from "shapla-button";

export default {
	name: "TicketStatuses",
	components: {shaplaButton, modal, textField, draggable},
	mixins: [CrudMixin],
	data() {
		return {
			statuses: [],
			showAddStatusModal: false,
			showEditStatusModal: false,
			addActiveStatus: {},
			editActiveStatus: {},
			statusName: '',
			statusNameError: '',
			statusColor: '',
			editError: '',
		}
	},
	mounted() {
		this.$store.commit('SET_LOADING_STATUS', false);
		this.getStatuses();
	},
	methods: {
		getStatuses() {
			this.get_item(StackonetSupportTicket.restRoot + '/statuses').then(data => {
				this.statuses = data.items;
			}).catch(error => {
				console.log(error);
			})
		},
		createStatus() {
			this.create_item(StackonetSupportTicket.restRoot + '/statuses', {
				name: this.statusName,
				color: this.statusColor
			}).then(() => {
				this.showAddStatusModal = false;
				this.getStatuses();
			}).catch(error => {
				this.statusNameError = error.response.data.message;
			})
		},
		editStatus(status) {
			this.editActiveStatus = status;
			this.showEditStatusModal = true;
		},
		updateStatus() {
			this.editError = '';
			this.create_item(StackonetSupportTicket.restRoot + '/statuses/' + this.editActiveStatus.term_id, {
				name: this.editActiveStatus.name,
				slug: this.editActiveStatus.slug,
				color: this.editActiveStatus.color,
			}).then(() => {
				this.showEditStatusModal = false;
				this.editActiveStatus = {};
				this.getStatuses();
			}).catch(error => {
				this.editError = error.response.data.message;
			})
		},
		deleteStatus(status) {
			this.$dialog.confirm('Are you sure to delete this status?').then(confirm => {
				if (confirm) {
					this.delete_item(StackonetSupportTicket.restRoot + '/statuses/' + status.term_id).then(() => {
						this.$store.commit('SET_SNACKBAR', {
							title: 'Success!',
							message: 'Status has been deleted?',
							type: 'success',
						});
						this.getStatuses();
					}).catch(error => {
						this.statusNameError = error.response.data.message;
					})
				}
			});
		},
		updateMenuOrder() {
			let menu_orders = this.statuses.map(el => el.term_id);
			this.create_item(StackonetSupportTicket.restRoot + '/statuses/batch', {menu_orders: menu_orders}).then(() => {
				this.$store.commit('SET_SNACKBAR', {
					title: 'Success!',
					message: 'Status orders have been updated.',
					type: 'success',
				});
			}).catch(error => {
				console.log(error.response.data);
			})
		}
	}
}
</script>

<style lang="scss">
.button-add-status-container {
	position: fixed;
	bottom: 1rem;
	right: 1rem;
	z-index: 10;
}
</style>
