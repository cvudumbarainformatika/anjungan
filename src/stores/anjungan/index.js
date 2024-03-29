import { defineStore } from 'pinia'

export const useAnjunganStore = defineStore('anjungan', {
  state: () => ({
    classes: 0
  }),
  getters: {
    // doubleCount: (state) => state.counter * 2
  },
  actions: {
    changeClasses () {
      this.classes === 0 ? this.classes = 1 : this.classes = 1
    },
    setAwal () {
      this.classes = 0
    }
  }
})
