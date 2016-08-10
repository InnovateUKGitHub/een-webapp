var gulp = require('gulp'),
    sass = require('gulp-sass');


gulp.task('sass', function () {
    gulp.src('drupal/themes/custom/een/scss/een.scss')
        .pipe(sass({
            outputStyle: 'compressed',
            sourceComments: 'map',
            includePaths : [
                'drupal/themes/custom/een/scss/',
                'node_modules/govuk_frontend_toolkit/stylesheets',
                'node_modules/govuk_template_mustache/assets/stylesheets',
                'node_modules/govuk-elements-sass/public/sass'
            ]
        }))
        .pipe(gulp.dest('drupal/themes/custom/een/css'));
});

gulp.task('default', function () {
    gulp.watch('drupal/themes/custom/een/scss/*.scss', ['sass']);
});
