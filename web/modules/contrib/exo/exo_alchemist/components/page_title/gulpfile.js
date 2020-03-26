const gulp = require('gulp');
const browserSync = require('browser-sync').create();
const execSync = require('child_process').execSync;
const plumber = require('gulp-plumber');
const sass = require('gulp-sass');
const autoprefixer = require('gulp-autoprefixer');
const sourcemaps = require('gulp-sourcemaps');
const cleanCss = require('gulp-clean-css');
const ts = require('gulp-typescript');

const config = {
  cssSrc: 'src/styles/*.scss',
  cssDist: '.',
  scssIncludePaths: [],
  tsSrc: 'src/scripts/*.ts',
  tsDist: '.',
  browserSyncProxy : "http://ind.ash",
  browserSyncPort : 3000,
}

gulp.task('drupal', function() {
  execSync('drush exo-scss');
  const drupal = JSON.parse(execSync('drush status --format=json').toString());
  config.scssIncludePaths.push(drupal['root'] + '/' + drupal['site'] + '/files/exo');
  return Promise.resolve();
});

gulp.task('sass-dev', function () {
  return gulp.src(config.cssSrc)
    .pipe(plumber())
    .pipe(sourcemaps.init())
    .pipe(sass({
      includePaths: config.scssIncludePaths,
    }).on('error', sass.logError))
    .pipe(autoprefixer('last 2 versions', '> 1%'))
    .pipe(sourcemaps.write())
    .pipe(gulp.dest(config.cssDist))
    .pipe(browserSync.stream());
});

gulp.task('sass', function () {
  return gulp.src(config.cssSrc)
    .pipe(plumber())
    .pipe(sass({
      includePaths: config.scssIncludePaths,
    }).on('error', sass.logError))
    .pipe(autoprefixer('last 2 versions', '> 1%'))
    .pipe(cleanCss())
    .pipe(gulp.dest(config.cssDist));
});

gulp.task('typescript-dev', function () {
  return gulp.src(config.tsSrc)
    .pipe(plumber())
    .pipe(sourcemaps.init())
    .pipe(ts({
        target: 'ES5'
    }))
    .pipe(sourcemaps.write())
    .pipe(gulp.dest(config.tsDist));
});

gulp.task('typescript', function () {
  return gulp.src(config.tsSrc)
    .pipe(ts({
      noImplicitAny: true,
      target: 'ES5',
    }))
    .pipe(gulp.dest(config.tsDist));
});

gulp.task('watch', function() {

  browserSync.init({
    proxy: config.browserSyncProxy,
    port: config.browserSyncPort,
    open: false,
    notify: false,
  });

	gulp.watch(config.cssSrc, gulp.parallel('sass-dev'));
  gulp.watch(config.tsSrc, gulp.parallel('typescript-dev'));
  gulp.watch(['*.twig', '*.html', '*.js']).on('change', function (file) {
    browserSync.reload(file.path);
  });
});

gulp.task('default', gulp.series('drupal', 'sass-dev', 'typescript-dev', 'watch'));
gulp.task('production', gulp.parallel('drupal', 'sass', 'typescript'));
