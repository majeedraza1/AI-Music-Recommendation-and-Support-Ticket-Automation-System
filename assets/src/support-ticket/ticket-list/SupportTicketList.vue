<template>
	<div class="stackont-support-ticket-container">
		<div class="flex items-center">
			<div>
				<h2 v-if="label === 'trash'">Trashed Tickets</h2>
				<h2 v-else>Tickets</h2>
			</div>
		</div>
		<div class="button--new-ticket-container">
			<shapla-button theme="primary" :fab="true" size="medium" @click="openNewTicket">+</shapla-button>
		</div>
		<div class="clear"></div>
		<div class="stackonet-support-ticket-icon-search">
			<columns>
				<column>
					<status-list :statuses="labels" @change="changeLabel"/>
				</column>
				<column>
				</column>
				<column>
					<div class="search-box">
						<search-form @search="searchTicket" @clear="searchTicket"/>
					</div>
				</column>
			</columns>
			<columns>
				<column :tablet="4">
					<div class="flex items-center">
                        <span class="" @click="openSideNav">
                        <svg xmlns="http://www.w3.org/2000/svg">
                            <title>Toggle Side Navigation</title>
                            <use xlink:href="#icon-menu"/>
                        </svg>
                    </span>
						<span>Filter Tickets</span>
					</div>
				</column>
				<column :tablet="4">
					<div class="flex justify-center">
						<div v-if="label !=='trash'" @click="exportExcel" class="button-export"
							 :class="{'is-active':selectedItems.length}">
							<svg xmlns="http://www.w3.org/2000/svg">
								<title>Import Export</title>
								<use xlink:href="#icon-import_export"/>
							</svg>
						</div>
						<div v-if="label==='trash'" @click="restoreItems" class="button-restore"
							 :class="{'is-active':selectedItems.length}">
							<svg xmlns="http://www.w3.org/2000/svg">
								<title>Restore</title>
								<use xlink:href="#icon-settings_restore"/>
							</svg>
						</div>
						<div @click="trashItems" class="button-trash" :class="{'is-active':selectedItems.length}">
							<svg xmlns="http://www.w3.org/2000/svg">
								<title>Trash</title>
								<use xlink:href="#icon-delete_outline"/>
							</svg>
						</div>
					</div>
				</column>
				<column :tablet="4">
					<pagination :total_items="pagination.total_items" :per_page="50" :current_page="currentPage"
								@pagination="paginate"/>
				</column>
			</columns>
		</div>
		<columns multiline>
			<column :tablet="12">
				<data-table :columns="columns" :items="tickets" :selected-items="selectedItems" :actions="actions"
							@action:click="onActionClick" @bulk:apply="onBulkAction" @item:select="updateSelectedItems"
				>
					<span slot="ticket_subject" slot-scope="data"><strong>#{{
							data.row.id
						}}</strong> - {{ data.row.ticket_subject }}</span>
					<div slot="customer_name" slot-scope="data">
						<span class="flex w-full"><strong>{{ data.row.customer_name }}</strong></span>
						<span class="flex w-full">{{ data.row.customer_email }}</span>
						<span class="flex w-full">{{ data.row.customer_phone }}</span>
					</div>
					<template slot="created_by" slot-scope="data" class="button--status">
						<span v-html="getAssignedAgents(data.row.assigned_agents)"></span>
					</template>
					<span slot="ticket_status" slot-scope="data" class="button--status"
						  :class="data.row.status.slug">{{ data.row.status.name }}</span>
					<span slot="ticket_category" slot-scope="data" class="button--category"
						  :class="data.row.category.slug">{{ data.row.category.name }}</span>
					<span slot="ticket_priority" slot-scope="data" class="button--priority"
						  :class="data.row.priority.slug">{{ data.row.priority.name }}</span>
				</data-table>
			</column>
			<column :tablet="12">
				<pagination :total_items="pagination.total_items" :per_page="50" :current_page="currentPage"
							@pagination="paginate"/>
			</column>
		</columns>

	</div>
</template>

<script>
import axios from 'axios';
import {mapState} from 'vuex';
import {column, columns} from 'shapla-columns';
import shaplaButton from "shapla-button";
import searchForm from "shapla-search-form";
import dataTable from "shapla-data-table";
import pagination from "shapla-data-table-pagination";
import Icon from "../../shapla/icon/icon";
import statusList from "shapla-data-table-status";

