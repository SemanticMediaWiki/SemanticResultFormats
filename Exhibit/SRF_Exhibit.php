<?php
/**
 * Use Exhibit to print query results.
 * @author Fabian Howahl
 * @file
 * @ingroup SMWQuery
 */

/**
 * Result printer using Exhibit to display query results
 *
 * @ingroup SMWQuery
 */
class SRFExhibit extends SMWResultPrinter {
	///mapping between SMW's and Exhibit's the data types
	protected $m_types = array("_wpg" => "text", "_num" => "number", "_dat" => "date", "_geo" => "text", "_uri" => "url");

	protected static $exhibitRunningNumber = 0; //not sufficient since there might be multiple pages rendered within one PHP run; but good enough now

	///overwrite function to allow execution of result printer even if no results are available (in case remote query yields no results in local wiki)
	public function getResult($results, $params, $outputmode) {
		$this->readParameters($params,$outputmode);
		$result = $this->getResultText($results,$outputmode);
		return $result;
	}

	///function aligns the format of SMW's property names to Exhibit's format
	protected function encodePropertyName($property){
		return strtolower(str_replace(" ","_",$property));
	}

	///implements an algorithm for automatic determination of a suitable intervall for numeric facets
	protected function determineSuitableInterval($res,$facet,$fieldcounter){
		$valuestack = array();
		while($row = $res->getNext()){
			$tmp = clone $row[$fieldcounter];
			$object = $tmp->getNextObject();
			if($object instanceof SMWNumberValue) $valuestack[] = $object->getNumericValue(); 
		}
		if(sizeof($valuestack) > 0){
			$average = (int)((max($valuestack) - min($valuestack))/2);
			$retval = str_pad(1,strlen($average)-1,0,STR_PAD_RIGHT);
		}
		else $retval = 0;
		return $retval;
	}	

	protected function determineNamespace($res){
		$row = $res->getNext();
		if($row != null){
			$tmp = clone $row[0];
			$object = $tmp->getNextObject();
			if($object instanceof SMWWikiPageValue){
				$value = $object->getPrefixedText();
				if(strpos($value,":")){
					$value = explode(":",$value,2);
					return $value[0].":";
				}
			}
			return "";
		} 
	}

