<?php

$wgAutoloadClasses['SRFCHistoricalDate'] = dirname( __FILE__ ) . '/SRFC_HistoricalDate.php';

/**
 * Result printer that prints query results as a year calendar.
 * Based on Yaron Koren's (monthly) SRF_calender
 * 
 * @file SRF_YearCal.php
 * @ingroup SemanticResultFormats
 * 
 * @author Hans Oleander
 */
class SRFYearCal extends SMWResultPrinter {

	protected $mTemplate;
	protected $mUserParam;
	protected $mRealUserLang = null;

	protected function setColors( $colorsText ) {
		$colors = array();
		$colorElements = explode( ',', $colorsText );
		foreach ( $colorElements as $colorElem ) {
			$propAndColor = explode( '=>', $colorElem );
			if ( count( $propAndColor ) == 2 ) {
				$colors[$propAndColor[0]] = $propAndColor[1];
			}
		}
		$this->mColors = $colors;
	}

	protected function handleParameters( array $params, $outputmode ) {
		parent::handleParameters( $params, $outputmode );

		$this->mTemplate = trim( $params['template'] );
		$this->mUserParam = trim( $params['userparam'] );

		if ( $params['lang'] !== false ) {
			global $wgLang;
			// Store the actual user's language, so we can revert
			// back to it after printing the calendar.
			$this->mRealUserLang = clone ( $wgLang );
			$wgLang = Language::factory( trim( $params['lang'] ) );
		}

		$this->setColors( $params['colors'] );
	}

	public function getName() {
		return wfMessage( 'srf_printername_yearcal' )->text();
	}

	/**
	 * @see SMWResultPrinter::buildResult
	 *
	 * @since 1.8
	 *
	 * @param SMWQueryResult $results
	 *
	 * @return string
	 */
	protected function buildResult( SMWQueryResult $results ) {
		$this->isHTML = false;
		$this->hasTemplates = false;

		// Skip checks - results with 0 entries are normal.
		return $this->getResultText( $results, SMW_OUTPUT_HTML );
	}

	/**
	 * (non-PHPdoc)
	 * @see SMWResultPrinter::getResultText()
	 * 
	 * TODO: split up megamoth 
	 */
	protected function getResultText( SMWQueryResult $res, $outputmode ) {
		$events = array();

		$result = "";
		$dates2 = array();
		$holidays = array();

        /* The following array is used for background colors of calendar entries. 
         * After the last color was used, it restarts at the beginning.
         * This array may be edited to change colors, add new ones, bring them in different order, etc.
         */
		$personalcolor = array();
		$colors = array("#ffaaaa", "#aaffaa", "#DEB887", "#aaaaff", "#aaffff", "#fd6317", "#ffffbb", "#ffbbfd");
		$colorarraylength = count($colors);
		$colorindex = 0;

		while ( $row = $res->getNext() ) {

			foreach ( $row as $i => $field ) {
				$end = "";
				while ( ( $object = $field->getNextDataValue() ) !== false ) {
					if ( $i == 0 ) {
						$link = $object->getWikiValue();
					} elseif ( $i == 1 ) {
						$begin = $this->formatDateStr( $object );
					} elseif ( $i == 2 ) {
						$end = $this->formatDateStr( $object );
					}

				}

				/* In case no end date is given: ...  */
				if (empty($end)) {
					$end = $begin;
				}

			}
			$y = explode("#", $link);

			$page = $y[0];
			$short = "";
			
			if (mb_strlen($y[1]) == 2 ) {
				$short = $y[1];
				} else {
				foreach (str_split($page) as $ch) {
					if ($ch >= "A" and $ch <= "Z") $short .= $ch;
				}
			$short = substr($short, 0, 2);
			}

               /* If we still do not have a two digit/letter abbreviation, use # sign. */
			while (strlen($short) < 2) {
				$short .= '#';
			}

               /* Any Abbreviation gets a color once, and then continues to use it. */

			if (isset($personalcolor[$short])) {
				$color = $personalcolor[$short];
				} else {
				$color = $colors[$colorindex % $colorarraylength];
				$colorindex += 1;
				$personalcolor[$short] = $color;
				}

			$short = "<span style=\"background:".$color.";\">".$short."</span>";

			$dates2[] = array($link, $page, $short, $begin, $end);
		}

		$result = "";
		$result .= $this->displayCalendar( $dates2, $holidays );

		// Go back to the actual user's language, in case a different
		// language had been specified for this calendar.
		if ( ! is_null( $this->mRealUserLang ) ) {
			global $wgLang;
			$wgLang = $this->mRealUserLang;
		}

		global $wgParser;

		if ( is_null( $wgParser->getTitle() ) ) {
			return $result;
		} else {
			return array( $result, 'noparse' => 'true', 'isHTML' => 'true' );
		}
	}

