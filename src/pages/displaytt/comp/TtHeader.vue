<template>
  <div class="tt-header-container row items-center justify-between no-wrap q-px-md q-py-xs">
    <!-- Kolom Kiri: Logo & Identitas Rumah Sakit -->
    <div class="row items-center q-gutter-x-sm no-wrap col-grow" style="min-width: 0;">
      <img
        src="~assets/images/logo-rsud.png"
        alt="Logo RSUD"
        class="tt-logo shrink-0"
      />
      <div class="column justify-center overflow-hidden">
        <div class="row items-center q-gutter-x-xs no-wrap">
          <span class="text-subtitle1 text-bold text-white tracking-wide ellipsis" style="font-size: 15px;">
            UOBK RSUD dr. MOHAMAD SALEH
          </span>
          <q-badge color="negative" class="text-bold text-caption q-px-xs q-py-none shrink-0" style="font-size: 10px;">
            IGD
          </q-badge>
        </div>
        <div class="text-caption text-cyan-2 ellipsis" style="font-size: 11px; line-height: 1.1;">
          Display Ketersediaan Tempat Tidur Rawat Inap
        </div>
      </div>
    </div>

    <!-- Kolom Kanan: Tools & Jam Digital -->
    <div class="row items-center justify-end q-gutter-x-sm no-wrap shrink-0 q-pl-sm">
      <!-- Icon Wifi Status Koneksi Server RSUD -->
      <q-btn
        round
        dense
        flat
        :color="store.isError ? 'negative' : 'cyan-3'"
        :icon="store.isError ? 'wifi_off' : 'wifi'"
        class="wifi-btn"
        @click="store.fetchData"
      >
        <q-tooltip class="bg-dark text-white text-caption">
          {{ store.isError ? 'Terputus dari 192.168.150.112:3501 (Klik untuk coba lagi)' : 'Terhubung ke Server RSUD (192.168.150.112:3501)' }}
        </q-tooltip>
      </q-btn>

      <!-- Tombol Refresh -->
      <q-btn
        flat
        round
        dense
        color="cyan-3"
        :icon="store.loading ? 'sync' : 'refresh'"
        :class="{ 'rotate-animation': store.loading }"
        @click="store.fetchData"
      >
        <q-tooltip class="bg-dark text-white text-caption">Refresh Data</q-tooltip>
      </q-btn>

      <!-- Tombol Pengaturan Durasi Rotasi Tab (Jelas & Menonjol dengan Angka Detik) -->
      <q-btn
        flat
        dense
        no-caps
        class="timer-badge-btn q-px-sm"
      >
        <div class="row items-center q-gutter-x-xs no-wrap">
          <q-icon name="timer" size="18px" color="cyan-3" />
          <span class="text-caption text-bold text-cyan-1 font-mono" style="font-size: 13px;">{{ cycleSeconds }}s</span>
        </div>
        <q-tooltip class="bg-dark text-white text-caption">
          Atur durasi rotasi tab (saat ini: {{ cycleSeconds }} detik)
        </q-tooltip>

        <!-- Menu Popover Gelap Solid Kontras Tinggi -->
        <q-menu
          anchor="bottom right"
          self="top right"
          class="bg-blue-grey-10 text-white shadow-10"
          style="background: #0d2732 !important; border: 2px solid #00e5ff !important; border-radius: 14px; min-width: 320px; box-shadow: 0 10px 30px rgba(0,0,0,0.8), 0 0 20px rgba(0, 229, 255, 0.4) !important;"
        >
          <div class="q-pa-md">
            <!-- Header Popover -->
            <div class="row items-center justify-between q-mb-xs">
              <div class="row items-center q-gutter-x-xs">
                <q-icon name="speed" color="cyan-3" size="20px" />
                <span class="text-subtitle2 text-bold text-white uppercase tracking-wider">Durasi Rotasi Tab</span>
              </div>
              <q-badge color="teal-7" text-color="white" class="text-bold text-caption q-px-sm q-py-xs font-mono">
                {{ cycleSeconds }} Detik
              </q-badge>
            </div>

            <div class="text-caption text-cyan-1 q-mb-md" style="font-size: 11px;">
              Pilih interval waktu perpindahan otomatis antar kelas:
            </div>

            <!-- Grid Tombol Pilihan Detik -->
            <div class="row q-col-gutter-xs">
              <div
                v-for="opt in intervalOptions"
                :key="opt.value"
                class="col-4"
              >
                <q-btn
                  unelevated
                  dense
                  no-caps
                  class="full-width q-py-xs text-bold font-mono"
                  :class="store.autoCycleInterval === opt.value ? 'btn-active-timer' : 'btn-inactive-timer'"
                  :label="opt.label"
                  @click="changeInterval(opt.value)"
                />
              </div>
            </div>

            <div class="text-caption text-grey-4 text-center q-mt-md" style="font-size: 10px;">
              * Pengaturan disimpan otomatis untuk display ini
            </div>
          </div>
        </q-menu>
      </q-btn>

      <!-- Tombol Fullscreen -->
      <q-btn
        flat
        round
        dense
        color="cyan-3"
        :icon="isFullscreen ? 'fullscreen_exit' : 'fullscreen'"
        @click="toggleFullscreen"
      >
        <q-tooltip class="bg-dark text-white text-caption">Layar Penuh (F11)</q-tooltip>
      </q-btn>

      <!-- Separator Halus -->
      <div class="header-divider q-mx-xs"></div>

      <!-- Jam Digital & Tanggal (Pasti Kelihatan & Aman dari Tepi Kanan Layar) -->
      <div class="text-right text-white q-pr-xs" style="min-width: 140px;">
        <div class="row items-baseline justify-end text-bold font-mono text-cyan-2" style="font-size: 22px; line-height: 1;">
          <span>{{ jam }}</span>
          <span class="q-mx-xs text-cyan-4 animate-blink">:</span>
          <span>{{ menit }}</span>
          <span class="q-mx-xs text-cyan-4 animate-blink">:</span>
          <span class="text-cyan-4" style="font-size: 17px;">{{ detik }}</span>
        </div>
        <div class="text-caption text-grey-4" style="font-size: 11px; margin-top: 1px;">
          {{ tanggalFormatted }}
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, onUnmounted, ref } from 'vue'
import { useQuasar } from 'quasar'
import { useKetersediaanTtStore } from 'src/stores/display/ketersediaanTt'

