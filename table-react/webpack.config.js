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
		}, {
			test: /\.css$/,
			loader: 'style-loader!css-loader'
		}, {
			test: /\.less$/,
			loader: 'less-loader!postcss-loader'
		}, {
			test: /\.(sass|scss)$/,
			loader: 'style-loader!css-loader!sass-loader'
		}, {
			test: /\.(png|jpg|jpeg|gif|svg|woff|woff2|ttf|eot)$/,
			loader: 'file-loader?publicPath=public'
		}]
	}
};