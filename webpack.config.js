const webpackConfig = require('@nextcloud/webpack-vue-config')
const CopyPlugin = require("copy-webpack-plugin")

webpackConfig.plugins.push(
	new CopyPlugin({
		patterns: [
		  { from: "node_modules/@sec-ant/zxing-wasm/dist/reader/zxing_reader.wasm", to: "zxing_reader.wasm" },
		],
	}),
)

module.exports = webpackConfig
