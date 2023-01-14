<template>
  <div v-if="categories.length">
    <draggable v-model="categories" item-key="term_id" class="list-group" handle=".handle" @update="updateMenuOrder">
      <template #item="{element}">
        <div class="bg-white rounded p-4 shapla-box--role flex w-full content-center shadow-sm">
          <div>
            <strong>{{ element.name }}</strong>
            <span class="extra_info">ID: {{ element.term_id }}</span>
          </div>
          <div class="flex">
            <div class="handle">
              <icon-container>
                <svg height="24" viewBox="0 0 320 512" width="24" xmlns="http://www.w3.org/2000/svg">
                  <path
                      d="M41 288h238c21.4 0 32.1 25.9 17 41L177 448c-9.4 9.4-24.6 9.4-33.9 0L24 329c-15.1-15.1-4.4-41 17-41zm255-105L177 64c-9.4-9.4-24.6-9.4-33.9 0L24 183c-15.1 15.1-4.4 41 17 41h238c21.4 0 32.1-25.9 17-41z"
                      fill="currentColor"></path>
                </svg>
              </icon-container>
            </div>
            <div @click.prevent="editCategory(element)">
              <icon-container>
                <svg height="24" viewBox="0 0 24 24" width="24" xmlns="http://www.w3.org/2000/svg">
                  <path
                      d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/>
                  <path d="M0 0h24v24H0z" fill="none"/>
                </svg>
              </icon-container>
            </div>
            <div @click.prevent="deleteCategory(element)">
              <icon-container>
                <svg height="24" viewBox="0 0 24 24" width="24" xmlns="http://www.w3.org/2000/svg">
                  <path
                      d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM8 9h8v10H8V9zm7.5-5l-1-1h-5l-1 1H5v2h14V4z"/>
                  <path d="M0 0h24v24H0V0z" fill="none"/>
                </svg>
              </icon-container>
            </div>
          </div>
        </div>
      </template>
    </draggable>
  </div>
  <modal :active="showAddCategoryModal" title="Add Category" @close="showAddCategoryModal = false">
    <text-field v-model="categoryName" label="Category"/>
    <div v-if="categoryNameError.length" class="help has-error">{{ categoryNameError }}</div>
    <template v-slot:foot>
      <shapla-button :disabled="categoryName.length < 3" theme="primary" @click="createCategory">
        Create
      </shapla-button>
    </template>
  </modal>

  <modal :active="showEditCategoryModal" title="Edit Category" @close="showEditCategoryModal = false">
    <template v-if="Object.keys(editActiveCategory).length">
      <text-field v-model="editActiveCategory.name" label="Category Name"/>
      <text-field v-model="editActiveCategory.slug" label="Category Slug"/>
      <p v-if="editError.length" class="help has-error" v-html="editError"/>
    </template>
    <template v-slot:foot>
      <shapla-button theme="primary" @click="updateCategory">Update</shapla-button>
    </template>
  </modal>

  <div class="button-add-category-container" title="Add Category">
    <shapla-button :fab="true" size="medium" theme="primary" @click="showAddCategoryModal = true">+
    </shapla-button>
  </div>
</template>

<script>
import {
  ShaplaButton,
  ShaplaIcon as iconContainer,
  ShaplaInput as textField,
  ShaplaModal as modal
} from '@shapla/vue-components'
import draggable from 'vuedraggable'
import {onMounted, reactive, toRefs} from "vue";
import {useStore} from "vuex";

export default {
  name: "TicketCategories",
  components: {iconContainer, ShaplaButton, modal, textField, draggable},
  setup() {
    const store = useStore();
    const state = reactive({
      categories: [],
      showAddCategoryModal: false,
      showEditCategoryModal: false,
      addActiveCategory: {},
      editActiveCategory: {},
      categoryName: '',
      categoryNameError: '',
      editError: '',
    })
    onMounted(() => {
      store.dispatch('getCategories').then(categories => {
        state.categories = categories;
      })
    })

    const createCategory = () => {
      store.dispatch('createCategory', state.categoryName).then(() => {
        state.showAddCategoryModal = false;
        state.categoryName = '';
      })
    }


    function editCategory(category) {
      state.editActiveCategory = category;
      state.showEditCategoryModal = true;
    }

    function updateCategory() {
      store.dispatch('updateCategory', state.editActiveCategory).then(() => {
        state.showEditCategoryModal = false;
        state.editActiveCategory = {};
      })
    }

    function deleteCategory(category) {
      store.dispatch('deleteCategory', category.term_id);
    }

    function updateMenuOrder() {
      store.dispatch('updateCategoryMenuOrder', store.state.categories)
    }

    return {
      ...toRefs(state),
      createCategory,
      editCategory,
      updateCategory,
      deleteCategory,
      updateMenuOrder
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
