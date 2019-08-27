import Vue from 'vue';
import VueRouter from 'vue-router';
import SupportTicketList from './tickets/SupportTicketList';
import SingleSupportTicket from './tickets/SingleSupportTicket';
import NewSupportTicket from './tickets/NewSupportTicket';

Vue.use(VueRouter);

const routes = [
    {path: '/', name: 'SupportTicketList', component: SupportTicketList},
    {path: '/ticket/:id/view', name: 'SingleSupportTicket', component: SingleSupportTicket},
    {path: '/ticket/new', name: 'NewSupportTicket', component: NewSupportTicket},
];

export default new VueRouter({
    routes: routes
});
