<template>
    <div class="stackonet-support-ticket-login">
        <form @submit.prevent="submitForm" class="stackonet-support-ticket-login-form">
            <div>
                <animated-input
                        label="Email or Username"
                        autocomplete="username"
                        v-model="user_login"
                        :has-error="hasUserLoginError"
                        :helptext="errors.user_login?errors.user_login[0]:''"
                />
            </div>
            <div>
                <animated-input
                        type="password"
                        label="Password"
                        v-model="password"
                        autocomplete="current-password"
                        :has-error="hasPasswordError"
                        :helptext="errors.password?errors.password[0]:''"
                />
            </div>
            <div class="flex space-between m-b-20">
                <div>
                    <mdl-checkbox v-model="remember">Remember me</mdl-checkbox>
                </div>
                <div><a :href="lostPasswordUrl">Forgot your password?</a></div>
            </div>
            <div>
                <big-button :fullwidth="true" :disabled="!canSubmit">Log in</big-button>
            </div>
        </form>
        <spinner :active="loading"></spinner>
    </div>
</template>

<script>
    import axios from 'axios'
    import spinner from 'shapla-spinner'
    import AnimatedInput from "../../components/AnimatedInput";
    import BigButton from "../../components/BigButton";
    import MdlCheckbox from "../../material-design-lite/checkbox/mdlCheckbox";

    export default {
        name: "Login",
        components: {MdlCheckbox, BigButton, AnimatedInput, spinner},
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