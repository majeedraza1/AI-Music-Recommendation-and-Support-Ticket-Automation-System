<template>
	<div class="stackont-support-ticket-container">
		<h1 class="wp-heading-inline">Support</h1>
		<a href="#" class="page-title-action" @click.prevent="openNewTicket">New Ticket</a>
		<button class="button button-primary" style="float: right;" @click="exportExcel">Export Excel</button>
		<div class="clear"></div>

		<wp-list-table
			action-column="ticket_subject"
			:columns="columns"
			:rows="items"
			:total-items="pagination.totalCount"
			:total-pages="pagination.pageCount"
			:per-page="pagination.limit"
			:current-page="pagination.currentPage"
			:actions="actions"
			:bulk-actions="bulkActions"
			@action:click="onActionClick"
			@bulk:apply="onBulkAction"
			@pagination="paginate"
			:show-search="true"
			@search="search"
		>
			<p slot="search" class="search-box">
				<search :dropdown-items="dropdownCategories" @search="categorySearch"></search>
			</p>
			<template slot="created_by" slot-scope="data" class="button--status">
				<span v-html="getAssignedAgents(data.row.assigned_agents)"></span>
			</template>
			<span slot="ticket_status" slot-scope="data" class="button--status" :class="data.row.status.slug">
				{{data.row.status.name}}
			</span>
			<span slot="ticket_category" slot-scope="data" class="button--category" :class="data.row.category.slug">
				{{data.row.category.name}}
			</span>
			<span slot="ticket_priority" slot-scope="data" class="button--priority" :class="data.row.priority.slug">
				{{data.row.priority.name}}
			</span>
			<template slot="filters">
				<label for="filter-address" class="screen-reader-text">Filter by status</label>
				<select id="filter-address" v-model="status" @change="changeStatus">
					<option :value="_status.key" v-for="_status in statuses">
						{{_status.label}} ({{_status.count}})
					</option>
				</select>
				<label for="filter-category" class="screen-reader-text">Filter by Category</label>
				<select id="filter-category" v-model="category" @change="changeStatus">
					<option :value="_status.key" v-for="_status in default_categories">
						{{_status.label}}
					</option>
				</select>
				<label for="filter-priority" class="screen-reader-text">Filter by Priority</label>
				<select id="filter-priority" v-model="priority" @change="changeStatus">
					<option :value="_status.key" v-for="_status in default_priorities">
						{{_status.label}}
					</option>
				</select>
				<label for="filter-city" class="screen-reader-text">Filter by Priority</label>
				<select id="filter-city" v-model="city" @change="changeStatus">
					<option value="all">All Cities</option>
					<option :value="_city" v-for="_city in cities">{{_city}}</option>
				</select>
				<button class="button" @click="clearFilter">Clear Filter</button>
			</template>
		</wp-list-table>
	</div>
</template>

