<template>
	<span :class="classes"><slot></slot></span>
</template>

<script>
	export default {
		name: "icon",
		props: {
			small: {type: Boolean, default: false},
			medium: {type: Boolean, default: false},
			large: {type: Boolean, default: false},
		},
		computed: {
			classes() {
				return {
					'shapla-icon': true,
					'is-small': this.small,
					'is-medium': this.medium,
					'is-large': this.large,
				}
			}
		}
	}
</script>

<style lang="scss">
	@import "icon";
</style>
