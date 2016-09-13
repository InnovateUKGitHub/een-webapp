var gulp = require('gulp'),
    sass = require('gulp-sass'),
    minify = require('gulp-minify'),
    watch = require('gulp-watch'),
    copy = require('gulp-contrib-copy'),
    gulp = require('gulp'),
    sourcemaps = require('gulp-sourcemaps'),
    babel = require('gulp-babel'),
    concat = require('gulp-concat');

var themeDir = 'drupal/themes/custom/een';

var entry = [
  'node_modules/govuk_frontend_toolkit/javascripts/govuk/selection-buttons.js',
  'node_modules/govuk_template_mustache/assets/javascripts/govuk-template.js',
  'node_modules/angular/lib/angular.min.js',
  (themeDir + '/js/**/*.js')
];

gulp.task('js', function () {
    gulp.src(entry)
        .pipe(babel({
            ignore: ['node_modules'],
            presets: ['es2015']
        }))
        .pipe(concat('bundle.js'))
        .pipe(minify())
        .pipe(gulp.dest(themeDir + '/dist'))
});

gulp.task('js-dev', function () {
    gulp.src(entry)
        .pipe(sourcemaps.init())
        .pipe(babel({
            ignore: ['node_modules'],
            presets: ['es2015']
        }))
        .pipe(concat('bundle.js'))
        .pipe(sourcemaps.write('.'))
        .pipe(gulp.dest(themeDir + '/dist'))
});

gulp.task('img', function () {
    gulp.src(themeDir + '/img/**')
        .pipe(copy())
        .pipe(gulp.dest(themeDir + '/dist'));
});

gulp.task('css', function () {
    gulp.src(themeDir + '/scss/een.scss')
        .pipe(sass({
            outputStyle: 'compressed',
            sourceComments: 'map',
            includePaths: [
                (themeDir + '/scss/'),
                'node_modules/govuk_frontend_toolkit/stylesheets',
                'node_modules/govuk_template_mustache/assets/stylesheets',
                'node_modules/govuk-elements-sass/public/sass',
                'node_modules/flag-icon-css/sass'
            ]
        }))
        .pipe(gulp.dest(themeDir + '/dist'));
});

gulp.task('watch', function () {
    gulp.watch(themeDir + '/scss/**/*.scss', ['css']);
    gulp.watch([themeDir + '/js/**/*.js'], ['js-dev']);
});

gulp.task('default', ['css', 'js', 'img']);
