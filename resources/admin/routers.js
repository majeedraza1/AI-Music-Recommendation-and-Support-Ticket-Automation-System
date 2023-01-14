import {createRouter, createWebHashHistory} from 'vue-router';
import SupportTicketList from './ticket-list/SupportTicketList'
import SingleSupportTicket from "./ticket-list/SingleSupportTicket";
import NewSupportTicket from "./ticket-list/NewSupportTicket";
import Settings from "./ticket-list/Settings";

const routes = [
    {path: '/', name: 'SupportTicketList', component: SupportTicketList},
    {path: '/ticket/:id/view', name: 'SingleSupportTicket', component: SingleSupportTicket},
    {path: '/ticket/new', name: 'NewSupportTicket', component: NewSupportTicket},
    {path: '/settings', name: 'Settings', component: Settings},
];

const router = createRouter({
    history: createWebHashHistory(),
    routes: routes
});

export default router;
