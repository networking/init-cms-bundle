const { merge } = require("webpack-merge")
const MiniCssExtractPlugin = require("mini-css-extract-plugin")
const TerserPlugin = require("terser-webpack-plugin")
const commonConfig = require("./webpack.config.common")

module.exports = merge(commonConfig, {
	mode: "production",
	devtool: "source-map",
	optimization: {
		minimize: true,
		minimizer: [new TerserPlugin()],
		splitChunks: {
			chunks: "all",
			minSize: 20000,
			minRemainingSize: 0,
			minChunks: 1,
			maxAsyncRequests: 30,
			maxInitialRequests: 30,
			enforceSizeThreshold: 50000,
			cacheGroups: {
				cmsAdmin: {
					test: /[\\/]assets[\\/]cms[\\/]/,
					minChunks: 2,
					priority: -20,
					reuseExistingChunk: true,
				},
				defaultVendors: {
					test: /[\\/]node_modules[\\/]/,
					priority: -10,
					reuseExistingChunk: true,
				},
				default: {
					minChunks: 2,
					priority: -20,
					reuseExistingChunk: true,
				},
			},
		},
	},
	module: {
		rules: [
			{
				test: /\.(png|jpg|jpeg|svg|gif)$/,
				loader: "file-loader",
				options: {
					name: "[name].[contenthash:8].[ext]",
					outputPath: "/img",
				},
			},
		],
	},
	plugins: [
		// make sure to include the plugin!
		new MiniCssExtractPlugin({
			// Options similar to the same options in webpackOptions.output
			// both options are optional
			filename: "[name].[contenthash:8].css",
			chunkFilename: "[id].[contenthash:8].css",
		}),
	],
})
