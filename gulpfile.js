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
console.log(themePath)

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

var hosts = {
  local: {
    folder: 'wp-content/',
    database: {
      host: 'http://jeroens-macbook-pro.local/',
      port: '3306',
      name: 'wordpress',
      user: 'root',
      password: 'password'
    },
    wordpress: {
      db_prefix: 'wp_',
      domain: 'jeroens-macbook-pro.local:8080',
      folder: 'a'
    }
  }
}

/*
#
PROJECT_ROOT_FOLDER="/home/server/public_html/clients/gandh/website/"
PROJECT_FOLDER="${PROJECT_ROOT_FOLDER}dev/wp-content/"
DEPLOY_FOLDER="${PROJECT_ROOT_FOLDER}_deploy/"

# SERVER
SERVER_DOMAIN="www.jeroenbraspenning.nl"
SERVER_FOLDER="clients/gandh/website/dev"
SERVER_HOST="jeroenbraspenning.nl"
SERVER_USER="server"

# DB
DB_HOST="localhost"
DB_NAME="server_gandh"
DB_USER="server_db"
DB_PASSWORD="7jlnY1sg"

# WORDPRESS
WP_PREFIX="wp_dev_"
 */

// var revision = Math.floor(Date.now() / 1000)

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

// gulp.task('upload-dev', shell.task([
//   'rsync -av --progress --delete --exclude "uploads"  --exclude ".git"  --exclude "docker-runtime"  --exclude "node_modules"  --exclude "w3tc-config" --exclude "cache" --exclude "advanced-cache.php" ' + hosts.local.folder + ' ' + hosts.dev.user + '@' + hosts.dev.host + ':' + hosts.dev.root + hosts.dev.folder
// ]))

// gulp.task('upload-prod', shell.task([
//   'rsync -av --progress --exclude "uploads" --exclude "plugins/daph-press/archives" --exclude "w3tc-config" --exclude "cache" --exclude "advanced-cache.php" ' + hosts.local.folder + ' ' + hosts.prod.user + '@' + hosts.prod.host + ':' + hosts.prod.root + hosts.prod.folder
// ]))

gulp.task('migrate-db-dev', shell.task([
  './scripts/migrate-db.sh --target-user ' + hosts.dev.user +
  ' --target-root-folder ' + hosts.dev.root +
  ' --db-host ' + hosts.local.database.host +
  ' --db-port ' + hosts.local.database.port +
  ' --db-user ' + hosts.local.database.user +
  ' --db-password ' + hosts.local.database.password +
  ' --db-name ' + hosts.local.database.name +
  ' --db-target-host ' + hosts.dev.database.host +
  ' --db-target-user ' + hosts.dev.database.user +
  ' --db-target-password ' + hosts.dev.database.password +
  ' --db-target-name ' + hosts.dev.database.name +
  ' --source-host ' + hosts.local.wordpress.domain +
  ' --target-host ' + hosts.dev.wordpress.domain +
  ' --source-wp-folder ' + hosts.local.wordpress.folder +
  ' --target-wp-folder ' + hosts.dev.wordpress.folder +
  ' --source-wp-db-prefix ' + hosts.local.wordpress.db_prefix +
  ' --target-wp-db-prefix ' + hosts.dev.wordpress.db_prefix
]))

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

  // gulp.watch(assetPath + '/fonts/**/*',      ['compile-fonts'])
  // gulp.watch(assetPath + '/images/**/*',     ['compile-images'])
  // gulp.watch(assetPath + '/templates/**/*',  ['compile-templates'])
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

// gulp.task('deploy-dev', function (callback) {
//   runSequence(
//     'clean',
//     ['compile-templates', 'process-images', 'compile-styles', 'copy-fonts', 'js-compile-app', 'js-concat-vendor'],
//     'upload-dev',
//     // 'migrate-db-dev',
//     callback
//   )
// })

