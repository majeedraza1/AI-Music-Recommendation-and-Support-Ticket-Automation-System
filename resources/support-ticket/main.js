import Vue from 'vue';
import SupportTicket from './ticket-list/SupportTicket'
import axios from "axios";
import store from './store'
import router from "./routers";
import Dialog from 'shapla-confirm-dialog'
import wpMenuFix from "@/support-ticket/admin-menu-fix";

Vue.config.productionTip = false;

Vue.use(Dialog);

if (window.StackonetSupportTicket.restNonce) {
    axios.defaults.headers.common['X-WP-Nonce'] = window.StackonetSupportTicket.restNonce;
}

// axios.defaults.baseURL = window.StackonetSupportTicket.restRoot;

let el = document.querySelector('#stackonet_support_ticket_list');
if (el) {
    wpMenuFix('stackonet-support-ticket');
    document.querySelector('body').classList.add('has-support-ticket');
    new Vue({el, store, router, render: h => h(SupportTicket)});
}
