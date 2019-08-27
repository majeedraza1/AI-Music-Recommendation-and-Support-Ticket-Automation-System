<template>
	<div :class="classes">
		<slot></slot>
	</div>
</template>

<script>
	export default {
		name: "imageContainer",
		props: {
			square: {type: Boolean, default: false},
		},
		computed: {
			classes() {
				return {
					'shapla-image': true,
					'is-square': this.square
				}
			}
		}
	}
</script>

<style lang="scss">
	@import "image";
</style>
