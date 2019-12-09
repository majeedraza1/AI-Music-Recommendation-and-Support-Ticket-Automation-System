<template>
    <div>
        <draggable :list="priorities" class="list-group" handle=".handle" @update="updateMenuOrder">
            <div v-for="_priority in priorities" :key="_priority.term_id">
                <div class="shapla-box shapla-box--role flex w-full content-center mdl-shadow--2dp">
                    <div>
                        <strong>{{_priority.name}}</strong>
                    </div>
                    <div class="flex">
                        <div class="handle">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 320 512">
                                <path fill="currentColor"
                                      d="M41 288h238c21.4 0 32.1 25.9 17 41L177 448c-9.4 9.4-24.6 9.4-33.9 0L24 329c-15.1-15.1-4.4-41 17-41zm255-105L177 64c-9.4-9.4-24.6-9.4-33.9 0L24 183c-15.1 15.1-4.4 41 17 41h238c21.4 0 32.1-25.9 17-41z"></path>
                            </svg>
                        </div>
                        <div @click.prevent="editPriority(_priority)">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                                <path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/>
                                <path d="M0 0h24v24H0z" fill="none"/>
                            </svg>
                        </div>
                        <div @click.prevent="deletePriority(_priority)">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                                <path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM8 9h8v10H8V9zm7.5-5l-1-1h-5l-1 1H5v2h14V4z"/>
                                <path fill="none" d="M0 0h24v24H0V0z"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </draggable>

        <modal :active="showAddPriorityModal" @close="showAddPriorityModal = false" title="Add Priority">
            <animated-input v-model="priorityName" label="Priority"></animated-input>
            <div class="help has-error" v-if="priorityNameError.length">{{priorityNameError}}</div>
            <template slot="foot">
                <shapla-button theme="primary" :disabled="priorityName.length < 3" @click="createPriority">
                    Create
                </shapla-button>
            </template>
        </modal>

        <modal :active="showEditPriorityModal" @close="showEditPriorityModal = false" title="Edit Priority">
            <template v-if="Object.keys(editActivePriority).length">
                <animated-input v-model="editActivePriority.name" label="Priority Name"/>
                <animated-input v-model="editActivePriority.slug" label="Priority Slug"/>
                <p class="help has-error" v-if="editError.length" v-html="editError"></p>
            </template>
            <template slot="foot">
                <shapla-button theme="primary" @click="updatePriority">Update</shapla-button>
            </template>
        </modal>

        <div class="button-add-priority-container" title="Add Priority">
            <shapla-button theme="primary" size="medium" :fab="true" @click="showAddPriorityModal = true">+
            </shapla-button>
        </div>
    </div>
</template>

<script>
    import modal from 'shapla-modal'
    import draggable from 'vuedraggable'
    import {CrudMixin} from "../../mixins/CrudMixin";
    import AnimatedInput from "../../components/AnimatedInput";
    import shaplaButton from "shapla-button";

    export default {
        name: "TicketPriorities",
        components: {shaplaButton, modal, AnimatedInput, draggable},
        mixins: [CrudMixin],
        data() {
            return {
                priorities: [],
                showAddPriorityModal: false,
                showEditPriorityModal: false,
                addActivePriority: {},
                editActivePriority: {},
                priorityName: '',
                priorityNameError: '',
                editError: '',
            }
        },
        mounted() {
            this.$store.commit('SET_LOADING_STATUS', false);
            this.getPriorities();
        },
        methods: {
            getPriorities() {
                this.get_item(StackonetSupportTicket.restRoot + '/priorities').then(data => {
                    this.priorities = data.items;
                }).catch(error => {
                    console.log(error);
                })
            },
            createPriority() {
                this.create_item(StackonetSupportTicket.restRoot + '/priorities', {name: this.priorityName}).then(() => {
                    this.showAddPriorityModal = false;
                    this.getPriorities();
                }).catch(error => {
                    this.priorityNameError = error.response.data.message;
                })
            },
            editPriority(priority) {
                this.editActivePriority = priority;
                this.showEditPriorityModal = true;
            },
            updatePriority() {
                this.editError = '';
                this.create_item(StackonetSupportTicket.restRoot + '/priorities/' + this.editActivePriority.term_id, {
                    name: this.editActivePriority.name,
                    slug: this.editActivePriority.slug,
                }).then(() => {
                    this.showEditPriorityModal = false;
                    this.editActivePriority = {};
                    this.getPriorities();
                }).catch(error => {
                    this.editError = error.response.data.message;
                })
            },
            deletePriority(priority) {
                this.$dialog.confirm('Are you sure to delete this priority?').then(confirm => {
                    if (confirm) {
                        this.delete_item(StackonetSupportTicket.restRoot + '/priorities/' + priority.term_id).then(() => {
                            this.$store.commit('SET_SNACKBAR', {
                                title: 'Success!',
                                message: 'Priority has been deleted?',
                                type: 'success',
                            });
                            this.getPriorities();
                        }).catch(error => {
                            this.priorityNameError = error.response.data.message;
                        })
                    }
                });
            },
            updateMenuOrder() {
                let menu_orders = this.priorities.map(el => el.term_id);
                this.create_item(StackonetSupportTicket.restRoot + '/priorities/batch', {menu_orders: menu_orders}).then(() => {
                    this.$store.commit('SET_SNACKBAR', {
                        title: 'Success!',
                        message: 'Priority orders have been updated.',
                        type: 'success',
                    });
                }).catch(error => {
                    console.log(error.response.data);
                })
            }
        }
    }
</script>

<style lang="scss">
    .button-add-priority-container {
        position: fixed;
        bottom: 1rem;
        right: 1rem;
        z-index: 10;
    }
</style>