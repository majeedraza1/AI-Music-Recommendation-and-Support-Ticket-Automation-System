import Vue from 'vue';
import VueRouter from 'vue-router';
import SupportTicketList from './ticket-list/SupportTicketList'
import SingleSupportTicket from "./ticket-list/SingleSupportTicket";
import NewSupportTicket from "./ticket-list/NewSupportTicket";
import Settings from "./ticket-list/Settings";

Vue.use(VueRouter);

const routes = [
    {path: '/', name: 'SupportTicketList', component: SupportTicketList},
    {path: '/ticket/:id/view', name: 'SingleSupportTicket', component: SingleSupportTicket},
    {path: '/ticket/new', name: 'NewSupportTicket', component: NewSupportTicket},
    {path: '/settings', name: 'Settings', component: Settings},
];

export default new VueRouter({
    routes: routes
});
