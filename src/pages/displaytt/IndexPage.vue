<template>
  <q-page class="display-tt-page flex flex-col justify-between fullscreen overflow-hidden bg-black">
    <!-- Header Display (Teal Gelap & Responsive) -->
    <TtHeader class="shrink-0" />

    <!-- Konten Tengah Utama -->
    <div class="col full-width flex flex-col overflow-hidden relative-position">
      <!-- Ringkasan Statistik Bed (Kompak & Padat) -->
      <TtStatsSummary class="shrink-0" />

      <!-- Tab Filter Kelas Perawatan -->
      <TtKelasTabs class="shrink-0" />

      <!-- Area Grid Kartu Ruang Perawatan (Rata Tengah Dinamis dengan Animasi Perpindahan) -->
      <div class="col overflow-y-auto q-px-md q-pt-xs q-pb-md scroll-area flex items-center justify-center">
        <!-- State Loading Awal -->
        <div v-if="store.loading && !store.data.length" class="row justify-center items-center full-height q-pa-xl">
          <q-card class="bg-transparent text-center" flat>
            <q-spinner-dots size="50px" color="cyan-3" />
            <div class="text-subtitle1 text-cyan-2 text-bold q-mt-md">Memuat Data Ketersediaan Tempat Tidur...</div>
            <div class="text-caption text-grey-5">Menghubungkan ke Server RSUD ({{ store.apiUrl }})</div>
          </q-card>
        </div>

        <!-- State Error -->
        <div v-else-if="store.isError && !store.data.length" class="row justify-center items-center full-height q-pa-xl">
          <q-card class="stat-error-card q-pa-lg text-center" style="max-width: 480px;" flat bordered>
            <q-icon name="error_outline" size="48px" color="negative" class="q-mb-sm" />
            <div class="text-subtitle1 text-negative text-bold">Gagal Mengambil Data Tempat Tidur</div>
            <div class="text-caption text-grey-4 q-my-sm">
              {{ store.errorMessage || 'Tidak dapat terhubung ke endpoint ketersediaan tempat tidur.' }}
            </div>
            <div class="row justify-center q-gutter-sm q-mt-md">
              <q-btn
                unelevated
                color="primary"
                icon="refresh"
                label="Coba Lagi"
                @click="store.fetchData"
              />
            </div>
          </q-card>
        </div>

        <!-- State Data Kosong Setelah Filter -->
        <div v-else-if="!store.filteredData.length" class="row justify-center items-center full-height q-pa-xl">
          <q-card class="bg-transparent text-center" flat>
            <q-icon name="hotel" size="44px" color="cyan-4" />
            <div class="text-subtitle2 text-grey-4 q-mt-sm">
              Tidak ada data ruang perawatan untuk kelas yang dipilih.
            </div>
          </q-card>
        </div>

        <!-- Wadah Kartu dengan Animasi Slide Halus saat Perpindahan Kelas -->
        <transition name="slide-card-tab" mode="out-in">
          <div
            :key="store.selectedKelas"
            class="rooms-flex-container"
          >
            <div
              v-for="(item, index) in store.filteredData"
              :key="`${item.ruang}-${index}`"
              class="room-card-wrapper"
              :class="cardSizeClass"
              :style="{ '--anim-delay': `${index * 35}ms` }"
            >
              <TtCardItem
                :item="item"
                :size="cardSize"
              />
            </div>
          </div>
        </transition>
      </div>
    </div>

    <!-- Footer Marquee Ticker -->
    <TtFooterTicker class="shrink-0" />
  </q-page>
</template>

<script setup>
import { computed, onMounted, onUnmounted } from 'vue'
import { useKetersediaanTtStore } from 'src/stores/display/ketersediaanTt'
import TtHeader from './comp/TtHeader.vue'
import TtStatsSummary from './comp/TtStatsSummary.vue'
import TtKelasTabs from './comp/TtKelasTabs.vue'
import TtCardItem from './comp/TtCardItem.vue'
import TtFooterTicker from './comp/TtFooterTicker.vue'

