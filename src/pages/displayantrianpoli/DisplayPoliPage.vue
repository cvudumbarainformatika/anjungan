<template>
  <div class="column full-height full-width">
    <!-- <div class="col-auto q-pa-sm opa" >
      <HeaderComp />
    </div> -->
    <div class="col break">
      <div class="row full-height ">
        <div class="col-auto full-height" style="width:70vw;">
          <div class="column full-height q-pr-md relative-position" >
            <div
                class="square"
                style="--i:0;"
              />
              <div
                class="square"
                style="--i:1;"
              />
              <div
                class="square"
                style="--i:2;"
              />
              <div
                class="square"
                style="--i:3;"
              />
              <div
                class="square"
                style="--i:4;"
              />
            <div class="col-auto q-pa-sm opa">
               <HeaderComp />
            </div>
            <div class="col full-height ">
              <CardcounterComp :items="display.items" />
            </div>

          </div>
        </div>
        <div class="col break q-mb-md relative-position">
          <div class="full-height full-width absolute">
            <div class="column full-height relative-position">
              <div class="col full-height q-pb-md">
                <WeatherComp />
              </div>
              <div class="col-auto bg-white relative-position" style="height: 40%;">
                <VideoComp :videos="video.videos" :loading="video.loading" class="z-top" />
              </div>
            </div>
            <!-- <div class="col-auto">
              <VideoComp :videos="video.videos" :loading="video.loading" class="" />
            </div> -->
          </div>
        </div>
      </div>
    </div>
    <div class="col-auto  bg-white z-top">
      <BottomPage />
    </div>
  </div>
</template>

<script setup>
import WeatherComp from './comp/WeatherComp.vue'
import BottomPage from './comp/BottomPage.vue'
import VideoComp from './comp/VideoComp.vue'
import HeaderComp from './comp/HeaderComp.vue'
import CardcounterComp from './comp/CardcounterComp.vue'
import { onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { useVideoStore } from 'src/stores/video'
import { useDisplayStore } from 'src/stores/display'
import { laravelEcho } from 'src/modules/websocket'

const route = useRoute()
const video = useVideoStore()
const display = useDisplayStore()

onMounted(() => {
  video.getData()
  display.getData(route?.params?.name)
  display.get_weather()

  subscribedChannel()
})

function subscribedChannel () {
  const kdDisplay = route?.params?.name
  if (kdDisplay) {
    // const channel = laravelEcho.join('presence.chat.display' + route?.params?.name)
    // channel.here((users) => {
    //   // usersOnline.value = [...users]
    //   console.log(`subscribed display${route?.params?.name} channel`)
    // })
    //   .joining((user) => {
    //     console.log({ user }, 'joined')
    //   })
    //   .leaving((user) => {
    //     console.log({ user }, 'leaving')
    //   })
    //   .listen('.chat-message', (e) => {
    //     console.log('listen', e)
    //   // const thumb = [...chatMessages.value]
    //   // if (e.message !== null || e.message !== '') { thumb.push(e.message) }
    //   // chatMessages.value = thumb
    //   })
    const channel = laravelEcho.private('private.notif.display' + kdDisplay)
    channel.subscribed(() => {
      console.log(`subscribed private.notif.display${kdDisplay} channel !!!`)
    }).listen('.notif-message', (e) => {
      console.log(`listen notif${kdDisplay}`, e)
      display.getData(route?.params?.name)
    })
  }
}

</script>

<style lang="scss" scoped>
.opa{
  background-color: (255,255,255,0.5);
}

.square {
    position: absolute;
    // filter: blur(1px);
    backdrop-filter: blur(15px);
    // backdrop-filter: sepia(.5);
    box-shadow: 0 25px 45px rgba(0,0,0,0.1);
    border: 1px solid rgba(255,255,255,0.5);
    border-radius: 10px;
    border-right: 1px solid rgba(255,255,255,0.2);
    border-bottom: 1px solid rgba(255,255,255,0.2);
    // background: rgba(255,255,255,0.1);
    animation: animate 10s linear infinite;
    animation-delay: calc(-1s * var(--i));
  }

  @keyframes animate {
    0%,100%{
      transform: translateY(-40px);
    }
    50% {
      transform: translateY(40px);
    }
  }

  .min-h {
    min-height: 400px;
  }

  .square:nth-child(1){
      top: -50px;
      right: -60px;
      width: 100px;
      height: 100px;
      z-index:-1;
    }
  .square:nth-child(2){
      top: 150px;
      left: -100px;
      width: 120px;
      height: 120px;
      z-index:1;
    }
  .square:nth-child(3){
    bottom: 50px;
    right: -60px;
    width: 80px;
    height: 80px;
    z-index: 1;
  }
  .square:nth-child(4){
    bottom: -70px;
    left: 100px;
    width: 50px;
    height: 50px;
  }
  .square:nth-child(5){
    top: -30px;
    left: 100px;
    width: 60px;
    height: 60px;
  }

</style>
