<?php

$wgAutoloadClasses['SRFCHistoricalDate'] = dirname( __FILE__ )
	. '/SRFC_HistoricalDate.php';

/**
 * Result printer that prints query results as a monthly calendar.
 *
 * @file SRF_Calendar.php
 * @ingroup SemanticResultFormats
 *
 * @author Yaron Koren
 */
class SRFCalendar extends SMWResultPrinter {

	protected $mTemplate;
	protected $mUserParam;
	protected $mRealUserLang = null;
	protected $mStartMonth;
	protected $mStartYear;

	protected function setColors( $colorsText ) {
		$colors = [];
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
		// startmonth is initialized with current month by default
		$this->mStartMonth = trim( $params['startmonth'] );
		// startyear is initialized with current year by default
		$this->mStartYear = trim( $params['startyear'] );

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
		return wfMessage( 'srf_printername_calendar' )->text();
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
	 * @todo Split up megamoth
	 */
	protected function getResultText( SMWQueryResult $res, $outputmode ) {
		$events = [];

		// Print all result rows.
		while ( $row = $res->getNext() ) {
			$dates = [];
			$title = $text = $color = '';

			if ( $this->mTemplate != '' ) {
				// Build template code
				$this->hasTemplates = true;

				if ( $this->mUserParam ) {
					$text = "|userparam=$this->mUserParam";
				}

				foreach ( $row as $i => $field ) {
					$pr = $field->getPrintRequest();
					$text .= '|' . ( $i + 1 ) . '=';

					while (
						( $object = $field->getNextDataValue() ) !== false
					) {
						if ( $object->getTypeID() == '_dat' ) {
							$text .= $object->getLongWikiText();

							// use shorter "LongText" for wikipage
						} elseif ( $object->getTypeID() == '_wpg' ) {
							// handling of "link=" param
							if ( $this->mLinkOthers ) {
								$text .=
									$object->getLongText( $outputmode, null );
							} else {
								$text .= $object->getWikiValue();
							}
						} else {
							$text .= $object->getShortText( $outputmode, null );
						}

						if (
							$pr->getMode() == SMWPrintRequest::PRINT_PROP &&
							$pr->getTypeID() == '_dat'
						) {
							$datePropLabel = $pr->getLabel();
							if ( !array_key_exists( $datePropLabel, $dates ) ) {
								$dates[$datePropLabel] = [];
							}
							$dates[$datePropLabel][] =
								$this->formatDateStr( $object );
						}
					}
				}
			} else {
				// Build simple text.
				$numNonDateProperties = 0;
				// Cycle through a 'row', which is the page
				// name (the first field) plus all its
				// properties.
				foreach ( $row as $i => $field ) {
					$pr = $field->getPrintRequest();
					// A property can have more than one
					// value - cycle through all the values
					// for this property.
					$textForProperty = '';

					while (
						( $object = $field->getNextDataValue() ) !== false
					) {
						if ( $object->getTypeID() == '_dat' ) {
							// Don't add date values to the display.

							// use shorter "LongText" for wikipage
						} elseif ( $object->getTypeID() == '_wpg' ) {
							if ( $i == 0 ) {
								$title = Title::newFromText(
									$object->getShortWikiText( false )
								);
							} else {
								$numNonDateProperties++;

								// handling of "headers=" param
								if ( $this->mShowHeaders == SMW_HEADERS_SHOW ) {
									$textForProperty .= $pr->getHTMLText(
											smwfGetLinker()
										) . ' ';
								} elseif (
									$this->mShowHeaders == SMW_HEADERS_PLAIN
								) {
									$textForProperty .= $pr->getLabel() . ' ';
								}

								// If $this->mShowHeaders == SMW_HEADERS_HIDE,
								//	print nothing.
								// handling of "link=" param
								if ( $this->mLinkOthers ) {
									$textForProperty .= $object->getLongText(
										$outputmode,
										smwfGetLinker()
									);
								} else {
									$textForProperty .= $object->getWikiValue();
								}
							}
						} else {
							$numNonDateProperties++;
							$textForProperty .=
								$pr->getHTMLText( smwfGetLinker() )
								. ' ' . $object->getShortText(
									$outputmode,
									smwfGetLinker()
								);
						}
						if (
							$pr->getMode() == SMWPrintRequest::PRINT_PROP &&
							$pr->getTypeID() == '_dat'
						) {
							$datePropLabel = $pr->getLabel();
							if ( !array_key_exists( $datePropLabel, $dates ) ) {
								$dates[$datePropLabel] = [];
							}
							$dates[$datePropLabel][] =
								$this->formatDateStr( $object );
						}
					}

					// Add the text for this property to
					// the main text, adding on parentheses
					// or commas as needed.
					if ( $numNonDateProperties == 1 ) {
						$text .= ' (';
					} elseif ( $numNonDateProperties > 1 ) {
						$text .= ', ';
					}
					$text .= $textForProperty;
				}
				if ( $numNonDateProperties > 0 ) {
					$text .= ')';
				}
			}

			if ( count( $dates ) > 0 ) {
				// Handle the 'color=' value, whether it came
				// from a compound query or a regular one.
				$resSubject = $field->getResultSubject();
				if ( isset( $resSubject->display_options )
					&& is_array( $resSubject->display_options ) ) {
					if ( array_key_exists(
						'color',
						$resSubject->display_options
					)
					) {
						$color = $resSubject->display_options['color'];
					}
					if (
					array_key_exists(
						'colors',
						$resSubject->display_options
					)
					) {
						$this->setColors(
							$resSubject->display_options['colors']
						);
					}
				}

				foreach ( $dates as $label => $datesForLabel ) {
					foreach ( $datesForLabel as $date ) {
						$curText = $text;
						// If there's more than one
						// label, i.e. more than one
						// date property being displayed,
						// show the name of the current
						// property in parentheses.
						if ( count( $dates ) > 1 ) {
							$curText = "($label) " . $curText;
						}
						$curColor = $color;
						if ( array_key_exists( $label, $this->mColors ) ) {
							$curColor = $this->mColors[$label];
						}
						$events[] = [ $title, $curText, $date, $curColor ];
					}
				}
			}
		}

		$result = $this->displayCalendar( $events );

		// Go back to the actual user's language, in case a different
		// language had been specified for this calendar.
		if ( !is_null( $this->mRealUserLang ) ) {
			global $wgLang;
			$wgLang = $this->mRealUserLang;
		}

		global $wgParser;

		if ( is_null( $wgParser->getTitle() ) ) {
			return $result;
		} else {
			return [ $result, 'noparse' => 'true', 'isHTML' => 'true' ];
		}
	}

	protected static function intToMonth( $int ) {
		$months = [
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
		];

		return wfMessage(
			array_key_exists( $int, $months )
				? $months[$int]
				: 'january'
		)->inContentLanguage()->text();
	}

	function formatDateStr( $object ) {
		// For some reason, getMonth() and getDay() sometimes return a
		// number with a leading zero - get rid of it using (int)
		return $object->getYear()
			. '-' . (int)$object->getMonth() . '-' . (int)$object->getDay();
	}

	function displayCalendar( $events ) {
		global $wgParser;
		global $srfgFirstDayOfWeek;
		global $srfgScriptPath;

		$context = RequestContext::getMain();
		$request = $context->getRequest();
		if ( !$wgParser->mFirstCall ) {
			$wgParser->disableCache();
		}

		$context->getOutput()->addLink(
			[
				'rel' => 'stylesheet',
				'type' => 'text/css',
				'media' => 'screen, print',
				'href' => $srfgScriptPath
					. '/formats/calendar/resources/ext.srf.calendar.css'
			]
		);

		// Set variables differently depending on whether this is
		// being called from a regular page, via #ask, or from a
		// special page: most likely either Special:Ask or
		// Special:RunQuery.
		$pageTitle = $context->getTitle();
		if ( !$pageTitle ) {
			$pageTitle = $wgParser->getTitle();
		}
		$additionalQueryString = '';
		$hiddenInputs = '';

		if ( $pageTitle->isSpecialPage() ) {
			$requestValues = $request->getValues();
			// Also go through the predefined PHP variable
			// $_REQUEST, because $request->getValues() for
			// some reason doesn't return array values - is
			// there a better (less hacky) way to do this?
			foreach ( $_REQUEST as $key => $value ) {
				if ( is_array( $value ) ) {
					foreach ( $value as $k2 => $v2 ) {
						$newKey = $key . '[' . $k2 . ']';
						$requestValues[$newKey] = $v2;
					}
				}
			}

			foreach ( $requestValues as $key => $value ) {
				if ( $key != 'month' && $key != 'year'
					// values from 'RunQuery'
					&& $key != 'query' && $key != 'free_text'
				) {
					$additionalQueryString .= "&$key=$value";
					$hiddenInputs .= "<input type=\"hidden\" " .
						"name=\"$key\" value=\"$value\" />";
				}
			}
		}

		// Set days of the week.
		$weekDayNames = [
			1 => wfMessage( 'sunday' )->text(),
			2 => wfMessage( 'monday' )->text(),
			3 => wfMessage( 'tuesday' )->text(),
			4 => wfMessage( 'wednesday' )->text(),
			5 => wfMessage( 'thursday' )->text(),
			6 => wfMessage( 'friday' )->text(),
			7 => wfMessage( 'saturday' )->text()
		];
		if ( empty( $srfgFirstDayOfWeek ) ) {
			$firstDayOfWeek = 1;
			$lastDayOfWeek = 7;
		} else {
			$firstDayOfWeek =
				array_search( $srfgFirstDayOfWeek, $weekDayNames );
			if ( $firstDayOfWeek === false ) {
				// Bad value for $srfgFirstDayOfWeek!
				print 'Warning: Bad value for $srfgFirstDayOfWeek "' .
					'(' . $srfgFirstDayOfWeek . '")';
				$firstDayOfWeek = 1;
			}
			if ( $firstDayOfWeek == 1 ) {
				$lastDayOfWeek = 7;
			} else {
				$lastDayOfWeek = $firstDayOfWeek - 1;
			}
		}

		// Now create the actual array of days of the week, based on
		// the start day
		$weekDays = [];
		for ( $i = 1; $i <= 7; $i++ ) {
			$curDay = ( ( $firstDayOfWeek + $i - 2 ) % 7 ) + 1;
			$weekDays[$i] = $weekDayNames[$curDay];
		}

		// Get all the date-based values we need - the current month
		// and year (i.e., the one the user is looking at - not
		// necessarily the "current" ones), the previous and next months
		// and years (same - note that the previous or next month could
		// be in a different year), the number of days in the current,
		// previous and next months, etc.

		if ( is_numeric( $this->mStartMonth ) &&
			( intval( $this->mStartMonth ) == $this->mStartMonth ) &&
			$this->mStartMonth >= 1 && $this->mStartMonth <= 12
		) {
			$curMonthNum = $this->mStartMonth;
		} else {
			$curMonthNum = date( 'n' );
		}
		if ( $request->getCheck( 'month' ) ) {
			$queryMonth = $request->getVal( 'month' );
			if ( is_numeric( $queryMonth ) &&
				( intval( $queryMonth ) == $queryMonth ) &&
				$queryMonth >= 1 && $queryMonth <= 12
			) {
				$curMonthNum = $request->getVal( 'month' );
			}
		}

		$curMonth = self::intToMonth( $curMonthNum );

		if ( is_numeric( $this->mStartYear ) &&
			( intval( $this->mStartYear ) == $this->mStartYear )
		) {
			$curYear = $this->mStartYear;
		} else {
			$curYear = date( 'Y' );
		}
		if ( $request->getCheck( 'year' ) ) {
			$queryYear = $request->getVal( 'year' );
			if ( is_numeric( $queryYear ) &&
				intval( $queryYear ) == $queryYear
			) {
				$curYear = $request->getVal( 'year' );
			}
		}

		if ( $curMonthNum == '1' ) {
			$prevMonthNum = '12';
			$prevYear = $curYear - 1;
		} else {
			$prevMonthNum = $curMonthNum - 1;
			$prevYear = $curYear;
		}

		if ( $curMonthNum == '12' ) {
			$nextMonthNum = '1';
			$nextYear = $curYear + 1;
		} else {
			$nextMonthNum = $curMonthNum + 1;
			$nextYear = $curYear;
		}

		// There's no year '0' - change it to '1' or '-1'.
		if ( $curYear == '0' ) {
			$curYear = '1';
		}
		if ( $nextYear == '0' ) {
			$nextYear = '1';
		}
		if ( $prevYear == '0' ) {
			$prevYear = '-1';
		}

		$prevMonthUrl = $pageTitle->getLocalURL(
			"month=$prevMonthNum&year=$prevYear" .
			$additionalQueryString
		);
		$nextMonthUrl = $pageTitle->getLocalURL(
			"month=$nextMonthNum&year=$nextYear" .
			$additionalQueryString
		);
		$todayUrl = $pageTitle->getLocalURL( $additionalQueryString );

		$todayText = wfMessage( 'srfc_today' )->text();
		$prevMonthText = wfMessage( 'srfc_previousmonth' )->text();
		$nextMonthText = wfMessage( 'srfc_nextmonth' )->text();
		$goToMonthText = wfMessage( 'srfc_gotomonth' )->text();

		// Get day of the week that the first of this month falls on.
		$firstDay = new SRFCHistoricalDate();
		$firstDay->create( $curYear, $curMonthNum, 1 );
		$startDay = $firstDayOfWeek - $firstDay->getDayOfWeek();
		if ( $startDay > 0 ) {
			$startDay -= 7;
		}
		$daysInPrevMonth =
			SRFCHistoricalDate::daysInMonth( $prevYear, $prevMonthNum );
		$daysInCurMonth =
			SRFCHistoricalDate::daysInMonth( $curYear, $curMonthNum );
		$todayString = date( 'Y n j', time() );
		$pageName = $pageTitle->getPrefixedDbKey();

		// Create table for holding title and navigation information.
		$text = <<<END
<table class="navigation_table">
<tr><td class="month_name">$curMonth $curYear</td>
<td class="nav_links"><a href="$prevMonthUrl" title="$prevMonthText">
<img src="{$srfgScriptPath}/formats/calendar/resources/images/left-arrow.png" border="0" />
</a>&#160;<a href="$todayUrl">$todayText</a>&#160;
<a href="$nextMonthUrl" title="$nextMonthText">
<img src="{$srfgScriptPath}/formats/calendar/resources/images/right-arrow.png" border="0" />
</a></td><td class="nav_form"><form>
<input type="hidden" name="title" value="$pageName">
<select name="month">

END;
		for ( $i = 1; $i <= 12; $i++ ) {
			$monthName = self::intToMonth( $i );
			$selectedStr = ( $i == $curMonthNum ) ? "selected" : "";
			$text .= "<option value=\"$i\" $selectedStr>
				$monthName</option>\n";
		}
		$text .= <<<END
</select>
<input name="year" type="text" value="$curYear" size="4">
$hiddenInputs
<input type="submit" value="$goToMonthText">
</form>
</td>
</tr>
</table>

<table class="month_calendar">
<tr class="weekdays">

END;
		// First row of the main table holds the days of the week
		foreach ( $weekDays as $weekDay ) {
			$text .= "<td>$weekDay</td>";
		}
		$text .= "</tr>\n";

		// Now, create the calendar itself -
		// loop through a set of weeks, from a "Sunday" (which might be
		// before the beginning of the month) to a "Saturday" (which
		// might be after the end of the month).
		// "Sunday" and "Saturday" are in quotes because the actual
		// start and end days of the week can be set by the admin.
		$dayOfTheWeek = $firstDayOfWeek;
		$isLastWeek = false;
		for ( $day = $startDay;
		( !$isLastWeek || $dayOfTheWeek != $firstDayOfWeek );
			  $day++ ) {
			if ( $dayOfTheWeek == $firstDayOfWeek ) {
				$text .= "<tr>\n";
			}
			if ( "$curYear $curMonthNum $day" == $todayString ) {
				$text .= "<td class=\"today\">\n";
			} elseif ( $dayOfTheWeek == 1 || $dayOfTheWeek == 7 ) {
				$text .= "<td class=\"weekend_day\">\n";
			} else {
				$text .= "<td>\n";
			}
			if ( $day == $daysInCurMonth || $day > 50 ) {
				$isLastWeek = true;
			}
			// If this day is before or after the current month,
			// set a "display day" to show on the calendar, and
			// use a different CSS style for it.
			if ( $day > $daysInCurMonth || $day < 1 ) {
				if ( $day < 1 ) {
					$displayDay = $day + $daysInPrevMonth;
					$dateStr =
						$prevYear . '-' . $prevMonthNum . '-' . $displayDay;
				}
				if ( $day > $daysInCurMonth ) {
					$displayDay = $day - $daysInCurMonth;
					$dateStr =
						$nextYear . '-' . $nextMonthNum . '-' . $displayDay;
				}
				$text .=
					"<div class=\"day day_other_month\">$displayDay</div>\n";
			} else {
				$dateStr = $curYear . '-' . $curMonthNum . '-' . $day;
				$text .= "<div class=\"day\">$day</div>\n";
			}
			// Finally, the most important step - get the events
			// that match this date, and the given set of criteria,
			// and display them in this date's box.
			$text .= "<div class=\"main\">\n";
			if ( $events == null ) {
				$events = [];
			}
			foreach ( $events as $event ) {
				list( $eventTitle, $otherText, $eventDate, $color ) = $event;
				if ( $eventDate == $dateStr ) {
					if ( $this->mTemplate != '' ) {
						$templatetext = '{{' . $this->mTemplate . $otherText .
							'|thisdate=' . $dateStr . '}}';
						$templatetext =
							$wgParser->replaceVariables( $templatetext );
						$templatetext =
							$wgParser->recursiveTagParse( $templatetext );
						$text .= $templatetext;
					} else {
						$eventStr = Linker::link( $eventTitle );
						if ( $color != '' ) {
							$text .= "<div class=\"colored-entry\">
								<p style=\"border-left: 7px $color solid;\">
								$eventStr $otherText</p></div>\n";
						} else {
							$text .= "$eventStr $otherText\n\n";
						}
					}
				}
			}
			$text .= <<<END
</div>
</td>

END;
			if ( $dayOfTheWeek == $lastDayOfWeek ) {
				$text .= "</tr>\n";
			}
			if ( $dayOfTheWeek == 7 ) {
				$dayOfTheWeek = 1;
			} else {
				$dayOfTheWeek++;
			}
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

		$params['lang'] = [
			'message' => 'srf_paramdesc_calendarlang',
			'default' => false,
			'manipulatedefault' => false,
		];

		$params['template'] = [
			'message' => 'smw-paramdesc-template',
			'default' => '',
		];

		$params['userparam'] = [
			'message' => 'smw-paramdesc-userparam',
			'default' => '',
		];

		$params['color'] = [
			'message' => 'srf-paramdesc-color',
			'default' => '',
		];

		$params['colors'] = [
			'message' => 'srf_paramdesc_calendarcolors',
			'default' => '',
		];

		$params['startmonth'] = [
			'message' => 'srf-paramdesc-calendar-startmonth',
			'default' => date( 'n' ),
		];

		$params['startyear'] = [
			'message' => 'srf-paramdesc-calendar-startyear',
			'default' => date( 'Y' ),
		];

		return $params;
	}
}
