<template>
    <div>
        <h1 class="wp-heading-inline">Support Agents</h1>
        <div class="clear"></div>
        <tabs>
            <tab name="Agents" :selected="true">
                <data-table
                        :columns="columns"
                        :rows="agents"
                        action-column="display_name"
                        index="term_id"
                >
                    <template slot="avatar_url" slot-scope="data">
                        <img :src="data.row.avatar_url" alt="" width="48" height="48">
                    </template>
                </data-table>
                <div class="button-add-agent-container" title="Add Agent">
                    <mdl-fab>+</mdl-fab>
                </div>
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
    import {tabs, tab} from "../../shapla/shapla-tabs";
    import dataTable from "../../shapla/shapla-data-table/src/dataTable";
    import {CrudMixin} from "../../components/CrudMixin";
    import MdlFab from "../../material-design-lite/button/mdlFab";
    import modal from 'shapla-modal'
    import {columns, column} from 'shapla-columns'
    import AnimatedInput from "../../components/AnimatedInput";
    import MdlSwitch from "../../material-design-lite/switch/mdlSwitch";
    import RoleEditor from "./RoleEditor";

    export default {
        name: "AgentsList",
        mixins: [CrudMixin],
        components: {RoleEditor, MdlSwitch, AnimatedInput, MdlFab, dataTable, tabs, tab, modal, columns, column},
        data() {
            return {
                showAddRoleModal: false,
                showEditRoleModal: false,
                agents: [],
                roles: [],
                columns: [
                    {key: 'display_name', label: 'Name'},
                    {key: 'email', label: 'Email'},
                    {key: 'role_label', label: 'Role'},
                    {key: 'avatar_url', label: 'Avatar'},
                ],
                activeRole: {},
                role: {}
            }
        },
        mounted() {
            this.$store.commit('SET_LOADING_STATUS', false);
            this.getAgents();
            this.getRoles();
        },
        computed: {
            ...mapGetters(['caps_settings']),
        },
        methods: {
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
        }
    }
</script>

<style lang="scss" scoped>
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
</style>