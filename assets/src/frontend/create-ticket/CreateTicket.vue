<template>
    <div class="support-ticket-form">
        <form action="#" @submit.prevent="submitTicket">
            <columns :multiline="true">
                <column :tablet="6" v-if="fields.name">
                    <div class="support-ticket-form__control">
                        <label for="customer_name">
                            <strong class="support-ticket-form__label">{{fields.name.label}}</strong>
                            <span class="support-ticket-form__description">{{fields.name.description}}</span>
                        </label>
                        <input type="text" id="customer_name" class="support-ticket-form__input" v-model="ticket.name"
                               :disabled="hasDefaultName">
                    </div>
                </column>
                <column :tablet="6" v-if="fields.email">
                    <div class="support-ticket-form__control">
                        <label for="customer_email">
                            <strong class="support-ticket-form__label">{{fields.email.label}}</strong>
                            <span class="support-ticket-form__description">{{fields.email.description}}</span>
                        </label>
                        <input type="text" id="customer_email" class="support-ticket-form__input" v-model="ticket.email"
                               :disabled="hasDefaultEmail">
                    </div>
                </column>
                <column :tablet="12" v-if="fields.subject">
                    <div class="support-ticket-form__control">
                        <label for="ticket_subject">
                            <strong class="support-ticket-form__label">{{fields.subject.label}}</strong>
                            <span class="support-ticket-form__description">{{fields.subject.description}}</span>
                        </label>
                        <input type="text" id="ticket_subject" class="support-ticket-form__input"
                               v-model="ticket.subject">
                    </div>
                </column>
                <column :tablet="12" v-if="fields.content">
                    <div class="support-ticket-form__control">
                        <label for="ticket_content">
                            <strong class="support-ticket-form__label">{{fields.content.label}}</strong>
                            <span class="support-ticket-form__description">{{fields.content.description}}</span>
                        </label>
                        <editor id="ticket_content" :init="mce" v-model="ticket.content"></editor>
                    </div>
                </column>
                <column :tablet="12" v-if="fields.category">
                    <div class="support-ticket-form__control">
                        <label for="ticket_category">
                            <strong class="support-ticket-form__label">{{fields.category.label}}</strong>
                            <span class="support-ticket-form__description">{{fields.category.description}}</span>
                        </label>
                        <select id="ticket_category" class="support-ticket-form__select" v-model="ticket.category">
                            <option v-for="(label,value) in fields.category.options" :value="value">{{label}}</option>
                        </select>
                    </div>
                </column>
                <column :tablet="12">
                    <button class="button button--create-ticket">Submit</button>
                </column>
            </columns>
        </form>
    </div>
</template>

<script>
    import axios from 'axios'
    import {columns, column} from 'shapla-columns'
    import Editor from '@tinymce/tinymce-vue'

    export default {
        name: "CreateTicket",
        components: {columns, column, Editor},
        data() {
            return {
                loading: false,
                showThankYouMessage: false,
                defaults: {},
                ticket: {
                    name: '',
                    email: '',
                    subject: '',
                    content: '',
                    phone_number: '',
                    category: 0,
                    status: 0,
                    priority: 0,
                },
                fields: []
            }
        },
        computed: {
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
            hasDefaultName() {
                return !!(this.defaults.name && this.defaults.name.length)
            },
            hasDefaultEmail() {
                return !!(this.defaults.email && this.defaults.email.length)
            },
            isValidEmail() {
                return !!(this.ticket.email.length && this.validateEmail(this.ticket.email));
            },
        },
        mounted() {
            let fieldsEl = document.querySelector('[data-form_fields]');
            if (fieldsEl) {
                this.fields = JSON.parse(fieldsEl.getAttribute('data-form_fields'));
                Object.values(this.fields).forEach(field => {
                    this.defaults[field.id] = field.default;
                    this.ticket[field.id] = field.default;
                });
            }
        },
        methods: {
            validateEmail(email) {
                let re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
                return re.test(String(email).toLowerCase());
            },
            submitTicket() {
                this.loading = true;
                axios.post('tickets', this.ticket).then(response => {
                    this.loading = false;
                    if (response.data.data.ticket_id) {
                        this.showThankYouMessage = true;
                    }
                }).catch(error => {
                    console.log(error);
                    this.loading = false;
                });
            }
        }
    }
</script>

<style lang="scss">
    .support-ticket-form {
        &__control {

        }

        &__label {

        }

        &__description {
            display: block;
            font-size: .75em;
            font-style: italic;
        }

        &__select {
            width: 100%;
        }
    }
</style>