import {createApp} from 'vue';
import Login from './Login'

let el = document.querySelector('#stackonet_support_ticket_login');
if (el) {
    const app = createApp(Login);
    app.mount(el);
}
