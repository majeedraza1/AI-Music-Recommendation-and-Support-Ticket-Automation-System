<template>
    <div class="Support-ticket-wrapper">

        <div class="support-tickets-side-nav">

            <div v-if="!showSideNav" class="support-tickets-side-nav__back-button">
                <mdl-button type="raised" color="primary" @click="backToTicketList">
                    Back
                </mdl-button>
            </div>

            <template v-if="showSideNav">
                <div class="support-tickets-side-nav__item" v-for="(filter, index) in filters"
                     v-if="filter.options.length">
                    <toggle :name="filter.name" :selected="index === 0">
                        <template slot="icon">
                            <svg xmlns="http://www.w3.org/2000/svg">
                                <use xlink:href="#icon-format_list_bulleted"></use>
                            </svg>
                        </template>
                        <span class="support-tickets-side-nav__text" v-for="_option in filter.options"
                              :class="{'is-active':_option.active}" @click="changeFilter(_option.value,filter.id)">
                        <span class="support-tickets-side-nav__label">{{_option.label}}</span>
                        <span class="support-tickets-side-nav__count">{{_option.count}}</span>
                    </span>

                    </toggle>
                </div>

                <div class="support-tickets-side-nav__item">
                    <div class="support-tickets-side-nav__title">
                    <span class="support-tickets-side-nav__icon">
                        <svg xmlns="http://www.w3.org/2000/svg">
                            <use xlink:href="#icon-delete_outline"></use>
                        </svg>
                    </span>
                        <span class="support-tickets-side-nav__text" :class="{'is-active':trashedTickets.active}"
                              @click="getTrashedItems">
                        <span class="support-tickets-side-nav__label">{{trashedTickets.name}}</span>
                        <span class="support-tickets-side-nav__count">{{trashedTickets.count}}</span>
                    </span>
                    </div>
                </div>

                <div class="support-tickets-side-nav__item">
                    <div class="support-tickets-side-nav__title">
                    <span class="support-tickets-side-nav__icon">
                        <svg xmlns="http://www.w3.org/2000/svg">
                            <use xlink:href="#icon-settings"></use>
                        </svg>
                    </span>
                        <span class="support-tickets-side-nav__text" @click="gotToSettings">
                        <span class="support-tickets-side-nav__label">Setting</span>
                    </span>
                    </div>
                </div>
            </template>

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
    import MdlButton from "../../material-design-lite/button/mdlButton";

    export default {
        name: "SupportTicket",
        components: {MdlButton, Toggle, notification, spinner, ConfirmDialog, columns, column},
        data() {
            return {
                isSelected: false,
            }
        },
        computed: {
            ...mapState(['snackbar', 'loading', 'filters', 'trashedTickets', 'label', 'showSideNav',
                'status', 'category', 'priority', 'agent', 'currentPage', 'city', 'search']),
        },
        methods: {
            getTrashedItems() {
                this.$store.commit('SET_STATUS', 0);
                this.$store.commit('SET_CATEGORY', 0);
                this.$store.commit('SET_PRIORITY', 0);
                this.$store.commit('SET_AGENT', 0);
                this.$store.commit('SET_LABEL', 'trash');

                this.$store.dispatch('getTickets');
            },
            changeFilter(option, filter) {
                this.$store.commit('SET_LABEL', 'all');
                if (filter === 'status') {
                    this.$store.commit('SET_STATUS', option);
                }
                if (filter === 'category') {
                    this.$store.commit('SET_CATEGORY', option);
                }
                if (filter === 'priority') {
                    this.$store.commit('SET_PRIORITY', option);
                }
                if (filter === 'agent') {
                    this.$store.commit('SET_AGENT', option);
                }

                this.$store.dispatch('getTickets');
            },
            gotToSettings() {
                this.$router.push({name: 'Settings'});
            },
            backToTicketList() {
                this.$router.push({name: 'SupportTicketList'})
            },
        }
    }
</script>

<style lang="scss">
    @import "../../material-design-lite/shadow/shadow";

    .Support-ticket-wrapper {
        display: flex;
        justify-content: space-between;
        min-height: 80vh;
    }

    .admin-support-tickets-container {
        box-sizing: border-box;
        flex-grow: 1;
        padding: 0 2rem;
        position: relative;


        * {
            box-sizing: border-box;
        }

    }

    .support-tickets-side-nav {
        background-color: #fff;
        padding: 2rem;
        max-width: 350px;
        min-width: 280px;

        &__back-button {
            margin-bottom: 10px;

            button {
                width: 100%;
            }
        }

        .shapla-toggle-panel--boxed-mode {
            margin-bottom: 0;
        }

        .shapla-toggle-panel__content {
            padding-left: 20px;

            .support-tickets-side-nav__text {
                margin-bottom: 10px;
                margin-top: 10px;
            }
        }

        &__item {
            display: flex;
            margin-bottom: 1rem;
        }

        &__title {
            cursor: pointer;
            display: flex;
            width: 100%;
        }

        &__icon {
            height: 24px;
            width: 24px;
        }

        &__text {
            align-items: center;
            color: var(--stackonet-ticket-text-primary, rgba(#000, .85));
            display: flex;
            justify-content: space-between;
            width: 100%;
        }

        &__label {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            width: calc(100% - 1.5em);
            margin-right: 10px;
        }

        &__count {
            font-size: 12px;
            min-width: 1.5em;
            height: 1.5em;
            background: rgba(#000, .1);
            text-align: center;
            justify-content: center;
            align-items: center;
            border-radius: 3px;
        }

        svg {
            height: 24px;
            width: 24px;
            fill: var(--stackonet-ticket-text-icon, rgba(#000, .38));
        }

        &__text.is-active {
            color: var(--stackonet-ticket-primary);

            .support-tickets-side-nav__count {
                background-color: var(--stackonet-ticket-primary);
                color: var(--stackonet-ticket-on-primary);
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
