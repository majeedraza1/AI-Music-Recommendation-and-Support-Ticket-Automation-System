import Vue from 'vue';
import Login from './Login'
import {modal} from 'shapla-confirm-modal'
import axios from "axios";

Vue.config.productionTip = false;

Vue.use(modal);

if (window.StackonetToolkit.restNonce) {
    axios.defaults.headers.common['X-WP-Nonce'] = window.SupportTickets.restNonce;
}

axios.defaults.baseURL = window.StackonetToolkit.restRoot;

let el = document.querySelector('#stackonet_support_ticket_login');
if (el) {
    new Vue({el: el, render: h => h(Login)});
}
