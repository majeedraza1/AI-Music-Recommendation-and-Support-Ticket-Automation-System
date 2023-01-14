<template>
  <div>
    <draggable v-model="statuses" item-key="term_id" class="list-group" handle=".handle" @update="updateMenuOrder">
      <template #item="{element}">
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
            <div @click.prevent="editStatus(element)">
              <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                <path
                    d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/>
                <path d="M0 0h24v24H0z" fill="none"/>
              </svg>
            </div>
            <div @click.prevent="deleteStatus(element)">
              <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                <path
                    d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM8 9h8v10H8V9zm7.5-5l-1-1h-5l-1 1H5v2h14V4z"/>
                <path fill="none" d="M0 0h24v24H0V0z"/>
              </svg>
            </div>
          </div>
        </div>
      </template>
    </draggable>

    <modal :active="showAddStatusModal" @close="showAddStatusModal = false" title="Add Status">
      <text-field v-model="statusName" label="Status"/>
      <div class="help has-error" v-if="statusNameError.length">{{ statusNameError }}</div>
      <text-field label="Color Hex code" v-model="statusColor"/>
      <template v-slot:foot>
        <shapla-button theme="primary" :disabled="statusName.length < 3" @click="createStatus">
          Create
        </shapla-button>
      </template>
    </modal>

    <modal :active="showEditStatusModal" @close="showEditStatusModal = false" title="Edit Status">
      <template v-if="Object.keys(editActiveStatus).length">
        <text-field v-model="editActiveStatus.name" label="Status Name"/>
        <text-field v-model="editActiveStatus.slug" label="Status Slug"/>
        <text-field v-model="editActiveStatus.color" label="Color Hex code"/>
        <p class="help has-error" v-if="editError.length" v-html="editError"/>
      </template>
      <template v-slot:foot>
        <shapla-button theme="primary" @click="updateStatus">Update</shapla-button>
      </template>
    </modal>

    <div class="button-add-status-container" title="Add Status">
      <shapla-button theme="primary" :fab="true" size="medium" @click="showAddStatusModal = true">+
      </shapla-button>
    </div>
  </div>
</template>

<script>
import {ShaplaButton, ShaplaInput as textField, ShaplaModal as modal} from '@shapla/vue-components'
import draggable from 'vuedraggable'
import {computed, onMounted, reactive, toRefs} from "vue";
import {useStore} from "vuex";

export default {
  name: "TicketStatuses",
  components: {ShaplaButton, modal, textField, draggable},
  setup() {
    const store = useStore();
    const state = reactive({
      showAddStatusModal: false,
      showEditStatusModal: false,
      addActiveStatus: {},
      editActiveStatus: {},
      statusName: '',
      statusNameError: '',
      statusColor: '',
      editError: '',
    });

    onMounted(() => {
      store.dispatch('getStatuses');
    })

    return {
      ...toRefs(state),
      statuses: computed(() => store.state.statuses),
      editStatus(status) {
        state.editActiveStatus = status;
        state.showEditStatusModal = true;
      },
      createStatus() {
        store.dispatch('createStatus', {name: state.statusName, color: state.statusColor}).then(() => {
          state.showAddStatusModal = false;
        });
      },
      updateStatus() {
        store.dispatch('updateStatus', state.editActiveStatus).then(() => {
          this.showEditStatusModal = false;
          this.editActiveStatus = {};
        });
      },
      deleteStatus(status) {
        store.dispatch('deleteStatus', status.term_id)
      },
      updateMenuOrder() {
        store.dispatch('updateStatusMenuOrder', store.state.statuses);
      }
    }
  }
}
</script>

<style lang="scss">
.button-add-status-container {
  position: fixed;
  bottom: 1rem;
  right: 1rem;
  z-index: 10;
}
</style>