const store = useKetersediaanTtStore()

let autoRefreshTimer = null

// Responsif otomatis: jika kartu sedikit (misal kelas 1 / VIP), kartu membesar dan tetap di tengah
const cardSizeClass = computed(() => {
  const count = store.filteredData.length
  if (count <= 3) return 'size-large'
  if (count <= 6) return 'size-medium'
  return 'size-normal'
})

const cardSize = computed(() => {
  const count = store.filteredData.length
  if (count <= 3) return 'lg'
  if (count <= 6) return 'md'
  return 'sm'
})

onMounted(() => {
  store.fetchData()

  // Polling data otomatis setiap 30 detik untuk kebutuhan TV display
  autoRefreshTimer = setInterval(() => {
    store.fetchData()
  }, store.refreshInterval)
})

onUnmounted(() => {
  if (autoRefreshTimer) clearInterval(autoRefreshTimer)
})
</script>

<style scoped>
.display-tt-page {
  /* Latar belakang solid hitam pekat */
  background: #000000 !important;
  color: #ffffff;
  min-height: 100vh;
}

/* Flexbox container agar SEMUA kartu selalu rata tengah, dan baris terakhir juga rata tengah */
.rooms-flex-container {
  display: flex;
  flex-wrap: wrap;
  justify-content: center;
  align-items: stretch;
  gap: 16px;
  width: 100%;
  margin: auto 0;
}

/* 1. Mode Normal (Mode Semua Kelas / Data > 6, misal 23 kartu: 5 kartu per baris, 3 kartu terakhir ada di tengah) */
.size-normal {
  flex: 0 1 calc(20% - 14px);
  min-width: 250px;
  max-width: 320px;
}

/* 2. Mode Medium (Data 4 - 6 kartu, misal Kelas 1: kartu membesar otomatis dengan stat box besar) */
.size-medium {
  flex: 0 1 calc(33.333% - 16px);
  min-width: 360px;
  max-width: 480px;
}

/* 3. Mode Large (Data 1 - 3 kartu, misal VIP / Presidential Suite: kartu membesar gagah di tengah layar) */
.size-large {
  flex: 0 1 520px;
  min-width: 400px;
  max-width: 600px;
}

/* Animasi Perpindahan Antar Tab Kelas (Slide & Fade) */
.slide-card-tab-enter-active,
.slide-card-tab-leave-active {
  transition: all 0.35s cubic-bezier(0.25, 0.8, 0.25, 1);
}

.slide-card-tab-enter-from {
  opacity: 0;
  transform: translateY(22px) scale(0.97);
}

.slide-card-tab-leave-to {
  opacity: 0;
  transform: translateY(-18px) scale(0.98);
}

/* Animasi Muncul Berurutan (Stagger Waterfall Slide) untuk Masing-Masing Kartu */
.room-card-wrapper {
  animation: cardFadeSlide 0.35s cubic-bezier(0.25, 0.8, 0.25, 1) both;
  animation-delay: var(--anim-delay, 0ms);
}

@keyframes cardFadeSlide {
  from {
    opacity: 0;
    transform: translateY(18px) scale(0.96);
  }
  to {
    opacity: 1;
    transform: translateY(0) scale(1);
  }
}

.scroll-area {
  scrollbar-width: thin;
  scrollbar-color: rgba(0, 229, 255, 0.3) transparent;
}

.scroll-area::-webkit-scrollbar {
  width: 6px;
}

.scroll-area::-webkit-scrollbar-track {
  background: transparent;
}

.scroll-area::-webkit-scrollbar-thumb {
  background: rgba(0, 229, 255, 0.3);
  border-radius: 6px;
}

.stat-error-card {
  background: #122e3a;
  border-radius: 12px;
  border: 1px solid rgba(0, 229, 255, 0.25);
}
</style>
