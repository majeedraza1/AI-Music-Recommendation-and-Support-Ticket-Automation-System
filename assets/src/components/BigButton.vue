<template>
	<div :class="wrapperClass">
		<button class="big-button" @click="handleClick" :disabled="disabled">
			<slot>{{text}}</slot>
		</button>
	</div>
</template>

<script>
	export default {
		name: "BigButton",
		props: {
			text: {type: String, default: 'Submit'},
			disabled: {type: Boolean, default: false},
			fullwidth: {type: Boolean, default: false},
		},
		computed: {
			wrapperClass() {
				return {
					'big-button-wrapper': true,
					'is-fullwidth': this.fullwidth,
				}
			}
		},
		methods: {
			handleClick() {
				this.$emit('click');
			}
		}
	}
</script>

<style lang="scss">
	.big-button-wrapper {
		align-items: center;
		display: flex;
		justify-content: center;

		.big-button {
			background-color: #f58730;
			border-radius: 5px;
			border-width: 0;
			color: #fff;
			cursor: pointer;
			font-size: 18px;
			line-height: 1.2 !important;
			padding: 1em;
			text-align: center;
			transition: all .4s ease;
			width: 100%;

			&:disabled {
				background-color: #e1e8ec;
				color: #9db2bf;
				cursor: not-allowed;
			}
		}

		&:not(.is-fullwidth) .big-button {
			width: 80%;
		}
	}
</style>
