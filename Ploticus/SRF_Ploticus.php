<?php
/**
 * A query printer using ploticus
 * loosely based on the Ploticus Extension by Flavien Scheurer
 * and CSV result printer
 * 
 * @note AUTOLOADED
 * @author Joel Natividad
 */

if( !defined( 'MEDIAWIKI' ) ) {
	die( 'Not an entry point.' );
}

class SRFPloticus extends SMWResultPrinter {
	protected $m_ploticusparams = '';
	protected $m_imageformat = 'png';
	protected $m_alttext = 'Ploticus chart';
	protected $m_showcsv = '0';
	protected $m_ploticusmode = 'prefab';
	protected $m_debug = '0';
	protected $m_liveupdating = '1';
	protected $m_updatefrequency = 60;  // by default, generate plot only once per minute
	protected $m_showtimestamp = '0';
	protected $m_showrefresh = '0';

	protected function readParameters($params, $outputmode) {
		SMWResultPrinter::readParameters($params, $outputmode);
		if (array_key_exists('ploticusparams', $this->m_params)) {
			$this->m_ploticusparams = trim($params['ploticusparams']);
		}
		if (array_key_exists('imageformat', $this->m_params)) {
			$this->m_imageformat = trim($params['imageformat']);
		}
		if (array_key_exists('alttext', $this->m_params)) {
			$this->m_alttext = trim($params['alttext']);
		}
		if (array_key_exists('showcsv', $this->m_params)) {
			$tmpcmp = strtolower(trim($params['showcsv']));
			$this->m_showcsv =  $tmpcmp == 'false' || $tmpcmp == 'no' ? false : $tmpcmp;
		}
		if (array_key_exists('ploticusmode', $this->m_params)) {
			$this->m_ploticusmode =  strtolower(trim($params['ploticusmode']));
		}
		if (array_key_exists('debug', $this->m_params)) {
			$tmpcmp = strtolower(trim($params['debug']));
			$this->m_debug =  $tmpcmp == 'false' || $tmpcmp == 'no' ? false : $tmpcmp;
		}
		if (array_key_exists('liveupdating', $this->m_params)) {
			$tmpcmp = strtolower(trim($params['liveupdating']));
			$this->m_liveupdating =  $tmpcmp == 'false' || $tmpcmp == 'no' ? false : $tmpcmp;
		}
		if (array_key_exists('updatefrequency', $this->m_params)) {
			$this->m_updatefrequency = trim($params['updatefrequency']);
		}
		if (array_key_exists('showtimestamp', $this->m_params)) {
			$tmpcmp = strtolower(trim($params['showtimestamp']));
			$this->m_showtimestamp =  $tmpcmp == 'false' || $tmpcmp == 'no' ? false : $tmpcmp;
		}
		if (array_key_exists('showrefresh', $this->m_params)) {
			$tmpcmp = strtolower(trim($params['showrefresh']));
			$this->m_showrefresh =  $tmpcmp == 'false' || $tmpcmp == 'no' ? false : $tmpcmp;
		}
	}

