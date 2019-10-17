<template>
    <div class="stackonet-support-ticket-settings">
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
                                    <template v-else-if="field.type === 'checkbox'">
                                        <switches v-model="options[field.id]"></switches>
                                    </template>
                                    <template v-else-if="field.type === 'radio'">
                                        <radio-buttons :options="field" v-model="options[field.id]"></radio-buttons>
                                    </template>
                                    <!--                                    <template v-else-if="field.type === 'color'">-->
                                    <!--                                        <color-picker v-model="options[field.id]"></color-picker>-->
                                    <!--                                    </template>-->
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
            </tab>
        </tabs>

        <p class="submit">
            <input type="submit" class="button button-primary" value="Save Changes" @click.prevent="saveOptions">
        </p>
    </div>
</template>

<script>

    import axios from 'axios';
    import {tabs, tab} from 'shapla-tabs';
    import radioButtons from "shapla-radio-buttons";
    import Switches from "../../components/Switches";
    import MdlButton from "../../material-design-lite/button/mdlButton";

    export default {
        name: "Settings",
        components: {MdlButton, tabs, tab, radioButtons, Switches},
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
                axios.get('settings').then(response => {
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
                axios.post('settings', {options: this.options}).then(() => {
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
        max-width: 1140px;
        margin-left: auto;
        margin-right: auto;
    }
</style>