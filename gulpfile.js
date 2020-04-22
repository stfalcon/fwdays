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
    rev = require("gulp-rev"),
    runSequence = require('run-sequence'),
    rimraf = require('rimraf'),
    argv = require('yargs').argv,
    reload = browserSync.reload,
    prod = argv.prod,
    dev = !argv.prod;

var path = {
    build: {
        js: 'public/build/js/en',
        jsuk: 'public/build/js/uk',
        styles: 'public/build/styles/',
        img: 'public/build/img/',
        fonts: 'public/build/fonts/'
    },
    src: {
        js: 'public/js/en/main.js',
        jsuk: 'public/js/uk/main.js',
        styles: 'public/styles/main.scss',
        img: 'public/img/**/*.*',
        fonts: 'public/fonts/**/*.*'
    },
    watch: {
        js: 'public/js/**/*.js',
        styles: 'public/styles/**/*.scss',
        img: 'public/img/**/*.*',
        fonts: 'public/fonts/**/*.*'
    }
};

gulp.task('clean', function (cb) {
    var paths = path.build.jsuk + ',' + path.build.js + ',' + path.build.styles + ',' + path.build.img + ',' + path.build.fonts;
    rimraf('{' + paths + '}', cb);
});

gulp.task('js:build', function () {
    gulp.src(path.src.js)
        .pipe(rigger())
        .pipe(gulpIf(dev, (sourceMaps.init())))
        .pipe(gulpIf(prod, uglify()))
        .pipe(rev())
        .pipe(gulpIf(dev, (sourceMaps.write())))
        .pipe(gulp.dest(path.build.js))
        .pipe(rev.manifest({
            merge: true
        }))
        .pipe(gulp.dest(path.build.js))
        .pipe(reload({stream: true}));

    gulp.src(path.src.jsuk)
        .pipe(rigger())
        .pipe(gulpIf(dev, (sourceMaps.init())))
        .pipe(gulpIf(prod, uglify()))
        .pipe(rev())
        .pipe(gulpIf(dev, (sourceMaps.write())))
        .pipe(gulp.dest(path.build.jsuk))
        .pipe(rev.manifest({
            merge: true
        }))
        .pipe(gulp.dest(path.build.jsuk))
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

    gulp.src('public/styles/**/*.scss')
        .pipe(gulpIf(dev, postCss(processors, {syntax: postScss})));
});

gulp.task('styles:build', function () {
    gulp.src(path.src.styles)
        .pipe(gulpIf(dev, sourceMaps.init()))
        .pipe(sass({
            includePaths: ['public/styles/'],
            outputStyle: 'compressed',
            sourceMap: true,
            errLogToConsole: true
        }))
        .pipe(cssImport())
        .pipe(prefixer())
        .pipe(cssMin())
        .pipe(rev())
        .pipe(gulpIf(dev, sourceMaps.write()))
        .pipe(gulp.dest(path.build.styles))
        .pipe(rev.manifest({
            merge: true
        }))
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
        'js:build',
        'scss-lint',
        'styles:build',
        'fonts:build',
        'image:build'
    ], cb)
});

gulp.task('watch', function(){
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
    runSequence('build', cb);
});