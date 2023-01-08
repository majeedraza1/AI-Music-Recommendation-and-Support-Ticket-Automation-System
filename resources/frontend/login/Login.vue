<template>
  <div class="stackonet-support-ticket-login">
    <form class="stackonet-support-ticket-login-form" @submit.prevent="submitForm">
      <div style="margin-bottom: 1rem">
        <text-field
            v-model="user_login"
            :has-error="hasUserLoginError"
            :helptext="errors.user_login?errors.user_login[0]:''"
            autocomplete="username"
            label="Email or Username"
        />
      </div>
      <div style="margin-bottom: 1rem">
        <text-field
            v-model="password"
            :has-error="hasPasswordError"
            :helptext="errors.password?errors.password[0]:''"
            autocomplete="current-password"
            label="Password"
            type="password"
        />
      </div>
      <div class="flex space-between mb-4">
        <div>
          <shapla-checkbox v-model="remember">Remember me</shapla-checkbox>
        </div>
        <div><a :href="lostPasswordUrl">Forgot your password?</a></div>
      </div>
      <div>
        <shapla-button :disabled="!canSubmit" :fullwidth="true" theme="primary">Log in</shapla-button>
      </div>
    </form>
    <spinner :active="loading"/>
  </div>
</template>

<script>
import axios from 'axios';
import {shaplaButton, shaplaCheckbox, spinner, textField} from 'shapla-vue-components';

export default {
  name: "Login",
  components: {shaplaCheckbox, shaplaButton, textField, spinner},
  data() {
    return {
      loading: false,
      user_login: '',
      password: '',
      remember: false,
      errors: {
        user_login: [],
        password: [],
      },
    }
  },
  computed: {
    lostPasswordUrl() {
      return StackonetSupportTicket.lostPasswordUrl;
    },
    canSubmit() {
      return !!(this.user_login.length >= 4 && this.password.length >= 4);
    },
    hasUserLoginError() {
      return !!(this.errors.user_login && this.errors.user_login.length);
    },
    hasPasswordError() {
      return !!(this.errors.password && this.errors.password.length);
    }
  },
  methods: {
    submitForm() {
      this.loading = true;
      axios.post('login', {
        username: this.user_login,
        password: this.password,
        remember: this.remember,
      }).then(() => {
        this.loading = false;
        window.location.reload();
      }).catch(error => {
        this.loading = false;
        if (error.response && error.response.data.errors) {
          this.errors = error.response.data.errors;
        }
      })
    }
  }
}
</script>

<style lang="scss">
.stackonet-support-ticket-login {
  max-width: 320px;
  margin-left: auto;
  margin-right: auto;
}
</style>
