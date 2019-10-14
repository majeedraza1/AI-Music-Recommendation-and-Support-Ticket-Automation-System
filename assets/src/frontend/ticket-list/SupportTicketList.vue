<template>
    <div class="stackont-support-ticket-container">
        <div class="button--new-ticket-container">
            <mdl-fab @click="openNewTicket">+</mdl-fab>
        </div>
        <div class="clear"></div>
        <div class="stackonet-support-ticket-icon-search">
            <columns>
                <column>
                    <h2>Unassigned</h2>
                </column>
                <column>
                    <div class="flex justify-center">
                        <div>
                            <svg xmlns="http://www.w3.org/2000/svg">
                                <title>Archive</title>
                                <use xlink:href="#icon-work_outline"></use>
                            </svg>
                        </div>

                        <div>
                            <svg xmlns="http://www.w3.org/2000/svg">
                                <title>Agents</title>
                                <use xlink:href="#icon-person_outline"></use>
                            </svg>
                        </div>

                        <div>
                            <svg xmlns="http://www.w3.org/2000/svg">
                                <use xlink:href="#icon-fa_tag"></use>
                            </svg>
                        </div>
                        <div @click="exportExcel">
                            <svg xmlns="http://www.w3.org/2000/svg">
                                <title>Import Export</title>
                                <use xlink:href="#icon-import_export"></use>
                            </svg>
                        </div>
                        <div>
                            <svg xmlns="http://www.w3.org/2000/svg">
                                <title>Trash</title>
                                <use xlink:href="#icon-delete_outline"></use>
                            </svg>
                        </div>
                    </div>
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
    import MdlFab from "../../material-design-lite/button/mdlFab";

    export default {
        name: "SupportTicketList",
        components: {MdlFab, SearchForm, Icon, MdlButton, dataTable, Search, columns, column},

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

<style lang="scss">
    .stackont-support-ticket-container {
        padding-top: 2rem;

        .shapla-search-form__input {
            padding-left: 2.5em;
        }

        .shapla-search-form__submit {
            right: auto;
            left: 3px;
            top: 5px;
        }
    }

    .button--new-ticket-container {
        position: fixed;
        bottom: 1rem;
        right: 1rem;
        z-index: 2;
    }
</style>
