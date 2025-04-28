/* eslint-disable */
const routes = [
  {
    path: '/',
    component: () => import('layouts/AnjunganLayout.vue'),
    children: [
      // { path: '', redirect: '/anjungan' },
      { path: '', redirect: '/display_antrian_poli/' },
      { path: '/anjungan', name: 'anjungan', component: () => import('pages/anjungan/IndexPage.vue') },
      { path: '/sistem-bayar', name: 'sistem.bayar', component: () => import('pages/anjungan/PilihSistemBayar.vue') },
      { path: '/pasien-umum', name: 'pasien.umum', component: () => import('pages/anjungan/PageUmum.vue') },
      { path: '/pasien-bpjs', name: 'pasien.bpjs', component: () => import('pages/anjungan/PageBpjs.vue') }
    ]
  },
  // {
  //   path: '/display_antrian',
  //   component: () => import('layouts/DisplayLayout.vue'),
  //   children: [
  //     // { path: '', component: () => import('pages/direktur/IndexPage.vue') },
  //     { path: '', redirect: '/display/' },
  //     { path: '/display/:name', name: 'display', component: () => import('pages/display/IndexPage.vue') }
  //   ]
  // },
  {
    path: '/display_antrian_poli',
    component: () => import('layouts/DisplayLayout.vue'),
    children: [
      // { path: '', component: () => import('pages/direktur/IndexPage.vue') },
      { path: '', redirect: '/display_antrian_poli/display' },
      { path: '/display_antrian_poli/display/:name', name: 'display_poli', component: () => import('pages/displayantrianpoli/IndexPage.vue') }
    ]
  },
  {
    path: '/display_antrian_tpprj',
    component: () => import('layouts/DisplayLayout.vue'),
    children: [
      // { path: '', component: () => import('pages/direktur/IndexPage.vue') },
      { path: '', redirect: '/display_antrian_tpprj/display' },
      { path: '/display_antrian_tpprj/display', name: 'display_tpprj', component: () => import('pages/displayantriantpprj/IndexPage.vue') }
    ]
  },
  // {
  //   path: '/logout',
  //   component: () => import('layouts/DisplayLayout.vue')
  // },

  // Always leave this as last one,
  // but you can also remove it
  {
    path: '/:catchAll(.*)*',
    component: () => import('pages/ErrorNotFound.vue')
  }
]

export default routes
