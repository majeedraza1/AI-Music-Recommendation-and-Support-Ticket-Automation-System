<template>
    <div class="support-tickets-side-nav">

        <div v-if="!showSideNav" class="support-tickets-side-nav__back-button">
            <shapla-button theme="primary" @click="backToTicketList"> Back</shapla-button>
        </div>

        <template v-if="showSideNav">
            <toggles :boxed-mode="false" :show-divider="false">
                <toggle :name="filter.name" :selected="index === 0" :key="filter.name"
                        v-if="filter.options.length" v-for="(filter, index) in filters">
                    <span class="support-tickets-side-nav__text" v-for="_option in filter.options"
                          :class="{'is-active':_option.active}" @click="changeFilter(_option.value,filter.id)">
                        <span class="support-tickets-side-nav__label">{{_option.label}}</span>
                        <span class="support-tickets-side-nav__count">{{_option.count}}</span>
                    </span>

                </toggle>
            </toggles>

            <div class="support-tickets-side-nav__item">
                <div class="support-tickets-side-nav__title">
                    <span class="support-tickets-side-nav__icon">
                        <svg xmlns="http://www.w3.org/2000/svg">
                            <use xlink:href="#icon-delete_outline"/>
                        </svg>
                    </span>
                    <span class="support-tickets-side-nav__text" :class="{'is-active':trashedTickets.active}"
                          @click="getTrashedItems">
                        <span class="support-tickets-side-nav__label">{{trashedTickets.name}}</span>
                        <span class="support-tickets-side-nav__count">{{trashedTickets.count}}</span>
                    </span>
                </div>
            </div>

            <div class="support-tickets-side-nav__item">
                <div class="support-tickets-side-nav__title">
                    <span class="support-tickets-side-nav__icon">
                        <svg xmlns="http://www.w3.org/2000/svg">
                            <use xlink:href="#icon-settings"/>
                        </svg>
                    </span>
                    <span class="support-tickets-side-nav__text" @click="gotToSettings">
                        <span class="support-tickets-side-nav__label">Setting</span>
                    </span>
                </div>
            </div>
        </template>

    </div>
</template>

<script>
    import {mapState} from 'vuex';
    import shaplaButton from "shapla-button";
    import {toggles, toggle} from "shapla-toggles";

    export default {
        name: "SupportTicketSideNav",
        components: {toggles, toggle, shaplaButton},
        data() {
            return {
                isSelected: false,
            }
        },
        computed: {
            ...mapState(['filters', 'trashedTickets', 'label', 'showSideNav', 'status', 'category', 'priority', 'agent',
                'currentPage', 'city', 'search']),
        },
        methods: {
            getTrashedItems() {
                this.$store.commit('SET_STATUS', 0);
                this.$store.commit('SET_CATEGORY', 0);
                this.$store.commit('SET_PRIORITY', 0);
                this.$store.commit('SET_AGENT', 0);
                this.$store.commit('SET_LABEL', 'trash');

                this.$store.dispatch('getTickets');
            },
            changeFilter(option, filter) {
                this.$store.commit('SET_LABEL', 'all');
                if (filter === 'status') {
                    this.$store.commit('SET_STATUS', option);
                }
                if (filter === 'category') {
                    this.$store.commit('SET_CATEGORY', option);
                }
                if (filter === 'priority') {
                    this.$store.commit('SET_PRIORITY', option);
                }
                if (filter === 'agent') {
                    this.$store.commit('SET_AGENT', option);
                }

                this.$store.dispatch('getTickets');
            },
            gotToSettings() {
                this.$router.push({name: 'Settings'});
            },
            backToTicketList() {
                this.$router.push({name: 'SupportTicketList'})
            },
        }
    }
</script>

<style lang="scss">
    .support-tickets-side-nav {

    }
</style>