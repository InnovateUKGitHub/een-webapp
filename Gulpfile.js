var gulp = require('gulp'),
    sass = require('gulp-sass'),
    minify = require('gulp-minify'),
    watch = require('gulp-watch');

gulp.task('js', function () {
    gulp.src([
        'node_modules/govuk_frontend_toolkit/javascripts/govuk/selection-buttons.js',
        'node_modules/govuk_template_mustache/assets/javascripts/govuk-template.js',
        'drupal/themes/custom/een/js/*.js'
    ])
        .pipe(minify())
        .pipe(gulp.dest('drupal/themes/custom/een/js/min'))
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

     gulp.src('drupal/themes/custom/een/scss/ie8.scss')
        .pipe(sass({
            outputStyle: 'compressed',
            sourceComments: 'map',
            includePaths: []
        }))
        .pipe(gulp.dest('drupal/themes/custom/een/css'));
});

gulp.task('watch', function () {
    gulp.watch('drupal/themes/custom/een/scss/**/*.scss', ['css']);
    gulp.watch(['drupal/themes/custom/een/js/*.js'], ['js']);
});

gulp.task('default', ['css', 'js']);

