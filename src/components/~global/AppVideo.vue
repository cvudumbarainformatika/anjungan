<template>
  <div class="player">
    <div class="player_sizer">
      <!-- <iframe src="~assets/video/NE.mp4" type="video/mp4" allow="autoplay" id="video" style="display:none"></iframe> -->
      <video v-if="!loading" id="vidEl" ref="refVideo"
        :key="idx"
        :autoplay="autoplay"
        :muted="muted"
        @loadedmetadata="loadedMD($event)"
        @loadstart="loadedStart($event)"
        @ended="onEnded()"
        controls
        >
        <source :type="getType()" :src="getVideo()">
      </video>
      <!-- <div class="absolute-bottom">
        <div class="row q-pa-md">
          <q-btn label="play" @click="played($event)" class="bg-blue"></q-btn>
        </div>
      </div> -->
    </div>
  </div>
</template>

<script setup>
import { pathImg } from 'src/boot/axios'
import { ref, onMounted } from 'vue'

const props = defineProps({
  videos: {
    type: Array,
    default: () => []
  },
  loading: {
    type: Boolean,
    default: true
  },
  volume: {
    type: String,
    default: '0.2'
  }
})

const refVideo = ref()
const autoplay = ref(false)
const muted = ref(false)

// const videos = ref(['NE.mp4', 'video.webm'])
const idx = ref(0)

function getVideo () {
  const file = props.videos[idx.value]
  if (props.videos.length === 0) {
    return new URL('../../assets/video/NE.mp4', import.meta.url).href
  }
  if (file === null || file === 'undefined' || file === undefined || file === '') {
    return new URL('../../assets/video/NE.mp4', import.meta.url).href
  } else {
    return pathImg + file.url
  }
}
function getType () {
  const file = props.videos[idx.value]
  if (props.videos.length === 0) {
    return 'video/mp4'
  }
  if (file === null || file === 'undefined' || file === undefined || file === '') {
    return 'video/mp4'
  } else {
    return file.type
  }
}

function played () {
  console.log()
  return refVideo.value ? refVideo.value.play() : false
}
function loadedStart (e) {
  e.target.volume = parseFloat(props.volume)
  // console.log('loaded', e.target.volume)
  played()
}

function onEnded () {
  // console.log('onEnded')
  // const vid = videos.value.length
  const vid = props.videos.length
  if (idx.value === vid - 1) {
    idx.value = 0
  } else {
    idx.value = idx.value + 1
  }
}
function loadedMD (ev) {
  return ev
}
onMounted(() => {
  //
})
</script>

<style lang="scss" scoped>
.player{
  width: 100%;
  // height: 0px;
  position: relative;
  // padding-top: 56.25%;
  video{
    position: absolute;
    width: 100%;
    height: 100%;
    left: 0;
    top:0;
  }

  &_sizer {
    width: 100%;
    // height: 100%;
    padding-top: 56.25%;
    position: relative;
    // outline: 3px solid red;
  }
}
</style>
