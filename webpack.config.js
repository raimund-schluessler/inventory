const webpackConfig = require('@nextcloud/webpack-vue-config')
const ESLintPlugin = require('eslint-webpack-plugin')

webpackConfig.plugins.push(
    new ESLintPlugin({
        extensions: ['js', 'vue'],
    }),
)

module.exports = webpackConfig
