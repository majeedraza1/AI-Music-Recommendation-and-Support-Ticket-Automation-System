<template>
    <div class="stackont-support-ticket-container">
        <a href="#" class="button stackonet-primary" @click.prevent="openNewTicket">New Ticket</a>
        <button class="button stackonet-primary" style="float: right;" @click="exportExcel">Export Excel</button>
        <div class="clear"></div>
        <div class="stackonet-support-ticket-icon-search">
            <columns>
                <column></column>
                <column>
                        <span>
                      <svg aria-hidden="true" focusable="false" data-prefix="far" data-icon="archive" role="img"
                           xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"
                           class="svg-inline--fa fa-archive fa-w-16"><path
                              fill="currentColor"
                              d="M464 32H48C21.5 32 0 53.5 0 80v80c0 8.8 7.2 16 16 16h16v272c0 17.7 14.3 32 32 32h384c17.7 0 32-14.3 32-32V176h16c8.8 0 16-7.2 16-16V80c0-26.5-21.5-48-48-48zm-32 400H80V176h352v256zm32-304H48V80h416v48zM204 272h104c6.6 0 12-5.4 12-12v-24c0-6.6-5.4-12-12-12H204c-6.6 0-12 5.4-12 12v24c0 6.6 5.4 12 12 12z"
                              class=""></path></svg>
                    </span>
                    <span>
                        <svg aria-hidden="true" focusable="false" data-prefix="fas" data-icon="user"
                             class="svg-inline--fa fa-user fa-w-14" role="img" xmlns="http://www.w3.org/2000/svg"
                             viewBox="0 0 448 512"><path fill="currentColor"
                                                         d="M224 256c70.7 0 128-57.3 128-128S294.7 0 224 0 96 57.3 96 128s57.3 128 128 128zm89.6 32h-16.7c-22.2 10.2-46.9 16-72.9 16s-50.6-5.8-72.9-16h-16.7C60.2 288 0 348.2 0 422.4V464c0 26.5 21.5 48 48 48h352c26.5 0 48-21.5 48-48v-41.6c0-74.2-60.2-134.4-134.4-134.4z"></path></svg></span>

                    <span>
                        <svg aria-hidden="true" focusable="false" data-prefix="fad" data-icon="tag" role="img"
                             xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"
                             class="svg-inline--fa fa-tag fa-w-16"
                             style="--fa-secondary-opacity:1; --fa-primary-opacity:0.1;"><g class="fa-group"><path
                                fill="currentColor"
                                d="M497.94 225.94L286.06 14.06A48 48 0 0 0 252.12 0H48A48 48 0 0 0 0 48v204.12a48 48 0 0 0 14.06 33.94l211.88 211.88a48 48 0 0 0 67.88 0l204.12-204.12a48 48 0 0 0 0-67.88zM112 160a48 48 0 1 1 48-48 48 48 0 0 1-48 48z"
                                class="fa-secondary"></path><path fill="currentColor" d=""
                                                                  class="fa-primary"></path></g></svg>
                    </span>
                    <span>
           <svg aria-hidden="true" focusable="false" data-prefix="fas" data-icon="trash" role="img"
                xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" class="svg-inline--fa fa-trash fa-w-14"><path
                   fill="currentColor"
                   d="M432 32H312l-9.4-18.7A24 24 0 0 0 281.1 0H166.8a23.72 23.72 0 0 0-21.4 13.3L136 32H16A16 16 0 0 0 0 48v32a16 16 0 0 0 16 16h416a16 16 0 0 0 16-16V48a16 16 0 0 0-16-16zM53.2 467a48 48 0 0 0 47.9 45h245.8a48 48 0 0 0 47.9-45L416 128H32z"
                   class=""></path></svg>
                    </span>
                </column>
                <column>
                    <div class="search-box">
                        <search-form></search-form>
                    </div>
                </column>
            </columns>
        </div>
        <data-table
                action-column="ticket_subject"
                :columns="columns"
                :rows="tickets"
                :total-items="pagination.totalCount"
                :total-pages="pagination.pageCount"
                :per-page="pagination.limit"
                :current-page="pagination.currentPage"
                :actions="actions"
                @action:click="onActionClick"
                @bulk:apply="onBulkAction"
                @pagination="paginate"
                :show-search="false"
                @search="search"
        >
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
    import SearchForm from "../../shapla/shapla-data-table/src/searchForm";
    import {columns, column} from 'shapla-columns'

    export default {
        name: "SupportTicketList",
        components: {SearchForm, Icon, MdlButton, dataTable, Search, columns, column},

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
