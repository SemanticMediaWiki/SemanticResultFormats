<?php

/**
 * Result printer that prints query results as a valuerank.
 * In other words, it prints a list of all occuring values, with duplicates removed,
 * together with their occurrence count.
 *
 * Build out of Tag Cloud Format by Jeroen De Dauw < jeroendedauw@gmail.com >
 *
 * For example, this result set: foo bar baz foo bar bar ohi
 * Will be turned into
 * * bar (3)
 * * foo (2)
 * * baz (1)
 * * ohi (1)
 *
 * @since 1.7
 *
 * @licence GNU GPL v2
 * @author DaSch < dasch@daschmedia.de >
 * @author mwjames
 */
class SRFValueRank extends SMWResultPrinter {

	/**
	 * @var array
	 */
	protected $tagsHtml = [];

	/**
	 * @see SMWResultPrinter::getName
	 *
	 * @return string
	 */
	public function getName() {
		return wfMessage( 'srf_printername_valuerank' )->text();
	}

	/**
	 * @see SMWResultPrinter::getResultText
	 *
	 * @since 1.7
	 *
	 * @param SMWQueryResult $results
	 * @param $outputMode
	 *
	 * @return string
	 */
	public function getResultText( SMWQueryResult $results, $outputMode ) {

		// Template support
		$this->hasTemplates = $this->params['template'] !== '';

		// Prioritize HTML setting
		$this->isHTML = $this->params['template'] === '';

		$outputMode = SMW_OUTPUT_HTML;

		return $this->getFormatOutput( $this->getValueRank( $this->getResultValues( $results, $outputMode ) ) );
	}

	/**
	 * Returns an array with the tags (keys) and the number of times they occur (values).
	 *
	 * @since 1.7
	 *
	 * @param SMWQueryResult $results
	 * @param $outputMode
	 *
	 * @return array
	 */
	protected function getResultValues( SMWQueryResult $results, $outputMode ) {
		$tags = [];

		/**
		 * @var $row SMWResultArray Objects (pages)
		 * @var $dataValue SMWDataValue
		 *
		 * @return array
		 */
		while ( $row = $results->getNext() ) {
			// SMWResultArray for a sinlge property
			for ( $i = 0, $n = count( $row ); $i < $n; $i++ ) {
				while ( ( $dataValue = $row[$i]->getNextDataValue() ) !== false ) {

					$isSubject = $row[$i]->getPrintRequest()->getMode() == SMWPrintRequest::PRINT_THIS;

					// If the main object should not be included, skip it.
					if ( $i == 0 && !$this->params['includesubject'] && $isSubject ) {
						continue;
					}

					// Get the HTML for the tag content. Pages are linked, other stuff is just plaintext.
					if ( $dataValue->getTypeID() == '_wpg' ) {
						$value = $dataValue->getTitle()->getText();
						$html = $dataValue->getLongText( $outputMode, $this->getLinker( $isSubject ) );
					} else {
						$html = $dataValue->getShortText( $outputMode, $this->getLinker( false ) );
						$value = $html;
					}

					if ( !array_key_exists( $value, $tags ) ) {
						$tags[$value] = 0;
						$this->tagsHtml[$value] = $html; // Store the HTML separetely, so sorting can be done easily.
					}

					$tags[$value]++;
				}
			}
		}

		foreach ( $tags as $name => $count ) {
			if ( $count < $this->params['min'] ) {
				unset( $tags[$name] );
			}
		}
		return $tags;
	}

	/**
	 * Determine ranks
	 *
	 * @since 1.7
	 *
	 * @param array $tags
	 *
	 * @return array
	 */
	protected function getValueRank( array $tags ) {
		if ( count( $tags ) == 0 ) {
			return $tags;
		}

		arsort( $tags, SORT_NUMERIC );

		if ( count( $tags ) > $this->params['maxtags'] ) {
			$tags = array_slice( $tags, 0, $this->params['maxtags'], true );
		}

		return $tags;
	}

	/**
	 * Format the output representation
	 *
	 * @since 1.8
	 *
	 * @param array $tags
	 *
	 * @return string
	 */
	protected function getFormatOutput( array $tags ) {
		$htmlTags = [];

		if ( $this->params['introtemplate'] !== '' && $this->params['template'] !== '' ) {
			$htmlTags[] = "{{" . $this->params['introtemplate'] . "}}";
		}

		foreach ( $tags as $name => $size ) {
			if ( $this->params['template'] !== '' ) {
				$htmlTags[] = $this->addTemplateOutput( $name, $size, $rownum );
			} else {
				$htmlTags[] = Html::rawElement(
					( $this->params['liststyle'] === 'none' ? 'span' : 'li' ),
					[ 'style' => "font-size:$size" ],
					$this->tagsHtml[$name] . '&nbsp;(' . $size . ')'
				);
			}
		}

		if ( $this->params['outrotemplate'] !== '' && $this->params['template'] !== '' ) {
			$htmlTags[] = "{{" . $this->params['outrotemplate'] . "}}";
		}

		return Html::rawElement(
			( $this->params['liststyle'] === 'none' ? 'div' : $this->params['liststyle'] ),
			[ 'class' => $this->params['class'] ],
			implode( '', $htmlTags )
		);
	}

	/**
	 * Create a template output
	 *
	 * @since 1.8
	 *
	 * @param string $name
	 * @param integer $rank
	 * @param integer $rownum
	 *
	 * @return string
	 */
	protected function addTemplateOutput( $name, $rank, &$rownum ) {
		$rownum++;
		$wikitext = $this->params['userparam'] ? "|userparam=" . $this->params['userparam'] : '';
		$wikitext .= "|" . $name;
		$wikitext .= "|rank=" . $rank;
		$wikitext .= "|#=$rownum";
		return '{{' . trim( $this->params['template'] ) . $wikitext . '}}';
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

		$params['includesubject'] = [
			'type' => 'boolean',
			'default' => false,
			'message' => 'srf_paramdesc_includesubject',
		];

		$params['min'] = [
			'type' => 'integer',
			'default' => 1,
			'message' => 'srf_paramdesc_mincount',
		];

		$params['maxtags'] = [
			'type' => 'integer',
			'default' => 1000,
			'message' => 'srf_paramdesc_maxtags',
		];

		$params['template'] = [
			'message' => 'srf-paramdesc-template',
			'default' => '',
		];

		$params['userparam'] = [
			'message' => 'srf-paramdesc-userparam',
			'default' => '',
		];

		$params['introtemplate'] = [
			'message' => 'smw-paramdesc-introtemplate',
			'default' => '',
		];

		$params['outrotemplate'] = [
			'message' => 'smw-paramdesc-outrotemplate',
			'default' => '',
		];

		$params['liststyle'] = [
			'message' => 'srf-paramdesc-liststyle',
			'default' => 'ul',
			'values' => [ 'ul', 'ol', 'none' ],
		];

		$params['class'] = [
			'message' => 'srf-paramdesc-class',
			'default' => '',
		];

		return $params;
	}
}