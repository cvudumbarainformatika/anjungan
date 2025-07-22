<template>
  <div>
    <Vue3Marquee
      :duration="50"
      :gradient="true"
      :gradientColor="[255, 255, 255]"
      :gradientWidth="`10%`"
    >
      <span
        v-for="(word, index) in helloArray"
        :key="index"
        class="word-style text-weight-bold"
        :class="{ word: true, odd: index % 2 === 0, even: index % 2 === 1 }"
      >
        {{ word }}
      </span>
    </Vue3Marquee>
  </div>
</template>

<script setup>
import { ref } from 'vue'

const helloArray = ref(
  [
    'KAMI SEGENAP KARYAWAN UOBK RSUD MOHAMAD SALEH MENGUCAPKAN TERIMAKASIH ANDA TELAH BERSEDIA MENUNGGU ... '
  ]
)

</script>

<style lang="scss" scoped>
.word {
  font-size: 30px;
  margin: 0 10px;
}
.odd {
  color: rgb(68, 77, 84);
}
.even {
  color: rgb(137, 147, 156);
}
</style>
