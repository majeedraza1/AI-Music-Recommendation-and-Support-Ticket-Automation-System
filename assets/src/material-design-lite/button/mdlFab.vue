<template>
	<button class="mdl-button mdl-js-button mdl-button--fab"
			:class="getClasses"
			:disabled="disabled"
			@click="$emit('click')"
	>
		<slot></slot>
	</button>
</template>

<script>
	import {MaterialButton} from './MaterialButton.js';
	import {MaterialRipple} from '../ripple/MaterialRipple.js';

	export default {
		name: "mdlFab",
		props: {
			ripple: {type: Boolean, default: true},
			colored: {type: Boolean, default: true},
			disabled: {type: Boolean, default: false},
			mini: {type: Boolean, default: false},
		},
		data() {
			return {}
		},
		computed: {
			getClasses() {
				return {
					'mdl-button--colored': this.colored,
					'mdl-js-ripple-effect': this.ripple,
					'mdl-button--mini-fab': this.mini,
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
