<template>
    <div>
        <draggable :list="categories" class="list-group" handle=".handle" @update="updateMenuOrder">
            <div v-for="_category in categories" :key="_category.term_id">
                <div class="shapla-box shapla-box--role flex w-full content-center mdl-shadow--2dp">
                    <div>
                        <strong>{{_category.name}}</strong>
                        <span class="extra_info">ID: {{_category.term_id}}</span>
                    </div>
                    <div class="flex">
                        <div class="handle">
                            <icon>
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 320 512">
                                    <path fill="currentColor"
                                          d="M41 288h238c21.4 0 32.1 25.9 17 41L177 448c-9.4 9.4-24.6 9.4-33.9 0L24 329c-15.1-15.1-4.4-41 17-41zm255-105L177 64c-9.4-9.4-24.6-9.4-33.9 0L24 183c-15.1 15.1-4.4 41 17 41h238c21.4 0 32.1-25.9 17-41z"></path>
                                </svg>
                            </icon>
                        </div>
                        <div @click.prevent="editCategory(_category)">
                            <icon>
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                                    <path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/>
                                    <path d="M0 0h24v24H0z" fill="none"/>
                                </svg>
                            </icon>
                        </div>
                        <div @click.prevent="deleteCategory(_category)">
                            <icon>
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                                    <path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM8 9h8v10H8V9zm7.5-5l-1-1h-5l-1 1H5v2h14V4z"/>
                                    <path fill="none" d="M0 0h24v24H0V0z"/>
                                </svg>
                            </icon>
                        </div>
                    </div>
                </div>
            </div>
        </draggable>

        <modal :active="showAddCategoryModal" @close="showAddCategoryModal = false" title="Add Category">
            <animated-input v-model="categoryName" label="Category"/>
            <div class="help has-error" v-if="categoryNameError.length">{{categoryNameError}}</div>
            <template slot="foot">
                <shapla-button theme="primary" :disabled="categoryName.length < 3" @click="createCategory">
                    Create
                </shapla-button>
            </template>
        </modal>

        <modal :active="showEditCategoryModal" @close="showEditCategoryModal = false" title="Edit Category">
            <template v-if="Object.keys(editActiveCategory).length">
                <animated-input v-model="editActiveCategory.name" label="Category Name"/>
                <animated-input v-model="editActiveCategory.slug" label="Category Slug"/>
                <p class="help has-error" v-if="editError.length" v-html="editError"></p>
            </template>
            <template slot="foot">
                <shapla-button theme="primary" @click="updateCategory">Update</shapla-button>
            </template>
        </modal>

        <div class="button-add-category-container" title="Add Category">
            <shapla-button theme="primary" size="medium" :fab="true" @click="showAddCategoryModal = true">+
            </shapla-button>
        </div>
    </div>
</template>

<script>
    import modal from 'shapla-modal'
    import draggable from 'vuedraggable'
    import {CrudMixin} from "../../mixins/CrudMixin";
    import AnimatedInput from "../../shapla/shapla-animated-input/AnimatedInput";
    import shaplaButton from "shapla-button";
    import Icon from "../../shapla/icon/icon";

    export default {
        name: "TicketCategories",
        components: {Icon, shaplaButton, modal, AnimatedInput, draggable},
        mixins: [CrudMixin],
        data() {
            return {
                categories: [],
                showAddCategoryModal: false,
                showEditCategoryModal: false,
                addActiveCategory: {},
                editActiveCategory: {},
                categoryName: '',
                categoryNameError: '',
                editError: '',
            }
        },
        mounted() {
            this.$store.commit('SET_LOADING_STATUS', false);
            this.getCategories();
        },
        methods: {
            getCategories() {
                this.get_item(StackonetSupportTicket.restRoot + '/categories').then(data => {
                    this.categories = data.items;
                }).catch(error => {
                    console.log(error);
                })
            },
            createCategory() {
                this.create_item(StackonetSupportTicket.restRoot + '/categories', {name: this.categoryName}).then(() => {
                    this.showAddCategoryModal = false;
                    this.getCategories();
                }).catch(error => {
                    this.categoryNameError = error.response.data.message;
                })
            },
            editCategory(category) {
                this.editActiveCategory = category;
                this.showEditCategoryModal = true;
            },
            updateCategory() {
                this.editError = '';
                this.create_item(StackonetSupportTicket.restRoot + '/categories/' + this.editActiveCategory.term_id, {
                    name: this.editActiveCategory.name,
                    slug: this.editActiveCategory.slug,
                }).then(() => {
                    this.showEditCategoryModal = false;
                    this.editActiveCategory = {};
                    this.getCategories();
                }).catch(error => {
                    this.editError = error.response.data.message;
                })
            },
            deleteCategory(category) {
                this.$dialog.confirm('Are you sure to delete this category?').then(confirm => {
                    if (confirm) {
                        this.delete_item(StackonetSupportTicket.restRoot + '/categories/' + category.term_id).then(() => {
                            this.$store.commit('SET_SNACKBAR', {
                                title: 'Success!',
                                message: 'Category has been deleted?',
                                type: 'success',
                            });
                            this.getCategories();
                        }).catch(error => {
                            this.categoryNameError = error.response.data.message;
                        })
                    }
                });
            },
            updateMenuOrder() {
                let menu_orders = this.categories.map(el => el.term_id);
                this.create_item(StackonetSupportTicket.restRoot + '/categories/batch', {menu_orders: menu_orders}).then(() => {
                    this.$store.commit('SET_SNACKBAR', {
                        title: 'Success!',
                        message: 'Category orders have been updated.',
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
    .button-add-category-container {
        position: fixed;
        bottom: 1rem;
        right: 1rem;
        z-index: 10;
    }

    .extra_info {
        font-size: .875em;
        font-style: italic;
        margin-left: 1em;
    }
</style>