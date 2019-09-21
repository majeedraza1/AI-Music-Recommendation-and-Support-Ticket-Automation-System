import Vue from 'vue';
import VueRouter from 'vue-router';
import SupportTicketList from './tickets/SupportTicketList';
import SingleSupportTicket from './tickets/SingleSupportTicket';
import NewSupportTicket from './tickets/NewSupportTicket';
import AgentsList from "./agents/AgentsList";

Vue.use(VueRouter);

const routes = [
    {path: '/', name: 'SupportTicketList', component: SupportTicketList},
    {path: '/ticket/:id/view', name: 'SingleSupportTicket', component: SingleSupportTicket},
    {path: '/ticket/new', name: 'NewSupportTicket', component: NewSupportTicket},
    {path: '/agents', name: 'AgentsList', component: AgentsList},
];

export default new VueRouter({
    routes: routes
});
