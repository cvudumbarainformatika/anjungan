import { setToken, deleteToken } from 'boot/axios'

function safeGetItem (key) {
  try {
    if (typeof window !== 'undefined' && window.localStorage) {
      return window.localStorage.getItem(key)
    }
  } catch (e) {
    console.warn(`localStorage blocked for key ${key}:`, e)
  }
  return null
}

function safeSetItem (key, value) {
  try {
    if (typeof window !== 'undefined' && window.localStorage) {
      window.localStorage.setItem(key, value)
    }
  } catch (e) {
    console.warn(`localStorage blocked for key ${key}:`, e)
  }
}

function safeRemoveItem (key) {
  try {
    if (typeof window !== 'undefined' && window.localStorage) {
      window.localStorage.removeItem(key)
    }
  } catch (e) {
    console.warn(`localStorage blocked for key ${key}:`, e)
  }
}

export const setHeaderToken = token => setToken(token)
export const setLocalToken = token => safeSetItem('token', JSON.stringify(token))
export const getLocalToken = () => {
  const val = safeGetItem('token')
  try {
    return val ? JSON.parse(val) : null
  } catch (e) {
    return null
  }
}
export const setUser = user => safeSetItem('user', JSON.stringify(user))
export const getUser = () => {
  const val = safeGetItem('user')
  try {
    return val ? JSON.parse(val) : null
  } catch (e) {
    return null
  }
}
export const deleteHeaderToken = () => deleteToken()
export const deleteLocalToken = () => safeRemoveItem('token')
export const isBlocked = () => safeGetItem('blocked') === 'true'
export const setBlocked = value => safeSetItem('blocked', JSON.stringify(value))
export const deleteUser = () => safeRemoveItem('user')
export const setTempData = data => safeSetItem('tempData', JSON.stringify(data))
export const getTempData = () => {
  const val = safeGetItem('tempData')
  try {
    return val ? JSON.parse(val) : null
  } catch (e) {
    return null
  }
}
export const deleteTempData = () => safeRemoveItem('tempData')

export const clearStore = () => {
  try {
    if (typeof window !== 'undefined' && window.localStorage) {
      window.localStorage.clear()
    }
  } catch (e) {
    console.warn('localStorage clear failed:', e)
  }
}
