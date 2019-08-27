<template>
	<div class="mdl-list-item">
		<span class="mdl-list-item-label">{{label}}</span>
		<span class="mdl-list-item-separator">:</span>
		<span class="mdl-list-item-value"><slot>{{value}}</slot></span>
	</div>
</template>

<script>
	export default {
		name: "ListItem",
		props: ['label', 'value']
	}
</script>

<style lang="scss">

</style>
