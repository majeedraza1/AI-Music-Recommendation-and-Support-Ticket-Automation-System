import Vuex from 'vuex'
import Vue from 'vue'
import axios from 'axios'

Vue.use(Vuex);

export default new Vuex.Store({
    // Same as Vue data
    state: {
        loading: true,
        snackbar: {},
        pagination: {},
        trashedTickets: {},
        tickets: [],
        filters: [],
        meta_data: [],
        categories: [],
        priorities: [],
        statuses: [],
        agents: [],
        status: '',
        category: '',
        priority: '',
        city: '',
        agent: '',
        search: '',
        currentPage: 1,
    },

    // Commit + track state changes
    mutations: {
        SET_LOADING_STATUS(state, loading) {
            state.loading = loading;
        },
        SET_SNACKBAR(state, snackbar) {
            state.snackbar = snackbar;
        },
        SET_PAGINATION(state, pagination) {
            state.pagination = pagination;
        },
        SET_TICKETS(state, tickets) {
            state.tickets = tickets;
        },
        SET_FILTERS(state, filters) {
            state.filters = filters;
        },
        SET_META_DATA(state, meta_data) {
            state.meta_data = meta_data;
        },
        SET_CATEGORIES(state, categories) {
            state.categories = categories;
        },
        SET_PRIORITIES(state, priorities) {
            state.priorities = priorities;
        },
        SET_STATUSES(state, statuses) {
            state.statuses = statuses;
        },
        SET_AGENTS(state, agents) {
            state.agents = agents;
        },
        SET_STATUS(state, status) {
            state.status = status;
        },
        SET_CATEGORY(state, category) {
            state.category = category;
        },
        SET_PRIORITY(state, priority) {
            state.priority = priority;
        },
        SET_CITY(state, city) {
            state.city = city;
        },
        SET_AGENT(state, agent) {
            state.agent = agent;
        },
        SET_SEARCH(state, search) {
            state.search = search;
        },
        SET_TRASHED_TICKETS(state, trashedTickets) {
            state.trashedTickets = trashedTickets;
        },
        SET_CURRENT_PAGE(state, currentPage) {
            state.currentPage = currentPage;
        },
    },

    // Same as Vue methods
    actions: {
        getTickets({commit}, params) {
            commit('SET_LOADING_STATUS', true);
            axios.get('tickets', {params: params}).then(response => {
                let data = response.data.data, filters = data.filters;
                commit('SET_LOADING_STATUS', false);
                commit('SET_TICKETS', data.items);
                commit('SET_PAGINATION', data.pagination);
                commit('SET_META_DATA', data.meta_data);
                commit('SET_TRASHED_TICKETS', data.trash);
                commit('SET_FILTERS', filters);
            }).catch(error => {
                console.log(error);
                commit('SET_LOADING_STATUS', false);
            });
        },
        getCategories({commit}) {
            commit('SET_LOADING_STATUS', true);
            axios.get('categories').then(response => {
                commit('SET_LOADING_STATUS', false);
                commit('SET_CATEGORIES', response.data.data.items);
            }).catch(error => {
                console.log(error);
                commit('SET_LOADING_STATUS', false);
            });
        },
        getPriorities({commit}) {
            commit('SET_LOADING_STATUS', true);
            axios.get('priorities').then(response => {
                commit('SET_LOADING_STATUS', false);
                commit('SET_PRIORITIES', response.data.data.items);
            }).catch(error => {
                console.log(error);
                commit('SET_LOADING_STATUS', false);
            });
        },
        getStatuses({commit}) {
            commit('SET_LOADING_STATUS', true);
            axios.get('statuses').then(response => {
                commit('SET_LOADING_STATUS', false);
                commit('SET_STATUSES', response.data.data.items);
            }).catch(error => {
                console.log(error);
                commit('SET_LOADING_STATUS', false);
            });
        },
        getAgents({commit}) {
            commit('SET_LOADING_STATUS', true);
            axios.get('agents').then(response => {
                commit('SET_LOADING_STATUS', false);
                commit('SET_AGENTS', response.data.data.items);
            }).catch(error => {
                console.log(error);
                commit('SET_LOADING_STATUS', false);
            });
        },
    },

    // Save as Vue computed property
    getters: {
        display_name() {
            return window.StackonetToolkit.display_name;
        },
        user_email() {
            return window.StackonetToolkit.user_email;
        }
    },
});
