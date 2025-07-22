<template>
  <div class="row fit q-col-gutter-md q-px-md">

     <div class="col-4">
      <div class=" q-pa-sm">
        <slot name="video" />
      </div>
    </div>
    <div
      v-for="(item, i) in items"
      :key="i"
      class="col-4"
    >
      <q-card class="modern-card column no-wrap q-pa-none" style="height: 100%;">
        <!-- Header -->
        <div
          :class="`text-white text-center q-pa-md text-bold bg-${colors[i % colors.length]}`"
          style="border-top-left-radius: 12px; border-top-right-radius: 12px;"
        >
          <div :style="`font-size: ${app.txtH5}px;`">{{ item?.panggil_antrian }}</div>
        </div>

        <!-- Body -->
        <div class="col bg-grey-9 text-white q-pa-md flex flex-center column">
          <div :style="`font-size: ${app.txtMd}px;`">Nomor Antrian</div>
          <div class="text-bold" :style="`font-size: ${app.txt70}px; margin-top: -5px`">
            {{ setNoAntrian(item) }}
          </div>
        </div>

        <!-- Footer -->
        <div class="row q-gutter-sm q-pa-sm bg-white" style="border-bottom-left-radius: 12px; border-bottom-right-radius: 12px;">
          <div class="col column flex-center bg-green-2 text-green-10 rounded-borders q-pa-sm">
            <div class="text-bold" :style="`font-size: ${app.txt28}px`">
              {{ getJmlAntrian(item) - getDiterima(item) }}
            </div>
            <div :style="`font-size: ${app.txtSm}px`">Sisa</div>
          </div>
          <div class="col column flex-center bg-brown-2 text-brown-10 rounded-borders q-pa-sm">
            <div class="text-bold" :style="`font-size: ${app.txt28}px`">
              {{ getJmlAntrian(item) }}
            </div>
            <div :style="`font-size: ${app.txtSm}px`">Jumlah</div>
          </div>
        </div>
      </q-card>

    </div>

  </div>
</template>

<script setup>
import { useAppStore } from 'src/stores/app'
import { ref } from 'vue'

const app = useAppStore()

// const idx = ref(1)

const colors = ref([
  'deep-orange',
  'teal',
  'orange',
  'purple',
  'indigo',
  'brown',
  'blue-grey',
  'blue'
])
defineProps({
  items: {
    type: Array,
    default: () => []
  }
})

function setNoAntrian (val) {
  // console.log(val)
  const panggilan = val?.panggilan
  if (panggilan.length) {
    return panggilan[0]?.noantrian
  } else {
    const arr = val?.jumlahkunjunganpoli
    return arr?.length > 0
      ? arr[0]?.noantrian
      : '----'
  }
}

function getJmlAntrian (val) {
  const arr = val?.jumlahkunjunganpoli
  return arr?.length ?? 0
}

function getDiterima (val) {
  const arr = val?.jumlahkunjunganpoli
  return arr?.length > 0
    ? arr.filter(x => x.status === '1' || x.status === '2' || x.status === '3').length
    : 0
}

</script>

<style scoped>
.modern-card {
  border-radius: 12px;
  overflow: hidden;
  transition: transform 0.3s ease, box-shadow 0.3s ease;
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
  background: #fdfdfd;
}
.modern-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 12px 32px rgba(0, 0, 0, 0.15);
}
</style>
