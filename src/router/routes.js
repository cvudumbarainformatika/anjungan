
const routes = [
  // {
  //   path: '/',
  //   component: () => import('layouts/ExecutiveLayout.vue'),
  //   children: [
  //     // { path: '', component: () => import('pages/direktur/IndexPage.vue') },
  //     { path: '', redirect: '/keuangan' },
  //     { path: '/splash', name: 'splash', component: () => import('pages/direktur/splash/SplashPage.vue') },
  //     { path: '/keuangan', name: 'keuangan', component: () => import('pages/direktur/keuangan/KeuanganPage.vue') },
  //     { path: '/kepegawaian', name: 'kepegawaian', component: () => import('pages/direktur/kepegawaian/KepegawaianPage.vue') },
  //     { path: '/layanan', name: 'layanan', component: () => import('pages/direktur/pelayanan/PelayananPage.vue') },
  //     { path: '/sarpras', name: 'sarpras', component: () => import('pages/direktur/sarpras/SarprasPage.vue') }
  //   ]
  // },
  {
    path: '/',
    component: () => import('layouts/AnjunganLayout.vue'),
    children: [
      // { path: '', component: () => import('pages/direktur/IndexPage.vue') },
      { path: '', redirect: '/anjungan' },
      { path: '/anjungan', name: 'anjungan', component: () => import('pages/anjungan/IndexPage.vue') },
      { path: '/sistem-bayar', name: 'sistem.bayar', component: () => import('pages/anjungan/PilihSistemBayar.vue') },
      { path: '/pasien-umum', name: 'pasien.umum', component: () => import('pages/anjungan/PageUmum.vue') },
      { path: '/pasien-bpjs', name: 'pasien.bpjs', component: () => import('pages/anjungan/PageBpjs.vue') }
    ]
  },

  // Always leave this as last one,
  // but you can also remove it
  {
    path: '/:catchAll(.*)*',
    component: () => import('pages/ErrorNotFound.vue')
  }
]

export default routes
