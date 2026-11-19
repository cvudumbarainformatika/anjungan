<template>
  <div class="row q-col-gutter-sm q-px-md q-pt-xs q-pb-none">
    <!-- Card Total Kapasitas -->
    <div class="col-6 col-sm-3 col-md-3">
      <q-card class="stat-card border-cyan">
        <q-card-section class="row items-center justify-between no-wrap q-py-xs q-px-sm">
          <div>
            <div class="text-caption text-cyan-2 text-weight-bold uppercase tracking-wider" style="font-size: 10px;">
              Total Kapasitas
            </div>
            <div class="text-h5 text-bold text-white font-mono" style="line-height: 1.1;">
              {{ store.totalBed }}
              <span class="text-caption text-cyan-1 text-weight-regular" style="font-size: 11px;">Bed</span>
            </div>
          </div>
          <div class="stat-icon-wrapper bg-cyan-9 text-cyan-1">
            <q-icon name="meeting_room" size="22px" />
          </div>
        </q-card-section>
      </q-card>
    </div>

    <!-- Card Bed Terisi -->
    <div class="col-6 col-sm-3 col-md-3">
      <q-card class="stat-card border-red">
        <q-card-section class="row items-center justify-between no-wrap q-py-xs q-px-sm">
          <div>
            <div class="text-caption text-red-2 text-weight-bold uppercase tracking-wider" style="font-size: 10px;">
              Bed Terisi (Pasien)
            </div>
            <div class="text-h5 text-bold text-red-2 font-mono" style="line-height: 1.1;">
              {{ store.totalTerisi }}
              <span class="text-caption text-red-1 text-weight-regular" style="font-size: 11px;">Bed</span>
            </div>
          </div>
          <div class="stat-icon-wrapper bg-red-9 text-red-1">
            <q-icon name="airline_seat_individual_suite" size="22px" />
          </div>
        </q-card-section>
      </q-card>
    </div>

    <!-- Card Bed Kosong (Tersedia) -->
    <div class="col-6 col-sm-3 col-md-3">
      <q-card class="stat-card border-emerald highlight-pulse">
        <q-card-section class="row items-center justify-between no-wrap q-py-xs q-px-sm">
          <div>
            <div class="text-caption text-green-2 text-weight-bold uppercase tracking-wider" style="font-size: 10px;">
              Bed Kosong (Tersedia)
            </div>
            <div class="text-h5 text-bold text-green-3 font-mono" style="line-height: 1.1;">
              {{ store.totalKosong }}
              <span class="text-caption text-green-1 text-weight-regular" style="font-size: 11px;">Siap Pakai</span>
            </div>
          </div>
          <div class="stat-icon-wrapper bg-green-9 text-green-1">
            <q-icon name="hotel" size="22px" />
          </div>
        </q-card-section>
      </q-card>
    </div>

    <!-- Card Keterisian (BOR) -->
    <div class="col-6 col-sm-3 col-md-3">
      <q-card class="stat-card border-amber">
        <q-card-section class="row items-center justify-between no-wrap q-py-xs q-px-sm">
          <div>
            <div class="text-caption text-amber-2 text-weight-bold uppercase tracking-wider" style="font-size: 10px;">
              Tingkat Hunian (BOR)
            </div>
            <div class="text-h5 text-bold text-amber-3 font-mono" style="line-height: 1.1;">
              {{ store.borPersen }}%
              <span class="text-caption text-amber-1 text-weight-regular" style="font-size: 11px;">Terpakai</span>
            </div>
          </div>
          <div class="stat-icon-wrapper bg-amber-9 text-amber-1">
            <q-icon name="pie_chart" size="22px" />
          </div>
        </q-card-section>
      </q-card>
    </div>
  </div>
</template>

<script setup>
import { useKetersediaanTtStore } from 'src/stores/display/ketersediaanTt'

const store = useKetersediaanTtStore()
</script>

<style scoped>
.stat-card {
  /* Warna teal gelap seperti screenshot awal yang disukai */
  background: #13313d;
  border-radius: 12px;
  border: 1px solid rgba(0, 229, 255, 0.25);
  box-shadow: 0 4px 14px rgba(0, 0, 0, 0.4);
  transition: transform 0.2s ease;
}

.stat-card:hover {
  transform: translateY(-1px);
}

.border-cyan {
  border-left: 5px solid #00e5ff !important;
}

.border-red {
  border-left: 5px solid #ff5252 !important;
}

.border-emerald {
  border-left: 5px solid #00e676 !important;
}

.border-amber {
  border-left: 5px solid #ffd600 !important;
}

.stat-icon-wrapper {
  width: 42px;
  height: 42px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
}

.highlight-pulse {
  animation: pulse-border 3s infinite;
}

@keyframes pulse-border {
  0%, 100% {
    box-shadow: 0 0 12px rgba(0, 230, 118, 0.3);
  }
  50% {
    box-shadow: 0 0 22px rgba(0, 230, 118, 0.6);
  }
}
</style>
