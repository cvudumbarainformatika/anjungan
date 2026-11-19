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

const SAVED_INTERVAL = typeof window !== 'undefined' ? parseInt(localStorage.getItem('display_tt_cycle_interval') || '12000', 10) : 12000

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
    // Sumber endpoint: 'lokal' (api.laborat docker di localhost:8080) atau 'xenter' (https://xenter.my.id)
    source: 'lokal',
    localUrl: 'http://localhost:8080',
    onlineUrl: 'https://xenter.my.id',
    autoCycleKelas: true,
    autoCycleInterval: Number.isFinite(SAVED_INTERVAL) && SAVED_INTERVAL > 0 ? SAVED_INTERVAL : 12000, // default 12 detik
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
    getBaseUrl () {
      return this.source === 'lokal' ? this.localUrl : this.onlineUrl
    },

    setSource (source) {
      this.source = source
      this.fetchData()
    },

    setKelas (kelas) {
      this.selectedKelas = kelas
    },

    setCycleInterval (ms) {
      this.autoCycleInterval = ms
      try {
        localStorage.setItem('display_tt_cycle_interval', String(ms))
      } catch (e) {
        console.warn('Gagal menyimpan interval ke localStorage', e)
      }
    },

    async fetchData () {
      this.loading = true
      this.isError = false
      this.errorMessage = ''

      const baseUrl = this.getBaseUrl()
      const url = `${baseUrl}/api/v4/cektt/ranap`

      try {
        const resp = await axios.get(url, {
          timeout: 8000,
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
          throw new Error('Respon tidak valid dari server')
        }
      } catch (err) {
        console.warn(`Gagal mengambil data dari ${url}:`, err.message)

        // Jika sumber lokal gagal, coba auto fallback ke online
        if (this.source === 'lokal') {
          console.info('Mencoba fallback otomatis ke server online xenter.my.id...')
          try {
            const fallbackResp = await axios.get(`${this.onlineUrl}/api/v4/cektt/ranap`, {
              timeout: 8000,
              headers: { Accept: 'application/json' }
            })
            if (fallbackResp.status === 200 && fallbackResp.data) {
              const list = fallbackResp.data?.tempat_tidur || []
              const sortedList = normalizeAndSortRooms(list)
              this.rawList = sortedList
              this.data = sortedList
              const uniqueKelas = [...new Set(sortedList.map((item) => item.jenis ?? item.kelas).filter(Boolean))].sort()
              this.kelasList = uniqueKelas
              this.lastUpdated = new Date()
              this.errorMessage = 'Data diambil dari server cadangan (xenter.my.id)'
              return
            }
          } catch (fallbackErr) {
            console.error('Fallback ke server online juga gagal:', fallbackErr.message)
          }
        }

        this.isError = true
        this.errorMessage = err.message || 'Gagal memuat data tempat tidur'
      } finally {
        this.loading = false
      }
    }
  }
})
