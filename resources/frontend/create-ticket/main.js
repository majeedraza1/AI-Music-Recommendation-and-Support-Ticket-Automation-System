import Vue from 'vue';
import CreateTicket from './CreateTicket'
import axios from "axios";

Vue.config.productionTip = false;

if (window.StackonetSupportTicket.restNonce) {
    axios.defaults.headers.common['X-WP-Nonce'] = window.StackonetSupportTicket.restNonce;
}

axios.defaults.baseURL = window.StackonetSupportTicket.restRoot;

let el = document.querySelector('#stackonet_support_ticket_form');
if (el) {
    new Vue({el: el, render: h => h(CreateTicket)});
}
