<template>
	<div>
		<data-table
			:show-cb="false"
			:columns="columns"
			:items="agents"
			:actions="actions"
			@action:click="onActionClick"
			action-column="display_name"
			index="term_id"
		>
			<template slot="avatar_url" slot-scope="data">
				<img :src="data.row.avatar_url" alt="" width="48" height="48">
			</template>
		</data-table>

		<div class="button-add-agent-container" title="Add Agent">
			<shapla-button theme="primary" size="medium" :fab="true" @click="showAddAgentModal = true">+
			</shapla-button>
		</div>
		<modal :active="showAddAgentModal" @close="showAddAgentModal = false" title="Add Agent">
			<div class="modal--add-agent-inner" style="min-height: 200px">
				<columns :multiline="true">
					<column :tablet="12">
						<label>Agent</label>
						<v-select @search="fetchUsers" :filterable="false" :options="users"
								  label="name" v-model="addAgentActiveAgent"/>
						<span class="help has-error" v-if="agentError.length">{{ agentError }}</span>
					</column>
					<column :tablet="12">
						<label>Role</label>
						<select-field v-if="roles.length" :options="roles" label="name" v-model="addAgentActiveRole"/>
					</column>
				</columns>
			</div>
			<template slot="foot">
				<shapla-button theme="primary" :disabled="!canCreateAgent"
							   @click="createNewAgent"> Create
				</shapla-button>
			</template>
		</modal>
		<modal :active="showEditAgentModal" @close="showEditAgentModal = false" title="Edit Agent Role">
			<select-field
				label="Role"
				:options="roles"
				v-model="editAgentActiveAgent.role_id"
				label-key="name"
				value-key="role"
			/>
			<div>
				<text-field
					label="Email"
					v-model="editAgentActiveAgent.email"
				/>
			</div>
			<div>
				<text-field
					label="Phone Number"
					v-model="editAgentActiveAgent.phone"
				/>
			</div>
			<template slot="foot">
				<shapla-button theme="primary" @click="updateAgentRole">Update</shapla-button>
			</template>
		</modal>
	</div>
</template>

<script>
import axios from 'axios';
import vSelect from 'vue-select'
import modal from 'shapla-modal'
import {column, columns} from 'shapla-columns'
import shaplaButton from "shapla-button";
import dataTable from "shapla-data-table";
import selectField from 'shapla-select-field';
import textField from 'shapla-text-field';
import {CrudMixin} from "../../mixins/CrudMixin";

export default {
	name: "Agents",
	mixins: [CrudMixin],
	components: {shaplaButton, vSelect, dataTable, modal, columns, column, selectField, textField},
	data() {
		return {
			showAddAgentModal: false,
			showEditAgentModal: false,
			users: [],
			agents: [],
			roles: [],
			columns: [
				{key: 'display_name', label: 'Name'},
				{key: 'email', label: 'Email'},
				{key: 'role_label', label: 'Role'},
				{key: 'avatar_url', label: 'Avatar'},
			],
			activeRole: {},
			role: {},
			addAgentActiveAgent: {},
			addAgentActiveRole: {},
			editAgentActiveAgent: {},
			agentError: '',
		}
	},
	mounted() {
		this.$store.commit('SET_LOADING_STATUS', false);
		this.getAgents();
		this.getRoles();
	},
	computed: {
		actions() {
			return [
				{key: 'edit', label: 'Edit'},
				{key: 'delete', label: 'Delete'}
			];
		},
		canCreateAgent() {
			if (!this.addAgentActiveAgent || !this.addAgentActiveRole) return false;

			return !!(Object.keys(this.addAgentActiveAgent).length && Object.keys(this.addAgentActiveRole).length);
		}
	},
	methods: {
		onActionClick(action, item) {
			if ('edit' === action) {
				this.showEditAgentModal = true;
				this.editAgentActiveAgent = item;
			}
			if ('delete' === action) {
				this.$dialog.confirm('Are you sure to delete this agent?').then(confirm => {
					if (confirm) {
						this.trashAction(item.term_id);
					}
				});
			}
		},
		trashAction(item) {
			this.delete_item(StackonetSupportTicket.restRoot + '/agents/' + item).then(() => {
				this.$store.commit('SET_SNACKBAR', {
					title: 'Success!',
					message: 'Support agent has been deleted.',
					type: 'success',
				});
				this.getAgents();
			}).catch(error => {
				if (error.response.data.message) {
					this.$store.commit('SET_SNACKBAR', {
						title: 'Error!',
						message: error.response.data.message,
						type: 'error',
					});
				}
			});
		},
		getAgents() {
			this.get_item(StackonetSupportTicket.restRoot + '/agents').then(data => {
				this.agents = data.items;
			}).catch(error => {
				console.log(error);
			})
		},
		getRoles() {
			this.get_item(StackonetSupportTicket.restRoot + '/roles').then(data => {
				this.roles = data.roles;
			}).catch(error => {
				console.log(error);
			})
		},
		fetchUsers(search, loading) {
			axios.get(StackonetSupportTicket.wpRestRoot + '/users', {
				params: {search: search}
			}).then(response => {
				let _data = response.data;
				this.users = _data.length ? _data : [];
			}).catch(error => {
				console.log(error);
			})
		},
		createNewAgent() {
			this.agentError = '';
			this.create_item(StackonetSupportTicket.restRoot + '/agents', {
				user_id: this.addAgentActiveAgent.id,
				role_id: this.addAgentActiveRole.role,
			}).then(() => {
				this.showAddAgentModal = false;
				this.getAgents();
			}).catch(error => {
				this.agentError = error.response.data.message;
			});
		},
		updateAgentRole() {
			this.update_item(StackonetSupportTicket.restRoot + '/agents/' + this.editAgentActiveAgent.term_id, {
				role_id: this.editAgentActiveAgent.role_id,
				phone_number: this.editAgentActiveAgent.phone,
				email: this.editAgentActiveAgent.email,
			}).then(() => {
				this.showEditAgentModal = false;
				this.getAgents();
			}).catch(error => {
				this.agentError = error.response.data.message;
			});
		}
	}
}
</script>

<style lang="scss">
.shapla-box--role {
	margin-bottom: 1rem;
	justify-content: space-between;
	align-items: center;
}

.agent-capabilities {
	margin-bottom: 16px;
}

.agent-capability {
	display: inline-flex;
	flex-direction: column;

	&__title {
		font-size: 14px;
		font-weight: bold;
	}

	&__description {
		font-size: 13px;
		font-style: italic;
		color: rgba(#000, .35);
	}
}

.modal--add-agent-inner {
}

.vs__search,
.vs__search:focus {
	border: 1px solid transparent !important;
	border-left: none !important;
	outline: none !important;
	box-shadow: none !important;
}

.help.has-error {
	color: darkred;
}
</style>
