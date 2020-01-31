<template>
    <div class="stackonet-support-ticket-settings">
        <shapla-button theme="primary" @click="backToTicketList">Back to Ticket</shapla-button>
        <tabs>
            <tab v-for="(panel,index) in panels" :key="panel.id" :name="panel.title" :selected="index === 0">
                <template v-for="section in sections" v-if="panel.id === section.panel">
                    <h2 class="title" v-if="section.title">{{section.title}}</h2>
                    <p class="description" v-if="section.description" v-html="section.description"></p>

                    <table class="form-table">
                        <template v-for="field in fields" v-if="field.section === section.id">
                            <tr>
                                <th scope="row">
                                    <label :for="field.id" v-text="field.title"></label>
                                </th>
                                <td>
                                    <template v-if="field.type === 'textarea'">
										<textarea class="regular-text" :id="field.id" :rows="field.rows"
                                                  v-model="options[field.id]"></textarea>
                                    </template>
                                    <template v-else-if="field.type === 'select'">
                                        <select class="regular-text" v-model="options[field.id]">
                                            <option value="">-- Choose --</option>
                                            <option v-for="(label, value) in field.options" :value="value"
                                                    v-text="label"></option>
                                        </select>
                                    </template>
                                    <template v-else>
                                        <input type="text" class="regular-text" :id="field.id"
                                               v-model="options[field.id]">
                                    </template>
                                    <p class="description" v-if="field.description" v-html="field.description"></p>
                                </td>
                            </tr>
                        </template>
                    </table>

                </template>
                <div class="button-save-settings-container">
                    <shapla-button theme="primary" size="medium" :fab="true" @click="saveOptions">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                            <path d="M0 0h24v24H0z" fill="none"/>
                            <path d="M17 3H5c-1.11 0-2 .9-2 2v14c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V7l-4-4zm-5 16c-1.66 0-3-1.34-3-3s1.34-3 3-3 3 1.34 3 3-1.34 3-3 3zm3-10H5V5h10v4z"/>
                        </svg>
                    </shapla-button>
                </div>
            </tab>

            <tab name="Categories">
                <ticket-categories/>
            </tab>

            <tab name="Priorities">
                <ticket-priorities/>
            </tab>

            <tab name="Statuses">
                <ticket-statuses/>
            </tab>

            <tab name="Agents">
                <agents-list/>
            </tab>
        </tabs>
    </div>
</template>

<script>

    import axios from 'axios';
    import {tab, tabs} from 'shapla-tabs';
    import shaplaButton from "shapla-button";
    import TicketCategories from "../../support-ticket/categories/TicketCategories";
    import TicketPriorities from "../../support-ticket/priorities/TicketPriorities";
    import AgentsList from "../../support-ticket/agents/AgentsList";
    import TicketStatuses from "../../support-ticket/statuses/TicketStatuses";

    export default {
        name: "Settings",
        components: {TicketStatuses, AgentsList, TicketPriorities, TicketCategories, shaplaButton, tabs, tab},
        data() {
            return {
                options: {},
                panels: [],
                sections: [],
                fields: [],
            }
        },
        mounted() {
            this.$store.commit('SET_LOADING_STATUS', false);
            this.$store.commit('SET_SHOW_SIDE_NAVE', false);
            this.getSettingsFields();
        },
        methods: {
            backToTicketList() {
                this.$router.push({name: 'SupportTicketList'})
            },
            getSettingsFields() {
                axios.get(StackonetSupportTicket.restRoot + '/settings', {params: {user_options: true}}).then(response => {
                    this.$store.commit('SET_LOADING_STATUS', false);
                    let data = response.data.data;
                    this.panels = data.panels;
                    this.sections = data.sections;
                    this.fields = data.fields;
                    this.options = data.options;
                }).catch(error => {
                    console.error(error);
                    this.$store.commit('SET_LOADING_STATUS', false);
                })
            },
            saveOptions() {
                this.$store.commit('SET_LOADING_STATUS', true);
                axios.post(StackonetSupportTicket.restRoot + '/settings/user', {options: this.options}).then(() => {
                    this.$store.commit('SET_LOADING_STATUS', false);
                    this.$store.commit('SET_SNACKBAR', {
                        title: 'Success!',
                        message: 'Options has been updated.',
                        type: 'success'
                    })
                }).catch(error => {
                    console.error(error);
                    this.$store.commit('SET_LOADING_STATUS', false);
                    this.$store.commit('SET_SNACKBAR', {
                        title: 'Error!',
                        message: 'Something went wrong.',
                        type: 'error'
                    })
                })
            }
        }
    }
</script>

<style lang="scss">
    .stackonet-support-ticket-settings {

        table.form-table {
            th {
                vertical-align: top;
            }
        }
    }
</style>