import Vue from 'vue';
import VueRouter from 'vue-router';
import SupportTicketList from './tickets/SupportTicketList';
import SingleSupportTicket from './tickets/SingleSupportTicket';
import NewSupportTicket from './tickets/NewSupportTicket';
import AgentsList from "./agents/AgentsList";
import TicketCategories from "./categories/TicketCategories";
import TicketStatuses from "./statuses/TicketStatuses";
import TicketPriorities from "./priorities/TicketPriorities";

Vue.use(VueRouter);

const routes = [
    {path: '/', name: 'SupportTicketList', component: SupportTicketList},
    {path: '/ticket/:id/view', name: 'SingleSupportTicket', component: SingleSupportTicket},
    {path: '/ticket/new', name: 'NewSupportTicket', component: NewSupportTicket},
    {path: '/agents', name: 'AgentsList', component: AgentsList},
    {path: '/categories', name: 'TicketCategories', component: TicketCategories},
    {path: '/statuses', name: 'TicketStatuses', component: TicketStatuses},
    {path: '/priorities', name: 'TicketPriorities', component: TicketPriorities},
];

export default new VueRouter({
    routes: routes
});
