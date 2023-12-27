import { createAppConfig } from '@nextcloud/vite-config'
import { defineConfig } from 'vite'
import { resolve } from 'node:path'

const config = defineConfig({
  build: {
    sourcemap: false, // disabled, otherwise vite runs out of memory
  },
  test: {
    coverage: {
      include: ['src/**'],
      provider: 'istanbul',
      reporter: ['lcov', 'text'],
    },
    environment: 'happy-dom',
    setupFiles: resolve(__dirname, './tests/javascript/unit/setup.js'),
    alias: [{ find: /^vue$/, replacement: 'vue/dist/vue.runtime.common.js' }],
    server: {
      deps: {
        inline: ['@nextcloud/vue'],
      }
    },
  },
})

export default createAppConfig({
	main: 'src/main.js',
}, {
  config
})
