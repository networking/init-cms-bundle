const {VueLoaderPlugin} = require('vue-loader')

module.exports = {
    mode: 'production',
    resolve: {alias: {vue: 'vue/dist/vue.esm.js'}},
    externals: {
        jquery: 'jQuery',
        filerobotImageEditor: 'FilerobotImageEditor'
    },
    module: {
        rules: [
            {
                test: /\.svg$/,
                loader: 'file-loader',
                options: {
                    name: '[name].[ext]',
                    outputPath: ''
                }
            },
            {
                test: /\.vue$/,
                loader: 'vue-loader'
            },
            {
                resourceQuery: /blockType=i18n/,
                type: 'javascript/auto',
                loader: '@kazupon/vue-i18n-loader'
            },
            {
                test: /\.css$/,
                use: [
                    'vue-style-loader',
                    'css-loader'
                ]
            },
            {
                test: /\.s[ac]ss$/i,
                use: [
                    // Creates `style` nodes from JS strings
                    'style-loader',
                    // Translates CSS into CommonJS
                    'css-loader',
                    // Compiles Sass to CSS
                    'sass-loader',
                ],
            }
        ]
    },
    output: {
        filename: 'imageEditor.js',

    },
    plugins: [
        // make sure to include the plugin!
        new VueLoaderPlugin()
    ]
}