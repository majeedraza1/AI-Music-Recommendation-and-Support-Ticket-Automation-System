import Vue from 'vue';
import router from './routers.js';
import store from './store.js';
import menuFix from "./utils/admin-menu-fix.js";
import SupportTicket from './tickets/SupportTicket.vue'
import axios from "axios";

if (window.SupportTickets.nonce) {
    axios.defaults.headers.common['X-WP-Nonce'] = window.SupportTickets.nonce;
}

axios.defaults.baseURL = window.SupportTickets.root;

let el = document.querySelector('#stackonet-support-tickets-admin');
if (el) {
    new Vue({el: el, store: store, router: router, render: h => h(SupportTicket)});
}

// fix the admin menu for the slug "stackonet-support-ticket"
menuFix('stackonet-support-ticket');
