import Vue from 'vue';
import router from './routers.js';
import store from './store.js';
import menuFix from "./utils/admin-menu-fix.js";
import SupportTicket from './tickets/SupportTicket.vue'
import axios from "axios";

if (window.SupportTickets.nonce) {
    axios.defaults.headers.common['X-WP-Nonce'] = window.SupportTickets.nonce;
}

let el = document.querySelector('#admin-stackonet-support-tickets');
if (el) {
    new Vue({el: el, store: store, router: router, render: h => h(SupportTicket)});
}

// fix the admin menu for the slug "wpsc-tickets"
menuFix('wpsc-tickets');
