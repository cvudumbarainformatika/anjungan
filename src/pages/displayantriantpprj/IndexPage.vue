
<template>
  <q-page class="fullscreen bg-dark">
    <div v-if="!isAuth">
      <BlockPage />
    </div>
    <div v-else class="fullscreen">
      <BoxAnimation class="absolute" style="z-index: -1;" />
      <AnimationWave class="z--" />
      <div class="full-height full-width q-pa-md" >
        <!-- <DisplayPoliPage :key="queryparam" /> -->
         <DisplayTpprj @on-panggil="panggil" />
      </div>
    </div>
  </q-page>
</template>

<script setup>
import BlockPage from './comp/BlockPage.vue'
import AnimationWave from './comp/AnimationWave.vue'
import BoxAnimation from './comp/BoxAnimation.vue'
import DisplayTpprj from './DisplayTpprj.vue'

import { computed, onMounted, ref } from 'vue'
import { useRoute } from 'vue-router'
import { useAuthStore } from 'src/stores/auth/index'
import { useSpeechStore } from 'src/stores/display/speech'

const route = useRoute()
const auth = useAuthStore()
const isAuth = computed(() => auth.user)
const speech = useSpeechStore()
const indexVoices = ref(0)

// eslint-disable-next-line no-unused-vars
const queryparam = computed(() => route.params.name)

onMounted(() => {
  getListVoices().then((x) => {
    settingsVoice()
  })
  // console.log('auth', isAuth.value)
  auth.logout()
  // console.log('route', queryparam)
})

function getListVoices () {
  return new Promise(
    function (resolve, reject) {
      const synth = speech.synth
      let id = 0

      id = setInterval(() => {
        if (synth.getVoices().length !== 0) {
          speech.voiceList = synth.getVoices()
          resolve(synth.getVoices())
          clearInterval(id)
        }
      }, 10)
    }
  )
}

function settingsVoice () {
  const voices = speech.voiceList
  if (voices.length) {
    const lang = voices?.map(x => x.lang)
    const ind = lang.findIndex(x => x === 'id-ID') ?? 0
    indexVoices.value = ind
  }

  // const synth = window.speechSynthesis
  speech.synth.onvoiceschanged = () => {
    speech.synth.setVoiceList(voices)
  }
  // console.log('voices', voices)
  // console.log('speech', speech)
  listenForSpeechEvents()
}

function listenForSpeechEvents () {
  speech.utterance.onstart = () => {
    console.log('start...')
    speech.isLoading = true
  }
  speech.utterance.onend = () => {
    console.log('end...')
    speech.isLoading = false
  }
}

function setSpeech (txt) {
  console.log('voice terpilih', speech.voiceList[indexVoices.value])
  const voice = speech.utterance
  voice.text = txt
  voice.voice = speech.voiceList[indexVoices.value]

  voice.volume = 1
  voice.pitch = 1
  voice.rate = 0.8

  return voice
}

function panggil (data) {
  console.log('panggil')
  const txt = 'Nomor Antrian ... ' + data?.nomorAntrian + '? ...Harap menujuu ke loket ' + data?.loketId
  // const txt = 'Nomor Antrian ... '
  speech.synth.speak(setSpeech(txt))
}

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
