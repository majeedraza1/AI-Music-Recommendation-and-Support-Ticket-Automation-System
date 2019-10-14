<template>
    <div class="Support-ticket-wrapper">
        <div class="support-tickets-left-content">
            <div class="support-ticket-toggle" v-for="(filter, index) in filters" v-if="filter.options.length">
                <toggle :name="filter.name" :selected="index === 0">
                    <template slot="icon">
                        <svg xmlns="http://www.w3.org/2000/svg">
                            <use xlink:href="#icon-format_list_bulleted"></use>
                        </svg>
                    </template>
                    <ul class="dropdown-content">
                        <li v-for="_option in filter.options" :class="{'is-active':_option.active}"
                            @click="changeFilter(_option,filter)">
                            <span>{{_option.label}}</span>
                            <span v-if="_option.count !== 'undefine'">({{_option.count}})</span>
                        </li>
                    </ul>
                </toggle>
            </div>
            <div class="support-ticket-toggle">
                <toggle name="Setting" :selected="isSelected">
                    <template slot="icon">
                        <svg xmlns="http://www.w3.org/2000/svg">
                            <use xlink:href="#icon-fa_cog"></use>
                        </svg>
                    </template>
                </toggle>
            </div>
        </div>
        <div class="admin-support-tickets-container">
            <router-view></router-view>
            <spinner :active="loading"></spinner>
            <notification v-model="snackbar"></notification>
            <confirm-dialog></confirm-dialog>
        </div>
    </div>

</template>

<script>
    import {mapState} from 'vuex';
    import {columns, column} from 'shapla-columns'
    import notification from 'shapla-notifications';
    import spinner from "shapla-spinner";
    import {ConfirmDialog} from 'shapla-confirm-modal/src';
    import Toggle from "../../components/Toggle";

    export default {
        name: "SupportTicket",
        components: {Toggle, notification, spinner, ConfirmDialog, columns, column},
        data() {
            return {
                isSelected: false,
            }
        },
        computed: {
            ...mapState(['snackbar', 'loading', 'filters',
                'status', 'category', 'priority', 'currentPage', 'city', 'search']),
        },
        methods: {
            changeFilter(option, filter) {
                if (filter.id === 'status') {
                    this.$store.commit('SET_STATUS', option.value);
                }

                this.$store.dispatch('getTickets', {
                    ticket_status: this.status,
                    ticket_category: this.category,
                    ticket_priority: this.priority,
                    paged: this.currentPage,
                    city: this.city,
                    search: this.search
                });
            }
        }
    }
</script>

<style lang="scss">
    @import "../../material-design-lite/shadow/shadow";

    .Support-ticket-wrapper {
        display: flex;
        justify-content: space-between;

    }

    .admin-support-tickets-container {
        position: relative;
        box-sizing: border-box;
        width: calc(100% - 350px);
        padding: 0 4rem;


        * {
            box-sizing: border-box;
        }

    }

    .support-tickets-left-content {
        background-color: #fff;
        padding: 2rem;
        max-width: 350px;

    }

    .support-ticket-toggle {
        display: flex;

        .dropdown-content {
            min-width: 160px;
            list-style: none;
            margin: 0 !important;

            li {
                line-height: 1.8em;
                color: #000;
                font-size: 16px;
                font-weight: 300;

                &.is-active {
                    color: var(--stackonet-ticket-primary);
                }
            }
        }

    }

    .shapla-search-form__input {
        box-shadow: 0 4px 6px rgba(50, 50, 93, .11), 0 1px 3px rgba(0, 0, 0, .08) !important;
        border-radius: 25px !important;
        border: none !important;
    }

    .stackonet-support-ticket-icon-search {
        svg {
            width: 1.5rem;
            height: 1.5rem;
            opacity: 0.6;
            margin: 5px;
        }
    }
</style>
