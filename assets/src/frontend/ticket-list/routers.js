import Vue from 'vue';
import VueRouter from 'vue-router';
import SupportTicketList from './SupportTicketList'
import SingleSupportTicket from "./SingleSupportTicket";
import NewSupportTicket from "./NewSupportTicket";

Vue.use(VueRouter);

const routes = [
    {path: '/', name: 'SupportTicketList', component: SupportTicketList},
    {path: '/ticket/:id/view', name: 'SingleSupportTicket', component: SingleSupportTicket},
    {path: '/ticket/new', name: 'NewSupportTicket', component: NewSupportTicket},
];

export default new VueRouter({
    routes: routes
});
