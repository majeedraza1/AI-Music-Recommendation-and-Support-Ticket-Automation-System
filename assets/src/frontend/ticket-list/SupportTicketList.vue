<template>
    <div class="stackont-support-ticket-container">
        <a href="#" class="button stackonet-primary" @click.prevent="openNewTicket">New Ticket</a>
        <button class="button stackonet-primary" style="float: right;" @click="exportExcel">Export Excel</button>
        <div class="clear"></div>

        <data-table
                action-column="ticket_subject"
                :columns="columns"
                :rows="tickets"
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
                <search :dropdown-items="[]" @search="categorySearch"></search>
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
            <template slot="filters" v-if="filters.length">
                <template v-for="_filter in filters" v-if="_filter.options.length">
                    <label :for="`filter-${_filter.id}`" class="screen-reader-text">
                        Filter by {{_filter.singular_name}}
                    </label>
                    <select :id="`filter-${_filter.id}`" v-model="_data[_filter.id]" @change="changeStatus">
                        <option value="all">All {{_filter.name}}</option>
                        <option :value="_status.value" v-for="_status in _filter.options">
                            {{_status.label}}
                        </option>
                    </select>
                </template>
                <button class="button" @click="clearFilter">Clear Filter</button>
            </template>
        </data-table>
    </div>
</template>

<script>
    import axios from 'axios';
    import {mapState} from 'vuex'
    import dataTable from "shapla-data-table";
    import Icon from "icon/icon";
    import Search from "search/Search";
    import MdlButton from "../../material-design-lite/button/mdlButton";

    export default {
        name: "SupportTicketList",
        components: {Icon, MdlButton, dataTable, Search},
        data() {
            return {
                loading: false,
                search_categories: [],
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
            if (!this.tickets.length) {
                this.getItems();
            }
        },
        computed: {
            ...mapState(['pagination', 'tickets', 'filters', 'meta_data']),
            actions() {
                return this.meta_data.actions;
            },
            bulkActions() {
                return this.meta_data.bulkActions;
            },
        },
        methods: {
            getItems() {
                this.$store.dispatch('getTickets', {
                    ticket_status: this.status,
                    ticket_category: this.category,
                    ticket_priority: this.priority,
                    paged: this.currentPage,
                    city: this.city,
                    search: this.query
                });
            },
            categorySearch(data) {
                this.category = data.cat;
                this.query = data.query;
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
            search(query) {
                this.query = query;
                this.getItems();
            },
            exportExcel() {
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
                axios.post('support-ticket/delete', {id: item.id, action: action}).then(() => {
                    this.getItems();
                    this.$store.commit('SET_LOADING_STATUS', false);
                }).catch(() => {
                    this.$store.commit('SET_LOADING_STATUS', false);
                });
            },
            batchTrashAction(ids, action) {
                this.$store.commit('SET_LOADING_STATUS', true);
                axios.post('support-ticket/batch_delete', {ids: ids, action: action}).then(() => {
                    this.getItems();
                    this.$store.commit('SET_LOADING_STATUS', false);
                }).catch(error => {
                    console.log(error);
                    this.$store.commit('SET_LOADING_STATUS', false);
                });
            }
        }
    }
</script>
