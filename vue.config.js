const path = require('path')

module.exports = {
	publicPath: './',
	outputDir: 'dist',
	assetsDir: 'static',
	productionSourceMap: false,
	devServer: {
		proxy: {
			'^/inc': {
				target: 'http://127.0.0.1:8000',
				changeOrigin: true
			}
		}
	},
	chainWebpack: config => {
		config.plugin('copy').tap(args => {
			args[0].push({
				from: path.resolve(__dirname, 'inc'),
				to: path.resolve(__dirname, 'dist/inc'),
				toType: 'dir',
				ignore: ['*.db']
			},
			{
				from: path.resolve(__dirname, 'fichiers'),
				to: path.resolve(__dirname, 'dist/fichiers'),
				toType: 'dir'
			},
			{
				from: path.resolve(__dirname, 'README.md'),
				to: path.resolve(__dirname, 'dist'),
				toType: 'dir'
			},
			{
				from: path.resolve(__dirname, 'LICENSE'),
				to: path.resolve(__dirname, 'dist'),
				toType: 'dir'
			})
			return args
		})
	}
}
