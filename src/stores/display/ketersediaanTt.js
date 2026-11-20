import { defineStore } from 'pinia'
import axios from 'axios'

function normalizeAndSortRooms (rawList) {
  if (!Array.isArray(rawList)) return []
  return [...rawList]
    .map((item) => {
      let namaRuang = (item.ruang || '').replace(/\s+/g, ' ').trim()
      if (/RUANG\s+WIJAYA\s+KUSUMA\s+PRESIDENTIAL\s+SUITE/i.test(namaRuang)) {
        namaRuang = 'WK PRESIDENTIAL SUITE'
      }
      return {
        ...item,
        ruang: namaRuang
      }
    })
    .sort((a, b) => {
      // Tetap kelompokkan WK PRESIDENTIAL SUITE bersama kelompok WIJAYA KUSUMA
      const keyA = a.ruang.startsWith('WK ') ? a.ruang.replace('WK ', 'RUANG WIJAYA KUSUMA ') : a.ruang
      const keyB = b.ruang.startsWith('WK ') ? b.ruang.replace('WK ', 'RUANG WIJAYA KUSUMA ') : b.ruang
      return keyA.localeCompare(keyB, 'id', { numeric: true, sensitivity: 'base' })
    })
}

function getSavedInterval () {
  try {
    if (typeof window !== 'undefined' && window.localStorage) {
      const val = parseInt(window.localStorage.getItem('display_tt_cycle_interval'), 10)
      if (Number.isFinite(val) && val > 0) return val
    }
  } catch (e) {
    console.warn('localStorage tidak dapat diakses di browser ini:', e)
  }
  return 12000
}

export const useKetersediaanTtStore = defineStore('ketersediaan-tt', {
  state: () => ({
    data: [],
    rawList: [],
    kelasList: [],
    selectedKelas: 'semua',
    loading: false,
    isError: false,
    errorMessage: '',
    lastUpdated: null,
    // Server API SIMRS RSUD
    apiUrl: 'http://192.168.150.112:3501',
    autoCycleKelas: true,
    autoCycleInterval: getSavedInterval(), // default 12 detik aman
    refreshInterval: 30000 // refresh data dari server setiap 30 detik
  }),

  getters: {
    totalBed: (state) => {
      if (!state.rawList?.length) return 0
      return state.rawList.reduce((acc, curr) => acc + (parseInt(curr.total) || 0), 0)
    },
    totalTerisi: (state) => {
      if (!state.rawList?.length) return 0
      return state.rawList.reduce((acc, curr) => acc + (parseInt(curr.terisi) || 0), 0)
    },
    totalKosong: (state) => {
      if (!state.rawList?.length) return 0
      return state.rawList.reduce((acc, curr) => acc + (parseInt(curr.sisa ?? curr.kosong) || 0), 0)
    },
    totalRusak: (state) => {
      if (!state.rawList?.length) return 0
      return state.rawList.reduce((acc, curr) => acc + (parseInt(curr.rusak) || 0), 0)
    },
    persenKosong: (state) => {
      const total = state.rawList.reduce((acc, curr) => acc + (parseInt(curr.total) || 0), 0)
      if (!total) return 0
      const kosong = state.rawList.reduce((acc, curr) => acc + (parseInt(curr.sisa ?? curr.kosong) || 0), 0)
      return Math.round((kosong / total) * 100)
    },
    borPersen: (state) => {
      const total = state.rawList.reduce((acc, curr) => acc + (parseInt(curr.total) || 0), 0)
      if (!total) return 0
      const terisi = state.rawList.reduce((acc, curr) => acc + (parseInt(curr.terisi) || 0), 0)
      return Math.round((terisi / total) * 100)
    },
    filteredData: (state) => {
      if (!state.data?.length) return []
      if (!state.selectedKelas || state.selectedKelas === 'semua') {
        return state.data
      }
      return state.data.filter((item) => (item.jenis ?? item.kelas) === state.selectedKelas)
    }
  },

  actions: {
    setKelas (kelas) {
      this.selectedKelas = kelas
    },

    setCycleInterval (ms) {
      this.autoCycleInterval = ms
      try {
        if (typeof window !== 'undefined' && window.localStorage) {
          window.localStorage.setItem('display_tt_cycle_interval', String(ms))
        }
      } catch (e) {
        console.warn('Gagal menyimpan interval ke localStorage', e)
      }
    },

    async fetchData () {
      this.loading = true
      this.isError = false
      this.errorMessage = ''

      const url = `${this.apiUrl}/api/v4/cektt/ranap`

      try {
        const resp = await axios.get(url, {
          timeout: 10000,
          headers: {
            Accept: 'application/json'
          }
        })

        if (resp.status === 200 && resp.data) {
          const list = resp.data?.tempat_tidur || []
          const sortedList = normalizeAndSortRooms(list)
          this.rawList = sortedList
          this.data = sortedList

          // Ambil daftar jenis/kelas unik dan urutkan
          const uniqueKelas = [...new Set(sortedList.map((item) => item.jenis ?? item.kelas).filter(Boolean))].sort()
          this.kelasList = uniqueKelas
          this.lastUpdated = new Date()
        } else {
          throw new Error('Respon tidak valid dari server API')
        }
      } catch (err) {
        console.error(`Gagal mengambil data dari ${url}:`, err.message)
        this.isError = true
        this.errorMessage = `Gagal memuat data dari ${this.apiUrl}`
      } finally {
        this.loading = false
      }
    }
  }
})
