import { date } from 'quasar'

const formatRupiah = (number) => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR'
  }).format(number)
}

const formatMoney = (bilangan) => {
  const n = bilangan.toString()
  const sisa = n.length % 3
  const ribuan = n.substr(sisa).match(/\d{3}/g)

  let rupiah = n.substr(0, sisa)
  if (ribuan) {
    const separator = sisa ? '.' : ''
    rupiah += separator + ribuan.join('.')
  } else {
    rupiah = bilangan
  }

  return rupiah
}

const formatDateTimeDb = (val) => {
  return date.formatDate(val, 'YYYY-MM-DD HH:mm:ss')
}
const formatDateDb = (val) => {
  return date.formatDate(val, 'YYYY-MM-DD')
}

export { formatRupiah, formatMoney, formatDateTimeDb, formatDateDb }
