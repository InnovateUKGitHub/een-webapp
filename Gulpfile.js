var gulp = require('gulp'),
    sass = require('gulp-sass'),
    minify = require('gulp-minify'),
    watch = require('gulp-watch'),
    gulp = require('gulp'),
    sourcemaps = require('gulp-sourcemaps'),
    babel = require('gulp-babel'),
    concat = require('gulp-concat');

gulp.task('6to5', function () {
    gulp.src([
        'node_modules/govuk_frontend_toolkit/javascripts/govuk/selection-buttons.js',
        'node_modules/govuk_template_mustache/assets/javascripts/govuk-template.js',
        'drupal/themes/custom/een/js/src/*.js'
    ])
    .pipe(sourcemaps.init())
    .pipe(babel({
        ignore: [
          'node_modules/govuk_frontend_toolkit/javascripts/govuk/selection-buttons.js',
          'node_modules/govuk_template_mustache/assets/javascripts/govuk-template.js'
        ],
        presets: ['es2015']
    }))
    .pipe(concat('bundle.js'))
    .pipe(sourcemaps.write('.'))
    .pipe(gulp.dest('drupal/themes/custom/een/js/dist'))
});

gulp.task('css', function () {
    gulp.src('drupal/themes/custom/een/scss/een.scss')
        .pipe(sass({
            outputStyle: 'compressed',
            sourceComments: 'map',
            includePaths: [
                'drupal/themes/custom/een/scss/',
                'node_modules/govuk_frontend_toolkit/stylesheets',
                'node_modules/govuk_template_mustache/assets/stylesheets',
                'node_modules/govuk-elements-sass/public/sass',
                'node_modules/flag-icon-css/sass'
            ]
        }))
        .pipe(gulp.dest('drupal/themes/custom/een/css'));
});

gulp.task('watch', function () {
    gulp.watch('drupal/themes/custom/een/scss/**/*.scss', ['css']);
    gulp.watch(['drupal/themes/custom/een/js/*.js'], ['6to5']);
});

gulp.task('default', ['css', '6to5']);
