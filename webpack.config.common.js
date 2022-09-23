const path = require('path');
const {VueLoaderPlugin} = require('vue-loader')
const WebpackConcatPlugin = require('webpack-concat-files-plugin');
const terser = require('terser');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const { WebpackManifestPlugin } = require('webpack-manifest-plugin');
const webpack = require('webpack');
const CopyPlugin = require("copy-webpack-plugin");

module.exports = {
    resolve: {
        alias: {
            vue: 'vue/dist/vue.esm.js',
            'bootstrap-saas': path.resolve(__dirname, './node_modules/bootstrap-saas'),
        },
    },
    externals: {
        filerobotImageEditor: 'FilerobotImageEditor'
    },
    entry: {
        admin: './assets/js/admin.js',
        imageEditor: './assets/js/filebot.js',
        networking_initcms: [
            './assets/vendor/select2/css/select2.min.css',
            './assets/vendor/select2/css/select2-bootstrap.min.css',
            './assets/vendor/jquery-ui-1.12.1/jquery-ui.css',
            './assets/vendor/smalot-bootstrap-datetimepicker/css/bootstrap-datetimepicker.css',
            './assets/scss/initcms_bootstrap.scss',
            './assets/vendor/x-editable/dist/bootstrap3-editable/css/bootstrap-editable.css'
        ],
        'tui-image-editor': './assets/css/tui-image-editor.css',
        'admin-navbar': './assets/scss/admin-navbar-standalone.scss',
    },
    output: {
        publicPath: '/bundles/networkinginitcms',
        pathinfo: true,
        path: path.resolve(__dirname, 'src/Resources/public/'),
        clean: true,
        filename: '[name].[contenthash:8].js',
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
        new webpack.ProvidePlugin({
            $: "jquery",
            jQuery: "jquery"
        }),
        new WebpackManifestPlugin(),
        new VueLoaderPlugin(),
        new CopyPlugin({
                patterns: [
                    {
                        from: './assets/js/admin-lte/',

                        // optional target path, relative to the output dir
                        to: './admin-lte/[path][name][ext]',
                    },
                    {
                        from: './assets/js/pdf-viewer.js',
                        to: './js/pdf-viewer.js',
                    },
                    {
                        from: './assets/js/sandbox.js',
                        to: './js/sandbox.js',
                    },
                    {
                        from: './assets/css/sandbox.css',
                        to: './css/sandbox.css',
                    },
                    {
                        from: './assets/vendor/featherlight/src/featherlight.css',
                        to: './vendor/featherlight/src/featherlight.css',
                    },
                    {
                        from: './assets/vendor/select2/js/i18n/',
                        to: './vendor/select2/js/i18n/',
                    },
                    {
                        from: './assets/vendor/smalot-bootstrap-datetimepicker/js/',
                        to: './vendor/smalot-bootstrap-datetimepicker/js/',
                    },
                    {
                        from: './assets/vendor/x-editable/dist/bootstrap3-editable/js/bootstrap-editable.js',
                        to: './vendor/x-editable/dist/bootstrap3-editable/js/bootstrap-editable.js',
                    },
                    {
                        from: './assets/vendor/ios-html5-drag-drop-shim/',
                        to: './vendor/ios-html5-drag-drop-shim/',
                    },
                    {
                        from: './assets/vendor/nestedSortable/',
                        to: './vendor/nestedSortable/',
                    },
                    {
                        from: './assets/vendor/dropzone/dropzone.js',
                        to: './vendor/dropzone/dropzone.js',
                    },
                    {
                        from: './assets/vendor/filerobot-image-editor/index_old.js',
                        to: './vendor/filerobot-image-editor/index_old.js',
                    },
                    {
                        from: './assets/js/ckeditor/',
                        to: './js/ckeditor/',
                    },
                    {
                        from: './assets/fonts/',
                        to: './fonts/',
                    },
                    {
                        from: './assets/img/',
                        to: './img/',
                    }
                ]
            }),
        new WebpackConcatPlugin({
            bundles: [
                {
                    dest: './src/Resources/public/jquery-plugins.js',
                    src: [
                        './assets/vendor/jquery-ui-1.12.1/jquery-ui.min.js',
                        './assets/vendor/jquery-form/jquery.form.js',
                    ],
                    transforms: {
                        after: async (code) => {
                            const minifiedCode = await terser.minify(code);
                            return minifiedCode.code;
                        },
                    },
                },
                {
                    dest: './src/Resources/public/bootstrap-plugins.js',
                    src: [
                        './assets/vendor/smalot-bootstrap-datetimepicker/js/bootstrap-datetimepicker.js',
                        './assets/vendor/x-editable/dist/bootstrap3-editable/js/bootstrap-editable.js',
                        './assets/vendor/bootstrap-contextmenu/bootstrap-contextmenu.js',
                    ],
                    transforms: {
                        after: async (code) => {
                            const minifiedCode = await terser.minify(code);
                            return minifiedCode.code;
                        },
                    },
                },
                {
                    dest: './src/Resources/public/app.js',
                    src: [
                        './assets/js/collection.js',
                        './assets/vendor/select2/js/select2.full.js',
                        './assets/vendor/featherlight/src/featherlight.js',
                        './assets/js/index.js',
                    ],
                    transforms: {
                        after: async (code) => {
                            const minifiedCode = await terser.minify(code);
                            return minifiedCode.code;
                        },
                    },
                },
                {
                    src: './node_modules/bootstrap/dist/js/bootstrap.js',
                    dest: './src/Resources/public/bootstrap.js',
                }
            ],
        })
    ]
}
