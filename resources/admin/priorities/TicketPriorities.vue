<template>
  <div>
    <draggable v-model="priorities" class="list-group" handle=".handle" @update="updateMenuOrder" item-key="term_id">
      <template #item="{element}">
        <div :key="element.term_id">
          <div class="bg-white rounded p-4 shapla-box--role flex w-full content-center shadow-sm">
            <div>
              <strong>{{ element.name }}</strong>
              <span class="extra_info">ID: {{ element.term_id }}</span>
            </div>
            <div class="flex">
              <div class="handle">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 320 512">
                  <path fill="currentColor"
                        d="M41 288h238c21.4 0 32.1 25.9 17 41L177 448c-9.4 9.4-24.6 9.4-33.9 0L24 329c-15.1-15.1-4.4-41 17-41zm255-105L177 64c-9.4-9.4-24.6-9.4-33.9 0L24 183c-15.1 15.1-4.4 41 17 41h238c21.4 0 32.1-25.9 17-41z"></path>
                </svg>
              </div>
              <div @click.prevent="editPriority(element)">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                  <path
                      d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/>
                  <path d="M0 0h24v24H0z" fill="none"/>
                </svg>
              </div>
              <div @click.prevent="deletePriority(element)">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                  <path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM8 9h8v10H8V9zm7.5-5l-1-1h-5l-1 1H5v2h14V4z"/>
                  <path fill="none" d="M0 0h24v24H0V0z"/>
                </svg>
              </div>
            </div>
          </div>
        </div>
      </template>
    </draggable>

    <modal :active="showAddPriorityModal" @close="showAddPriorityModal = false" title="Add Priority">
      <text-field v-model="priorityName" label="Priority"/>
      <div class="help has-error" v-if="priorityNameError.length">{{ priorityNameError }}</div>
      <template v-slot:foot>
        <shapla-button theme="primary" :disabled="priorityName.length < 3" @click="createPriority">
          Create
        </shapla-button>
      </template>
    </modal>

    <modal :active="showEditPriorityModal" @close="showEditPriorityModal = false" title="Edit Priority">
      <template v-if="Object.keys(editActivePriority).length">
        <text-field v-model="editActivePriority.name" label="Priority Name"/>
        <text-field v-model="editActivePriority.slug" label="Priority Slug"/>
        <p class="help has-error" v-if="editError.length" v-html="editError"/>
      </template>
      <template v-slot:foot>
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
import {ShaplaButton as shaplaButton, ShaplaInput as textField, ShaplaModal as modal} from '@shapla/vue-components'
import draggable from 'vuedraggable'
import {useStore} from "vuex";
import {onMounted, reactive, toRefs} from "vue";

const store = useStore();

export default {
  name: "TicketPriorities",
  components: {shaplaButton, modal, textField, draggable},
  setup() {
    const store = useStore();
    const state = reactive({
      priorities: [],
      showAddPriorityModal: false,
      showEditPriorityModal: false,
      addActivePriority: {},
      editActivePriority: {},
      priorityName: '',
      priorityNameError: '',
      editError: '',
    })

    onMounted(() => {
      store.dispatch('getPriorities').then(priorities => {
        state.priorities = priorities;
      })
    })

    return {
      ...toRefs(state),

      getPriorities() {
        store.dispatch('getPriorities').then(priorities => {
          state.priorities = priorities;
        })
      },
      editPriority(priority) {
        state.editActivePriority = priority;
        state.showEditPriorityModal = true;
      },
      createPriority() {
        store.dispatch('createPriority', state.priorityName).then(() => {
          state.showAddPriorityModal = false;
        })
      },
      updatePriority() {
        store.dispatch('updatePriority', state.editActivePriority).then(() => {
          state.showEditPriorityModal = false;
          state.editActivePriority = {};
        })
      },
      deletePriority(priority) {
        store.dispatch('deletePriority', priority.term_id);
      },
      updateMenuOrder() {
        store.dispatch('updatePriorityMenuOrder', state.priorities);
      }
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
