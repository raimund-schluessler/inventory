import { createAppConfig } from '@nextcloud/vite-config'
import { defineConfig } from 'vite'
import { viteStaticCopy } from 'vite-plugin-static-copy'
import { resolve } from 'node:path'

const config = defineConfig({
  plugins: [
    viteStaticCopy({
      targets: [
        {
          dest: 'js',
          src: 'node_modules/zxing-wasm/dist/reader/zxing_reader.wasm'
        }
      ]
    })
  ],
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
    server: {
      deps: {
        inline: [/@nextcloud\//],
      }
    },
  },
})

export default createAppConfig({
	main: 'src/main.js',
}, {
    config
})
