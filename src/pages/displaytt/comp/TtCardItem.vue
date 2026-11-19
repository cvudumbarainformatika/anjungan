<template>
  <q-card
    class="room-card full-width full-height"
    :class="[`card-size-${size}`]"
    flat
    bordered
  >
    <!-- Background Rotating Glow Animasi -->
    <div class="card-glow" />

    <!-- ======================================================== -->
    <!-- MODE BESAR (Untuk Tab Rincian Kelas dengan Kartu Sedikit) -->
    <!-- ======================================================== -->
    <q-card-section
      v-if="size !== 'sm'"
      class="relative-position full-height flex flex-col justify-between q-pa-md"
      style="z-index: 1;"
    >
      <!-- Header Ruangan -->
      <div>
        <div class="row items-center justify-between no-wrap q-mb-sm">
          <div
            class="text-bold text-white ellipsis text-glow-title"
            :class="size === 'lg' ? 'text-h5' : 'text-h6'"
            :title="item.ruang"
          >
            {{ item.ruang }}
          </div>
          <q-chip
            dense
            :size="size === 'lg' ? 'md' : 'sm'"
            color="cyan-9"
            text-color="cyan-1"
            class="text-weight-bold q-px-sm shrink-0"
          >
            Kelas: {{ item.jenis || item.kelas || '-' }}
          </q-chip>
        </div>

        <!-- Progress Bar Keterisian -->
        <div class="q-my-sm">
          <div class="row justify-between items-center text-grey-3 q-mb-xs">
            <span class="text-caption text-bold tracking-wide" style="font-size: 13px;">
              Keterisian: {{ terisiCount }} / {{ totalCount }} Bed
            </span>
            <span
              class="font-mono text-bold"
              :class="persenTerisi >= 90 ? 'text-red-3' : 'text-cyan-2'"
              style="font-size: 16px;"
            >
              {{ persenTerisi }}%
            </span>
          </div>
          <q-linear-progress
            :value="persenTerisi / 100"
            rounded
            :size="size === 'lg' ? '12px' : '9px'"
            :color="persenTerisi >= 90 ? 'negative' : (persenTerisi >= 70 ? 'warning' : 'positive')"
            track-color="blue-grey-10"
          />
        </div>
      </div>

      <!-- Kotak Statistik Besar: Total, Terisi, dan Kosong yang Sangat Terlihat Jelas -->
      <div class="q-mt-md">
        <div class="row q-col-gutter-sm items-stretch">
          <!-- Box Total -->
          <div class="col-4">
            <div class="stat-mini-box bg-cyan-box border-cyan-subtle text-center q-pa-sm">
              <div class="text-caption text-cyan-2 text-weight-bold uppercase" style="font-size: 11px;">
                Total Bed
              </div>
              <div class="text-h4 font-mono text-bold text-cyan-1 q-mt-xs">
                {{ totalCount }}
              </div>
            </div>
          </div>

          <!-- Box Terisi -->
          <div class="col-4">
            <div class="stat-mini-box bg-red-box border-red-subtle text-center q-pa-sm">
              <div class="text-caption text-red-2 text-weight-bold uppercase" style="font-size: 11px;">
                Terisi
              </div>
              <div class="text-h4 font-mono text-bold text-red-3 q-mt-xs">
                {{ terisiCount }}
              </div>
            </div>
          </div>

          <!-- Box Kosong (Highlight Hijau Besar Menyala) -->
          <div class="col-4">
            <div
              class="stat-mini-box bg-green-box border-green-glow text-center q-pa-sm"
              :class="{ 'animate-glow-box': kosongCount > 0 }"
            >
              <div class="text-caption text-green-2 text-weight-bolder uppercase" style="font-size: 11px;">
                Kosong
              </div>
              <div class="text-h4 font-mono text-bold text-green-3 q-mt-xs">
                {{ kosongCount }}
              </div>
            </div>
          </div>
        </div>
      </div>
    </q-card-section>

    <!-- ======================================================== -->
    <!-- MODE KOMPAK (Untuk Tab "Semua" - 23 Ruangan)             -->
    <!-- ======================================================== -->
    <q-card-section
      v-else
      class="relative-position full-height flex flex-col justify-between q-pa-sm"
      style="z-index: 1;"
    >
      <!-- Header Ruangan -->
      <div>
        <div class="row items-center justify-between no-wrap q-mb-xs">
          <div
            class="text-caption text-bold text-white ellipsis"
            style="font-size: 13px; max-width: 72%;"
            :title="item.ruang"
          >
            {{ item.ruang }}
          </div>
          <q-chip
            dense
            size="xs"
            color="cyan-9"
            text-color="cyan-1"
            class="text-weight-bold q-px-xs shrink-0"
          >
            Kelas: {{ item.jenis || item.kelas || '-' }}
          </q-chip>
        </div>

        <!-- Progress Bar Keterisian -->
        <div class="q-my-xs">
          <div class="row justify-between text-caption text-grey-3 q-mb-none" style="font-size: 10px;">
            <span>Keterisian ({{ terisiCount }} / {{ totalCount }})</span>
            <span :class="persenTerisi >= 90 ? 'text-red-3 text-bold' : 'text-cyan-2 text-bold'">
              {{ persenTerisi }}%
            </span>
          </div>
          <q-linear-progress
            :value="persenTerisi / 100"
            rounded
            size="5px"
            :color="persenTerisi >= 90 ? 'negative' : (persenTerisi >= 70 ? 'warning' : 'positive')"
            track-color="blue-grey-10"
          />
        </div>
      </div>

      <div>
        <q-separator dark class="q-my-xs opacity-20" />

        <!-- Baris Chip Status Tempat Tidur -->
        <div class="row items-center justify-between no-wrap q-gutter-x-xs">
          <!-- Chip Total -->
          <q-chip
            dense
            size="sm"
            color="cyan-9"
            text-color="black"
            class="text-bold q-px-xs col-auto"
          >
            <q-icon name="meeting_room" size="12px" class="q-mr-xs" />
            Total: {{ totalCount }}
          </q-chip>

          <!-- Chip Terisi -->
          <q-chip
            dense
            size="sm"
            color="red-5"
            text-color="white"
            class="text-bold q-px-xs col-auto"
          >
            <q-icon name="person" size="12px" class="q-mr-xs" />
            Terisi: {{ terisiCount }}
          </q-chip>

          <!-- Chip Kosong (Sisa) -->
          <q-chip
            dense
            size="sm"
            :color="kosongCount > 0 ? 'green-5' : 'grey-8'"
            :text-color="kosongCount > 0 ? 'black' : 'grey-4'"
            class="text-bold q-px-xs col-auto"
            :class="{ 'animate-glow-green': kosongCount > 0 }"
          >
            <q-icon name="hotel" size="12px" class="q-mr-xs" />
            Kosong: {{ kosongCount }}
          </q-chip>
        </div>
      </div>
    </q-card-section>
  </q-card>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  item: {
    type: Object,
    required: true
  },
  size: {
    type: String,
    default: 'sm' // 'sm', 'md', 'lg'
  }
})

