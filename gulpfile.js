/**
 * Nextcloud - Inventory
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Bernhard Posselt <dev@bernhard-posselt.com>
 * @copyright Bernhard Posselt 2012, 2014
 *
 * @author Georg Ehrke
 * @copyright 2017 Georg Ehrke <oc.list@georgehrke.com>
 *
 * @author Raimund Schlüßler
 * @copyright 2017 Raimund Schlüßler <raimund.schluessler@mailbox.org>
 */

/*jslint node: true */
'use strict';

// get plugins
const gulp = require('gulp'),
	uglify = require('gulp-uglify'),
	jshint = require('gulp-jshint'),
	gutil = require('gulp-util'),
	KarmaServer = require('karma').Server,
	concat = require('gulp-concat'),
	wrap = require('gulp-wrap'),
	strip = require('gulp-strip-banner'),
	babel = require('gulp-babel'),
	stylelint = require('gulp-stylelint'),
	sourcemaps = require('gulp-sourcemaps'),
	svgSprite = require('gulp-svg-sprite'),
	webpack = require('webpack'),
	webpackStream = require('webpack-stream'),
	webpackConfig = require('./webpack.common.js');

// configure
const buildTarget = 'app.min.js';
const scssBuildTarget = 'style.scss';
const karmaConfig = __dirname + '/tests/js/config/karma.js';
const destinationFolder = __dirname + '/js/public/';
const scssDestinationFolder = __dirname + '/css/';

const jsSources = [
	'js/app/**/*.js'
];
const scssSources = [
	'css/src/*.scss'
];

const testSources = ['tests/js/unit/**/*.js'];
const lintSources = jsSources.concat(testSources).concat(['*.js']);
const watchSources = lintSources.concat(['*js/app/**/*.vue']);

const svgConfig = {
	shape: {
		transform: []
	},
	mode: {
		css: {		// Activate the «css» mode
			bust: false,
			common: 'icon',
			dimensions: '',
			prefix: '.icon-%s',
			sprite: "../img/sprites.svg",
			render: {
				scss: {
					dest: "src/sprites.scss"
				}
			}
		}
	}
};

// tasks
// gulp.task('build', ['lint'], () => {
	// return gulp.pipe(webpackStream( require('./webpack.config.js') ), webpack);
// 	return gulp.src(jsSources)
// 		.pipe(babel({
// 			presets: ['babel-preset-env'],
// 			compact: false,
// 			babelrc: false,
// 			ast: false
// 		}))
// 		.pipe(strip())
// 		.pipe(sourcemaps.init({identityMap: true}))
// 		.pipe(concat(buildTarget))
// 		.pipe(wrap(`(function($, oc_requesttoken, undefined){
// 	'use strict';
//
// 	<%= contents %>
// })(jQuery, oc_requesttoken);`))
// 		.pipe(uglify())
// 		.pipe(sourcemaps.write('./'))
// 		.pipe(gulp.dest(destinationFolder));
// });
gulp.task('build', ['lint'], function(callback) {
	return webpackStream(webpackConfig)
	.pipe(gulp.dest(destinationFolder));
});


gulp.task('default', ['build', 'scsslint', 'scssConcat']);

gulp.task('lint', () => {
	return gulp.src(lintSources)
		.pipe(jshint('.jshintrc'))
		.pipe(jshint.reporter('default'))
		.pipe(jshint.reporter('fail'));
});

gulp.task('scsslint', () => {
	return gulp.src(scssSources)
		.pipe(stylelint ({
			reporters: [{
				formatter: 'string',
				console: true
			}]
		}));
});

gulp.task('scssConcat', ['svg_sprite'], () => {
	return gulp.src(scssSources)
		.pipe(concat(scssBuildTarget))
		.pipe(gulp.dest(scssDestinationFolder));
});

gulp.task('scssConcatWatch', () => {
	return gulp.src(scssSources)
		.pipe(concat(scssBuildTarget))
		.pipe(gulp.dest(scssDestinationFolder));
});

gulp.task('watch', () => {
	gulp.watch(watchSources, ['build']);
	gulp.watch(scssSources, ['scssConcatWatch']);
});

gulp.task('karma', (done) => {
	new KarmaServer({
		configFile: karmaConfig,
		singleRun: true,
		browsers: ['Firefox'],
		reporters: ['progress', 'coverage']
	}, done).start();
});

gulp.task('watch-karma', (done) => {
	new KarmaServer({
		configFile: karmaConfig,
		autoWatch: true,
		browsers: ['Firefox'],
		reporters: ['progress', 'coverage']
	}, done).start();
});

gulp.task('svg_sprite', () => {
	return gulp.src('**/*.svg', {cwd: 'img/src'})
		.pipe(svgSprite(svgConfig))
		.pipe(gulp.dest('.'));
});
