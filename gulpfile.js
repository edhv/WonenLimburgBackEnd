var fs = require('fs')
var gulp = require('gulp')
var runSequence = require('run-sequence')
var browserSync = require('browser-sync')
var plumber = require('gulp-plumber')
var bump = require('gulp-bump')

// // Project plugins
// var rev          = require('gulp-rev')
var sass = require('gulp-sass')
var include = require('gulp-include')

var clean = require('gulp-clean')
var concat = require('gulp-concat')
// var rename       = require('gulp-rename')
var replace = require('gulp-replace-task')
// var collect      = require('gulp-rev-collector')
// var imagemin     = require('gulp-imagemin')
// var pngquant     = require('imagemin-pngquant')
var uglify = require('gulp-uglify')
// var minifyCss    = require('gulp-minify-css')
// var awspublish   = require('gulp-awspublish')
// var autoprefixer = require('gulp-autoprefixer')
var shell = require('gulp-shell')

// // Configuration
var config = JSON.parse(fs.readFileSync('./gulp.json'))

var themePath = 'wp-content/themes/' + config.theme
var distPath = themePath + '/dist'
var assetPath = themePath + '/assets'

var path = {
  src_css: assetPath + '/scss/',
  dist_css: distPath + '/css/',
  src_js: assetPath + '/js/',
  dist_js: distPath + '/js/',
  src_twig: assetPath + '/templates/',
  dist_twig: distPath + '/templates/',
  src_img: assetPath + '/img/',
  dist_img: distPath + '/img/'

}

/* Helpers */
gulp.task('clean', function () {
  return gulp.src(distPath, {read: false})
    .pipe(clean({force: true}))
})

gulp.task('browsersync', function () {
  browserSync(config.browserSync.development)
})

gulp.task('browsersync-watch',
  ['browsersync'],
  function () {
    // browsersync watch the dist folder for changes
    gulp.watch([
      path.dist_twig + '**/*.*',
      path.dist_js + '**/*.*',
      themePath + '/**/*.php'
    ]).on('change', browserSync.reload)
  }
)

/* Watch */

gulp.task('compile-templates', function () {
  return gulp.src(path.src_twig + '**/*.twig')
    .pipe(gulp.dest(path.dist_twig))
})

gulp.task('process-images', function () {
  return gulp.src(path.src_img + '**/*')
    .pipe(gulp.dest(path.dist_img))
})

gulp.task('bump-patch', function () {
  gulp.src(['./package.json'])
    .pipe(bump({type: 'patch'}))
    .pipe(gulp.dest('./'))
})

gulp.task('bump-minor', function () {
  gulp.src(['./package.json'])
    .pipe(bump({type: 'minor'}))
    .pipe(gulp.dest('./'))
})

gulp.task('bump-major', function () {
  gulp.src(['./package.json'])
    .pipe(bump({type: 'major'}))
    .pipe(gulp.dest('./'))
})


/* Docker */

gulp.task('docker-compose', shell.task([
  'eval $(docker-machine env ' + config.docker.machine + ') && docker-compose up -d'
]))

gulp.task('docker-build', shell.task([
  'eval $(docker-machine env ' + config.docker.machine + ') && docker-compose build'
]))

gulp.task('docker-status', shell.task([
  'eval $(docker-machine env ' + config.docker.machine + ') && docker-compose ps'
]))

gulp.task('docker-logs', shell.task([
  'eval $(docker-machine env ' + config.docker.machine + ') && docker-compose logs'
]))


/* Styles */
gulp.task('compile-styles', function () {
  return (
  gulp.src([
    path.src_css + 'styles.scss',
    path.src_css + 'editor-styles.scss'
  ])
    .pipe(plumber())
    .pipe(sass().on('error', sass.logError))
    // {outputStyle: 'compressed'}
    // .pipe(autoprefixer({
    //     browsers: ['last 2 versions'],
    //     cascade: false
    // }))
    .pipe(gulp.dest(path.dist_css))
    .pipe(browserSync.stream())
  )
})

gulp.task('compile-styles-prd', function () {
  return (
  gulp.src([
    path.src_css + 'styles.scss',
    path.src_css + 'editor-styles.scss'
  ])
    .pipe(plumber())
    .pipe(sass({outputStyle: 'compressed'}).on('error', sass.logError))
    // {outputStyle: 'compressed'}
    // .pipe(autoprefixer({
    //     browsers: ['last 2 versions'],
    //     cascade: false
    // }))
    .pipe(gulp.dest(path.dist_css))
    .pipe(browserSync.stream())
  )
})

// uglify the javascript
gulp.task('copy-fonts', function () {
  return gulp.src(assetPath + '/fonts/**/*')
    .pipe(gulp.dest(distPath + '/fonts/'))
})

/* Scripts */

// compile the application javascript using gulp-include
gulp.task('js-compile-app', function () {
  return (
  gulp.src(path.src_js + '/app.js')
    .pipe(include())
    .on('error', console.log)
    .pipe(gulp.dest(path.dist_js))
  )
})

// concat the vendors into a single js
gulp.task('js-concat-vendor', function () {
  return (
  gulp.src([
    path.src_js + 'vendor/*.js'
  ])
    .pipe(concat('vendor.js'))
    .pipe(gulp.dest(path.dist_js))
  )
})

// uglify the javascript
gulp.task('js-optimize', function () {
  return gulp.src(path.dist_js + '/app.js')
    .pipe(uglify())
    .pipe(gulp.dest(path.dist_js))
})

// gulp.task('optimize-styles', function () {
//   return gulp.src(distPath + '/css/style.css')
//     .pipe(minifyCss())
//     .pipe(gulp.dest(distPath + '/css'))
// })

// gulp.task('develop', ['watch'])

// gulp.task('clean', function () {
//   return gulp.src(distPath, {read: false})
//     .pipe(clean({force: true}))
// })

gulp.task(
  'develop',
  [
    'browsersync-watch',
    'compile-styles',
    'copy-fonts',
    'js-compile-app',
    'js-concat-vendor',
    'compile-templates',
    'process-images'
    // 'compile-fonts',
    // 'compile-images',

  ],
  function () {
    gulp.watch([ path.src_css + '**/*.scss' ], ['compile-styles'])
    gulp.watch([ path.src_js + '**/*.js', '!' + path.src_js + 'vendor/*.js' ], ['js-compile-app'])
    gulp.watch([ path.src_img + '**/*.*' ], ['process-images'])
    gulp.watch([ path.src_twig + '**/*.twig' ], ['compile-templates'])
    gulp.watch([ path.src_js + 'vendor/**/*.js' ], ['js-concat-vendor'])
  }
)

gulp.task('production', function (callback) {
  runSequence(
    'clean',
    ['copy-fonts', 'process-images', 'compile-templates', 'compile-styles-prd', 'js-compile-app', 'js-concat-vendor'],
    'js-optimize',
    callback
  )
})

gulp.task('deploy', function (callback) {
  runSequence(
    'clean',
    ['copy-fonts', 'process-images', 'compile-templates', 'compile-styles', 'js-compile-app', 'js-concat-vendor'],
    callback
  )
})


