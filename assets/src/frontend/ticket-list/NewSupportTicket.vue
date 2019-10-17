<template>
    <div class="stackont-support-ticket-container stackont-support-ticket-container--new">

        <columns multiline>

            <column :desktop="6">
                <div class="form-field">
                    <label for="customer_name">Name</label>
                    <input type="text" id="customer_name" v-model="customer_name">
                </div>
            </column>

            <column :desktop="6">
                <div class="form-field">
                    <label for="email_address">Email Address</label>
                    <input type="text" id="email_address" v-model="customer_email">
                </div>
            </column>

            <column :desktop="12">
                <div class="form-field">
                    <label for="ticket_subject">Subject</label>
                    <input type="text" id="ticket_subject" v-model="ticket_subject">
                </div>
            </column>

            <column :desktop="12">
                <div class="form-field">
                    <label for="ticket_description">Description</label>
                    <editor id="ticket_description" :init="mce" v-model="ticket_content"></editor>
                </div>
            </column>

            <column :desktop="6">
                <div class="form-field">
                    <label for="ticket_category">Category *</label>
                    <select id="ticket_category" v-model="ticket_category">
                        <option v-for="_category in categories" :value="_category.term_id"
                                v-html="_category.name"></option>
                    </select>
                </div>
            </column>

            <column :desktop="6">
                <div class="form-field">
                    <label for="ticket_priority">Priority *</label>
                    <select id="ticket_priority" v-model="ticket_priority">
                        <option v-for="_priority in priorities" :value="_priority.term_id"
                                v-html="_priority.name"></option>
                    </select>
                </div>
            </column>

            <column :desktop="12">
                <div class="form-field">
                    <mdl-button type="raised" color="primary" :disabled="!canSubmit" @click="submitTicket">Submit
                        Ticket
                    </mdl-button>
                </div>
            </column>
        </columns>

    </div>
</template>

<script>
    import {mapState, mapGetters} from 'vuex';
    import axios from 'axios'
    import Editor from '@tinymce/tinymce-vue'
    import MdlButton from "../../material-design-lite/button/mdlButton";
    import Icon from "../../shapla/icon/icon";
    import {columns, column} from "shapla-columns";

    export default {
        name: "NewSupportTicket",
        components: {column, columns, Icon, MdlButton, Editor},
        data() {
            return {
                customer_name: '',
                customer_email: '',
                ticket_subject: '',
                ticket_content: '',
                ticket_category: '',
                ticket_priority: '',
            }
        },
        computed: {
            ...mapState(['categories', 'priorities', 'statuses', 'agents']),
            ...mapGetters(['display_name', 'user_email']),
            mce() {
                return {
                    branding: false,
                    plugins: 'lists link paste wpemoji',
                    toolbar: 'undo redo bold italic underline strikethrough bullist numlist link unlink table inserttable',
                    min_height: 150,
                    inline: false,
                    menubar: false,
                    statusbar: true
                }
            },
            isValidEmail() {
                return !!(this.customer_email.length && this.validateEmail(this.customer_email));
            },
            canSubmit() {
                return !!(this.customer_name.length > 2 && this.isValidEmail &&
                    this.ticket_subject.length > 3 && this.ticket_content.length > 5);
            }
        },
        mounted() {
            this.$store.commit('SET_LOADING_STATUS', false);
            this.$store.commit('SET_SHOW_SIDE_NAVE', false);
            this.customer_name = this.display_name;
            this.customer_email = this.user_email;
            if (!this.categories.length) {
                this.$store.dispatch('getCategories');
            }
            if (!this.priorities.length) {
                this.$store.dispatch('getPriorities');
            }
        },
        methods: {
            validateEmail(email) {
                let re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
                return re.test(String(email).toLowerCase());
            },
            ticketList() {
                this.$router.push({name: 'SupportTicketList'});
            },
            submitTicket() {
                let self = this;
                axios
                    .post('support-ticket', {
                        customer_name: self.customer_name,
                        customer_email: self.customer_email,
                        ticket_subject: self.ticket_subject,
                        ticket_content: self.ticket_content,
                        ticket_category: self.ticket_category,
                        ticket_priority: self.ticket_priority,
                    })
                    .then((response) => {
                        self.$store.commit('SET_LOADING_STATUS', false);
                        let id = response.data.data.ticket_id;
                        this.$router.push({name: 'SingleSupportTicket', params: {id: id}});
                    })
                    .catch((error) => {
                        self.$store.commit('SET_LOADING_STATUS', false);
                    });
            }
        }
    }
</script>

<style lang="scss">
    .stackont-support-ticket-container--new {
        margin-top: 50px;
    }

    .display-flex.justify-space-between {
        margin-bottom: 20px;
    }

    .stackont-support-ticket-container {
        &--new {
            max-width: 800px;
            margin-left: auto;
            margin-right: auto;
        }

        .form-field {
            display: block;

            label {
                display: block;
            }

            input, select {
                display: block;
                width: 100%;
            }
        }
    }
</style>
