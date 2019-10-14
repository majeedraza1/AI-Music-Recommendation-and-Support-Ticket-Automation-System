<template>
    <div class="shapla-toggle-panel" :class="panelClass">
        <div class="shapla-toggle-panel__heading">
            <h4 class="shapla-toggle-panel__title toggle" @click.prevent="toggleActive">
                <span class="shapla-toggle-panel__icon-wrapper">
                    <slot name="icon">
                        <template v-if="isSelected">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                                <path d="M19 13H5v-2h14v2z"/>
                                <path d="M0 0h24v24H0z" fill="none"/>
                            </svg>
                        </template>
                        <template v-if="!isSelected">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                                <path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/>
                                <path d="M0 0h24v24H0z" fill="none"/>
                            </svg>
                        </template>
                    </slot>
                </span>
                <span class="shapla-toggle-panel__title-text">{{name}}</span>
            </h4>
        </div>
        <div class="shapla-toggle-panel__body" :class="panelBodyClass">
            <div class="shapla-toggle-panel__content">
                <slot></slot>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        name: "Toggle",
        props: {
            name: {type: String, required: true},
            selected: {type: Boolean, required: false, default: false}
        },
        data() {
            return {
                isSelected: false,
                panelContent: null,
            }
        },
        computed: {
            panelClass() {
                return {
                    'shapla-toggle-panel--default': true,
                    'shapla-toggle-panel--no-divider': true,
                    'shapla-toggle-panel--boxed-mode': true,
                }
            },
            panelBodyClass() {
                return {
                    'is-active': this.isSelected,
                }
            }
        },
        mounted() {
            this.isSelected = this.selected;

            this.panelContent = this.$el.querySelector('.shapla-toggle-panel__body');
            this.handleSelect();
        },
        methods: {
            toggleActive() {
                this.isSelected = !this.isSelected;
                this.handleSelect();
            },
            handleSelect() {
                if (this.isSelected) {
                    this.panelContent.style.maxHeight = this.panelContent.scrollHeight + "px";
                    setTimeout(() => {
                        this.panelContent.style.maxHeight = null;
                    }, 300);
                } else {
                    this.panelContent.style.maxHeight = this.panelContent.scrollHeight + "px";
                    setTimeout(() => {
                        this.panelContent.style.maxHeight = null;
                    }, 10);
                }
            }
        }
    }
</script>

<style lang="scss">
    .shapla-toggle-panel {
        border: 1px none #eeeeee;
        box-shadow: none;
        font-size: 1rem;

        &--boxed-mode {
            margin-bottom: 1em;
            cursor: pointer;
        }

        &__title {
            display: flex;
            position: relative;
            line-height: 1.5;
            font-size: 1.25em;
            font-weight: 400;
            color: var(--stackonet-ticket-text-secondary);
            margin: 0;
            padding: 0;

            a {
                outline: none;
                align-items: center;
                box-shadow: none;
                display: inline-flex;
                font-size: 1em;
                padding: 0em 1.25em;
                text-decoration: none;
            }

            .shapla-toggle-panel__icon-wrapper {
            }

            svg {
                float: left;
                fill: currentColor;
                overflow: hidden;
                margin-right: 10px;
                width: 1em;
                height: 1em;
            }
        }

        &__title-text {
            color: var(--stackonet-ticket-text-secondary);
            font-size: 16px;
            font-weight: 500;
            line-height: 1.2em;
            margin-bottom: -15px;
        }

        &__body {
            transition: max-height 0.2s ease-out;
            overflow: hidden;

            &:not(.is-active) {
                max-height: 0;
            }
        }

        &__content {
            border: none;
            padding: 10px 25px 15px;
            position: relative;

            &:before,
            &:after {
                display: table;
                content: "";
            }

            &:after {
                clear: both;
            }
        }
    }

    .link-navigation a {
        display: flex !important;
        font-weight: 300;
    }
</style>