const $q = useQuasar()
const store = useKetersediaanTtStore()

const jam = ref('00')
const menit = ref('00')
const detik = ref('00')
const tanggalFormatted = ref('')

let timer = null

const isFullscreen = computed(() => $q.fullscreen?.isActive)

const cycleSeconds = computed(() => Math.round(store.autoCycleInterval / 1000))

const intervalOptions = [
  { label: '5 Detik', value: 5000 },
  { label: '8 Detik', value: 8000 },
  { label: '10 Detik', value: 10000 },
  { label: '12s (Default)', value: 12000 },
  { label: '15 Detik', value: 15000 },
  { label: '20 Detik', value: 20000 },
  { label: '30 Detik', value: 30000 }
]

function changeInterval (val) {
  store.setCycleInterval(val)
}

function updateClock () {
  const now = new Date()
  jam.value = String(now.getHours()).padStart(2, '0')
  menit.value = String(now.getMinutes()).padStart(2, '0')
  detik.value = String(now.getSeconds()).padStart(2, '0')

  const options = { weekday: 'short', day: 'numeric', month: 'short', year: 'numeric' }
  tanggalFormatted.value = now.toLocaleDateString('id-ID', options)
}

function toggleFullscreen () {
  $q.fullscreen.toggle()
}

onMounted(() => {
  updateClock()
  timer = setInterval(updateClock, 1000)
})

onUnmounted(() => {
  if (timer) clearInterval(timer)
})
</script>

<style scoped>
.tt-header-container {
  /* Warna header teal gelap futuristik khas RSUD */
  background: linear-gradient(90deg, #0e2730 0%, #153744 50%, #0e2730 100%);
  border-bottom: 1px solid rgba(0, 229, 255, 0.25);
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.6);
  position: relative;
  z-index: 20;
  width: 100%;
}

.tt-logo {
  width: 44px;
  height: 44px;
  border-radius: 50%;
  border: 2px solid #00e5ff;
  background: #ffffff;
}

.wifi-btn {
  background: rgba(0, 229, 255, 0.12);
  border: 1px solid rgba(0, 229, 255, 0.3);
}

.timer-badge-btn {
  background: rgba(0, 229, 255, 0.15);
  border: 1px solid rgba(0, 229, 255, 0.4);
  border-radius: 20px;
  transition: all 0.2s ease;
}

.timer-badge-btn:hover {
  background: rgba(0, 229, 255, 0.25);
  border-color: #00e5ff;
}

.btn-active-timer {
  background: #00838f !important;
  color: #ffffff !important;
  border: 1.5px solid #00e5ff !important;
  box-shadow: 0 0 10px rgba(0, 229, 255, 0.5) !important;
}

.btn-inactive-timer {
  background: rgba(255, 255, 255, 0.07) !important;
  color: #b2ebf2 !important;
  border: 1px solid rgba(0, 229, 255, 0.2) !important;
}

.btn-inactive-timer:hover {
  background: rgba(0, 229, 255, 0.2) !important;
  color: #ffffff !important;
}

.header-divider {
  width: 1px;
  height: 28px;
  background: rgba(0, 229, 255, 0.25);
}

.animate-blink {
  animation: blink 1s infinite;
}

@keyframes blink {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.2; }
}

.rotate-animation {
  animation: spin 1s linear infinite;
}

@keyframes spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}
</style>
