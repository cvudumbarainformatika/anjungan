import { boot } from 'quasar/wrappers'
import axios from 'axios'
import { getLocalToken } from 'src/utility/storrage'

// const SERV = 'http://192.168.101.79/api.laborat/public'
// const SERV = 'http://api.laborat.test'
// const SERV = 'https://xenter.my.id'
const SERV = process.env.API
// const SERV = 'http://192.168.101.80/api.laborat/public'
// const SERV = 'http://192.168.150.103:3501'
// const SERV = 'http://localhost/api.laborat/public'
// const SERV = 'https://rsudmochsaleh.my.id'

const WSHOST = process.env.WSHOST
// const WSHOST = '192.168.101.80'
const WSPORT = 6003

const SERVER = SERV + '/api'
const api = axios.create({ baseURL: SERVER })
api.defaults.headers.get.Accepts = 'application/json'

api.defaults.headers.common.Authorization = `Bearer ${getLocalToken()}`

const interceptResErrors = (err) => {
  try {
    console.log(err.response)
  } catch (e) {
    console.log('1. catch', e)
  }
  return Promise.reject(err)
}

const interceptResponse = (res) => {
  try {
    return Promise.resolve(res)
  } catch (e) {
    console.log('2. catch interceptResponse', res)
    return Promise.resolve(res)
  }
}

api.interceptors.response.use(interceptResponse, interceptResErrors)

const interceptReqErrors = err => Promise.reject(err)
const interceptRequest = (config) => {
  return config
}
api.interceptors.request.use(interceptRequest, interceptReqErrors)

const pathImg = process.env.PATHIMG + '/storage/'

export default boot(({ app }) => {
  app.config.globalProperties.$axios = axios
  app.config.globalProperties.$api = api
  app.config.globalProperties.$SERV = SERV
  app.config.globalProperties.$WSHOST = WSHOST
  app.config.globalProperties.$WSPORT = WSPORT
})

const setToken = (token) => {
  api.defaults.headers.common.Authorization = `Bearer ${token}`
}
const deleteToken = () => delete api.defaults.headers.common.Authorization

export { api, pathImg, setToken, deleteToken, SERV, WSHOST, WSPORT }
