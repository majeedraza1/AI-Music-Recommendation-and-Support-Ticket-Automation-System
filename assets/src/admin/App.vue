<template>
	<div>
		<router-view></router-view>
	</div>
</template>

<script>
    import {mapState} from 'vuex';

    export default {
        name: 'App',
        components: {notification, spinner},
        computed: {
            ...mapState(['loading', 'notification']),
        }
    }
</script>

<style lang="scss">

</style>
