import Vue from 'vue';
import SupportTicket from './ticket-list/SupportTicket'
import axios from "axios";
import store from './store'
import router from "./routers";
import {modal} from 'shapla-confirm-modal'

Vue.config.productionTip = false;

Vue.use(modal);

if (window.StackonetSupportTicket.restNonce) {
    axios.defaults.headers.common['X-WP-Nonce'] = window.StackonetSupportTicket.restNonce;
}

axios.defaults.baseURL = window.StackonetSupportTicket.restRoot;

let el = document.querySelector('#stackonet_support_ticket_list');
if (el) {
    document.querySelector('body').classList.add('has-support-ticket');
    new Vue({el, store, router, render: h => h(SupportTicket)});
}
