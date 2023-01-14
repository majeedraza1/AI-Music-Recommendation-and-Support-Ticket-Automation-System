import {createApp} from 'vue';
import SupportTicket from './ticket-list/SupportTicket'
import store from './store'
import router from "./routers";
import wpMenuFix from "@/admin/admin-menu-fix";

let el = document.querySelector('#stackonet_support_ticket_list');
if (el) {
    wpMenuFix('stackonet-support-ticket');
    document.querySelector('body').classList.add('has-support-ticket');
    const app = createApp(SupportTicket)
    app.use(store)
    app.use(router)
    app.mount(el)
}
