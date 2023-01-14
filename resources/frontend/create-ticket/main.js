import {createApp} from 'vue';
import CreateTicket from './CreateTicket'

let el = document.querySelector('#stackonet_support_ticket_form');
if (el) {
    const app = createApp(CreateTicket);
    app.mount(el);
}
