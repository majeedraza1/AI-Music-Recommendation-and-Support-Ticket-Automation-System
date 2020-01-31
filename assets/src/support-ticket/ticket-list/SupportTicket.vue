<template>
    <div class="Support-ticket-wrapper">

        <side-navigation :active="showSideNav" @close="hideSideNav" nav-width="320px">
            <support-ticket-side-nav/>
        </side-navigation>

        <div class="admin-support-tickets-container">
            <router-view/>
            <spinner :active="loading"/>
            <notification v-model="snackbar"/>
            <confirm-dialog/>
        </div>
    </div>

</template>

<script>
    import {mapState} from 'vuex';
    import notification from 'shapla-notifications';
    import spinner from "shapla-spinner";
    import {ConfirmDialog} from 'shapla-confirm-dialog';
    import sideNavigation from "shapla-side-navigation";
    import SupportTicketSideNav from "./SupportTicketSideNav";

    export default {
        name: "SupportTicket",
        components: {SupportTicketSideNav, notification, spinner, ConfirmDialog, sideNavigation},

        computed: {
            ...mapState(['snackbar', 'loading', 'showSideNav']),
        },

        methods: {
            hideSideNav() {
                this.$store.commit('SET_SHOW_SIDE_NAVE', false);
            }
        }
    }
</script>

<style lang="scss">
    .Support-ticket-wrapper {
        display: flex;
        justify-content: space-between;
        min-height: 80vh;

        svg {
            color: var(--stackonet-ticket-text-icon, rgba(0, 0, 0, 0.38));
            fill: var(--stackonet-ticket-text-icon, rgba(0, 0, 0, 0.38));
        }

        .shapla-sidenav__background,
        .shapla-sidenav__body {
            position: fixed;
            z-index: 9999;
            height: 100vh;

            .admin-bar & {
                top: 32px;
                height: calc(100vh - 32px);
            }
        }
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
        padding: 20px;
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

        .shapla-toggle-panel__heading {
            padding-left: 0;
            padding-right: 0;
        }

        .shapla-toggle-panel__title-text {
            font-size: inherit;
        }

        .shapla-toggle-panel__content {
            padding-left: 0;
            padding-right: 0;

            .support-tickets-side-nav__text {
                margin-bottom: 10px;
                margin-top: 10px;
            }
        }

        &__item {
            display: flex;
            margin-bottom: 1rem;
            margin-top: 1rem;
        }

        &__title {
            cursor: pointer;
            display: flex;
            width: 100%;
        }

        &__icon {
            height: 24px;
            width: 24px;
            margin-right: 10px;
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

    .stackonet-support-ticket-icon-search {
        svg {
            width: 1.5rem;
            height: 1.5rem;
            opacity: 0.6;
            margin: 5px;
        }
    }
</style>
