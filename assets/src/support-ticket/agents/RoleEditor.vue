<template>
    <modal :active="active" @close="close" :title="title" :content-size="contentSize">
        <div class="stackonet-role-editor">
            <columns :multiline="true">
                <column :tablet="12">
                    <animated-input label="Role" :required="true" v-model="role"/>
                </column>
                <column :tablet="12">
                    <animated-input label="Role Name" :required="true" v-model="name"/>
                </column>
                <column :tablet="12" v-for="_setting in caps_settings" :key="_setting.id">
                    <div class="clearfix flex w-full stackonet-role-editor__capabilities">
                        <shapla-switch v-model="capabilities[_setting.id]">
                            <div class="stackonet-role-editor__capability">
                                <strong class="stackonet-role-editor__capability-title">{{_setting.label}}</strong>
                                <span class="stackonet-role-editor__capability-description">{{_setting.description}}</span>
                            </div>
                        </shapla-switch>
                    </div>
                </column>
            </columns>
        </div>
        <template slot="foot">
            <shapla-button class="stackonet-primary" theme="primary" @click="submit">
                {{btnSave}}
            </shapla-button>
        </template>
    </modal>
</template>

<script>
    import {column, columns} from 'shapla-columns'
    import modal from 'shapla-modal'
    import AnimatedInput from "../../shapla/shapla-animated-input/AnimatedInput";
    import shaplaSwitch from "../../shapla/shapla-switch";
    import shaplaButton from "shapla-button";

    export default {
        name: "RoleEditor",
        components: {shaplaButton, shaplaSwitch, AnimatedInput, columns, column, modal},
        props: {
            active: {type: Boolean, required: true},
            title: {type: String, default: 'Add Role'},
            contentSize: {type: String, default: 'medium'},
            btnSave: {type: String, default: 'Save'},
            value: {
                type: Object, default: () => {
                }
            }
        },
        data() {
            return {
                role: "",
                name: "",
                capabilities: {
                    view_unassigned: false,
                    view_assigned_me: false,
                    view_assigned_others: false,
                    assign_unassigned: false,
                    assign_assigned_me: false,
                    assign_assigned_others: false,
                    reply_unassigned: false,
                    reply_assigned_me: false,
                    reply_assigned_others: false,
                    delete_unassigned: false,
                    delete_assigned_me: false,
                    delete_assigned_others: false,
                    change_ticket_status_unassigned: false,
                    change_ticket_status_assigned_me: false,
                    change_ticket_status_assigned_others: false,
                    change_ticket_field_unassigned: false,
                    change_ticket_field_assigned_me: false,
                    change_ticket_field_assigned_others: false,
                    change_ticket_agent_only_unassigned: false,
                    change_ticket_agent_only_assigned_me: false,
                    change_ticket_agent_only_assigned_others: false,
                    change_ticket_raised_by_unassigned: false,
                    change_ticket_raised_by_assigned_me: false,
                    change_ticket_raised_by_assigned_others: false
                }
            }
        },
        mounted() {
            if (this.value) {
                this.refreshData(this.value);
            }
        },
        watch: {
            value(newValue) {
                this.refreshData(newValue);
            }
        },
        computed: {
            caps_settings() {
                return StackonetSupportTicket.caps_settings;
            }
        },
        methods: {
            refreshData(data) {
                if (data.role) {
                    this.role = data.role;
                } else {
                    this.role = '';
                }
                if (data.name) {
                    this.name = data.name;
                } else {
                    this.name = '';
                }
                if (data.capabilities) {
                    Object.assign(this.capabilities, data.capabilities);
                } else {
                    Object.keys(this.capabilities).forEach((cap) => {
                        this.capabilities[cap] = false;
                    })
                }
            },
            close() {
                this.$emit('close');
            },
            submit() {
                this.$emit('submit', this._data);
            }
        }
    }
</script>

<style lang="scss">
    .stackonet-role-editor {
        &__capabilities {
            margin-bottom: 16px;
        }

        &__capability {
            display: inline-flex;
            flex-direction: column;
        }

        &__capability-title {
            font-size: 14px;
            font-weight: bold;
        }

        &__capability-description {
            font-size: 13px;
            font-style: italic;
            color: rgba(#000, .35);
        }
    }
</style>