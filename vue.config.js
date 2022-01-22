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
	}
}
