<template>
  <q-page class="fullscreen bg-dark">
    <div v-if="!isAuth" >
      <BlockPage :param="queryparam"/>
    </div>
     <div v-else class="fullscreen">
      <BoxAnimation class="absolute" style="z-index: -1;" />
      <AnimationWave class="z--" />
      <div class="full-height full-width ">
        <DisplayPoliPage :key="queryparam" />
      </div>
     </div>
  </q-page>
</template>

<script setup>
import BlockPage from './comp/BlockPage.vue'
import AnimationWave from './comp/AnimationWave.vue'
import BoxAnimation from './comp/BoxAnimation.vue'
import DisplayPoliPage from './DisplayPoliPage.vue'

import { computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { useAuthStore } from 'src/stores/auth/index'

const route = useRoute()
const auth = useAuthStore()
const isAuth = computed(() => auth.user)

const queryparam = computed(() => route.params.name)

onMounted(() => {
  auth.logout()
  // console.log('route', queryparam)
})
</script>

<style lang="scss" scoped>
$grad:#187DC1;

.top-page {
  position: relative;
  text-align: center;
  background: linear-gradient(60deg, $secondary 0%, $primary 100%);

  .inner-top-page {
      width: 100%;
      height:100vh;
      margin:0;
      padding: 0;
    }

}

.login-form {
  width: 50%;
  margin-top: 40px;
  z-index: 2;
}

.bot {
  position: absolute;
  width: 100%;
  height: 5vh;
  bottom: 0;
  background-color: transparent ;
}
.z-- {
  z-index: -1;
  position: absolute;
  width: 100%;
  top: 0;
  height: 30vh;
  transform: scaleY(-1);
}
</style>
