<template>
    <div class="admin-support-tickets-container">
        <router-view></router-view>
        <spinner :active="loading"></spinner>
        <notification v-model="snackbar"></notification>
        <confirm-dialog></confirm-dialog>
    </div>
</template>

<script>
    import {mapState} from 'vuex';
    import notification from 'shapla-notifications';
    import spinner from "shapla-spinner";
    import {ConfirmDialog} from 'shapla-confirm-modal/src';

    export default {
        name: "SupportTicket",
        components: {notification, spinner, ConfirmDialog},
        computed: {
            ...mapState(['snackbar', 'loading']),
        }
    }
</script>

<style lang="scss">
    @import "../../material-design-lite/shadow/shadow";

    .admin-support-tickets-container {
        position: relative;
        box-sizing: border-box;

        * {
            box-sizing: border-box;
        }
    }
</style>
