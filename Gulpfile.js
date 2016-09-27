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

var jsDirs = [
  'node_modules/govuk_frontend_toolkit/javascripts/govuk/selection-buttons.js',
  'node_modules/govuk_template_mustache/assets/javascripts/govuk-template.js',
  'node_modules/angular/lib/angular.min.js',
  (themeDir + '/js/**/*.js')
];

var imgDirs = [
  'node_modules/govuk_frontend_toolkit/images/**/*',
  'node_modules/govuk_template_mustache/assets/images/**/*',
  'node_modules/govuk_template_mustache/assets/stylesheets/images/**/*',
  (themeDir + '/img/**/*')
];

var fontDirs = [
  'node_modules/font-awesome/fonts/*',
  (themeDir + '/fonts/**/*')
];

var flagDir = 'node_modules/flag-icon-css/flags/**/*';

gulp.task('js', function () {
    gulp.src(jsDirs)
        .pipe(babel({
            ignore: ['node_modules'],
            presets: ['es2015']
        }))
        .pipe(concat('bundle.js'))
        .pipe(minify())
        .pipe(gulp.dest(themeDir + '/dist/js'))
});

gulp.task('js-dev', function () {
    gulp.src(jsDirs)
        .pipe(sourcemaps.init())
        .pipe(babel({
            ignore: ['node_modules'],
            presets: ['es2015']
        }))
        .pipe(concat('bundle.js'))
        .pipe(sourcemaps.write('.'))
        .pipe(gulp.dest(themeDir + '/dist/js'))
});

gulp.task('img', function () {
    gulp.src(imgDirs)
        .pipe(copy())
        .pipe(gulp.dest(themeDir + '/dist/img'));
});

gulp.task('fonts', function () {
    gulp.src(fontDirs)
        .pipe(copy())
        .pipe(gulp.dest(themeDir + '/dist/fonts'));
});

gulp.task('flags', function () {
    gulp.src(flagDir)
        .pipe(copy())
        .pipe(gulp.dest(themeDir + '/dist/flags'));
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
                'node_modules/flag-icon-css/sass',
                'node_modules/font-awesome/scss'
            ]
        }))
        .pipe(gulp.dest(themeDir + '/dist/css'));

     gulp.src('drupal/themes/custom/een/scss/ie8.scss')
        .pipe(sass({
            outputStyle: 'compressed',
            sourceComments: 'map',
            includePaths: []
        }))
        .pipe(gulp.dest(themeDir + '/dist/css'));
});

gulp.task('watch', function () {
    gulp.watch(themeDir + '/scss/**/*.scss', ['css']);
    gulp.watch([themeDir + '/js/**/*.js'], ['js-dev']);
});

gulp.task('default', ['js', 'img', 'css', 'fonts', 'flags']);
