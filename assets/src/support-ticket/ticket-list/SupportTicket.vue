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
    @import "~shapla-color-system/src/variables";

    .Support-ticket-wrapper {
        display: flex;
        justify-content: space-between;
        min-height: 80vh;

        svg {
            fill: $text-icon;
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

    .stackonet-support-ticket-icon-search {
        svg {
            width: 1.5rem;
            height: 1.5rem;
            opacity: 0.6;
            margin: 5px;
        }
    }
</style>
