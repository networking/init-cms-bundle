const path = require('path');
const {VueLoaderPlugin} = require('vue-loader')
const terser = require('terser');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const { WebpackManifestPlugin } = require('webpack-manifest-plugin');
const webpack = require('webpack');
const CopyPlugin = require("copy-webpack-plugin");


module.exports = {
    resolve: {
        alias: {
            'bootstrap-saas': path.resolve(__dirname, './node_modules/bootstrap-saas'),
        },
    },
    externals: {
        filerobotImageEditor: 'FilerobotImageEditor'
    },
    entry: {
        cmsRouting: './assets/cms/cms-routing.js',
        cmsAdmin: './assets/cms/cms-admin.js',
        globalSearch: './assets/cms/global-search.js',
        list: './assets/cms/list.js',
        mediaAdmin: './assets/cms/media-admin.js',
        menuAdmin: './assets/cms/menu-admin.js',
        pageAdmin: './assets/cms/page-admin.js',
        formAdmin: './assets/cms/form-admin.js',
        imageEditor: './assets/js/filebot.js',
        twoFactorSignin: './assets/cms/authentication/sign-in/two-factor.js',
        generalSignin: './assets/cms/authentication/sign-in/general.js',
        webauthnRegister: './assets/cms/authentication/webauthn/register.js',
        networking_initcms: './assets/cms/scss/style.scss',
        'tui-image-editor': './assets/css/tui-image-editor.css',
        'admin-navbar': './assets/scss/admin-navbar-standalone.scss',
        'maintenance': './assets/css/maintenance.css',

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
        new webpack.DefinePlugin({
            __VUE_OPTIONS_API__: true,
            __VUE_PROD_DEVTOOLS__: true
        }),
        new WebpackManifestPlugin(),
        new VueLoaderPlugin(),
        new CopyPlugin({
                patterns: [
                    {
                        from: './assets/admin-theme/',
                        to: './admin-theme/[path][name][ext]',
                    },
                    {
                        from: './assets/js/pdf-viewer.js',
                        to: './js/pdf-viewer.js',
                    },
                    {
                        from: './assets/vendor/x-editable/dist/bootstrap3-editable/js/bootstrap-editable.js',
                        to: './vendor/x-editable/dist/bootstrap3-editable/js/bootstrap-editable.js',
                    },
                    // {
                    //     from: './assets/vendor/filerobot-image-editor/index_old.js',
                    //     to: './vendor/filerobot-image-editor/index_old.js',
                    // },
                    {
                        from: './assets/vendor/fontawesome',
                        to: './vendor/fontawesome',
                    },
                    {
                        from: './assets/js/ckeditor/',
                        to: './js/ckeditor/',
                    },
                    {
                        from: './assets/vendor/x-editable/dist/bootstrap5-editable/',
                        to: './vendor/bootstrap5-editable/',
                    },
                    {
                        from: './assets/vendor/pdfjs',
                        to: './vendor/pdfjs',
                    },
                    {
                        from: './assets/img',
                        to: './img',
                    },
                    {
                        from: './assets/cms/app.js',
                        to: './cmsApp.js',
                    }
                ]
            }),

    ],
    performance: {
        maxAssetSize: 120000,
        maxEntrypointSize: 120000,
        assetFilter: function(assetFilename) {
            return !assetFilename.endsWith('.jpg') && !assetFilename.endsWith('.svg')  && !assetFilename.endsWith('.png') && !assetFilename.endsWith('.eot') && !assetFilename.endsWith('.ttf') && !assetFilename.endsWith('.woff') && !assetFilename.endsWith('.woff2');
        },
    }
}
