const path = require('path');

module.exports = {
	entry: './src/index.js',
	output: {
		path: path.resolve(__dirname, 'public'),
		publicPath: '/assets/',
		filename: 'bundle.js'
	},
	module: {
		loaders: [{
			test: /\.(js|jsx)$/,
			loader: 'babel-loader'
		}]
	}
};