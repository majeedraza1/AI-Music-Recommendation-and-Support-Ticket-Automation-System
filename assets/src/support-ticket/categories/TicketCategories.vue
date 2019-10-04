<template>
    <div>
        <h1 class="wp-heading-inline">Categories</h1>
        <hr class="wp-header-end"/>

        <draggable :list="categories" class="list-group" handle=".handle" @update="updateMenuOrder">
            <div v-for="_category in categories" :key="_category.term_id">
                <div class="shapla-box shapla-box--role flex w-full content-center mdl-shadow--2dp">
                    <div>
                        <strong>{{_category.name}}</strong>
                        <span class="extra_info">ID: {{_category.term_id}}</span>
                    </div>
                    <div class="flex">
                        <div class="handle">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 320 512">
                                <path fill="currentColor"
                                      d="M41 288h238c21.4 0 32.1 25.9 17 41L177 448c-9.4 9.4-24.6 9.4-33.9 0L24 329c-15.1-15.1-4.4-41 17-41zm255-105L177 64c-9.4-9.4-24.6-9.4-33.9 0L24 183c-15.1 15.1-4.4 41 17 41h238c21.4 0 32.1-25.9 17-41z"></path>
                            </svg>
                        </div>
                        <div @click.prevent="editCategory(_category)">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                                <path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/>
                                <path d="M0 0h24v24H0z" fill="none"/>
                            </svg>
                        </div>
                        <div @click.prevent="deleteCategory(_category)">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                                <path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM8 9h8v10H8V9zm7.5-5l-1-1h-5l-1 1H5v2h14V4z"/>
                                <path fill="none" d="M0 0h24v24H0V0z"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </draggable>

        <modal :active="showAddCategoryModal" @close="showAddCategoryModal = false" title="Add Category">
            <animated-input v-model="categoryName" label="Category"></animated-input>
            <div class="help has-error" v-if="categoryNameError.length">{{categoryNameError}}</div>
            <template slot="foot">
                <mdl-button type="raised" color="primary" :disabled="categoryName.length < 3" @click="createCategory">
                    Create
                </mdl-button>
            </template>
        </modal>

        <modal :active="showEditCategoryModal" @close="showEditCategoryModal = false" title="Edit Category">
            <template v-if="Object.keys(editActiveCategory).length">
                <animated-input v-model="editActiveCategory.name" label="Category Name"></animated-input>
                <animated-input v-model="editActiveCategory.slug" label="Category Slug"></animated-input>
                <p class="help has-error" v-if="editError.length" v-html="editError"></p>
            </template>
            <template slot="foot">
                <mdl-button type="raised" color="primary" @click="updateCategory">Update</mdl-button>
            </template>
        </modal>

        <div class="button-add-category-container" title="Add Category">
            <mdl-fab @click="showAddCategoryModal = true">+</mdl-fab>
        </div>
    </div>
</template>

<script>
    import modal from 'shapla-modal'
    import draggable from 'vuedraggable'
    import {CrudMixin} from "../../components/CrudMixin";
    import MdlFab from "../../material-design-lite/button/mdlFab";
    import AnimatedInput from "../../components/AnimatedInput";
    import MdlButton from "../../material-design-lite/button/mdlButton";

    export default {
        name: "TicketCategories",
        components: {MdlButton, MdlFab, modal, AnimatedInput, draggable},
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
                this.get_item('categories').then(data => {
                    this.categories = data.items;
                }).catch(error => {
                    console.log(error);
                })
            },
            createCategory() {
                this.create_item('categories', {name: this.categoryName}).then(() => {
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
                this.create_item('categories/' + this.editActiveCategory.term_id, {
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
                this.$modal.confirm('Are you sure to delete this category?').then(confirm => {
                    if (confirm) {
                        this.delete_item('categories/' + category.term_id).then(() => {
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
                this.create_item('categories/batch', {menu_orders: menu_orders}).then(() => {
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