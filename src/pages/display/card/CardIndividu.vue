<template>
  <div style="height:60%">
    <q-card class="my-card"  v-if="!item===null">
      <q-card-section :class="`bg-${colors[7]} text-white`">
        <div class="text-center text-weight-bold" :style="`font-size: ${app.txtXl}px;`">POLI GIGI</div>
        <div class="text-center" :style="`font-size: ${app.txtH5}px; margin-top:-5px;`">PENDAFTARAN PASIEN UMUM</div>
      </q-card-section>

      <q-separator />
      <q-card-section class="">
        <div class="text-center" :style="`font-size: ${app.txtH5}px;`">Nomor Antrian</div>
        <div class="text-center" :style="`font-size: ${app.txt50}px; margin-top:-5px`">A003</div>
      </q-card-section>
      <q-separator />
      <q-card-actions align="center" class="bg-negative text-white">
        <div> Sisa Antrian : - </div>
      </q-card-actions>
    </q-card>
    <q-card class="my-card"  v-else>
      <q-card-section :class="`bg-${colors[7]} text-white`">
        <div class="text-center text-weight-bold" :style="`font-size: ${app.txtXl}px;`">MATA</div>
        <div class="text-center" :style="`font-size: ${app.txtH5}px; margin-top:-5px;`">POLIKLINIK MATA</div>
      </q-card-section>

      <q-separator />
      <q-card-section class="">
        <div class="text-center" :style="`font-size: ${app.txtH5}px;`">Nomor Antrian</div>
        <div class="text-center" :style="`font-size: ${app.txt50}px; margin-top:-5px`">MT015</div>
      </q-card-section>
      <q-separator />
      <q-card-actions align="center" class="bg-negative text-white">
        <div> Sisa Antrian : 7 </div>
      </q-card-actions>
    </q-card>
  </div>

</template>

<script setup>
import { useAppStore } from 'src/stores/app'
import { ref } from 'vue'

const app = useAppStore()

const colors = ref([
  'deep-orange',
  'teal',
  'indigo',
  'purple',
  'orange',
  'brown',
  'blue-grey',
  'blue'
])
defineProps({
  item: {
    type: Object,
    default: null
  }
})
</script>