export default {
	name: "SupportTicketList",
	components: {Icon, shaplaButton, dataTable, searchForm, columns, column, pagination, statusList},

	data() {
		return {
			loading: false,
			search_categories: [],
			selectedItems: [],
			columns: [
				{key: 'ticket_subject', label: 'Subject', numeric: false},
				{key: 'customer_name', label: 'Raised By', numeric: false},
				{key: 'created_by', label: 'Assigned Agent', numeric: false},
				{key: 'ticket_status', label: 'Status', numeric: false},
				{key: 'ticket_category', label: 'Category', numeric: false},
				{key: 'ticket_priority', label: 'Priority', numeric: false},
				{key: 'updated_human_time', label: 'Updated', numeric: false},
			]
		}
	},
	mounted() {
		this.$store.commit('SET_LOADING_STATUS', false);
		if (!this.tickets.length) {
			this.getItems();
		}
	},
	computed: {
		...mapState(['pagination', 'tickets', 'filters', 'meta_data', 'label',
			'status', 'category', 'priority', 'currentPage', 'city', 'search', 'labels']),
		actions() {
			return this.meta_data.actions;
		},
		bulkActions() {
			return this.meta_data.bulkActions;
		},
	},
	methods: {
		changeLabel(label) {
			if ('trash' === label.key) {
				this.getTrashedItems();
			} else {
				this.getActiveItems();
			}
		},
		getActiveItems() {
			this.$store.commit('SET_STATUS', 0);
			this.$store.commit('SET_CATEGORY', 0);
			this.$store.commit('SET_PRIORITY', 0);
			this.$store.commit('SET_AGENT', 0);
			this.$store.commit('SET_LABEL', 'active');
			this.$store.commit('SET_SHOW_SIDE_NAVE', false);

			this.$store.dispatch('getTickets');
		},
		getTrashedItems() {
			this.$store.commit('SET_STATUS', 0);
			this.$store.commit('SET_CATEGORY', 0);
			this.$store.commit('SET_PRIORITY', 0);
			this.$store.commit('SET_AGENT', 0);
			this.$store.commit('SET_LABEL', 'trash');
			this.$store.commit('SET_SHOW_SIDE_NAVE', false);

			this.$store.dispatch('getTickets');
		},
		openSideNav() {
			this.$store.commit('SET_SHOW_SIDE_NAVE', true);
		},
		getItems() {
			this.$store.dispatch('getTickets');
		},
		categorySearch(data) {
			this.$store.commit('SET_CATEGORY', data.cat);
			this.$store.commit('SET_SEARCH', data.query);
			this.getItems();
		},
		openNewTicket() {
			this.$router.push({name: 'NewSupportTicket'});
		},
		getAssignedAgents(data) {
			if (data.length < 1) return 'None';

			let html = '';
			for (let i = 0; i < data.length; i++) {
				html += (i !== 0) ? ', ' : '';
				html += data[i].display_name;
			}
			return html;
		},
		clearFilter() {
			this.$store.commit('SET_STATUS', 'all');
			this.$store.commit('SET_CATEGORY', 'all');
			this.$store.commit('SET_PRIORITY', 'all');
			this.$store.commit('SET_CITY', 'all');

			this.getItems();
		},
		changeStatus() {
			this.$store.commit('SET_CURRENT_PAGE', 1);
			this.getItems();
		},
		paginate(page) {
			this.$store.commit('SET_CURRENT_PAGE', page);
			this.getItems();
		},
		searchTicket(query) {
			this.$store.commit('SET_SEARCH', query);
			this.getItems();
		},
		updateSelectedItems(ids) {
			this.selectedItems = ids;
		},
		exportExcel() {
			let ajaxurl = window.StackonetSupportTicket.ajaxurl;
			window.location.href = `${ajaxurl}?action=download_support_ticket&ticket_status=${this.status}&ticket_category=${this.category}&ticket_priority=${this.priority}`;
		},
		onActionClick(action, item) {
			if ('view' === action) {
				this.$router.push({name: 'SingleSupportTicket', params: {id: item.id}});
			}
			if ('trash' === action && window.confirm('Are you sure move this item to trash?')) {
				this.trashAction(item, 'trash');
			}
			if ('restore' === action && window.confirm('Are you sure restore this item again?')) {
				this.trashAction(item, 'restore');
			}
			if ('delete' === action && window.confirm('Are you sure to delete permanently?')) {
				this.trashAction(item, 'delete');
			}
		},
		restoreItems() {
			this.onBulkAction('restore', this.selectedItems);
		},
		trashItems() {
			if (this.selectedItems.length) {
				if ('trash' === this.label) {
					this.onBulkAction('delete', this.selectedItems);
				} else {
					this.onBulkAction('trash', this.selectedItems);
				}
			}
		},
		onBulkAction(action, items) {
			if ('trash' === action && confirm('Are you sure to trash all selected items?')) {
				this.batchTrashAction(items, action);
			} else if ('delete' === action && confirm('Are you sure to delete all selected items permanently?')) {
				this.batchTrashAction(items, action);
			} else if ('restore' === action && confirm('Are you sure to restore all selected items?')) {
				this.batchTrashAction(items, action);
			}
		},
		trashAction(item, action) {
			this.$store.commit('SET_LOADING_STATUS', true);
			axios.delete(StackonetSupportTicket.restRoot + '/tickets/' + item.id, {
				params: {
					action: action
				}
			}).then(() => {
				this.getItems();
				this.$store.commit('SET_LOADING_STATUS', false);
			}).catch(() => {
				this.$store.commit('SET_LOADING_STATUS', false);
			});
		},
		batchTrashAction(ids, action) {
			this.$store.commit('SET_LOADING_STATUS', true);
			let data = {};
			data[action] = ids;
			axios.post(StackonetSupportTicket.restRoot + '/tickets/batch', data).then(() => {
				this.getItems();
				this.$store.commit('SET_LOADING_STATUS', false);
				this.selectedItems = [];
			}).catch(error => {
				console.log(error);
				this.$store.commit('SET_LOADING_STATUS', false);
			});
		}
	}
}
</script>

<style lang="scss">
@import "~shapla-color-system/src/variables";

.stackont-support-ticket-container {
	padding-top: 2rem;

	.shapla-data-table__table {
		white-space: normal;
	}
}

.shapla-status-list__item-count {
	padding: 0 5px;
}

.button-restore,
.button-trash,
.button-export {
	&:not(.is-active) {
		cursor: not-allowed;
		opacity: .5;
	}

	&.is-active svg {
		fill: $text-primary;
	}
}

.button--new-ticket-container {
	position: fixed;
	bottom: 1rem;
	right: 1rem;
	z-index: 2;
}
</style>
