/**
 * @file
 * Gulp task definition.
 */

"use strict";

const fs = require('fs');
const extend = require('extend');
const browserSync = require('browser-sync').create();
const gulp = require('gulp');
const gutil = require('gulp-util');
const execSync = require('child_process').execSync;
// Plugins.
const sequence = require('gulp-sequence');
const clean = require('gulp-clean');
const sass = require('gulp-sass');
const sassLint = require('gulp-sass-lint');
const plumber = require('gulp-plumber');
const notify = require('gulp-notify');
const cache = require('gulp-cached');
const autoprefix = require('gulp-autoprefixer');
const rename = require('gulp-rename');
// const sourcemaps = require('gulp-sourcemaps');
const uglify = require('gulp-uglify');
const eslint = require('gulp-eslint');
const ts = require('gulp-typescript');
const babel = require('gulp-babel');
const fileinclude = require('gulp-file-include');
// Typescript
var tsProject = ts.createProject('./tsconfig.json');

/**
 * Config init.
 */
let originalConfig = require('./config.json');
if (fs.existsSync('./config.local.json')) {
  originalConfig = extend(true, originalConfig, require('./config.local'));
}
let config = extend(true, {}, originalConfig);

/**
 * Clean task.
 */
gulp.task('clean', function () {
  return gulp.src(config.clean.src, {read: false})
    .pipe(clean());
});

/**
 * Run drush to clear the theme registry
 */
let drupal;
gulp.task('drupal', function() {
  execSync('drush exo-scss');
  drupal = JSON.parse(execSync('drush status --format=json').toString());
  config.scss.includePaths.push(drupal['root'] + '/' + drupal['site'] + '/files/exo');
});

/**
 * SASS compiling.
 */
gulp.task('scss', function () {
  return gulp.src(config.scss.src)
    .pipe(plumber({
      errorHandler: function (error) {
        notify.onError({
          title: "Gulp",
          subtitle: "Failure!",
          message: "Error: <%= error.message %>",
          sound: "Beep"
        })(error);
        this.emit('end');
      }
    }))
    // .pipe(sourcemaps.init())
    .pipe(sass({
      outputStyle: config.scss.outputStyle,
      errLogToConsole: true,
      includePaths: config.scss.includePaths
    }))
    .pipe(autoprefix('last 2 versions', '> 1%', 'ie 9', 'ie 10'))
    .pipe(cache('scss'))
    .pipe(rename(function(path) {
      var matches;
      path.dirname = path.dirname.replace('scss', config.scss.dest);
      path.dirname = path.dirname.replace('/src', '');
      // exoTheme Support.
      matches = path.dirname.match(/ExoTheme\/(.*)\//);
      var exoTheme = (matches && typeof matches[1] !== 'undefined') ? matches[1] : null;
      if (exoTheme) {
        path.dirname = path.dirname.replace('/ExoTheme', '');
        path.dirname = path.dirname.replace('/' + exoTheme, '') + ('/' + exoTheme);
      }
      // exoThemeProvider Support.
      matches = path.dirname.match(/ExoThemeProvider\/(.*)\//);
      var exoThemeProvider = (matches && typeof matches[1] !== 'undefined') ? matches[1] : null;
      if (exoThemeProvider) {
        path.dirname = path.dirname.replace('/ExoThemeProvider', '');
        path.dirname = path.dirname.replace('/' + exoThemeProvider, '');
      }
    }))
    // .pipe(sourcemaps.write('./'))
    .pipe(gulp.dest('.'))
    .on('finish', function () {
      gulp.src(config.scss.src)
        .pipe(sassLint({
            configFile: '.sass-lint.yml'
          }))
        .pipe(sassLint.format());
    });
});

/**
 * Javascript compiling.
 */
gulp.task('js', function () {
  return gulp.src(config.js.src)
    .pipe(plumber())
    .pipe(eslint({
      configFile: '.eslintrc',
      useEslintrc: false
    }))
    .pipe(eslint.format())
    .pipe(uglify())
    .pipe(cache('js'))
    .pipe(rename(function(path) {
      path.dirname = path.dirname.replace('/src/js', '/' + config.js.dest);
    }))
    .pipe(gulp.dest('.'));
});

/**
 * Typescript task.
 */
gulp.task('ts', function (callback) {
  sequence('tsPackage', 'tsCompile', 'tsLint', 'tsClean')(callback);
});

/**
 * Typescript package lint.
 */
gulp.task('tsLint', function () {
  return gulp.src(config.ts.watch)
    .pipe(plumber())
    .pipe(tsProject());
});

/**
 * Typescript pagacking.
 */
gulp.task('tsPackage', function () {
  return gulp.src(config.ts.src)
    .pipe(plumber())
    .pipe(fileinclude({
      prefix: 'TS',
      basepath: '@file'
    }))
    .pipe(rename(function(path) {
      path.dirname = path.dirname.replace('/src/ts', '/' + config.ts.tmp);
    }))
    .pipe(gulp.dest('.'));
});

/**
 * Typescript package compiling.
 */
gulp.task('tsCompile', function () {
  return gulp.src(config.ts.tmpsrc)
    .pipe(plumber())
    // .pipe(sourcemaps.init())
    .pipe(tsProject(ts.reporter.nullReporter()))
    .pipe(babel({
      presets: [[
        '@babel/env', {
          "targets": {
            "browsers": ["last 2 versions", "ie >= 11"]
          },
        }
      ]]
    }))
    .pipe(uglify())
    .pipe(cache('ts'))
    .pipe(rename(function(path) {
      path.dirname = path.dirname.replace('/' + config.ts.tmp, '/' + config.ts.dest);
    }))
    // .pipe(sourcemaps.write('.'))
    .pipe(gulp.dest('.'));
});

/**
 * Clean task.
 */
gulp.task('tsClean', function () {
  return gulp.src(config.ts.clean, {read: false})
    .pipe(clean());
});

/**
 * Build task.
 */
gulp.task('build', sequence(['clean', 'drupal'], ['scss', 'js', 'ts']));

/**
 * Watch task.
 */
gulp.task('watch', ['build'], function () {
  gulp.watch(config.scss.src, ['scss']);
  gulp.watch(config.js.src, ['js']);
  // gulp.watch(config.ts.watch, ['ts']);
  // Magic project switching to ts compile only needed.
  gulp.watch(config.ts.watch).on("change", function(file) {
    const match = file.path.match(/exo\/([A-Za-z_]+)/);
    const exoModule = match[1];
    for (var i = 0; i < config.ts.src.length; i++) {
      config.ts.src[i] = originalConfig.ts.src[i].replace('exo*', '*' + exoModule);
    }
    for (var i = 0; i < config.ts.tmpsrc.length; i++) {
      config.ts.tmpsrc[i] = originalConfig.ts.tmpsrc[i].replace('exo*', '*' + exoModule);
    }
    config.ts.clean = originalConfig.ts.clean.replace('exo*', '*' + exoModule);
    gulp.start('ts');
  });
  // Watch compiled files for changes.
  gulp.watch(config.watch.src).on("change", function(file) {
    if (config.browserSync.enabled) {
      browserSync.reload(file.path);
    }
  });
});

/**
 * Static serve + watch.
 */
gulp.task('serve', ['watch'], function () {
  if (config.browserSync.enabled) {
    browserSync.init({
      proxy: config.browserSync.proxy,
      port: config.browserSync.port,
      open: config.browserSync.openAutomatically,
      notify: config.browserSync.notify,
    });
  }
});

/**
 * Default task.
 */
gulp.task('default', ['serve']);
