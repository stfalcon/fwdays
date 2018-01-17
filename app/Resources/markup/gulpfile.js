'use strict';

var gulp = require('gulp'),
    postCss = require('gulp-postcss'),
    reporter    = require('postcss-reporter'),
    styleLint   = require('stylelint'),
    postScss = require('postcss-scss'),
    watch = require('gulp-watch'),
    prefixer = require('gulp-autoprefixer'),
    sass = require('gulp-sass'),
    sourceMaps = require('gulp-sourcemaps'),
    cssImport = require('gulp-cssimport'),
    cssMin = require('gulp-clean-css'),
    uglify = require('gulp-uglify'),
    imageMin = require('gulp-imagemin'),
    pngQuant = require('imagemin-pngquant'),
    gulpIf = require('gulp-if'),
    rigger = require('gulp-rigger'),
    browserSync = require('browser-sync'),
    runSequence = require('run-sequence'),
    rimraf = require('rimraf'),
    argv = require('yargs').argv,
    reload = browserSync.reload,
    prod = argv.prod,
    dev = !argv.prod;

var path = {
    build: {
        html: 'build/',
        js: 'build/js/',
        styles: 'build/styles/',
        img: 'build/img/',
        fonts: 'build/fonts/'
    },
    src: {
        html: 'src/*.html',
        js: 'src/js/main.js',
        styles: 'src/styles/main.scss',
        img: 'src/img/**/*.*',
        fonts: 'src/fonts/**/*.*'
    },
    watch: {
        html: 'src/**/*.html',
        js: 'src/js/**/*.js',
        styles: 'src/styles/**/*.scss',
        img: 'src/img/**/*.*',
        fonts: 'src/fonts/**/*.*'
    },
    clean: './build'
};

gulp.task('webServer', function () {
    var config = {
        server: {
            baseDir: "./build"
        },
        // tunnel: true,
        host: 'localhost',
        port: 9000,
        logPrefix: "frontend"
    };
    
    browserSync(config);
});

gulp.task('clean', function (cb) {
    rimraf(path.clean, cb);
});

gulp.task('html:build', function () {
    gulp.src(path.src.html)
        .pipe(rigger())
        .pipe(reload({stream: true}))
        .pipe(gulp.dest(path.build.html));
});

gulp.task('js:build', function () {
    gulp.src(path.src.js)
        .pipe(rigger())
        .pipe(gulpIf(dev, (sourceMaps.init())))
        .pipe(gulpIf(prod, uglify()))
        .pipe(gulpIf(dev, (sourceMaps.write())))
        .pipe(gulp.dest(path.build.js))
        .pipe(reload({stream: true}));
});

gulp.task('scss-lint', function () {
    var config = require('./stylelintrc.config.js');
    var processors = [
        styleLint(config),
        reporter({
            clearAllMessages: true
        })
    ];

    gulp.src('src/styles/**/*.scss')
        .pipe(gulpIf(dev, postCss(processors, {syntax: postScss})));
});

gulp.task('styles:build', function () {
    gulp.src(path.src.styles)
        .pipe(gulpIf(dev, sourceMaps.init()))
        .pipe(sass({
            includePaths: ['src/styles/'],
            outputStyle: 'compressed',
            sourceMap: true,
            errLogToConsole: true
        }))
        .pipe(cssImport())
        .pipe(prefixer())
        .pipe(cssMin())
        .pipe(gulpIf(dev, sourceMaps.write()))
        .pipe(gulp.dest(path.build.styles))
        .pipe(reload({stream: true}));
});

gulp.task('image:build', function () {
    gulp.src(path.src.img)
        .pipe(gulpIf(prod, imageMin({
            progressive: true,
            svgoPlugins: [{removeViewBox: false}],
            use: [pngQuant()],
            interlaced: true
        })))
        .pipe(gulp.dest(path.build.img));
});

gulp.task('fonts:build', function() {
    gulp.src(path.src.fonts)
        .pipe(gulp.dest(path.build.fonts));
});

gulp.task('build', function (cb) {
    runSequence('clean',[
        'html:build',
        'js:build',
        'scss-lint',
        'styles:build',
        'fonts:build',
        'image:build'
    ], cb)
});

gulp.task('watch', function(){
    watch([path.watch.html], function() {
        gulp.start('html:build');
    });
    watch([path.watch.js], function() {
        gulp.start('js:build');
    });
    watch([path.watch.styles], function() {
        gulp.start('scss-lint');
    });
    watch([path.watch.styles], function() {
        gulp.start('styles:build');
    });
    watch([path.watch.img], function() {
        gulp.start('image:build');
    });
    watch([path.watch.fonts], function() {
        gulp.start('fonts:build');
    });
});

gulp.task('default', function (cb) {
    runSequence('build',[
        'webServer',
        'watch'
    ], cb);
});