<script>
	import axios from 'axios';
	import WpListTable from "../../wp/wpListTable";
	import MdlButton from "../../material-design-lite/button/mdlButton";
	import Icon from "../../shapla/icon/icon";
	import Search from "../../shapla/search/Search";

	export default {
		name: "SupportTicketList",
		components: {Icon, MdlButton, WpListTable, Search},
		data() {
			return {
				loading: false,
				default_statuses: [],
				default_categories: [],
				default_priorities: [],
				search_categories: [],
				cities: [],
				columns: [
					{key: 'ticket_subject', label: 'Subject', numeric: false},
					{key: 'id', label: 'Ticket ID', numeric: true},
					{key: 'ticket_status', label: 'Status', numeric: false},
					{key: 'customer_name', label: 'Name', numeric: false},
					{key: 'customer_email', label: 'Email Address', numeric: false},
					{key: 'customer_phone', label: 'Phone', numeric: false},
					{key: 'created_by', label: 'Assigned Agent', numeric: false},
					{key: 'ticket_category', label: 'Category', numeric: false},
					{key: 'ticket_priority', label: 'Priority', numeric: false},
					{key: 'updated_human_time', label: 'Updated', numeric: false},
				],
				items: [],
				counts: [],
				pagination: {},
				currentPage: 1,
				count_trash: 0,
				status: 'all',
				category: 'all',
				priority: 'all',
				city: 'all',
				query: '',
			}
		},
		mounted() {
			this.$store.commit('SET_LOADING_STATUS', false);
			if (!this.items.length) {
				this.getItems();
			}
			this.default_statuses = SupportTickets.statuses;
			this.default_categories = SupportTickets.categories;
			this.default_priorities = SupportTickets.priorities;
			this.count_trash = SupportTickets.count_trash;
			this.cities = SupportTickets.cities;
			this.search_categories = SupportTickets.search_categories;
		},
		computed: {
			dropdownCategories() {
				let _categories = [{label: 'All', value: 'all'}], self = this;
				self.default_categories.forEach(function (element) {
					if (-1 !== self.search_categories.indexOf(element.key)) {
						_categories.push({label: element.label, value: element.key,})
					}
				});

				return _categories;
			},
			statuses() {
				let _status = [], self = this;
				self.default_statuses.forEach(status => {
					status.count = self.counts[status.key];
					_status.push(status);
				});

				return _status;
			},
			actions() {
				if ('trash' === this.status) {
					return [{key: 'restore', label: 'Restore'}, {key: 'delete', label: 'Delete Permanently'}];
				}

				return [
					{key: 'view', label: 'View'},
					{key: 'trash', label: 'Trash'}
				];
			},
			bulkActions() {
				if ('trash' === this.status) {
					return [{key: 'restore', label: 'Restore'}, {key: 'delete', label: 'Delete Permanently'}];
				} else {
					return [{key: 'trash', label: 'Move to Trash'}];
				}
			},
		},
		methods: {
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
				this.status = 'all';
				this.category = 'all';
				this.priority = 'all';
				this.city = 'all';
				this.getItems();
			},
			changeStatus() {
				this.currentPage = 1;
				this.getItems();
			},
			paginate(page) {
				this.currentPage = page;
				this.getItems();
			},
			categorySearch(data) {
				let self = this;
				self.$store.commit('SET_LOADING_STATUS', true);
				let parms = `ticket_status=${self.status}&ticket_category=${data.cat}&ticket_priority=${self.priority}&paged=${self.currentPage}&city=${self.city}&search=${data.query}`;
				axios
					.get(stackonetSettings.root + `/support-ticket?${parms}`)
					.then((response) => {
						self.$store.commit('SET_LOADING_STATUS', false);
						let data = response.data.data;
						self.items = data.items;
						self.counts = data.counts;
						self.pagination = data.pagination;
					})
					.catch((error) => {
						console.log(error);
					});
			},
			search(query) {
				this.query = query;
				this.getItems();
			},
			exportExcel() {

				let url = `${ajaxurl}?action=download_support_ticket&ticket_status=${this.status}&ticket_category=${this.category}&ticket_priority=${this.priority}`;
				window.location.href = url;
			},
			getItems() {
				let self = this;
				self.$store.commit('SET_LOADING_STATUS', true);
				let parms = `ticket_status=${self.status}&ticket_category=${self.category}&ticket_priority=${self.priority}&paged=${self.currentPage}&city=${self.city}&search=${self.query}`;
				axios
					.get(stackonetSettings.root + `/support-ticket?${parms}`)
					.then((response) => {
						self.$store.commit('SET_LOADING_STATUS', false);
						let data = response.data.data;
						self.items = data.items;
						self.counts = data.counts;
						self.pagination = data.pagination;
					})
					.catch((error) => {
						console.log(error);
					});
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
			trashAction(item, action) {
				let self = this;
				self.$store.commit('SET_LOADING_STATUS', true);
				axios
					.post(stackonetSettings.root + '/support-ticket/delete', {
						id: item.id,
						action: action
					})
					.then((response) => {
						self.getItems();
						self.$store.commit('SET_LOADING_STATUS', false);
					})
					.catch((error) => {
						self.$store.commit('SET_LOADING_STATUS', false);
					});
			},
			onBulkAction(action, items) {
				if ('trash' === action) {
					if (confirm('Are you sure to trash all selected items?')) {
						this.batchTrashAction(items, action);
					}
				} else if ('delete' === action) {
					if (confirm('Are you sure to delete all selected items permanently?')) {
						this.batchTrashAction(items, action);
					}
				} else if ('restore' === action) {
					if (confirm('Are you sure to restore all selected items?')) {
						this.batchTrashAction(items, action);
					}
				}
			},
			batchTrashAction(ids, action) {
				let self = this;
				self.$store.commit('SET_LOADING_STATUS', true);
				axios
					.post(stackonetSettings.root + '/support-ticket/batch_delete', {
						ids: ids,
						action: action
					})
					.then((response) => {
						self.getItems();
						self.$store.commit('SET_LOADING_STATUS', false);
					})
					.catch((error) => {
						console.log(error);
						self.$store.commit('SET_LOADING_STATUS', false);
					});
			}
		}
	}
</script>

<style scoped>

</style>
