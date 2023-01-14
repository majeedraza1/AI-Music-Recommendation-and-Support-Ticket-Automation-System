<template>
  <div class="stackont-support-ticket-container stackont-support-ticket-container--new">
    <h1>Add new ticket</h1>
    <div class="mb-4 w-full"></div>
    <div class="mb-8">
      <shapla-button theme="primary" outline size="small" @click="backToTicketList">Back to Ticket</shapla-button>
    </div>

    <div class="container container--add-ticket">
      <columns multiline>

        <column :desktop="6">
          <div class="form-field">
            <text-field label="Name" id="customer_name" v-model="customer_name"/>
          </div>
        </column>

        <column :desktop="6">
          <div class="form-field">
            <text-field label="Email Address" id="email_address" v-model="customer_email"/>
          </div>
        </column>

        <column :desktop="12">
          <div class="form-field">
            <text-field type="textarea" :rows="2" label="Subject" id="ticket_subject"
                        v-model="ticket_subject"/>
          </div>
        </column>

        <column :desktop="12">
          <div class="form-field">
            <label>Description</label>
            <editor id="ticket_description" :init="mce" v-model="ticket_content"/>
          </div>
        </column>

        <column :desktop="6">
          <div class="form-field">
            <select-field label="Category *" v-model="ticket_category" :options="categories"
                          label-key="name" value-key="term_id"/>
          </div>
        </column>

        <column :desktop="6">
          <div class="form-field">
            <select-field label="Priority *" v-model="ticket_priority" :options="priorities"
                          label-key="name" value-key="term_id"/>
          </div>
        </column>

        <column :desktop="12">
          <div class="form-field">
            <label class="flex mb-2" for="attachments">Attachments</label>
            <input type="file" id="attachments" class="ticket-attachments" multiple>
          </div>
        </column>

        <column :desktop="12">
          <div class="form-field">
            <shapla-button theme="primary" :disabled="!canSubmit" @click="submitTicket">
              Submit Ticket
            </shapla-button>
          </div>
        </column>
      </columns>
    </div>

  </div>
</template>

<script>
import {default as axios} from "@/admin/axios";
import {useStore} from 'vuex';
import {useRouter} from "vue-router";
import Editor from '@tinymce/tinymce-vue'
import {
  ShaplaButton as shaplaButton,
  ShaplaColumn as column,
  ShaplaColumns as columns,
  ShaplaInput as textField,
  ShaplaSelect as selectField
} from "@shapla/vue-components";

const store = useStore();
const router = useRouter();

export default {
  name: "NewSupportTicket",
  components: {column, columns, shaplaButton, Editor, selectField, textField},
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
    store.commit('SET_LOADING_STATUS', false);
    store.commit('SET_SHOW_SIDE_NAVE', false);
    this.customer_name = this.display_name;
    this.customer_email = this.user_email;
    if (!this.categories.length) {
      store.dispatch('getCategories');
    }
    if (!this.priorities.length) {
      store.dispatch('getPriorities');
    }
  },
  methods: {
    backToTicketList() {
      router.push({name: 'SupportTicketList'})
    },
    validateEmail(email) {
      let re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
      return re.test(String(email).toLowerCase());
    },
    ticketList() {
      router.push({name: 'SupportTicketList'});
    },
    submitTicket() {
      let headers = {'Content-Type': 'multipart/form-data'};
      let data = {
        name: this.customer_name, email: this.customer_email, subject: this.ticket_subject,
        content: this.ticket_content, category: this.ticket_category, priority: this.ticket_priority,
      };
      let formData = new FormData();
      let fileList = this.$el.querySelector('.ticket-attachments').files;
      for (let i = 0, numFiles = fileList.length; i < numFiles; i++) {
        formData.append("files[]", fileList[i]);
      }
      for (const [key, value] of Object.entries(data)) {
        formData.append(key, value);
      }
      axios.post(StackonetSupportTicket.restRoot + '/tickets', formData, {headers: headers}).then((response) => {
        store.commit('SET_LOADING_STATUS', false);
        let id = response.data.data.ticket_id;
        router.push({name: 'SingleSupportTicket', params: {id: id}});
      }).catch(error => {
        console.log(error);
        store.commit('SET_LOADING_STATUS', false);
      });
    }
  },
  setup() {
    return {
      categories: store.state.categories,
      priorities: store.state.priorities,
      statuses: store.state.statuses,
      agents: store.state.agents,
      display_name: store.getters.display_name,
      user_email: store.getters.user_email,
    }
  }
}
</script>

<style lang="scss">
.container--add-ticket {
  max-width: 960px;
}
</style>
