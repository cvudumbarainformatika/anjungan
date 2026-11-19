<template>
  <div class="tt-footer-ticker row items-center no-wrap q-px-md">
    <!-- Label Badge Ticker -->
    <div class="col-auto q-mr-md z-top">
      <q-badge color="teal-9" class="text-bold text-caption q-px-sm q-py-xs shadow-2 flex items-center q-gutter-xs">
        <q-icon name="campaign" size="16px" />
        <span>INFO IGD</span>
      </q-badge>
    </div>

    <!-- Ticker Running Text -->
    <div class="col ticker-wrapper overflow-hidden">
      <div class="ticker-content text-caption text-cyan-1">
        <span>
          Selamat Datang di UOBK RSUD dr. Mohamad Saleh Kota Probolinggo • Informasi Ketersediaan Tempat Tidur Rawat Inap diperbarui secara otomatis setiap 30 detik • Untuk informasi lebih lanjut mengenai alur rawat inap dari IGD, silakan hubungi petugas admisi IGD •
        </span>
        <span v-if="lastUpdatedText" class="text-warning text-bold q-ml-sm">
          [Terakhir Diperbarui: {{ lastUpdatedText }}]
        </span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { useKetersediaanTtStore } from 'src/stores/display/ketersediaanTt'

const store = useKetersediaanTtStore()

const lastUpdatedText = computed(() => {
  if (!store.lastUpdated) return ''
  const d = new Date(store.lastUpdated)
  return d.toLocaleTimeString('id-ID')
})
</script>

<style scoped>
.tt-footer-ticker {
  height: 36px;
  background: #000000;
  border-top: 2px solid #16202c;
  box-shadow: 0 -4px 15px rgba(0, 0, 0, 0.8);
}

.ticker-wrapper {
  position: relative;
  white-space: nowrap;
}

.ticker-content {
  display: inline-block;
  white-space: nowrap;
  padding-left: 100%;
  animation: ticker-slide 35s linear infinite;
}

@keyframes ticker-slide {
  0% {
    transform: translate3d(0, 0, 0);
  }
  100% {
    transform: translate3d(-100%, 0, 0);
  }
}
</style>
