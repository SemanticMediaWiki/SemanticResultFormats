var gulp = require( 'gulp' );

var browserify = require( 'browserify' );
var tsify = require( 'tsify' );
var uglify = require( 'gulp-uglify' );
var concat = require( 'gulp-concat' );
var replace = require( 'gulp-replace' );
var sourcemaps = require( 'gulp-sourcemaps' );
var source = require( 'vinyl-source-stream' );
var buffer = require( 'vinyl-buffer' );


gulp.task( 'buildFiltered', function () {

	return browserify( {
		basedir: '.',
		debug: true, // true, to enable source mapping
		entries: [ 'resources/ts/bootstrap.ts' ],
		cache: {},
		packageCache: {}
	} )
	.exclude( 'jquery' )
	.plugin( tsify )
	.bundle()

	.pipe( source( 'ext.srf.filtered.js' ) )
	.pipe( buffer() )
	.pipe( sourcemaps.init( { loadMaps: true } ) )
	// .pipe( uglify() )
	.pipe( sourcemaps.write( './' ) )
	.pipe( gulp.dest( 'resources/js' ) );

} );

gulp.task( 'buildFilteredTests', function () {

	return browserify( {
		basedir: '.',
		debug: false, // false, to disable source mapping
		entries: [ 'tests/qunit/bootstrap.ts' ],
		cache: {},
		packageCache: {}
	} )
	// .exclude( 'jquery' )
	.plugin( tsify )
	.bundle()

	.pipe( source( 'ext.srf.formats.filtered.test.js' ) )
	// .pipe( buffer() )
	// .pipe( sourcemaps.init( { loadMaps: true } ) )
	// .pipe( uglify() )
	// .pipe( sourcemaps.write( './' ) )
	.pipe( gulp.dest( '../../tests/qunit/formats' ) );

} );

gulp.task( 'buildExternalJS', function () {

	var config = {
		'ext.srf.filtered.leaflet.js': [
			'node_modules/leaflet/dist/leaflet-src.js',
			'node_modules/leaflet.markercluster/dist/leaflet.markercluster-src.js',
			'node_modules/leaflet-providers/leaflet-providers.js'
		],
		'ext.srf.filtered.slider.js': [
			'node_modules/ion-rangeslider/js/ion.rangeSlider.js'
		],
		'ext.srf.filtered.select.js': [
			'node_modules/select2/dist/js/select2.js'
		]
	};

	var res = true;

	for ( var target in config ) {

		res = res && gulp.src( config[ [ target ] ] )
		.pipe( concat( target ) )
		// .pipe( uglify() )
		.pipe( gulp.dest( 'resources/js' ) );

	}

	return res;

} );

gulp.task( 'buildLeafletCSS', function () {

	return gulp
		.src( [
			'node_modules/leaflet/dist/leaflet.css',
			'node_modules/leaflet.markercluster/dist/MarkerCluster.css',
			'node_modules/leaflet.markercluster/dist/MarkerCluster.Default.css'
		] )
		.pipe( concat( 'ext.srf.filtered.leaflet.css' ) )
		.pipe( gulp.dest( 'resources/css' ) );

} );

gulp.task( 'buildSliderCSS', function () {

	return gulp
		.src( [
			'node_modules/ion-rangeslider/css/ion.rangeSlider.css',
			'node_modules/ion-rangeslider/css/ion.rangeSlider.skinNice.css'
		] )
		.pipe( concat( 'ext.srf.filtered.slider.css' ) )
		// Need to remove some upstream CSS:
		.pipe( replace( '.irs-line-mid,\n' +
			'.irs-line-left,\n' +
			'.irs-line-right,\n' +
			'.irs-bar,\n' +
			'.irs-bar-edge,\n' +
			'.irs-slider {\n' +
			'    background: url(../img/sprite-skin-nice.png) repeat-x;\n' +
			'}\n' +
			'\n', '' ) )
		.pipe( gulp.dest( 'resources/css' ) );
} );

gulp.task( 'buildSelectCSS', function () {

	return gulp
		.src( [ 'node_modules/select2/dist/css/select2.css' ] )
		.pipe( concat( 'ext.srf.filtered.select.css' ) )
		.pipe( gulp.dest( 'resources/css' ) );

} );

gulp.task( 'copyExternalImages', function () {

	var config = {
		'ext.srf.filtered.leaflet.css': [
			'node_modules/leaflet/dist/images/*'
		],
		// 'ext.srf.filtered.slider.css': [
		// 	'node_modules/ion-rangeslider/img/*nice.png'
		// ]
	};

	var ret = true;

	for ( var target in config ) {

		ret = ret && gulp.src( config[ target ] )
		.pipe( gulp.dest( 'resources/css/images' ) );
	}

	return ret;
} );

gulp.task( 'buildExternalCSS', gulp.parallel( 'buildLeafletCSS', 'buildSliderCSS', 'buildSelectCSS' ) );
gulp.task( 'default', gulp.parallel( 'buildFiltered', 'buildFilteredTests', 'buildExternalJS', 'buildExternalCSS', 'copyExternalImages' ) );
