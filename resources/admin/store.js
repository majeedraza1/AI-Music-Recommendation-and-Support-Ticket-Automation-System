import {createStore} from 'vuex'
import http from "@/admin/axios";
import {Dialog, Notify} from '@shapla/vue-components'

export default createStore({
    // Same as Vue data
    state: {
        loading: true,
        showSideNav: false,
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
        roles: [],
        status: 0,
        category: 0,
        priority: 0,
        agent: 0,
        label: 'all',
        city: '',
        search: '',
        currentPage: 1,
        labels: [],
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
        SET_LABEL(state, label) {
            state.label = label;
        },
        SET_CITY(state, city) {
            state.city = city;
        },
        SET_AGENT(state, agent) {
            state.agent = agent;
        },
        SET_ROLES(state, roles) {
            state.roles = roles;
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
        SET_SHOW_SIDE_NAVE(state, showSideNav) {
            state.showSideNav = showSideNav;
        },
        SET_TICKETS_LABELS(state, labels) {
            state.labels = labels;
        },
    },

    // Same as Vue methods
    actions: {
        getTickets({commit, state}) {
            commit('SET_LOADING_STATUS', true);
            http.get('tickets', {
                params: {
                    ticket_status: state.status,
                    ticket_category: state.category,
                    ticket_priority: state.priority,
                    agent: state.agent,
                    page: state.currentPage,
                    label: state.label,
                    city: state.city,
                    search: state.search,
                    per_page: 50,
                }
            }).then(response => {
                let data = response.data.data,
                    filters = data.filters;
                commit('SET_LOADING_STATUS', false);
                commit('SET_TICKETS', data.items);
                commit('SET_PAGINATION', data.pagination);
                commit('SET_META_DATA', data.meta_data);
                commit('SET_TRASHED_TICKETS', data.trash);
                commit('SET_TICKETS_LABELS', data.statuses);
                commit('SET_FILTERS', filters);
            }).catch(error => {
                console.log(error);
                commit('SET_LOADING_STATUS', false);
            });
        },

        // Categories
        getCategories({commit}, params = {}) {
            return new Promise(resolve => {
                commit('SET_LOADING_STATUS', true);
                http.get('categories', {params: params}).then(response => {
                    commit('SET_LOADING_STATUS', false);
                    commit('SET_CATEGORIES', response.data.data.items);
                    resolve(response.data.data.items);
                }).catch(error => {
                    console.log(error);
                    commit('SET_LOADING_STATUS', false);
                });
            })
        },
        createCategory({commit, dispatch}, categoryName) {
            return new Promise((resolve) => {
                commit('SET_LOADING_STATUS', true);
                http.post('categories', {name: categoryName}).then((response) => {
                    dispatch('getCategories');
                    resolve(response.data.data);
                }).catch(error => {
                    Notify.error(error.response.data.message);
                }).finally(() => {
                    commit('SET_LOADING_STATUS', false);
                })
            });
        },
        updateCategory({commit, dispatch}, categoryObject) {
            commit('SET_LOADING_STATUS', true);
            return new Promise((resolve) => {
                http.post('categories/' + categoryObject.term_id, {
                    name: categoryObject.name,
                    slug: categoryObject.slug,
                }).then(response => {
                    dispatch('getCategories');
                    resolve(response.data.data);
                }).catch(error => {
                    Notify.error(error.response.data.message);
                }).finally(() => {
                    commit('SET_LOADING_STATUS', false);
                })
            })
        },
        deleteCategory({commit, dispatch}, id) {
            Dialog.confirm('Are you sure to delete this category').then((confirmed) => {
                if (confirmed) {
                    commit('SET_LOADING_STATUS', true);
                    http.delete('categories/' + id).then(() => {
                        Notify.success('Category has been deleted?', 'Success!');
                        dispatch('getCategories');
                    }).catch(error => {
                        Notify.error(error.response.data.message);
                    }).finally(() => {
                        commit('SET_LOADING_STATUS', false);
                    })
                }
            })
        },
        updateCategoryMenuOrder({commit}, categories) {
            let menu_orders = categories.map(el => el.term_id);
            http.post('categories/batch', {menu_orders: menu_orders}).then(() => {
                Notify.success('Category orders have been updated.', 'Success!');
            }).catch(error => {
                Notify.error(error.response.data);
            })
        },

        // Priorities
        getPriorities({commit}) {
            return new Promise(resolve => {
                commit('SET_LOADING_STATUS', true);
                http.get('priorities').then(response => {
                    commit('SET_LOADING_STATUS', false);
                    commit('SET_PRIORITIES', response.data.data.items);
                    resolve(response.data.data.items);
                }).catch(error => {
                    console.log(error);
                    commit('SET_LOADING_STATUS', false);
                });
            })
        },
        createPriority({commit, dispatch}, priorityName) {
            return new Promise((resolve) => {
                commit('SET_LOADING_STATUS', true);
                http.post('priorities', {name: priorityName}).then((response) => {
                    dispatch('getPriorities');
                    resolve(response.data.data);
                }).catch(error => {
                    Notify.error(error.response.data.message, 'Error!');
                }).finally(() => {
                    commit('SET_LOADING_STATUS', false);
                })
            })
        },
        updatePriority({commit, dispatch}, priorityObject = {term_id: 0, name: '', slug: ''}) {
            commit('SET_LOADING_STATUS', true);
            http.post('priorities/' + priorityObject.term_id, {
                name: priorityObject.name,
                slug: priorityObject.slug,
            }).then(() => {
                dispatch('getPriorities')
            }).catch(error => {
                Notify.error(error.response.data.message, 'Error!')
            }).finally(() => {
                commit('SET_LOADING_STATUS', false);
            });
        },
        deletePriority({commit, dispatch}, priority_id) {
            Dialog.confirm('Are you sure to delete this priority?').then(confirm => {
                if (confirm) {
                    commit('SET_LOADING_STATUS', true);
                    http.delete('priorities/' + priority_id).then(() => {
                        Notify.success('Priority has been deleted.', 'Success!');
                    }).catch(error => {
                        Notify.error(error.response.data.message, 'Error!');
                    }).finally(() => {
                        commit('SET_LOADING_STATUS', false);
                    })
                }
            });
        },
        updatePriorityMenuOrder({commit, dispatch}, priorities) {
            const menu_orders = priorities.map(el => el.term_id);
            commit('SET_LOADING_STATUS', true);
            http.post('priorities/batch', {menu_orders: menu_orders}).then(() => {
                Notify.success('Priority orders have been updated.', 'Success!');
            }).catch(error => {
                console.log(error.response.data);
            }).finally(() => {
                commit('SET_LOADING_STATUS', false);
            })
        },

        // Statuses
        getStatuses({commit}) {
            commit('SET_LOADING_STATUS', true);
            http.get('statuses').then(response => {
                commit('SET_LOADING_STATUS', false);
                commit('SET_STATUSES', response.data.data.items);
            }).catch(error => {
                console.log(error);
                commit('SET_LOADING_STATUS', false);
            });
        },
        createStatus({commit, dispatch}, payload = {name: '', color: ''}) {
            return new Promise(resolve => {
                commit('SET_LOADING_STATUS', true);
                http.post('statuses', {
                    name: payload.name,
                    color: payload.color,
                }).then((response) => {
                    resolve(response.data.data);
                    dispatch('getStatuses');
                }).catch(error => {
                    Notify.error(error.response.data.message);
                }).finally(() => {
                    commit('SET_LOADING_STATUS', false);
                })
            })
        },
        updateStatus({commit, dispatch}, payload = {term_id: '', slug: '', name: '', color: ''}) {
            return new Promise(resolve => {
                commit('SET_LOADING_STATUS', true);
                http.post('statuses/' + payload.term_id, {
                    name: payload.name,
                    slug: payload.slug,
                    color: payload.color,
                }).then((response) => {
                    dispatch('getStatuses');
                    resolve(response.data.data);
                    Notify.success('Status has been updated.', 'Success!');
                }).catch(error => {
                    Notify.error(error.response.data.message, 'Error!');
                }).finally(() => {
                    commit('SET_LOADING_STATUS', false);
                })
            })
        },
        deleteStatus({commit, dispatch}, status_id) {
            Dialog.confirm('Are you sure to delete this status?').then(confirm => {
                if (confirm) {
                    this.delete_item('statuses/' + status_id).then(() => {
                        Notify.success('Status has been deleted.', 'Success!');
                        dispatch('getStatuses');
                    }).catch(error => {
                        Notify.error(error.response.data.message, 'Error!');
                    })
                }
            });
        },
        updateStatusMenuOrder({commit, dispatch}, statuses) {
            commit('SET_LOADING_STATUS', true);
            const menu_orders = statuses.map(el => el.term_id);
            http.post('statuses/batch', {menu_orders}).then(() => {
                Notify.success('Status orders have been updated.', 'Success!');
                dispatch('getStatuses');
            }).catch(error => {
                Notify.error(error.response.data.message, 'Error!');
            }).finally(() => {
                commit('SET_LOADING_STATUS', false);
            })
        },

        // Agents
        getAgents({commit}) {
            return new Promise(resolve => {
                commit('SET_LOADING_STATUS', true);
                http.get('agents').then(response => {
                    commit('SET_AGENTS', response.data.data.items);
                    resolve(response.data.data.items)
                }).catch(error => {
                    console.log(error);
                }).finally(() => {
                    commit('SET_LOADING_STATUS', false);
                });
            })
        },

        // Roles
        getRoles({commit}) {
            return new Promise(resolve => {
                http.get('roles').then(response => {
                    commit('SET_ROLES', response.data.data.roles);
                    resolve(response.data.data.roles);
                }).catch(error => {
                    console.log(error);
                }).finally(() => {
                    commit('SET_LOADING_STATUS', false);
                });
            })
        },
        createRole({commit, dispatch}, role) {
            commit('SET_LOADING_STATUS', true);
            http.post('roles', role).then(() => {
                dispatch('getRoles');
                Notify.success('New role has been created.', 'Success!');
            }).catch(error => {
                if (error.response.data.message) {
                    Notify.error(error.response.data.message, 'Error!');
                }
            }).finally(() => {
                commit('SET_LOADING_STATUS', false);
            });
        },
        updateRole({commit, dispatch}, role) {
            commit('SET_LOADING_STATUS', true);
            http.put('role', role).then(() => {
                Notify.success('Role has been updated.', 'Success!');
                dispatch('getRoles');
            }).catch(error => {
                if (error.response.data.message) {
                    Notify.error(error.response.data.message, 'Error!');
                }
            }).finally(() => {
                commit('SET_LOADING_STATUS', false);
            });
        },
        deleteRole({commit, dispatch}, role) {
            Dialog.confirm('Are you sure to delete this role?').then(confirm => {
                if (confirm) {
                    http.delete('role', {params: {role: role}}).then(() => {
                        dispatch('getRoles');
                        Notify.success('Role has been deleted.', 'Success!');
                    }).catch(error => {
                        if (error.response.data.message) {
                            Notify.error(error.response.data.message, 'Error!');
                        }
                    }).finally(() => {
                        commit('SET_LOADING_STATUS', false);
                    });
                }
            })
        },

        // Settings
        getSettingsFields({commit}) {
            return new Promise(resolve => {
                commit('SET_LOADING_STATUS', true);
                http
                    .get('settings', {params: {user_options: true}})
                    .then(response => {
                        resolve(response.data.data)
                    }).catch(error => {
                    console.error(error);
                }).finally(() => {
                    commit('SET_LOADING_STATUS', false);
                })
            })
        },
        saveSettingsFields({commit}, options) {
            commit('SET_LOADING_STATUS', true);
            http
                .post('settings/user', {options: options})
                .then(() => {
                    commit('SET_SNACKBAR', {
                        title: 'Success!',
                        message: 'Options has been updated.',
                        type: 'success'
                    })
                }).catch(error => {
                console.error(error);
                commit('SET_SNACKBAR', {
                    title: 'Error!',
                    message: 'Something went wrong.',
                    type: 'error'
                })
            }).finally(() => {
                commit('SET_LOADING_STATUS', false);
            })
        }
    },

    // Save as Vue computed property
    getters: {
        display_name() {
            return window.StackonetSupportTicket.display_name;
        },
        user_email() {
            return window.StackonetSupportTicket.user_email;
        }
    },
});
