/* eslint-env node */

/*
 * This file runs in a Node context (it's NOT transpiled by Babel), so use only
 * the ES6 features that are supported by your Node version. https://node.green/
 */

// Configuration for your app
// https://v2.quasar.dev/quasar-cli-vite/quasar-config-js

import { configure } from 'quasar/wrappers'
import { fileURLToPath } from 'node:url'
import { dirname } from 'node:path'
import path from 'node:path'

const __filename = fileURLToPath(import.meta.url)
const __dirname = dirname(__filename)

export default configure(async function (ctx) {  // Menambahkan async di sini
  return {
    eslint: {
      warnings: true,
      errors: true
    },

    boot: [
      'axios',
      'globalComponents',
      'other'
    ],

    css: [
      'app.scss'
    ],

    extras: [
      'eva-icons',
      'material-icons'
    ],

    build: {
      target: {
        browser: ['es2019', 'edge88', 'firefox78', 'chrome87', 'safari13.1'],
        node: 'node16'
      },

      vueRouterMode: 'history',

      env: {
        API: ctx?.dev
          ? 'http://192.168.150.112:3501'
          : 'http://192.168.150.112:3501',

        WSHOST: ctx?.dev
          ? '192.168.150.112'
          : '192.168.150.112',

        PATHIMG: ctx?.dev
          ? 'http://192.168.150.111:3507'
          : 'http://192.168.150.111:3507',

        ENABLE_MONITORING: ctx.dev,
        MONITORING_LOG: true,
        APP_VERSION: JSON.stringify((await import('./package.json', { assert: { type: 'json' } })).default.version)
      }
    },

    devServer: {
      port: ctx.mode.spa
        ? 9001
        : (ctx.mode.pwa ? 9100 : 9200),
      open: true
    },

    framework: {
      config: {},
      plugins: ['AppFullscreen', 'AppVisibility', 'Notify', 'Loading', 'Dialog']
    },

    animations: [],

    ssr: {
      pwa: false,
      prodPort: 3000,
      middlewares: [
        'render'
      ]
    },

    pwa: {
      workboxMode: 'generateSW',
      injectPwaMetaTags: true,
      swFilename: 'sw.js',
      manifestFilename: 'manifest.json',
      useCredentialsForManifestTag: false
    },

    cordova: {
    },

    capacitor: {
      hideSplashscreen: true
    },

    electron: {
      inspectPort: 5858,
      bundler: 'packager',

      packager: {
      },

      builder: {
        appId: 'anjungan'
      }
    },

    bex: {
      contentScripts: [
        'my-content-script'
      ]
    }
  }
})
