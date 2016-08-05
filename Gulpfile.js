var gulp = require('gulp');
var sass = require('gulp-sass');

gulp.task('sass', function () {
    gulp.src('drupal/themes/custom/een/scss/een.scss')
        .pipe(sass({
            includePaths: ['scss']
        }))
        .pipe(gulp.dest('drupal/themes/custom/een/css'));
});

gulp.task('default', function () {
    gulp.watch('drupal/themes/custom/een/scss/*.scss', ['sass']);
});