// gulp.task('deploy-prod', function (callback) {
//   runSequence(
//     'clean',
//     ['copy-fonts', 'process-images', 'compile-templates', 'compile-styles', 'js-compile-app', 'js-concat-vendor'],
//     'js-optimize',
//     'upload-prod',
//     callback
//   )
// })

// 
// 
// gulp.task('deploy-assets', function (callback) {
//   runSequence(
//     'clean',
//     ['compile-styles', 'compile-scripts', 'compile-images', 'compile-fonts', 'compile-templates'],
//     ['optimize-styles', 'optimize-scripts', 'optimize-images'],
//     'version-assets',
//     ['replace-versisonned-assets-in-assets', 'replace-versisonned-assets-in-templates'],
//     'gzip-assets',
//     'publish-to-s3',
//     callback
//   )
// })

// // Scripts
// // -------

// gulp.task('compile-scripts', function () {
//   return (
//     gulp.src([
//       assetPath + '/js/script.js'
//     ])
//     .pipe(concat('script.js'))
//     .pipe(gulp.dest(distPath + '/js'))
//   )
// })

// gulp.task('optimize-scripts', function () {
//   return gulp.src(distPath + '/js/script.js')
//     .pipe(uglify())
//     .pipe(gulp.dest(distPath + '/js/'))
// })

// // Images
// // -------

// gulp.task('compile-images', function () {
//   return gulp.src(assetPath + '/images/**/*')
//     .pipe(gulp.dest(distPath + '/images'))
// })

// gulp.task('optimize-images', function () {
//   return gulp.src(distPath + '/images/**/*')
//     .pipe(imagemin({
//       progressive: true,
//       svgoPlugins: [{removeViewBox: false}],
//       use: [pngquant()]
//     }))
//     .pipe(gulp.dest(distPath + '/images'))
// })

// // Fonts
// // -----

// gulp.task('compile-fonts', function () {
//   return gulp.src(assetPath + '/fonts/**/*')
//     .pipe(gulp.dest(distPath + '/fonts'))
// })

// // Templates
// // ---------

// gulp.task('compile-templates', function () {
//   return gulp.src(assetPath + '/templates/**/*')
//     .pipe(gulp.dest(themePath))
// })

// // Versionning
// // -----------

// gulp.task('version-assets', function () {
//   return gulp.src(distPath + '/**/*')
//     .pipe(rev())
//     .pipe(gulp.dest(distPath))
//     .pipe(rev.manifest())
//     .pipe(gulp.dest(themePath))
// })

// gulp.task('replace-versionned-assets-in-assets', function () {
//   return gulp.src([
//       themePath + '/**/*.json',
//       distPath + '/**/*.css',
//       distPath + '/**/*.js'
//     ])
//     .pipe(collect({
//       replaceReved: true,
//       dirReplacements: {
//         '/wp-content/themes/visible/dist': config.productionAssetURL
//       }
//     }))
//     .pipe(gulp.dest(distPath))
// })

// gulp.task('replace-versisonned-assets-in-templates', function () {
//   return gulp.src([
//       themePath + '/**/*.json',
//       themePath + '/*.php'
//     ])
//     .pipe(collect({
//       replaceReved: true,
//       dirReplacements: {
//         '/wp-content/themes/visible/dist': config.productionAssetURL
//       }
//     }))
//     .pipe(gulp.dest(themePath))
// })

// // S3
// // --

// gulp.task('gzip-assets', function () {
//   return gulp.src([
//       '!' + distPath + '/**/*.gz',
//       distPath + '/**/*'
//     ])
//     .pipe(awspublish.gzip({ ext: '.gz' }))
//     .pipe(gulp.dest(distPath))
// })

// gulp.task('publish-to-s3', function () {
//   var publisher = awspublish.create(config.aws)
//   var headers = {
//     'Cache-Control': 'max-age=31536000, no-transform, public'
//   }

//   return gulp.src(distPath + '/**')
//     .pipe(publisher.publish(headers))
//     .pipe(publisher.sync())
//     .pipe(awspublish.reporter())
// })
