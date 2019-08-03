<template>
	<p class="search-box">
		<label class="screen-reader-text" :for="id">{{screenReaderText}}</label>
		<input type="search" class="search-input" :id="id" v-model="query" @input="input">
		<button type="button" class="button" id="search-submit" @click.prevent="submit">{{buttonText}}</button>
	</p>
</template>

<script>
	export default {
		name: "wpSearch",
		props: {
			buttonText: {type: String, required: false, default: 'Search'},
			screenReaderText: {type: String, required: false, default: 'Search:'},
			id: {type: String, required: true,}
		},
		data() {
			return {
				query: '',
			}
		},
		methods: {
			input() {
				this.$emit('input', this.query);
			},
			submit() {
				this.$emit('submit', this.query);
			},
		},
	}
</script>

<style lang="scss">
	.search-input {
		float: left;
		height: 28px;
		margin: 0 4px 0 0;
	}
</style>
