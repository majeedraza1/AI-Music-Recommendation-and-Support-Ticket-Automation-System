<template>
  <div class="support-ticket-form">
    <div v-if="showThankYouMessage" v-html="thank_you_message">
      <h4>Thank you for contacting us!</h4>
      <p>We will get back to you as soon as possible.</p>
    </div>
    <form v-if="Object.keys(fields).length && false === showThankYouMessage" action="#" @submit.prevent="submitTicket">
      <columns :multiline="true">
        <column v-if="fields.name" :tablet="12">
          <div class="support-ticket-form__control">
            <label for="customer_name">
              <strong class="support-ticket-form__label">{{ fields.name.label }}</strong>
              <span class="support-ticket-form__description">{{ fields.name.description }}</span>
            </label>
            <input id="customer_name" v-model="ticket.name" class="support-ticket-form__input" type="text">
          </div>
        </column>
        <column v-if="fields.email" :tablet="12">
          <div class="support-ticket-form__control">
            <label for="customer_email">
              <strong class="support-ticket-form__label">{{ fields.email.label }}</strong>
              <span class="support-ticket-form__description">{{ fields.email.description }}</span>
            </label>
            <input id="customer_email" v-model="ticket.email" class="support-ticket-form__input" type="text">
          </div>
        </column>
        <column v-if="fields.phone_number && fields.phone_number.type !== 'hidden'" :tablet="12">
          <div class="support-ticket-form__control">
            <label for="customer_email">
              <strong class="support-ticket-form__label">{{ fields.phone_number.label }}</strong>
              <span class="support-ticket-form__description">{{ fields.phone_number.description }}</span>
            </label>
            <input id="customer_email" v-model="ticket.phone_number" class="support-ticket-form__input" type="tel">
          </div>
        </column>
        <column v-show="fields.subject && fields.subject.type !== 'hidden'" :tablet="12">
          <div class="support-ticket-form__control">
            <label for="ticket_subject">
              <strong class="support-ticket-form__label">{{ fields.subject.label }}</strong>
              <span class="support-ticket-form__description">{{ fields.subject.description }}</span>
            </label>
            <input id="ticket_subject" v-model="ticket.subject" class="support-ticket-form__input"
                   type="text">
          </div>
        </column>
        <column v-if="fields.content" :tablet="12">
          <div class="support-ticket-form__control">
            <label for="ticket_content">
              <strong class="support-ticket-form__label">{{ fields.content.label }}</strong>
              <span class="support-ticket-form__description">{{ fields.content.description }}</span>
            </label>
            <editor id="ticket_content" v-model="ticket.content" :init="mce"/>
          </div>
        </column>
        <column v-if="fields.category && fields.category.type !== 'hidden'" :tablet="12">
          <div class="support-ticket-form__control">
            <label for="ticket_category">
              <strong class="support-ticket-form__label">{{ fields.category.label }}</strong>
              <span class="support-ticket-form__description">{{ fields.category.description }}</span>
            </label>
            <select id="ticket_category" v-model="ticket.category" class="support-ticket-form__select">
              <option v-for="(label,value) in fields.category.options" :value="value">{{ label }}</option>
            </select>
          </div>
        </column>
        <column :tablet="12">
          <shapla-button theme="primary" size="large" :class="{'is-loading':loading}">Submit</shapla-button>
        </column>
      </columns>
    </form>
  </div>
</template>

<script>
import axios from 'axios'
import {column, columns, shaplaButton} from 'shapla-vue-components';
import Editor from '@tinymce/tinymce-vue'

export default {
  name: "CreateTicket",
  components: {shaplaButton, columns, column, Editor},
  data() {
    return {
      loading: false,
      showThankYouMessage: false,
      defaults: {},
      thank_you_message: '',
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
      const data = JSON.parse(fieldsEl.getAttribute('data-form_fields'));
      this.fields = data.fields;
      this.thank_you_message = data.thank_you_message;
      Object.values(data.fields).forEach(field => {
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