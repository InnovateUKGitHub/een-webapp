var gulp = require('gulp'),
    sass = require('gulp-sass'),
    cssMin = require('gulp-cssmin'),
    rename = require('gulp-rename');


gulp.task('sass', function () {
    gulp.src('drupal/themes/custom/een/scss/een.scss')
        .pipe(sass())
        .pipe(gulp.dest('drupal/themes/custom/een/css'));
});

gulp.task('css', function() {
    return gulp.src('drupal/themes/custom/een/css/*.css')
        .pipe(cssMin())
        .pipe(rename({suffix: '.min'}))
        .pipe(gulp.dest('drupal/themes/custom/een/css'));
});

gulp.task('default', function () {
    gulp.watch('drupal/themes/custom/een/scss/*.scss', ['sass']);
});
