<template>
  <div class="q-px-md q-pt-xs q-pb-xs">
    <div class="row items-center justify-between q-mb-xs">
      <div class="row items-center q-gutter-x-xs">
        <q-icon name="filter_list" color="cyan-3" size="16px" />
        <span class="text-caption text-bold text-cyan-2 uppercase tracking-wide">
          Filter Kelas
        </span>
        <span class="text-caption text-grey-4" style="font-size: 11px;">
          ({{ store.filteredData.length }} Ruang)
        </span>
      </div>

      <!-- Toggle Auto-Cycling untuk Layar TV IGD -->
      <div class="row items-center q-gutter-xs">
        <q-badge
          :color="store.autoCycleKelas ? 'teal-8' : 'grey-8'"
          class="cursor-pointer text-caption q-px-xs q-py-none"
          style="font-size: 10px;"
          @click="store.autoCycleKelas = !store.autoCycleKelas"
        >
          <q-icon :name="store.autoCycleKelas ? 'autorenew' : 'pause'" size="12px" class="q-mr-xs" />
          <span>{{ store.autoCycleKelas ? 'Rotasi Aktif' : 'Rotasi Diam' }}</span>
        </q-badge>
      </div>
    </div>

    <!-- Barisan Glass Tile Kelas Perawatan -->
    <div class="row items-center q-gutter-xs no-wrap scroll-x q-pb-xs">
      <!-- Tombol Semua -->
      <q-card
        class="glass-tile cursor-pointer col-auto"
        :class="{ 'glass-tile-active': store.selectedKelas === 'semua' }"
        flat
        @click="selectKelas('semua')"
      >
        <q-card-section class="q-py-none q-px-sm row items-center q-gutter-x-xs no-wrap" style="height: 28px;">
          <q-icon name="dashboard" size="14px" :color="store.selectedKelas === 'semua' ? 'white' : 'cyan-3'" />
          <span class="text-caption text-bold" style="font-size: 11px;">Semua</span>
        </q-card-section>
      </q-card>

      <!-- Daftar Kelas -->
      <q-card
        v-for="kelas in store.kelasList"
        :key="kelas"
        class="glass-tile cursor-pointer col-auto"
        :class="{ 'glass-tile-active': store.selectedKelas === kelas }"
        flat
        @click="selectKelas(kelas)"
      >
        <q-card-section class="q-py-none q-px-sm row items-center q-gutter-x-xs no-wrap" style="height: 28px;">
          <q-icon
            :name="iconMap[kelas] || 'hotel'"
            size="14px"
            :color="store.selectedKelas === kelas ? 'white' : 'cyan-3'"
          />
          <span class="text-caption text-bold" style="font-size: 11px;">Kelas {{ kelas }}</span>
        </q-card-section>
      </q-card>
    </div>
  </div>
</template>

<script setup>
import { onMounted, onUnmounted, watch } from 'vue'
import { useKetersediaanTtStore } from 'src/stores/display/ketersediaanTt'

const store = useKetersediaanTtStore()

const iconMap = {
  1: 'looks_one',
  2: 'looks_two',
  3: 'looks_3',
  HCU: 'favorite',
  ICCU: 'monitor_heart',
  ICU: 'local_hospital',
  ISO: 'coronavirus',
  NICU: 'baby_changing_station',
  PS: 'star',
  VIP: 'workspace_premium'
}

let cycleInterval = null

function selectKelas (kelas) {
  store.setKelas(kelas)
}

function startAutoCycle () {
  if (cycleInterval) clearInterval(cycleInterval)
  cycleInterval = setInterval(() => {
    if (!store.autoCycleKelas || !store.kelasList.length) return

    const allOptions = ['semua', ...store.kelasList]
    const currentIndex = allOptions.indexOf(store.selectedKelas)
    const nextIndex = (currentIndex + 1) % allOptions.length
    store.setKelas(allOptions[nextIndex])
  }, store.autoCycleInterval)
}

onMounted(() => {
  startAutoCycle()
})

watch(() => store.autoCycleInterval, () => {
  startAutoCycle()
})

watch(() => store.autoCycleKelas, (val) => {
  if (val) startAutoCycle()
  else if (cycleInterval) clearInterval(cycleInterval)
})

onUnmounted(() => {
  if (cycleInterval) clearInterval(cycleInterval)
})
</script>

<style scoped>
.glass-tile {
  /* Warna teal seperti awal */
  background: #143542;
  border: 1px solid rgba(0, 229, 255, 0.25);
  border-radius: 8px;
  color: #e0f7fa;
  transition: all 0.2s ease;
  user-select: none;
}

.glass-tile:hover {
  background: #1a4455;
  border-color: #00e5ff;
  color: #ffffff;
}

.glass-tile-active {
  background: #00838f !important;
  border-color: #00e5ff !important;
  color: #ffffff !important;
  box-shadow: 0 0 10px rgba(0, 229, 255, 0.4);
}

.scroll-x {
  overflow-x: auto;
  scrollbar-width: none;
}

.scroll-x::-webkit-scrollbar {
  display: none;
}
</style>
