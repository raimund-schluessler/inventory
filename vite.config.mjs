import { createAppConfig } from '@nextcloud/vite-config'
import { defineConfig } from 'vite'
import { viteStaticCopy } from 'vite-plugin-static-copy'

const config = defineConfig({
  plugins: [
    viteStaticCopy({
      targets: [
        {
          src: 'js/zxing_reader.wasm',
          dest: 'node_modules/@sec-ant/zxing-wasm/dist/reader/zxing_reader.wasm'
        }
      ]
    })
  ]
})

export default createAppConfig({
	main: 'src/main.js',
}, {
    config
})