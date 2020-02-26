const gulp = require('gulp');
const uglify = require('gulp-uglify');
const rename = require('gulp-rename');
const jshint = require('gulp-jshint');
const filter = require('gulp-filter');
const jshintStylish = require('jshint-stylish');
const guppy = require('git-guppy')(gulp);

const config = {
  scripts: [
    './assets/js/basic_config_mercadopago.js',
    './assets/js/basic-cho.js',
    './assets/js/credit-card.js',
    './assets/js/custom_config_mercadopago.js',
    './assets/js/ticket_config_mercadopago.js',
    './assets/js/ticket.js',
  ],
};

gulp.task('jshint', function() {
  return gulp.src(config.scripts)
    .pipe(jshint('.jshintrc'))
    .pipe(jshint.reporter(jshintStylish))
    .pipe(jshint.reporter('fail'));
});

gulp.task('scripts', function() {
  return gulp.src(config.scripts)
    .pipe(uglify())
    .pipe(rename({ extname: '.min.js' }))
    .pipe(gulp.dest('./assets/js/'));
});

gulp.task('pre-commit', function () {
  return guppy.stream('pre-commit')
    .pipe(filter(config.scripts))
    .pipe(jshint('.jshintrc'))
    .pipe(jshint.reporter(jshintStylish))
    .pipe(jshint.reporter('fail'));
});

gulp.task('default', gulp.series('scripts'));