const totalCount = computed(() => parseInt(props.item?.total) || 0)
const terisiCount = computed(() => parseInt(props.item?.terisi) || 0)
const kosongCount = computed(() => parseInt(props.item?.sisa ?? props.item?.kosong) || 0)

const persenTerisi = computed(() => {
  if (!totalCount.value) return 0
  return Math.min(100, Math.round((terisiCount.value / totalCount.value) * 100))
})
</script>

<style scoped>
.room-card {
  position: relative;
  background: #122e3a;
  border: 1px solid rgba(0, 229, 255, 0.25);
  border-radius: 12px;
  box-shadow: 0 4px 14px rgba(0, 0, 0, 0.4);
  transition: transform 0.2s ease, border-color 0.2s ease, box-shadow 0.2s ease;
  overflow: hidden;
}

/* Ukuran untuk Mode Besar (Tab Kelas Tertentu) */
.card-size-lg {
  border-radius: 18px;
  border: 2px solid rgba(0, 229, 255, 0.4);
  min-height: 230px;
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.6);
}

.card-size-md {
  border-radius: 16px;
  border: 1.5px solid rgba(0, 229, 255, 0.35);
  min-height: 200px;
  box-shadow: 0 6px 20px rgba(0, 0, 0, 0.5);
}

.text-glow-title {
  text-shadow: 0 0 10px rgba(0, 229, 255, 0.4);
}

/* Stat Mini Box untuk Mode Besar */
.stat-mini-box {
  border-radius: 10px;
  backdrop-filter: blur(8px);
}

.bg-cyan-box {
  background: rgba(0, 188, 212, 0.15);
}

.border-cyan-subtle {
  border: 1px solid rgba(0, 229, 255, 0.35);
}

.bg-red-box {
  background: rgba(244, 67, 54, 0.18);
}

.border-red-subtle {
  border: 1px solid rgba(244, 67, 54, 0.4);
}

.bg-green-box {
  background: rgba(0, 230, 118, 0.2);
}

.border-green-glow {
  border: 1.5px solid #00e676;
}

.animate-glow-box {
  box-shadow: 0 0 14px rgba(0, 230, 118, 0.4);
}

.room-card:hover {
  transform: translateY(-2px);
  border-color: #00e5ff;
  box-shadow: 0 4px 18px rgba(0, 229, 255, 0.35);
}

.card-glow {
  content: "";
  position: absolute;
  top: -30%;
  left: -30%;
  width: 140%;
  height: 140%;
  background: radial-gradient(circle at center, rgba(0, 229, 255, 0.09), transparent 70%);
  animation: rotate-glow 12s linear infinite;
  pointer-events: none;
  z-index: 0;
}

@keyframes rotate-glow {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

.opacity-20 {
  opacity: 0.2;
}

.animate-glow-green {
  box-shadow: 0 0 8px rgba(0, 230, 118, 0.5);
}
</style>
