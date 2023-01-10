<template>
    <div class="support-tickets-side-nav">
        <div class="support-tickets-side-nav__filters">
            <toggles :boxed-mode="false" :show-divider="false" v-if="!trashedTickets.active">
                <toggle :name="filter.name" :selected="index === 0" :key="filter.name" v-if="filter.options.length"
                        v-for="(filter, index) in filters">
                    <ul class="shapla-status-list shapla-status-list--vertical">
                        <li v-for="_option in filter.options" :key="_option.id" class="shapla-status-list__item"
                            :class="{'is-active':_option.active}">
                            <a href="#" @click.prevent="changeFilter(_option.value,filter.id)"
                               class="shapla-status-list__item-link">
                                <span class="shapla-status-list__item-label">{{_option.label}}</span>
                                <span class="shapla-status-list__item-count">{{_option.count}}</span>
                            </a>
                        </li>
                    </ul>
                </toggle>
            </toggles>
        </div>
    </div>
</template>

<script>
    import {mapState} from 'vuex';
    import {toggles, toggle} from "shapla-toggles";
    import statusList from "shapla-data-table-status";

    export default {
        name: "SupportTicketSideNav",
        components: {toggles, toggle, statusList},
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
            changeFilter(option, filter) {
                this.$store.commit('SET_LABEL', 'active');
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
        }
    }
</script>

<style lang="scss">
    @import "~shapla-color-system/src/variables";

    .support-tickets-side-nav {
        padding: 20px;

        .shapla-toggle-panel__heading,
        .shapla-toggle-panel__content {
            padding-left: 0;
            padding-right: 0;
        }

        .shapla-status-list.shapla-status-list--vertical {
            width: 100%;
        }

        &__filters {
            margin-bottom: 48px;
        }

        &__settings {
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            display: flex;
            align-items: center;
            height: 48px;

            a {
                align-items: center;
                background: #f5f5f5;
                display: flex;
                padding: 10px;
                width: 100%;
                line-height: 24px;
                font-size: 20px;
                text-decoration: none;
                color: $primary;

                svg {
                    fill: currentColor;
                }

                &:hover {
                    background: #f1f1f1;
                }
            }

            &--icon {
                margin-right: 8px;
            }
        }
    }
</style>