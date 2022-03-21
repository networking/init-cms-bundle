const { merge }  = require('webpack-merge')
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const TerserPlugin = require('terser-webpack-plugin');
const commonConfig = require('./webpack.config.common');
const path = require("path");

module.exports = merge(commonConfig, {
    mode: 'production',
    devtool: 'source-map',
    module: {
        rules: [
            {
                test: /\.(png|jpg|jpeg|svg|gif)$/,
                loader: 'file-loader',
                options: {
                    name: '[name].[contenthash:8].[ext]',
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
            filename: '[name].[contenthash:8].css',
            chunkFilename: '[id].[contenthash:8].css',
        })
    ],
    optimization: {
        minimize: true,
        minimizer: [new TerserPlugin()],
    },
})