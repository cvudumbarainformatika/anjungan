<template>
  <!-- <div class="full-height full-width bg-red relative-position"> -->
  <div class="video-container">
    <video
      v-if="!loading" id="vidEl" ref="refVideo"
      :key="idx"
      :autoplay="autoplay"
      :muted="muted"
      @loadedmetadata="loadedMD($event)"
      @loadstart="loadedStart($event)"
      @ended="onEnded()"
      controls
      class="video"
    >
      <source :type="getType()" :src="getVideo()">
    </video>
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
.video-container{
  background-color: black;
  width: 100%;
  height: 100%;
  position: relative;
  .video {
    aspect-ratio: 16 / 9;
    width: 100%;
    // position: absolute;
    // background-color: $primary;
}
}

</style>
