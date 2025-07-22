<template>
  <div class="row head items-center">
    <div class="q-mx-md bg--1 col-auto">
      <div class="row items-center">
        <img src="~assets/images/logo-rsud.png" :style="`width:70px; height:70px; border-radius:50%; overflow:hidden;`" class="shadow-4">
        <div class="q-ml-md text-dark">
          <div class="text-weight-bold text-yellow byg" :style="`font-size:${app.txt32}px;`">UOBK RSUD  MOHAMAD SALEH</div>
          <div class=" bg-dark text-white rounded-borders q-px-sm" :style="`font-size:${app.txtMd}px; margin-top:-8px`">Jl. Mayjen Panjaitan No.65 Kota Probolinggo Sukabumi, Kec. Mayangan</div>
          <div class="text-weight-bold byg-light" :style="`font-size: ${app.txt28}; `">KOTA PROBOLINGGO</div>
        </div>
      </div>
    </div>
    <!-- TEMPAT JAM DIGITAL -->
    <!-- <q-space></q-space>
    <div class="q-pt-sm">
        <div class="row justify-end byg-light" style="font-size: 40px; line-height:40px">
          <div class="text-weight-bold">{{ jam }} </div>
          <div class="text-weight-bold q-mx-sm"> : {{ menit }} </div>
          <div class="text-weight-bold q-ml-sm"> : {{ detik }}</div>
        </div>
        <div class="text-right q-mt-sm ">
          <div style="margin-top:-10px;" class="text-caption">{{ tanggal.toDateString() }}</div>
        </div>
    </div> -->

    <div class="col full-width z-top bg-white ">
      <BottomPage />
    </div>
  </div>
</template>

<script setup>
import { useAppStore } from 'src/stores/app'
import BottomPage from './BottomPage.vue'
// import { ref } from 'vue'

const app = useAppStore()
// const date = new Date()
// const tanggal = ref(date)

// const jam = ref(date.getHours() > 9 ? date.getHours() : '0' + date.getHours())
// const menit = ref(date.getMinutes() > 9 ? date.getMinutes() : '0' + date.getMinutes())
// const detik = ref(date.getSeconds() > 9 ? date.getSeconds() : '0' + date.getSeconds())

</script>

<style lang="scss" scoped>

.byg {
  text-shadow: 4px 2px 10px #000000;
}
.byg-light {
  text-shadow: 4px 2px 10px #ffffff;
}
</style>
