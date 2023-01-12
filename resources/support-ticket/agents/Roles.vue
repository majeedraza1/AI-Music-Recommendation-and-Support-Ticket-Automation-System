<template>
  <div>
    <div v-for="role in roles" :key="role.role">
      <div class="bg-white rounded p-4 shapla-box--role flex w-full content-center shadow-sm">
        <div>
          <strong>{{ role.name }}</strong>
        </div>
        <div class="flex">
          <div @click.prevent="editRole(role)">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
              <path
                  d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/>
              <path d="M0 0h24v24H0z" fill="none"/>
            </svg>
          </div>
          <div @click.prevent="deleteRole(role)">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
              <path
                  d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM8 9h8v10H8V9zm7.5-5l-1-1h-5l-1 1H5v2h14V4z"/>
              <path fill="none" d="M0 0h24v24H0V0z"/>
            </svg>
          </div>
        </div>
      </div>
    </div>
    <div class="button-add-role-container" title="Add Role">
      <shapla-button theme="primary" size="medium" :fab="true" @click="showAddRoleModal = true">
        +
      </shapla-button>
    </div>
    <role-editor :value="role" :active="showAddRoleModal" @close="closeAddNewRoleModal" @submit="addNewRole"/>
    <role-editor :value="activeRole" :active="showEditRoleModal" @close="closeEditRoleModal" @submit="updateRole"/>
  </div>
</template>

<script>
import {column, columns, dataTable, modal, shaplaButton, tab, tabs} from 'shapla-vue-components'
import {CrudMixin} from "../../mixins/CrudMixin";
import RoleEditor from "./RoleEditor";

export default {
  name: "AgentsList",
  mixins: [CrudMixin],
  components: {shaplaButton, RoleEditor, dataTable, tabs, tab, modal, columns, column},
  data() {
    return {
      showAddRoleModal: false,
      showEditRoleModal: false,
      users: [],
      roles: [],
      activeRole: {},
      role: {},
      editAgentActiveAgent: {},
    }
  },
  mounted() {
    this.$store.commit('SET_LOADING_STATUS', false);
    this.getAgents();
    this.getRoles();
  },
  computed: {
    actions() {
      return [
        {key: 'edit', label: 'Edit'},
        {key: 'delete', label: 'Delete'}
      ];
    }
  },
  methods: {
    getAgents() {
      this.get_item(StackonetSupportTicket.restRoot + '/agents').then(data => {
        this.agents = data.items;
      }).catch(error => {
        console.log(error);
      })
    },
    getRoles() {
      this.get_item(StackonetSupportTicket.restRoot + '/roles').then(data => {
        this.roles = data.roles;
      }).catch(error => {
        console.log(error);
      })
    },
    closeAddNewRoleModal() {
      this.role = {};
      this.showAddRoleModal = false;
    },
    closeEditRoleModal() {
      this.activeRole = {};
      this.showEditRoleModal = false;
    },
    editRole(role) {
      this.activeRole = role;
      this.showEditRoleModal = true;
    },
    deleteRole(role) {
      this.$dialog.confirm('Are you sure to delete this role?').then(confirm => {
        if (confirm) {
          this.delete_item(StackonetSupportTicket.restRoot + '/role', {params: {role: role.role}}).then(() => {
            this.$delete(this.roles, this.roles.indexOf(role));
          }).catch(error => {
            if (error.response.data.message) {
              this.$store.commit('SET_SNACKBAR', {
                title: 'Error!',
                message: error.response.data.message,
                type: 'error',
              });
            }
          });
        }
      })
    },
    addNewRole(role) {
      this.create_item(StackonetSupportTicket.restRoot + '/roles', role).then(() => {
        this.closeAddNewRoleModal();
        this.$store.commit('SET_SNACKBAR', {
          title: 'Created!',
          message: 'New role has been created.',
          type: 'success',
        });
        this.getRoles();
      }).catch(error => {
        if (error.response.data.message) {
          this.$store.commit('SET_SNACKBAR', {
            title: 'Error!',
            message: error.response.data.message,
            type: 'error',
          });
        }
      })
    },
    updateRole(role) {
      this.update_item(StackonetSupportTicket.restRoot + '/role', role).then(() => {
        this.closeEditRoleModal();
        this.$store.commit('SET_SNACKBAR', {
          title: 'Updated!',
          message: 'Role has been updated.',
          type: 'success',
        });
        this.getRoles();
      }).catch(error => {
        if (error.response.data.message) {
          this.$store.commit('SET_SNACKBAR', {
            title: 'Error!',
            message: error.response.data.message,
            type: 'error',
          });
        }
      })
    },
  }
}
</script>

<style lang="scss">

</style>
