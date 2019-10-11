import Vue from 'vue';
import SupportTicket from './SupportTicket'
import axios from "axios";
import store from './store'
import router from "./routers";
import {modal} from 'shapla-confirm-modal'

Vue.config.productionTip = false;

Vue.use(modal);

if (window.StackonetToolkit.restNonce) {
    axios.defaults.headers.common['X-WP-Nonce'] = window.StackonetToolkit.restNonce;
}

axios.defaults.baseURL = window.StackonetToolkit.restRoot;

let el = document.querySelector('#stackonet_support_ticket_list');
if (el) {
    new Vue({el, store, router, render: h => h(SupportTicket)});
}
