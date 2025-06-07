import { defineStore } from 'pinia';

export const useSearchStore = defineStore('search', {
  state: () => ({
    query: ''
  }),
  actions: {
    updateSearch(newQuery) {
      this.query = newQuery;
    }
  }
});
