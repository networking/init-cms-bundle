const path = require('path');
const {VueLoaderPlugin} = require('vue-loader')
const WebpackConcatPlugin = require('webpack-concat-files-plugin');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const { WebpackManifestPlugin } = require('webpack-manifest-plugin');
const devMode = process.env.NODE_ENV !== 'production';
const mode =  process.env.NODE_ENV || 'development'
module.exports = {
    mode: mode,
    devtool: 'source-map',
    resolve: {alias: {vue: 'vue/dist/vue.esm.js'}},
    externals: {
        jquery: 'jQuery',
        filerobotImageEditor: 'FilerobotImageEditor'
    },
    entry: {
        imageEditor: './src/Resources/public/js/filebot.js',
        networking_initcms: [
            './src/Resources/public/vendor/select2/css/select2.min.css',
            './src/Resources/public/vendor/select2/css/select2-bootstrap.min.css',
            './src/Resources/public/vendor/jqueryui/themes/base/jquery-ui.css',
            './src/Resources/public/vendor/smalot-bootstrap-datetimepicker/css/bootstrap-datetimepicker.css',
            './src/Resources/public/scss/initcms_bootstrap.scss',
            './src/Resources/public/vendor/x-editable/dist/bootstrap3-editable/css/bootstrap-editable.css'
        ],
        'tui-image-editor': './src/Resources/public/css/tui-image-editor.css',
        'sandbox': './src/Resources/public/css/sandbox.css',
        'admin-navbar': './src/Resources/public/scss/admin-navbar-standalone.scss',
    },
    output: {
        publicPath: '/bundles/networkinginitcms/build/',
        pathinfo: true,
        path: path.resolve(__dirname, 'src/Resources/public/build/'),
        clean: true,
        filename: devMode ? '[name].js' : '[name].[contenthash].js',
    },
    module: {
        rules: [
            {
                test: /\.(ttf|woff|eot|woff2)$/,
                loader: 'file-loader',
                options: {
                    name: '[name].[ext]',
                    outputPath: '/fonts'
                }
            },
            {
                test: /\.(png|jpg|jpeg|svg|gif)$/,
                loader: 'file-loader',
                options: {
                    name: devMode ?'[name].[ext]' : '[name].[contenthash].[ext]',
                    outputPath: '/img'
                }
            },
            {
                test: /\.vue$/,
                loader: 'vue-loader'
            },
            {
                test: /\.css$/,
                use: [
                    MiniCssExtractPlugin.loader,
                    'css-loader',
                    'postcss-loader',
                ]
            },
            {
                test: /\.(sa|sc)ss$/,
                use: [
                    MiniCssExtractPlugin.loader,
                    {
                        loader: 'css-loader',
                        options: { sourceMap: true },
                    },
                    'postcss-loader',
                    {
                        loader: 'sass-loader',
                        options: { sourceMap: true },
                    },
                ],
            }
        ]
    },
    plugins: [
        // make sure to include the plugin!
        new MiniCssExtractPlugin({
            // Options similar to the same options in webpackOptions.output
            // both options are optional
            filename: devMode ? '[name].css' : '[name].[contenthash].css',
            chunkFilename: devMode ? '[id].css' : '[id].[contenthash].css',
        }),
        new WebpackManifestPlugin(),
        new VueLoaderPlugin(),
        new WebpackConcatPlugin({
            bundles: [
                {
                    dest: './src/Resources/public/build/jquery.js',
                    src: [
                        './src/Resources/public/vendor/jquery/dist/jquery.min.js',
                        './src/Resources/public/vendor/jqueryui/jquery-ui.min.js',
                        './src/Resources/public/vendor/jquery-ui-touch-punch/jquery.ui.touch-punch.min.js',
                    ]

                },
                {
                    dest: './src/Resources/public/build/bootstrap.js',
                    src: [
                        './../../mopa/bootstrap-bundle/Resources/public/bootstrap-sass/assets/javascripts/bootstrap.js',
                        './src/Resources/public/vendor/x-editable/dist/bootstrap3-editable/js/bootstrap-editable.js'
                    ]

                },
                {
                    dest: './src/Resources/public/build/app.js',
                    src: [
                        './src/Resources/public/js/mopabootstrap-collection.js',
                        './src/Resources/public/vendor/smalot-bootstrap-datetimepicker/js/bootstrap-datetimepicker.js',
                        './src/Resources/public/vendor/select2/js/select2.full.js',
                        './src/Resources/public/vendor/jquery-form/jquery.form.js',
                        './src/Resources/public/vendor/bootstrap-contextmenu/bootstrap-contextmenu.js',
                        './src/Resources/public/vendor/featherlight/src/featherlight.js',
                        './../../sonata-project/admin-bundle/src/Resources/public/treeview.js'
                    ]

                },
                {
                    dest: './src/Resources/public/build/init_cms.js',
                    src: './src/Resources/public/js/init_cms.js'
                },
            ],
        })
    ]
}
