const { merge }  = require('webpack-merge')
const commonConfig = require('./webpack.config.common')
const MiniCssExtractPlugin = require("mini-css-extract-plugin");
const path = require("path");

module.exports = merge(commonConfig, {
    mode: 'development',
    devtool: 'inline-source-map',
    output: {
        publicPath: '/bundles/networkinginitcms',
        pathinfo: true,
        path: path.resolve(__dirname, 'src/Resources/public/'),
        clean: true,
        filename: '[name].js',
    },
    module: {
        rules: [
            {
                test: /\.(png|jpg|jpeg|svg|gif)$/,
                loader: 'file-loader',
                options: {
                    name: '[name].[ext]',
                    outputPath: '/img'
                }
            }
        ]
    },
    plugins: [
        // make sure to include the plugin!
        new MiniCssExtractPlugin({
            // Options similar to the same options in webpackOptions.output
            // both options are optional
            filename: '[name].css',
            chunkFilename: '[id].css',
        })
    ]
})