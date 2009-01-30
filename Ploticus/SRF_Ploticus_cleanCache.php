<?php
/**
 * Purges old/orphan/temporary plots, maps, CSVs, drawdumps from the ploticus cache directory.
 * 
 *
 * Note: if SMW is not installed in its standard path under ./extensions
 *       then the MW_INSTALL_PATH environment variable must be set.
 *       See README in the maintenance directory.
 *
 * Usage:
 * php SRF_Ploticus_cleanCache.php [options...]
 *
 * -a <age in hours>    Override $srfgPloticusCacheAge setting and purge files of this age and greater      
 * -v                   Be verbose about the progress.
 *
 * @author Joel Natividad
 * @file
 * @ingroup SRFMaintenance
 */

$optionsWithArgs = array('a');

require_once ( getenv('MW_INSTALL_PATH') !== false
    ? getenv('MW_INSTALL_PATH')."/maintenance/commandLine.inc"
    : dirname( __FILE__ ) . '/../../../maintenance/commandLine.inc' );

global $wgUploadDirectory, $srfgPloticusCacheAgeHours;

if ( !empty( $options['a'] ) ) {
	$fileAge = intval($options['a']) * 3600; // 60 secs * 60 mins
} else {
        if ( isset($srfgPloticusCacheAgeHours) ) {
                $fileAge = $srfgPloticusCacheAgeHours * 3600;
        } else {
                $fileAge = 604800; // if $srfgPloticusCacheAgeHours is not set in LocalSettings.php defaults to 7 days
        }
}

$verbose = array_key_exists( 'v', $options );

if ($fileAge <= 0) {
    if ($verbose)
        echo "Ploticus cache cleaning disabled.\n";
    return;
}

$ploticusDirectory = $wgUploadDirectory . '/ploticus';
$deletecount = 0;

if( $dirhandle = @opendir($ploticusDirectory) ) {
    while( false !== ($filename = readdir($dirhandle)) ) {
            if( $filename != '.' && $filename != '..' ) {
                    $filename = $ploticusDirectory . '/' . $filename;
    
                    if( @filemtime($filename) < (time()-$fileAge) ) {
                            if ($verbose)
                                echo "deleting $filename...\n";
                            if (@unlink($filename))
                                $deletecount ++;
                    }
            }
    }
} else {
    if ($verbose)
        echo "$ploticusDirectory not found...  Aborting...\n";    
}

@closedir($dirhandle);
if ($verbose)
    echo "$deletecount files successfully deleted from Ploticus cache.\n";