	protected function getResultText($res, $outputmode) {

		global $smwgIQRunningNumber, $wgScriptPath, $wgGoogleMapsKey, $smwgScriptPath, $srfgIP, $srfgScriptPath;

		wfLoadExtensionMessages('SemanticMediaWiki');

		//the following variables indicate the use of special views
		//the variable's values define the way Exhibit is called
		$timeline = false;
		$map = false;


		/*The javascript file adopted from Wibbit uses a bunch of javascript variables in the header to store information about the Exhibit markup.
		 The following code sequence creates these variables*/

		//prepare sources (the sources holds information about the table which contains the information)
		$colstack = array();
		foreach ($res->getPrintRequests() as $pr){
			$colstack[] = $this->encodePropertyName($pr->getLabel()) . ':' .(array_key_exists($pr->getTypeID(),$this->m_types)?$this->m_types[$pr->getTypeID()]:'text') ;
		}
		array_shift($colstack);
		array_unshift($colstack, 'label');
	
		if(SRFExhibit::$exhibitRunningNumber == 0){
			$sourcesrc = "var sources = { source".($smwgIQRunningNumber-1).": { id:  'querytable".$smwgIQRunningNumber."' , columns: '".implode(',',$colstack)."'.split(','), hideTable: '1', type: 'Item', label: 'Item', pluralLabel: 'Items' } };";
		}
		else{
			$sourcesrc = "sources.source".$smwgIQRunningNumber." =  { id:  'querytable".$smwgIQRunningNumber."' , columns: '".implode(',',$colstack)."'.split(','), hideTable: '1', type: 'Item', label: 'Item', pluralLabel: 'Items' };";
		}
		$sourcesrc = "<script type=\"text/javascript\">".$sourcesrc."</script>";
		
		//prepare facets
		$facetcounter = 0;
		if(array_key_exists('facets', $this->m_params)){
			$facets = explode(',', $this->m_params['facets']);
			$facetstack = array();
			$params = array('height');
			$facparams = array();
			foreach($params as $param){
				if(array_key_exists($param, $this->m_params)) $facparams[] = 'ex:'.$param.'="'.$this->encodePropertyName($this->m_params[$param]).'" ';
			}
			foreach( $facets as $facet ) {
				$facet = trim($facet);
				$fieldcounter=0;
				foreach ($res->getPrintRequests() as $pr){
					if($this->encodePropertyName($pr->getLabel()) == $this->encodePropertyName($facet)){
						switch($pr->getTypeID()){
							case '_num':
								$intervall = $this->determineSuitableInterval(clone $res,$facet,$fieldcounter);
								$facetstack[] = ' facet'.$facetcounter++.': { position : "right", innerHTML: \'ex:role="facet" ex:showMissing="false" ex:facetClass="NumericRange"  ex:interval="'.$intervall.'" '.implode(" ",$facparams).' ex:expression=".'.$this->encodePropertyName($facet).'"\'}';  
								break;
							default:
								$facetstack[] = ' facet'.$facetcounter++.': { position : "right", innerHTML: \'ex:role="facet" ex:showMissing="false" '.implode(" ",$facparams).' ex:expression=".'.$this->encodePropertyName($facet).'"\'}';
						}
						break;
					}
					$fieldcounter++;
				}
			}
			$facetstring = implode(',',$facetstack);
		}
		else $facetstring = '';
		$facetsrc = "var facets = {".$facetstring." };";


		//prepare views
		$stylesrc = '';
		$viewcounter = 0;
		if(array_key_exists('views', $this->m_params)) $views = explode(',', $this->m_params['views']);
		else $views[] = 'tiles';

		foreach( $views as $view ){
			switch( $view ){
				case 'tabular'://table view (the columns are automatically defined by the selected properties)
					$thstack = array();
					foreach ($res->getPrintRequests() as $pr){
						$thstack[] = ".".$this->encodePropertyName($pr->getLabel());
					}
					array_shift($thstack);
					array_unshift($thstack, '.label');
					$stylesrc = 'var myStyler = function(table, database) {table.className=\'smwtable\';};'; //assign SMWtable CSS to Exhibit tabular view
					$viewstack[] = 'ex:role=\'view\' ex:viewClass=\'Tabular\' ex:showSummary=\'false\' ex:sortAscending=\'true\' ex:tableStyler=\'myStyler\'  ex:label=\'Table\' ex:columns=\''.implode(',',$thstack).'\' ex:sortAscending=\'false\'' ;
					break;
				case 'timeline'://timeline view
					$timeline = true;
					$exparams = array('start','end', 'proxy', 'colorkey'); //parameters expecting an Exhibit graph expression
					$usparams = array('timelineheight','topbandheight','bottombandheight','bottombandunit','topbandunit'); //parametes expecting a textual or numeric value
					$tlparams = array();
					foreach($exparams as $param){
						if(array_key_exists($param, $this->m_params)) $tlparams[] = 'ex:'.$param.'=\'.'.$this->encodePropertyName($this->m_params[$param]).'\' ';
					}
					foreach($usparams as $param){
						if(array_key_exists($param, $this->m_params)) $tlparams[] = 'ex:'.$param.'=\''.$this->encodePropertyName($this->m_params[$param]).'\' ';
					}
					if(!array_key_exists('start', $this->m_params)){//find out if a start and/or end date is specified
						$dates = array();
						foreach ($res->getPrintRequests() as $pr){
							if($pr->getTypeID() == '_dat') {
								$dates[] = $pr;
								if(sizeof($dates) > 2) break;
							}
						}
						if(sizeof($dates) == 1){
							$tlparams[] = 'ex:start=\'.'.$this->encodePropertyName($dates[0]->getLabel()).'\' ';
						}
						else if(sizeof($dates) == 2){
							$tlparams[] = 'ex:start=\'.'.$this->encodePropertyName($dates[0]->getLabel()).'\' ';
							$tlparams[] = 'ex:end=\'.'.$this->encodePropertyName($dates[1]->getLabel()).'\' ';
						}
					}
					$viewstack[] = 'ex:role=\'view\' ex:viewClass=\'Timeline\' ex:label=\'Timeline\' ex:showSummary=\'false\' '.implode(" ",$tlparams);
					break;
				case 'map'://map view
					if(isset($wgGoogleMapsKey)){
					   $map = true;
					   $exparams = array('latlng','colorkey');
					   $usparams = array('type','center','zoom','size','scalecontrol','overviewcontrol','mapheight');
					   $mapparams = array();
					   foreach($exparams as $param){
						if(array_key_exists($param, $this->m_params)) $mapparams[] = 'ex:'.$param.'=\'.'.$this->encodePropertyName($this->m_params[$param]).'\' ';
					   }
					   foreach($usparams as $param){
						if(array_key_exists($param, $this->m_params)) $mapparams[] = 'ex:'.$param.'=\''.$this->encodePropertyName($this->m_params[$param]).'\' ';
					   }
					   if(!array_key_exists('start', $this->m_params) && !array_key_exists('end', $this->m_params)){ //find out if a geographic coordinate is available
						foreach ($res->getPrintRequests() as $pr){
							if($pr->getTypeID() == '_geo') {
								$mapparams[] = 'ex:latlng=\'.'.$this->encodePropertyName($pr->getLabel()).'\' ';
								break;
							}
						}
					   }
					   $viewstack[] .= 'ex:role=\'view\' ex:viewClass=\'Map\' ex:showSummary=\'false\' ex:label=\'Map\' '.implode(" ",$mapparams);
					}
					break;
				default:case 'tiles'://tile view
					$viewstack[] = 'ex:role=\'view\' ex:showSummary=\'false\'';
					break;
			}
		}

		$viewsrc = 'var views = "'.implode("/", $viewstack).'".split(\'/\');;';
    
		

		//prepare automatic lenses

		global $wgParser;
		$lenscounter = 0;
		$linkcounter = 0;

		if(array_key_exists('lens', $this->m_params)){//a customized lens is specified via the lens parameter within the query
			$lenstitle    = Title::newFromText("Template:".$this->m_params['lens']);
			$lensarticle  = new Article($lenstitle);
			$lenswikitext = $lensarticle->getContent();

			if(preg_match_all("/[\[][\[][{][{][{][1-9A-z\-[:space:]]*[}][}][}][\]][\]]/u",$lenswikitext,$matches)){
				foreach($matches as $match){
					foreach($match as $value){
						$strippedvalue = trim($value,"[[{}]]");
						$lenswikitext = str_replace($value,'<div class="inlines" id="linkcontent'.$linkcounter.'">'.$this->encodePropertyName(strtolower(str_replace("\n","",$strippedvalue))).'</div>',$lenswikitext);
						$linkcounter++;
					}
				}
			}
      
			if (preg_match_all("/[{][{][{][1-9A-z\-[:space:]]*[}][}][}]/u",$lenswikitext,$matches)) {
				foreach($matches as $match){
					foreach($match as $value){
						$strippedvalue = trim($value,"{}");
						$lenswikitext = str_replace($value,'<div class="inlines" id="lenscontent'.$lenscounter.'">'.$this->encodePropertyName(strtolower(str_replace("\n","",$strippedvalue))).'</div>',$lenswikitext);
						$lenscounter++;
					}
				}
			}
	
			$lenshtml = $wgParser->internalParse($lenswikitext);//$wgParser->parse($lenswikitext, $lenstitle, new ParserOptions(), true, true)->getText();

			$lenssrc = "var lens = '".str_replace("\n","",$lenshtml)."';lenscounter =".$lenscounter.";linkcounter=".$linkcounter.";";
		} else {//generic lens (creates links to further content (property-pages, pages about values)
			foreach ($res->getPrintRequests() as $pr){
				if($pr->getTypeID() == '_wpg') {
					$prefix='';
					if($pr->getLabel()=='Category') $prefix = "Category:";
					$lensstack[] = '<tr ex:if-exists=".'.$this->encodePropertyName($pr->getLabel()).'"><td width="20%">'.$pr->getText(0, $this->mLinker).'</td><td width="80%" ex:content=".'.$this->encodePropertyName($pr->getLabel()).'"><a ex:href-subcontent="'.$wgScriptPath.'/index.php?title='.$prefix.'{{value}}"><div ex:content="value" class="name"></div></a></td></tr>';
				}
				else{
					$lensstack[] = '<tr ex:if-exists=".'.$this->encodePropertyName($pr->getLabel()).'"><td width="20%">'.$pr->getText(0, $this->mLinker).'</td><td width="80%"><div ex:content=".'.$this->encodePropertyName($pr->getLabel()).'" class="name"></div></td></tr>';
				}
			}
			array_shift($lensstack);
			$lenssrc = 'var lens = \'<table width=100% cellpadding=3><tr><th class="head" align=left bgcolor="#DDDDDD"><a ex:href-subcontent="'.$wgScriptPath.'/index.php?title='.$this->determineNamespace(clone $res).'{{.label}}" class="linkhead"><div ex:content=".label" class="name"></div></a></th></tr></table><table width="100%" cellpadding=3>'.implode("", $lensstack).'</table>\'; lenscounter = 0; linkcounter=0;';
		}

		
		//create script header with variables containing the Exhibit markup
		$headervars = "<script type='text/javascript'>\n\t\t\t".$facetsrc."\n\t\t\t".$viewsrc."\n\t\t\t".$lenssrc."\n\t\t\t".$stylesrc."\n</script>";


		//To run Exhibit some links to the scripts of the API need to be included in the header
		$ExhibitScriptSrc1 = '<script type="text/javascript" src="'.$srfgScriptPath.'/Exhibit/includes/src/webapp/api/exhibit-api.js?autoCreate=false&safe=true'; //former: auto create = remote
		if($timeline) $ExhibitScriptSrc1 .= '&views=timeline';
		if($map) $ExhibitScriptSrc1 .= '&gmapkey='.$wgGoogleMapsKey;
		$ExhibitScriptSrc1 .= '"></script>';
		$ExhibitScriptSrc2 = '<script type="text/javascript" src="'.$srfgScriptPath.'/Exhibit/SRF_Exhibit.js"></script>';
		$CSSSrc = '<link rel="stylesheet" type="text/css" href="'.$srfgScriptPath.'/Exhibit/SRF_Exhibit.css"></link>';
	  
		SMWOutputs::requireHeadItem('CSS', $CSSSrc); //include CSS
		SMWOutputs::requireHeadItem('EXHIBIT1', $ExhibitScriptSrc1); //include Exhibit API
		SMWOutputs::requireHeadItem('EXHIBIT2', $ExhibitScriptSrc2); //includes javascript overwriting the Exhibit start-up functions
		SMWOutputs::requireHeadItem('SOURCES'.$smwgIQRunningNumber, $sourcesrc);//include sources variable
		SMWOutputs::requireHeadItem('VIEWSFACETS', $headervars);//include views and facets variable
	  
		//print input table
		// print header
		if ('broadtable' == $this->mFormat) $widthpara = ' width="100%"';
		else $widthpara = '';
		$result = "<table style=\"display:none\" class=\"smwtable\" id=\"querytable" . $smwgIQRunningNumber . "\">\n";
		if ($this->mShowHeaders) { // building headers
			$result .= "\t<tr>\n";
			foreach ($res->getPrintRequests() as $pr) {
				$result .= "\t\t<th>" .$pr->getText($outputmode,$this->getLinker(0)). "</th>\n";
			}
			$result .= "\t</tr>\n";
		}

		// print all result rows
		while ( $row = $res->getNext() ) {
			$result .= "\t<tr>\n";
			foreach ($row as $field) {
				$result .= "\t\t<td>";
				$textstack = array();
				while ( ($object = $field->getNextObject()) !== false ) {
					switch($object->getTypeID()){
						case '_wpg':
							$textstack[] = $object->getLongText($outputmode,$this->getLinker(0));
							break;
						case '_geo':
							$textstack[] = $object->getXSDValue($outputmode,$this->getLinker(0));
							break;
						case '_num':
							$textstack[] = $object->getNumericValue($outputmode,$this->getLinker(0));
							break;
						case '_dat':
							$textstack[] = $object->getYear()."-".str_pad($object->getMonth(),2,'0',STR_PAD_LEFT)."-".str_pad($object->getDay(),2,'0',STR_PAD_LEFT)." ".$object->getTimeString();
							break;
						case '_uri':
							$textstack[] = $object->getXSDValue($outputmode,$this->getLinker(0));
							break;
						case '__sin':
							$tmp = $object->getShortText($outputmode,null);
							if(strpos($tmp,":")){
								$tmp = explode(":",$tmp,2);
								$tmp = $tmp[1];
							}
							$textstack[] = $tmp;
							break;
						default:
							$textstack[] = $object->getLongHTMLText($outputmode,$this->getLinker(0));
					}
				}

				if($textstack != null){
					$result .= implode(';',$textstack)."</td>\n";
				}
				else $result .= "</td>\n";
			}
			$result .= "\t</tr>\n";
		}	
		$result .= "</table>\n";
    		
		if (SRFExhibit::$exhibitRunningNumber == 0) $result .= "<div id=\"exhibitLocation\"></div>"; // print placeholder (just print it one time)
		$this->isHTML = ($outputmode == SMW_OUTPUT_HTML); // yes, our code can be viewed as HTML if requested, no more parsing needed
		SRFExhibit::$exhibitRunningNumber++;
		return $result;
	}

	public function getParameters() {
		$params = parent::getParameters();
		$params[] = array('name' => 'views', 'type' => 'enum-list', 'description' => wfMsg('srf_paramdesc_views'), 'values' => array('tiles', 'tabular', 'timeline', 'maps'));
		$params[] = array('name' => 'facets', 'type' => 'string', 'description' => wfMsg('srf_paramdesc_facets'));
		$params[] = array('name' => 'lens', 'type' => 'string', 'description' => wfMsg('srf_paramdesc_lens'));
		return $params;
	}

}