	protected static function intToMonth( $int ) {
		$months = array(
			'1' => 'january',
			'2' => 'february',
			'3' => 'march',
			'4' => 'april',
			'5' => 'may_long',
			'6' => 'june',
			'7' => 'july',
			'8' => 'august',
			'9' => 'september',
			'10' => 'october',
			'11' => 'november',
			'12' => 'december',
		);

		return wfMessage( array_key_exists( $int, $months ) ? $months[$int] : 'january' )->inContentLanguage()->text();
	}

	function formatDateStr( $object ) {
		// For some reason, getMonth() and getDay() sometimes return a
		// number with a leading zero - get rid of it using (int)
		return sprintf("%04d-%02d-%02d", $object->getYear(), $object->getMonth(), $object->getDay());
		// GH: return $object->getYear() . '-' . (int)$object->getMonth() . '-' . (int)$object->getDay();
	}

	function displayCalendar( $dates2, $holidays ) {
		global $wgOut, $wgParser, $wgRequest;
		global $srfgFirstDayOfWeek;

		$wgParser->disableCache();

		$wgOut->addLink( array(
			'rel' => 'stylesheet',
			'type' => 'text/css',
			'media' => 'screen, print',
			'href' => $GLOBALS['srfgScriptPath'] . '/formats/yearcal/skins/SRFC_main.css'
		) );

		// Set variables differently depending on whether this is
		// being called from a regular page, via #ask, or from a
		// special page: most likely either Special:Ask or
		// Special:RunQuery.
		$page_title = $wgParser->getTitle();
		$additional_query_string = '';
		$hidden_inputs = '';
		$in_special_page = is_null( $page_title ) || $page_title->isSpecialPage();

		if ( $in_special_page ) {
			global $wgTitle;
			$page_title = $wgTitle;
			global $wgUser;
			$skin = $wgUser->getSkin();
			$request_values = $wgRequest->getValues();
			// Also go through the predefined PHP variable
			// $_REQUEST, because $wgRequest->getValues() for
			// some reason doesn't return array values - is
			// there a better (less hacky) way to do this?
			foreach ( $_REQUEST as $key => $value ) {
				if ( is_array( $value ) ) {
					foreach ($value as $k2 => $v2 ) {
						$new_key = $key . '[' . $k2 . ']';
						$request_values[$new_key] = $v2;
					}
				}
			}

			foreach ( $request_values as $key => $value ) {
				if ( $key != 'month' && $key != 'year'
					// values from 'RunQuery'
					&& $key != 'query' && $key != 'free_text'
				) {
					$additional_query_string .= "&$key=$value";
					$hidden_inputs .= "<input type=\"hidden\" name=\"$key\" value=\"$value\" />";
				}
			}
		} else {
			$skin = $wgParser->getOptions()->getSkin();
		}

		// Get all the date-based values we need - the current month
		// and year (i.e., the one the user is looking at - not
		// necessarily the "current" ones), the previous and next months
		// and years (same - note that the previous or next month could
		// be in a different year), the number of days in the current,
		// previous and next months, etc.

		$year = date( 'Y', time() );
		if ( $wgRequest->getCheck( 'year' ) ) {
			$query_year = $wgRequest->getVal( 'year' );
			if ( is_numeric( $query_year ) && intval( $query_year ) == $query_year ) {
				$year = $wgRequest->getVal( 'year' );
			}
		}

		$prev_year = $year - 1;
		$next_year = $year + 1;

		$today_text = wfMsg( 'srfc_today' );

		$page_name = $page_title->getPrefixedDbKey();

		$prev_year_url = $page_title->getLocalURL( "year=$prev_year" . $additional_query_string );
		$next_year_url = $page_title->getLocalURL( "year=$next_year" . $additional_query_string );

		$text = "";

		$text .= "<h2>";
		$text .= "<a href=\"".$prev_year_url."\" style=\"color:#aaa;\">".$prev_year."</a>";
		$text .= " ".$year." ";
		$text .= "<a href=\"".$next_year_url."\" style=\"color:#aaa;\">".$next_year."</a>";
		$text .= "</h2>";

		$text .= "<table class=\"year_calendar\">\n";
		$text .= "<tr class=\"months\">\n";

              /* The actual HTML rendering is done horizontally, i.e. line by line.
               * However, for vertical alignment of series all days must be pre-rendered, first.
               */

		$entries = array();
		$i = 0;
		$pos = 0;
		$date_str = "";
		$positions = array();
		

		for ( $month = 1; ( $month <= 12 ); $month++ ) {
			for ( $day = 1; ( $day <= 31 ); $day++ ) {
				if ( $day <= SRFCHistoricalDate::daysInMonth( $year, $month)  ) {


					/* Generate date string */
					$date_str = sprintf("%04d-%02d-%02d", $year, $month, $day);
					$today = array();
					$entries[$date_str] = "";
					$newpositions = array();
					$maxpos = 0;


					foreach ( $dates2 as $x ) {
						if ( $date_str >= $x[3] and $date_str <= $x[4] ) {


						if ($x[1] == "SRF-YearCal") {
							$title = Title::newFromText($x[0]);
							$entries[$date_str] .= "<div style=\"position:absolute;background-color:grey;width:1.2em\"><a style=\"color:white\" href=\"".$title->getFullURL()."\" title=\"".$x[1]."\">".$day."</a></div>";
							} else {
 
							/* Generate Entry */
							$pos = array_search($x[2], $positions, true);

							if ($pos===false) {

								for ($i = 0; ; $i++ ) {

									if (!isset($newpositions[$i]) and !isset($positions[$i]) ) {
										$newpositions[$i] = $x[2];
										break;
										}

									}

								if ($maxpos <= $i) {
									$maxpos = $i + 1;
									} 
								} else {
								$newpositions[$pos] = $x[2];

								if ($maxpos < $pos + 1) $maxpos = $pos + 1;
							}

							$title = Title::newFromText($x[0]);


							$today[$x[2]] = "<div class=\"main\" style=\"font-family:monospace;font-size:medium\"><a href=\"".$title->getFullURL()."\" title=\"".$x[1]."\">".$x[2]."</a></div>";
							}

							} /* end if $date_str */


						}  /* end foreach $dates2 ...  */
	
					/* Build box for this day if there is at least one entry */

					if (!empty($newpositions)) {

						for ($i = 0; $i <= $maxpos - 1; $i++) {
							if (isset($newpositions[$i])) {
								$entries[$date_str] .= $today[$newpositions[$i]];
								} else {
								$entries[$date_str] .= "<div class=\"main\" style=\"font-family:monospace;font-size:medium\">&nbsp;&nbsp;</div>";
								} 
							}
						} 
					} /* end if SRFChistorical dates */

				$positions = $newpositions;
				}  /* end for $day ... */
			} /* end for $month */



		for ( $month = 1; ( $month <= 12 ); $month++ ) {
			$monthname = self::intToMonth($month);
			if (mb_strlen($monthname) > 5 ) {
				$monthname = mb_substr($monthname, 0, 3).".";
				}

			$text .= "<td width=\"75\">" . $monthname . "</td>";
		}	
		$text .= "</tr>\n";

			for ( $day = 1; ( $day <= 31 ); $day++ ) {
			$text .= "<tr>";

			for ( $month = 1; ( $month <= 12 ); $month++ ) {
				$date_str = sprintf("%04d-%02d-%02d", $year, $month, $day);
				if ( $day <= SRFCHistoricalDate::daysInMonth( $year, $month) ) { /* begin SRF */
					$d0 = new SRFCHistoricalDate();
					$d0->create( $year, $month, $day );
					$dayofweek = $d0->getDayOfWeek();
					if ($dayofweek >= 1 and $dayofweek <= 5) {
						$text0 = "<td>";
						foreach ( $holidays as $x ) {
							if ( $date_str >= $x[0] and $date_str <= $x[1] ) {
								$text0 = "<td>";
							}
						}
						$text .= $text0;
					} else {
						$text .= "<td style=\"background:#ddd\">";
					}
					$text .= "<div class=\"day\">".$day."</div>";
					if ($dayofweek == 1) {
						$ts = mktime(0, 0, 0, $month, $day, $year);
						$kw = date("W", $ts);
						$text .= "<span style=\"color:#aaa; float:left; padding-left:2px;position:absolute\">|".$kw."</span>";
					}

					$text .= $entries[$date_str];
	
				} else {
					$text .= "<td>";
				}
			
				$text .= "</td>";
			}	
			$text .= "</tr>\n";
		}
		$text .= "</table>\n";

		return $text;
	}

	/**
	 * @see SMWResultPrinter::getParamDefinitions
	 *
	 * @since 1.8
	 *
	 * @param $definitions array of IParamDefinition
	 *
	 * @return array of IParamDefinition|array
	 */
	public function getParamDefinitions( array $definitions ) {
		$params = parent::getParamDefinitions( $definitions );

		$params['lang'] = array(
			'message' => 'srf_paramdesc_calendarlang',
			'default' => false,
			'manipulatedefault' => false,
		);

		$params['template'] = array(
			'message' => 'srf-paramdesc-template',
			'default' => '',
		);

		$params['userparam'] = array(
			'message' => 'srf-paramdesc-userparam',
			'default' => '',
		);

		$params['color'] = array(
			'message' => 'srf-paramdesc-color',
			'default' => '',
		);

		$params['colors'] = array(
			'message' => 'srf_paramdesc_calendarcolors',
			'default' => '',
		);

		return $params;
	}

}
