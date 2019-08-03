import Vue from 'vue';
import axios from 'axios';
import App from './App.vue'
import router from './routers.js';
import store from './store.js';
import wpMenuFix from "./utils/admin-menu-fix.js";

if (window.StackonetToolkit.restNonce) {
	axios.defaults.headers.common['X-WP-Nonce'] = window.StackonetToolkit.restNonce;
}

let el = document.querySelector('#stackonet-toolkit-admin');
if (el) {
	new Vue({el, store, router, render: h => h(App)});
}

// fix the admin menu for the slug "stackonet-toolkit"
wpMenuFix('stackonet-toolkit');
