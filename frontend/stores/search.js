import { defineStore } from 'pinia';

export const useSearchStore = defineStore('search', {
  state: () => ({
    userId: null,
    searchQuery: '',
  }),
  actions: {
    setUserId(id) {
      this.userId = id;
    },
    updateSearch(query) {
      this.searchQuery = query;
    },
  },
});