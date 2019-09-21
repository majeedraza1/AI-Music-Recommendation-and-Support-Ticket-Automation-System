<template>
    <div>
        <h1 class="wp-heading-inline">Support Agents</h1>
        <div class="clear"></div>
        <data-table
                :columns="columns"
                :rows="agents"
                action-column="display_name"
                index="term_id"
        >
            <template slot="avatar_url" slot-scope="data">
                <img :src="data.row.avatar_url" alt="" width="48" height="48">
            </template>
        </data-table>
        {{roles}}
    </div>
</template>

<script>
    import dataTable from "../../shapla/shapla-data-table/src/dataTable";
    import {CrudMixin} from "../../components/CrudMixin";

    export default {
        name: "AgentsList",
        mixins: [CrudMixin],
        components: {dataTable},
        data() {
            return {
                agents: [],
                roles: [],
                columns: [
                    {key: 'display_name', label: 'Name'},
                    {key: 'email', label: 'Email'},
                    {key: 'role_label', label: 'Role'},
                    {key: 'avatar_url', label: 'Avatar'},
                ],
            }
        },
        mounted() {
            this.$store.commit('SET_LOADING_STATUS', false);
            this.getAgents();
            this.getRoles();
        },
        methods: {
            getAgents() {
                this.get_item('agents').then(data => {
                    this.agents = data.items;
                }).catch(error => {
                    console.log(error);
                })
            },
            getRoles() {
                this.get_item('roles').then(data => {
                    this.roles = data.roles;
                }).catch(error => {
                    console.log(error);
                })
            }
        }
    }
</script>

<style scoped>

</style>