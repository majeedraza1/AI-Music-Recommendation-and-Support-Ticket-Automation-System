import Vue from 'vue';
import App from './App'
import axios from "axios";

Vue.config.productionTip = false;

if (window.StackonetToolkit.restNonce) {
    axios.defaults.headers.common['X-WP-Nonce'] = window.StackonetToolkit.restNonce;
}

axios.defaults.baseURL = window.StackonetToolkit.restRoot;

let el = document.querySelector('#stackonet_support_ticket_list');
if (el) {
    new Vue({el: el, render: h => h(App)});
}
