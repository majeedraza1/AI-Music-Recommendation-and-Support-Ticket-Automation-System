<template>
  <div class="wrap Support-ticket-wrapper">

    <shapla-sidenav :active="showSideNav" @close="hideSideNav" nav-width="320px">
      <support-ticket-side-nav/>
    </shapla-sidenav>

    <div class="admin-support-tickets-container">
      <router-view/>
      <shapla-spinner :active="loading"/>
      <shapla-notification-container v-model="snackbar"/>
      <shapla-confirm-container/>
    </div>
  </div>

</template>

<script>
import {useStore} from "vuex";
import {computed} from "vue";
import {
  ShaplaConfirmContainer,
  ShaplaNotificationContainer,
  ShaplaSidenav,
  ShaplaSpinner
} from '@shapla/vue-components';
import SupportTicketSideNav from "./SupportTicketSideNav";

export default {
  name: "SupportTicket",
  components: {
    SupportTicketSideNav,
    ShaplaNotificationContainer,
    ShaplaSpinner,
    ShaplaConfirmContainer,
    ShaplaSidenav
  },
  setup() {
    const store = useStore();

    return {
      snackbar: computed(() => store.state.snackbar),
      loading: computed(() => store.state.loading),
      showSideNav: computed(() => store.state.showSideNav),
      hideSideNav: () => store.commit('SET_SHOW_SIDE_NAVE', false),
    }
  }
}
</script>

<style lang="scss">
.Support-ticket-wrapper {
  display: flex;
  justify-content: space-between;
  min-height: 80vh;

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