	protected function getResultText($res, $outputmode) {
		global $smwgIQRunningNumber, $wgUploadDirectory, $wgUploadPath, $srfgPloticusPath, $srfgGDFontPath;

		$this->isHTML = true;

		if(!strlen($this->m_ploticusparams)) {
		    return ('<p><strong>ERROR: <em>ploticusparams</em> required.</strong></p>');
		}
		
		// remove potentially dangerous keywords (prefab mode) or ploticus directives (script mode);
		if ($this->m_ploticusmode == 'prefab') {
		     // we also remove line endings for prefab - this is done for readability so the user can specify the prefab
		     // params over several lines rather than one long command line
		    $searches = array('/`/m', '/system/im', '/shell/im', "/\s*?\n/m");
		    $replaces = array('', '', '', ' ');
		} else {
		    $searches = array('/`/m', '/#include/im', '/#shell/im', '/#sql/im', '/#write/im','/#cat/im');
		    $replaces = array('', '// ERROR: INCLUDE not allowed', '// ERROR: SHELL not allowed',
			    '// ERROR: SQL not allowed', '// ERROR: WRITE not allowed', '// ERROR: CAT not allowed');
		}
		$sanitized_ploticusparams = preg_replace($searches, $replaces, $this->m_ploticusparams); 

		// Create the image directory if it doesnt exist
		$ploticusDirectory = $wgUploadDirectory . '/ploticus/';
		if (!is_dir($ploticusDirectory)) {
			mkdir($ploticusDirectory, 0777);
		}

		// create result csv file that we pass on to ploticus
		$tmpFile = tempnam($ploticusDirectory, 'srf-');
		$fhandle = fopen($tmpFile, 'w');
		while ( $row = $res->getNext() ) {
			 $row_items = array();
			 foreach ($row as $field) {
				 $growing = array();
				 while (($object = $field->getNextObject()) !== false) {
					 $text = Sanitizer::decodeCharReferences($object->getWikiValue());
					 // decode: CSV knows nothing of possible HTML entities
					 $growing[] = $text;
				 }
				 $row_items[] = implode(',', $growing);
			 }
			 fputcsv($fhandle, $row_items);
		}
		fclose($fhandle);

		// we create a hash based on params and csv file.
		// this is a great way to see if the params and/or the query result has changed	    
		$hashname = md5($this->m_ploticusparams . $this->m_imageformat . $this->m_showcsv . $this->m_ploticusmode .
				$this->m_liveupdating . $this->m_updatefrequency . $this->m_showtimestamp);
		if ($this->m_liveupdating) {
		    // only include contents of result csv in hash when liveupdating is on
		    // in this way, doing file_exists check against hash filename will fail when query result has changed
		    $hashname .= md5_file($tmpFile);
		}
		
		$dataFile = $ploticusDirectory . $hashname . '.csv';
		rename($tmpFile, $dataFile);
		
		$graphFile = $ploticusDirectory . $hashname . '.' . $this->m_imageformat;
		$graphURL = $wgUploadPath . '/ploticus/' . $hashname . '.' . $this->m_imageformat;
		$errorFile = $ploticusDirectory . $hashname . '.err';
		$mapFile = $ploticusDirectory . $hashname . '.map';
		$mapURL = $wgUploadPath . '/ploticus/' . $hashname . '.map';
		$scriptFile = $ploticusDirectory . $hashname . '.scr';
		$scriptURL = $wgUploadPath . '/ploticus/' . $hashname . '.scr';
		
		// get time graph was last generated and if liveupdating is on, check to see if the 
		// generated plot has expired per the updatefrequency and needs to be redrawn
		if (file_exists($graphFile)) {
		    $graphLastGenerated = filemtime($graphFile);
		    $expireTime = $graphLastGenerated + $this->m_updatefrequency;
		    if ($this->m_liveupdating && $expireTime < time()) {
			unlink($graphFile);
		    }
		}
		
		// check if previous plot generated with the same params and result data is available
		// we know this from the md5 hash.  This should eliminate
		// unneeded, CPU-intensive invocations of ploticus and minimize
		// the need to periodically clean-up graph, csv, script and map files
		$errorData = '';
		if ($this->m_debug || !file_exists($graphFile)) {
 
			// Verify that ploticus is installed.
			if (!file_exists($srfgPloticusPath)) {
				return ('<p><strong>ERROR: Could not find ploticus in <em>' .
					$srfgPloticusPath . '</em></strong></p>');
			}
			
			// we set GDFONTPATH if specified
			$commandline = strlen($srfgGDFontPath) ? 'GDFONTPATH=' . $srfgGDFontPath . ' ' : ' ';
			if ($this->m_ploticusmode == 'script') {
			    // Script mode.  Search for special strings in ploticusparam
			    // and replace it with actual values. (case-sensitive)
			    // The special strings currently are:  %DATAFILE.CSV%, %WORKINGDIR% 
			    $replaces = array('%DATAFILE.CSV%'  => wfEscapeShellArg($dataFile),
					      '%WORKINGDIR%' => $ploticusDirectory);
			    $literal_ploticusparams = strtr($sanitized_ploticusparams, $replaces);
			    $fhandle = fopen($scriptFile, 'w');
			    fputs($fhandle, $literal_ploticusparams);
			    fclose($fhandle);
			    
			    $commandline .= wfEscapeShellArg($srfgPloticusPath) .
				    ' -' . $this->m_imageformat .
				    ' -o ' . wfEscapeShellArg($graphFile) .
				    ' ' . $scriptFile;
				    
			} else {
			    // prefab mode, build the command line accordingly		       
			    $commandline .= wfEscapeShellArg($srfgPloticusPath) .
				    ' ' . $sanitized_ploticusparams .
				    ' -' . $this->m_imageformat .
				    ' data=' . wfEscapeShellArg($dataFile) .
				    ' -o ' . wfEscapeShellArg($graphFile);
			}
			// create the imagemap file if clickmap is specified for ploticus       
			if (strpos($sanitized_ploticusparams, 'clickmap')) {
				$commandline .= ' >' . wfEscapeShellArg($mapFile);
			}
			
			// send errors to this file
			$commandline .= ' 2>' . wfEscapeShellArg($errorFile);

			// Execute ploticus.
			wfShellExec($commandline);
			$errorData = file_get_contents($errorFile);
			unlink($errorFile);
			
			$graphLastGenerated = filemtime($graphFile);
			
			if($this->m_ploticusmode == 'script' && !$this->m_debug) {
			    unlink($scriptFile);
			}
		}
		
		//Prepare output
		$rtnstr = '<table cols="3"><tr>';
		if (strlen($errorData)) {
			// there was an error
			$rtnstr .= '<th colspan="3"><strong>Error processing ploticus data:</strong></th></tr><tr><td colspan="3"' .
				$errorData . '</td></tr>';
		}
		else {
			// if we are using clickmaps, create HTML snippet to enable client-side imagemaps
			if (strpos($sanitized_ploticusparams, 'clickmap')) {
				$mapData = file_get_contents($mapFile);
				$mapData = str_replace("+","_",$mapData);
				$rtnstr .= ('<td colspan="3"><map name="'. $hashname . '">'. $mapData .
					'</map><img src="' . $graphURL . '" border="0" usemap="#' . $hashname . '"></td></tr>');
			} else {
			    $rtnstr .= '<td colspan="3"><img src="' . $graphURL . '" alt="' . $this->alttext .'"></td></tr>';
			}
		}
		$rtnstr .= '<tr>';
		// if showcsv is on, add link to data file (CSV)
		if ($this->m_showcsv || $this->m_debug) {
			$dataURL = $wgUploadPath . '/ploticus/' . $hashname . '.csv';
			$rtnstr .= '<td width="33%" colspan="1" align="left"><small><a href="' . $dataURL . '">CSV</a></small></td>';
		} else {
		    // otherwise, clean it up
		    unlink($dataFile);
		    $rtnstr .= '<td width="33%" colspan="1"></td>';
		}
		
		// if showrefresh is on, create link to force refresh
		if ($this->m_showrefresh) {
			$rtnstr .= '<td width="33%" colspan="1" align="center"><small><a href="' .
			$wgArticlePath . '?action=purge">Refresh</a></small></td>';
		} else {
		    $rtnstr .= '<td width="33%" colspan="1"></td>';
		}
		
		// if showtimestamp is on, add plot generation timestamp
		if ($this->m_showtimestamp) {
		    $rtnstr .= '<td width="33%" colspan="1" align="right"><small> Generated: ' .
		      date('Y-m-d h:i:s A', $graphLastGenerated) . '</small></td>';
		} else {
		    $rtnstr .= '<td width="33%" colspan="1"></td>';
		}
		
		$rtnstr .= '</tr></table>';
		
		// if debug is on, add link to script or display prefab cmdline
		if ($this->m_debug) {
		    if ($this->m_ploticusmode == 'script') {
			$rtnstr .= '<p><strong>DEBUG: <a href="' . $scriptURL . '" target="_blank">SCRIPT</a></strong></p>';
		    } else {
			$rtnstr .= '<p><strong>DEBUG: PREFAB</strong></p><table width="100%"><tr>' . $commandline . '</tr></table>';
		    }
		}
		return ($rtnstr);
	}
}

