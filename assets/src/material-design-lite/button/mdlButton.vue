<template>
    <button class="mdl-button mdl-js-button"
            :class="getClass"
            :disabled="disabled"
            @click="$emit('click', $event)"
    >
        <slot></slot>
    </button>
</template>

<script>
    import {MaterialButton} from './MaterialButton.js';
    import {MaterialRipple} from '../ripple/MaterialRipple.js';

    export default {
        name: "mdlButton",
        props: {
            ripple: {type: Boolean, default: true},
            disabled: {type: Boolean, default: false},
            type: {
                type: String, default: 'flat', validator: function (value) {
                    return ['raised', 'flat', 'icon'].indexOf(value) !== -1
                }
            },
            color: {
                type: String, default: 'default', validator: function (value) {
                    return ['default', 'primary', 'accent'].indexOf(value) !== -1
                }
            }
        },
        computed: {
            getClass() {
                return {
                    'mdl-js-ripple-effect': this.ripple,
                    'mdl-button--icon': this.type === 'icon',
                    'mdl-button--raised': this.type === 'raised',
                    'mdl-button--colored': this.color === 'primary' && this.type !== 'flat',
                    'mdl-button--primary': this.color === 'primary' && this.type === 'flat',
                    'stackonet-primary': this.color === 'primary' && this.type === 'flat',
                    'stackonet-secondary': this.color === 'primary' && this.type !== 'flat',
                    'mdl-button--accent': this.color === 'accent',
                }
            }
        },
        mounted() {
            new MaterialButton(this.$el);
            if (this.ripple) {
                new MaterialRipple(this.$el);
            }
        }
    }
</script>

<style lang="scss">
    @import "button";
</style>
