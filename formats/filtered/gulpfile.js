var gulp = require( 'gulp' );

var browserify = require( 'browserify' );
var tsify = require( 'tsify' );
var source = require( 'vinyl-source-stream' );
var uglify = require( 'gulp-uglify' );
var sourcemaps = require( 'gulp-sourcemaps' );
var buffer = require( 'vinyl-buffer' );
var concat = require( 'gulp-concat' );


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
	.pipe( buffer() )
	// .pipe( sourcemaps.init( { loadMaps: true } ) )
	// .pipe( uglify() )
	// .pipe( sourcemaps.write( './' ) )
	.pipe( gulp.dest( '../../tests/qunit/formats' ) );

} );

gulp.task( 'buildLeafletJS', function () {

	return gulp.src( [
		'node_modules/leaflet/dist/leaflet-src.js',
		'node_modules/leaflet.markercluster/dist/leaflet.markercluster-src.js',
		'node_modules/leaflet-providers/leaflet-providers.js'
	] )
	.pipe( concat( 'ext.srf.filtered.leaflet.js' ) )
	.pipe( uglify() )
	.pipe( gulp.dest( 'resources/js' ) );

} );


gulp.task( 'buildLeafletCSS', function () {

	return gulp.src( [
		'node_modules/leaflet/dist/leaflet.css',
		'node_modules/leaflet.markercluster/dist/MarkerCluster.css',
		'node_modules/leaflet.markercluster/dist/MarkerCluster.Default.css'
	] )
	.pipe( concat( 'ext.srf.filtered.leaflet.css' ) )
	.pipe( gulp.dest( 'resources/css' ) );

} );

gulp.task( 'copyLeafletIcons', function () {

	return gulp.src( [
		'node_modules/leaflet/dist/images/*'
	] )
	.pipe( gulp.dest( 'resources/css/images' ) );

} );

gulp.task( 'default', [ 'buildFiltered', 'buildFilteredTests', 'buildLeafletJS', 'buildLeafletCSS', 'copyLeafletIcons' ] );