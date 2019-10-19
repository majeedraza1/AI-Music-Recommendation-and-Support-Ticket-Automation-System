<template>
    <modal :active="active" @close="close" :title="title" :content-size="contentSize">
        <div class="stackonet-role-editor">
            <columns :multiline="true">
                <column :tablet="12">
                    <animated-input label="Role" :required="true" v-model="role"></animated-input>
                </column>
                <column :tablet="12">
                    <animated-input label="Role Name" :required="true" v-model="name"></animated-input>
                </column>
                <column :tablet="12" v-for="_setting in caps_settings" :key="_setting.id">
                    <div class="clearfix flex w-full stackonet-role-editor__capabilities">
                        <mdl-switch v-model="capabilities[_setting.id]">
                            <div class="stackonet-role-editor__capability">
                                <strong class="stackonet-role-editor__capability-title">{{_setting.label}}</strong>
                                <span class="stackonet-role-editor__capability-description">{{_setting.description}}</span>
                            </div>
                        </mdl-switch>
                    </div>
                </column>
            </columns>
        </div>
        <template slot="foot">
            <mdl-button class="stackonet-primary" type="raised" color="primary" @click="submit">{{btnSave}}</mdl-button>
        </template>
    </modal>
</template>

<script>
    import {columns, column} from 'shapla-columns'
    import modal from 'shapla-modal'
    import AnimatedInput from "../../components/AnimatedInput";
    import MdlSwitch from "../../material-design-lite/switch/mdlSwitch";
    import MdlButton from "../../material-design-lite/button/mdlButton";

    export default {
        name: "RoleEditor",
        components: {MdlButton, MdlSwitch, AnimatedInput, columns, column, modal},
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