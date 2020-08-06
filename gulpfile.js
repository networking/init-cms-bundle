var gulp = require('gulp'),
    concat = require('gulp-concat'),
    uglify = require('gulp-uglify'),
    sourcemaps = require('gulp-sourcemaps'),
    csso = require('gulp-csso'),
    less = require('gulp-less'),
    sass = require('gulp-sass')
    webpack = require('webpack-stream');


var config = {
    projectDir: __dirname + '/src/Resources/public',
    mopa: __dirname + '/../../mopa/bootstrap-bundle/Mopa/Bundle/BootstrapBundle/Resources/public',
    sonata: __dirname + '/../../sonata-project/admin-bundle/src/Resources/public',
    nodeDir: __dirname + '/node_modules'
};

gulp.task('sass', function () {
    return gulp.src([
        config.projectDir + '/vendor/select2/dist/css/select2.min.css',
        config.projectDir + '/vendor/select2/dist/css/select2-bootstrap.min.css',
        config.projectDir + '/vendor/jqueryui/themes/base/jquery-ui.css',
        config.projectDir + '/vendor/smalot-bootstrap-datetimepicker/css/bootstrap-datetimepicker.css',
        config.projectDir + '/sass/initcms_bootstrap.scss',
        config.projectDir + '/vendor/x-editable-bs4/dist/bootstrap4-editable/css/bootstrap-editable.css'
    ])
        .pipe(sourcemaps.init())
        .pipe(sass())
        // .pipe(csso())
        .pipe(concat('networking_initcms.css'))
        .pipe(sourcemaps.write('.'))
        .pipe(gulp.dest(config.projectDir + '/css'));
});

gulp.task('admin-navbar', function () {
    return gulp.src([
        config.projectDir + '/less/admin-navbar-standalone.less',
    ])
        .pipe(sourcemaps.init())
        .pipe(less())
        // .pipe(csso())
        .pipe(concat('admin-navbar.css'))
        .pipe(sourcemaps.write('.'))
        .pipe(gulp.dest(config.projectDir + '/css'));
});

gulp.task('jquery', function () {
    return gulp.src([
        config.projectDir + '/vendor/jquery/dist/jquery.min.js',
        config.projectDir + '/vendor/jqueryui/jquery-ui.min.js',
        config.projectDir + '/vendor/jquery-ui-touch-punch/jquery.ui.touch-punch.min.js'
    ])
        .pipe(sourcemaps.init())
        .pipe(uglify())
        .pipe(concat('jquery.js'))
        .pipe(sourcemaps.write('./maps/'))
        .pipe(gulp.dest(config.projectDir + '/js'));
});


gulp.task('bootstrap', function () {
    return gulp.src([
        config.nodeDir + '/bootstrap/dist/js/bootstrap.bundle.js',
        config.nodeDir + '/bootstrap/js/dist/util.js',
        config.projectDir + '/vendor/x-editable-bs4/dist/bootstrap4-editable/js/bootstrap-editable.js'
    ])
        .pipe(concat('bootstrap.js'))
        .pipe(sourcemaps.write('./maps/'))
        .pipe(gulp.dest(config.projectDir + '/js'));
});

gulp.task('app', function () {
    return gulp.src([
        config.projectDir + '/js/mopabootstrap-collection.js',
        config.projectDir + '/vendor/smalot-bootstrap-datetimepicker/js/bootstrap-datetimepicker.js',
        config.projectDir + '/vendor/select2/dist/js/select2.full.js',
        config.projectDir + '/vendor/jquery-form/jquery.form.js',
        config.projectDir + '/vendor/bootstrap-contextmenu/bootstrap-contextmenu.js',
        config.projectDir + '/vendor/featherlight/src/featherlight.js',
        config.sonata + '/treeview.js'
    ])
        .pipe(sourcemaps.init())
        .pipe(uglify())
        .pipe(concat('app.js'))
        .pipe(sourcemaps.write('./maps/'))
        .pipe(gulp.dest(config.projectDir + '/js'));
});

gulp.task('imageEditors', function () {
    return gulp.src([
        config.projectDir + '/js/filebot.js'

    ])
        .pipe(webpack(require('./webpack.config.js')))
        .pipe(gulp.dest(config.projectDir + '/js'));
})


gulp.task('default', gulp.parallel('sass', 'jquery', 'bootstrap', 'app', 'admin-navbar', 'imageEditors'));