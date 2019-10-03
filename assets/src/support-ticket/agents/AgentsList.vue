<template>
    <div>
        <div class="clear"></div>
        <tabs>
            <tab name="Support Agents" :selected="true">
                <data-table
                        :columns="columns"
                        :rows="agents"
                        :actions="actions"
                        @action:click="onActionClick"
                        action-column="display_name"
                        index="term_id"
                >
                    <template slot="avatar_url" slot-scope="data">
                        <img :src="data.row.avatar_url" alt="" width="48" height="48">
                    </template>
                </data-table>
                <div class="button-add-agent-container" title="Add Agent">
                    <mdl-fab @click="showAddAgentModal = true">+</mdl-fab>
                </div>
                <modal :active="showAddAgentModal" @close="showAddAgentModal = false" title="Add Agent">
                    <div class="modal--add-agent-inner" style="min-height: 200px">
                        <columns :multiline="true">
                            <column :tablet="12">
                                <label>Agent</label>
                                <v-select @search="fetchUsers" :filterable="false" :options="users"
                                          label="name" v-model="addAgentActiveAgent"></v-select>
                                <span class="help has-error" v-if="agentError.length">{{agentError}}</span>
                            </column>
                            <column :tablet="12">
                                <label>Role</label>
                                <v-select :filterable="false" :options="roles" label="name"
                                          v-model="addAgentActiveRole"></v-select>
                            </column>
                        </columns>
                    </div>
                    <template slot="foot">
                        <mdl-button type="raised" color="primary" :disabled="!canCreateAgent" @click="createNewAgent">
                            Create
                        </mdl-button>
                    </template>
                </modal>
                <modal :active="showEditAgentModal" @close="showEditAgentModal = false" title="Edit Agent Role">
                    <div style="min-height: 200px">
                        <label>Role</label>
                        <select v-model="editAgentActiveAgent.role_id" style="width: 100%">
                            <option v-for="_role in roles" :value="_role.role" v-html="_role.name"></option>
                        </select>
                    </div>
                    <template slot="foot">
                        <mdl-button type="raised" color="primary" @click="updateAgentRole">
                            Create
                        </mdl-button>
                    </template>
                </modal>
            </tab>
            <tab name="Roles & Capabilities">
                <div v-for="role in roles" :key="role.role">
                    <div class="shapla-box shapla-box--role flex w-full content-center mdl-shadow--2dp">
                        <div>
                            <strong>{{role.name}}</strong>
                        </div>
                        <div class="flex">
                            <div @click.prevent="editRole(role)">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                                    <path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/>
                                    <path d="M0 0h24v24H0z" fill="none"/>
                                </svg>
                            </div>
                            <div @click.prevent="deleteRole(role)">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                                    <path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM8 9h8v10H8V9zm7.5-5l-1-1h-5l-1 1H5v2h14V4z"/>
                                    <path fill="none" d="M0 0h24v24H0V0z"/>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="button-add-role-container" title="Add Role">
                    <mdl-fab @click="showAddRoleModal = true">+</mdl-fab>
                </div>
            </tab>
        </tabs>
        <role-editor :value="role" :active="showAddRoleModal" @close="closeAddNewRoleModal"
                     @submit="addNewRole"></role-editor>
        <role-editor :value="activeRole" :active="showEditRoleModal" @close="closeEditRoleModal"
                     @submit="updateRole"></role-editor>
    </div>
</template>

