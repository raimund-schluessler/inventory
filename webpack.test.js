const merge = require('webpack-merge');
const common = require('./webpack.common.js');

// test specific setups
module.exports = merge(common, {
	mode: "production",
	externals: [require('webpack-node-externals')()],
	devtool: 'eval'
});
