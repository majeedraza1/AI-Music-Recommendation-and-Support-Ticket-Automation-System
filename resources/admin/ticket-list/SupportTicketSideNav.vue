<template>
  <div class="support-tickets-side-nav">
    <div class="support-tickets-side-nav__filters">
      <toggles :boxed-mode="false" :show-divider="false" v-if="!trashedTickets.active">
        <template v-if="filters.length">
          <toggle :name="filter.name" :selected="index === 0" :key="filter.name"
                  v-for="(filter, index) in filters">
            <ul class="shapla-status-list shapla-status-list--vertical">
              <li v-for="_option in filter.options" :key="_option.id" class="shapla-status-list__item"
                  :class="{'is-active':_option.active}">
                <a href="#" @click.prevent="changeFilter(_option.value,filter.id)"
                   class="shapla-status-list__item-link">
                  <span class="shapla-status-list__item-label">{{ _option.label }}</span>
                  <span class="shapla-status-list__item-count">{{ _option.count }}</span>
                </a>
              </li>
            </ul>
          </toggle>
        </template>
      </toggles>
    </div>
  </div>
</template>

<script>
import {useStore} from 'vuex';
import {
  ShaplaTableStatusList as statusList,
  ShaplaToggle as toggle,
  ShaplaToggles as toggles
} from "@shapla/vue-components";
import {computed, reactive, toRefs} from "vue";

export default {
  name: "SupportTicketSideNav",
  components: {toggles, toggle, statusList},
  setup() {
    const store = useStore();
    const state = reactive({
      isSelected: false,
    })

    const changeFilter = (option, filter) => {
      store.commit('SET_LABEL', 'active');
      if (filter === 'status') {
        store.commit('SET_STATUS', option);
      }
      if (filter === 'category') {
        store.commit('SET_CATEGORY', option);
      }
      if (filter === 'priority') {
        store.commit('SET_PRIORITY', option);
      }
      if (filter === 'agent') {
        store.commit('SET_AGENT', option);
      }

      store.dispatch('getTickets');
    }

    return {
      ...toRefs(state),
      filters: computed(() => store.state.filters),
      trashedTickets: computed(() => store.state.trashedTickets),
      label: computed(() => store.state.label),
      showSideNav: computed(() => store.state.showSideNav),
      status: computed(() => store.state.status),
      category: computed(() => store.state.category),
      priority: computed(() => store.state.priority),
      agent: computed(() => store.state.agent),
      currentPage: computed(() => store.state.currentPage),
      city: computed(() => store.state.city),
      search: computed(() => store.state.search),
      changeFilter,
    }
  }
}
</script>

<style lang="scss">
@import "shapla-css/src/colors";

.support-tickets-side-nav {
  padding: 20px;

  .shapla-toggle-panel__heading,
  .shapla-toggle-panel__content {
    padding-left: 0;
    padding-right: 0;
  }

  .shapla-status-list.shapla-status-list--vertical {
    width: 100%;
  }

  &__filters {
    margin-bottom: 48px;
  }

  &__settings {
    position: absolute;
    bottom: 0;
    left: 0;
    width: 100%;
    display: flex;
    align-items: center;
    height: 48px;

    a {
      align-items: center;
      background: #f5f5f5;
      display: flex;
      padding: 10px;
      width: 100%;
      line-height: 24px;
      font-size: 20px;
      text-decoration: none;
      color: $primary;

      svg {
        fill: currentColor;
      }

      &:hover {
        background: #f1f1f1;
      }
    }

    &--icon {
      margin-right: 8px;
    }
  }
}
</style>