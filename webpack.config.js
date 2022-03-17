const Encore = require('@symfony/webpack-encore');
const WebpackConcatPlugin = require('webpack-concat-files-plugin');
// Manually configure the runtime environment if not already configured yet by the "encore" command.
// It's useful when you use tools that rely on webpack.config.js file.
if (!Encore.isRuntimeEnvironmentConfigured()) {
    Encore.configureRuntimeEnvironment(process.env.NODE_ENV || 'dev');
}

Encore
    // directory where compiled assets will be stored
    .setOutputPath('src/Resources/public/build/')
    .copyFiles({
        from: './src/Resources/public/img',

        // optional target path, relative to the output dir
        to: 'img/[path][name].[ext]',

        // if versioning is enabled, add the file hash too
        //to: 'img/[path][name].[hash:8].[ext]',

        // only copy files matching this pattern
        pattern: /\.(png|jpg|jpeg|svg)$/
    })
    .copyFiles({
        from: './src/Resources/public/js/',

        // optional target path, relative to the output dir
        to: '[path][name].[ext]',

        // if versioning is enabled, add the file hash too
        //to: 'img/[path][name].[hash:8].[ext]',

        // only copy files matching this pattern
        pattern: /init_cms\.(js)$/
    })
    .copyFiles({
        from: './src/Resources/public/font',

        // optional target path, relative to the output dir
        to: 'font/[path][name].[ext]',

        // if versioning is enabled, add the file hash too
        //to: 'img/[path][name].[hash:8].[ext]',

        // only copy files matching this pattern
        pattern: /\.(ttf|woff|eot|woff2|svg)$/
    })
    // public path used by the web server to access the output path
    .setPublicPath('/bundles/networkinginitcms/build/')
    // only needed for CDN's or sub-directory deploy
    .setManifestKeyPrefix('bundles/networkinginitcms/build/')


    /*
     * ENTRY CONFIG
     *
     * Add 1 entry for each "page" of your app
     * (including one that's included on every page - e.g. "app")
     *
     * Each entry will result in one JavaScript file (e.g. app.js)
     * and one CSS file (e.g. app.css) if you JavaScript imports CSS.
     */
    .addEntry('imageEditor', './src/Resources/public/js/filebot.js')
    .addStyleEntry('networking_initcms', [
        './src/Resources/public/vendor/select2/css/select2.min.css',
        './src/Resources/public/vendor/select2/css/select2-bootstrap.min.css',
        './src/Resources/public/vendor/jqueryui/themes/base/jquery-ui.css',
        './src/Resources/public/vendor/smalot-bootstrap-datetimepicker/css/bootstrap-datetimepicker.css',
        './src/Resources/public/scss/initcms_bootstrap.scss',
        './src/Resources/public/vendor/x-editable/dist/bootstrap3-editable/css/bootstrap-editable.css'
    ])
    .addStyleEntry('admin-navbar', './src/Resources/public/scss/admin-navbar-standalone.scss')

    // When enabled, Webpack "splits" your files into smaller pieces for greater optimization.
    .splitEntryChunks()

    // will require an extra script tag for runtime.js
    // but, you probably want this, unless you're building a single-page app
    .disableSingleRuntimeChunk()

    /*
     * FEATURE CONFIG
     *
     * Enable & configure other features below. For a full
     * list of features, see:
     * https://symfony.com/doc/current/frontend.html#adding-more-features
     */
    .cleanupOutputBeforeBuild()
    .enableBuildNotifications()
    .enableSourceMaps(!Encore.isProduction())
    // enables hashed filenames (e.g. app.abc123.css)
    .enableVersioning(Encore.isProduction())

    // enables @babel/preset-env polyfills
    .configureBabel(() => {
    }, {
        useBuiltIns: 'usage',
        corejs: 3
    })
    // .enableVueLoader(() => {}, { runtimeCompilerBuild: true })
    .enableVueLoader((options) => {
        options.extractCSS = !Encore.isProduction()
    }, {runtimeCompilerBuild: false})

    // enables Sass/SCSS support
    .enableSassLoader()

    // uncomment if you use TypeScript
    //.enableTypeScriptLoader()

    // uncomment to get integrity="..." attributes on your script & link tags
    // requires WebpackEncoreBundle 1.4 or higher
    .enableIntegrityHashes()

    // uncomment if you're having problems with a jQuery plugin
    // .autoProvidejQuery()
// build the second configuration


Encore.addPlugin(new WebpackConcatPlugin({
    allowOptimization: true,
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
    ],
}))

const cms = Encore.getWebpackConfig();
cms.externals = {filerobotImageEditor: 'FilerobotImageEditor'}

module.exports = cms