<script>
    import {mapGetters} from 'vuex';
    import axios from 'axios';
    import vSelect from 'vue-select'
    import modal from 'shapla-modal'
    import {columns, column} from 'shapla-columns'
    import {tabs, tab} from "../../shapla/shapla-tabs";
    import dataTable from "../../shapla/shapla-data-table/src/dataTable";
    import {CrudMixin} from "../../components/CrudMixin";
    import MdlFab from "../../material-design-lite/button/mdlFab";
    import AnimatedInput from "../../components/AnimatedInput";
    import MdlSwitch from "../../material-design-lite/switch/mdlSwitch";
    import RoleEditor from "./RoleEditor";
    import MdlButton from "../../material-design-lite/button/mdlButton";

    export default {
        name: "AgentsList",
        mixins: [CrudMixin],
        components: {
            MdlButton,
            RoleEditor, MdlSwitch, AnimatedInput, vSelect, MdlFab, dataTable, tabs, tab, modal, columns, column
        },
        data() {
            return {
                showAddAgentModal: false,
                showAddRoleModal: false,
                showEditRoleModal: false,
                showEditAgentModal: false,
                users: [],
                agents: [],
                roles: [],
                columns: [
                    {key: 'display_name', label: 'Name'},
                    {key: 'email', label: 'Email'},
                    {key: 'role_label', label: 'Role'},
                    {key: 'avatar_url', label: 'Avatar'},
                ],
                activeRole: {},
                role: {},
                addAgentActiveAgent: {},
                addAgentActiveRole: {},
                editAgentActiveAgent: {},
                agentError: '',
            }
        },
        mounted() {
            this.$store.commit('SET_LOADING_STATUS', false);
            this.getAgents();
            this.getRoles();
        },
        computed: {
            ...mapGetters(['caps_settings']),
            actions() {
                return [
                    {key: 'edit', label: 'Edit'},
                    {key: 'delete', label: 'Delete'}
                ];
            },
            canCreateAgent() {
                if (!this.addAgentActiveAgent || !this.addAgentActiveRole) return false;

                return !!(Object.keys(this.addAgentActiveAgent).length && Object.keys(this.addAgentActiveRole).length);
            }
        },
        methods: {
            onActionClick(action, item) {
                if ('edit' === action) {
                    this.showEditAgentModal = true;
                    this.editAgentActiveAgent = item;
                }
                if ('delete' === action) {
                    this.$modal.confirm('Are you sure to delete this agent?').then(confirm => {
                        if (confirm) {
                            this.trashAction(item.term_id);
                        }
                    });
                }
            },
            trashAction(item) {
                this.delete_item('agents/' + item).then(() => {
                    this.$store.commit('SET_SNACKBAR', {
                        title: 'Success!',
                        message: 'Support agent has been deleted.',
                        type: 'success',
                    });
                    this.getAgents();
                }).catch(error => {
                    if (error.response.data.message) {
                        this.$store.commit('SET_SNACKBAR', {
                            title: 'Error!',
                            message: error.response.data.message,
                            type: 'error',
                        });
                    }
                });
            },
            getAgents() {
                this.get_item('agents').then(data => {
                    this.agents = data.items;
                }).catch(error => {
                    console.log(error);
                })
            },
            getRoles() {
                this.get_item('roles').then(data => {
                    this.roles = data.roles;
                }).catch(error => {
                    console.log(error);
                })
            },
            closeAddNewRoleModal() {
                this.role = {};
                this.showAddRoleModal = false;
            },
            closeEditRoleModal() {
                this.activeRole = {};
                this.showEditRoleModal = false;
            },
            editRole(role) {
                this.activeRole = role;
                this.showEditRoleModal = true;
            },
            deleteRole(role) {
                this.$modal.confirm('Are you sure to delete this role?').then(confirm => {
                    if (confirm) {
                        this.delete_item('role', {params: {role: role.role}}).then(() => {
                            this.$delete(this.roles, this.roles.indexOf(role));
                        }).catch(error => {
                            if (error.response.data.message) {
                                this.$store.commit('SET_SNACKBAR', {
                                    title: 'Error!',
                                    message: error.response.data.message,
                                    type: 'error',
                                });
                            }
                        });
                    }
                })
            },
            addNewRole(role) {
                this.create_item('roles', role).then(() => {
                    this.closeAddNewRoleModal();
                    this.$store.commit('SET_SNACKBAR', {
                        title: 'Created!',
                        message: 'New role has been created.',
                        type: 'success',
                    });
                    this.getRoles();
                }).catch(error => {
                    if (error.response.data.message) {
                        this.$store.commit('SET_SNACKBAR', {
                            title: 'Error!',
                            message: error.response.data.message,
                            type: 'error',
                        });
                    }
                })
            },
            updateRole(role) {
                this.update_item('role', role).then(() => {
                    this.closeEditRoleModal();
                    this.$store.commit('SET_SNACKBAR', {
                        title: 'Updated!',
                        message: 'Role has been updated.',
                        type: 'success',
                    });
                    this.getRoles();
                }).catch(error => {
                    if (error.response.data.message) {
                        this.$store.commit('SET_SNACKBAR', {
                            title: 'Error!',
                            message: error.response.data.message,
                            type: 'error',
                        });
                    }
                })
            },
            fetchUsers(search, loading) {
                axios.get(StackonetToolkit.wpRestRoot + '/users', {
                    params: {search: search}
                }).then(response => {
                    let _data = response.data;
                    if (_data.length) {
                        this.users = _data;
                    } else {
                        this.users = [];
                    }
                }).catch(error => {
                    console.log(error);
                })
            },
            createNewAgent() {
                this.agentError = '';
                this.create_item('agents', {
                    user_id: this.addAgentActiveAgent.id,
                    role_id: this.addAgentActiveRole.role,
                }).then(() => {
                    this.showAddAgentModal = false;
                    this.getAgents();
                }).catch(error => {
                    this.agentError = error.response.data.message;
                });
            },
            updateAgentRole() {
                this.update_item('agents/' + this.editAgentActiveAgent.term_id, {
                    role_id: this.editAgentActiveAgent.role_id,
                }).then(() => {
                    this.showEditAgentModal = false;
                    this.getAgents();
                }).catch(error => {
                    this.agentError = error.response.data.message;
                });
            }
        }
    }
</script>

<style lang="scss">
    .shapla-box--role {
        margin-bottom: 1rem;
        justify-content: space-between;
        align-items: center;
    }

    .button-add-agent-container,
    .button-add-role-container {
        position: fixed;
        bottom: 1rem;
        right: 1rem;
        z-index: 10;
    }

    .agent-capabilities {
        margin-bottom: 16px;
    }

    .agent-capability {
        display: inline-flex;
        flex-direction: column;

        &__title {
            font-size: 14px;
            font-weight: bold;
        }

        &__description {
            font-size: 13px;
            font-style: italic;
            color: rgba(#000, .35);
        }
    }

    .modal--add-agent-inner {
    }

    .vs__search,
    .vs__search:focus {
        border: 1px solid transparent !important;
        border-left: none !important;
        outline: none !important;
        box-shadow: none !important;
    }

    .help.has-error {
        color: darkred;
    }
</style>