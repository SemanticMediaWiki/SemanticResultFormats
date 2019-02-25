<?php
/**
 * SMW result printer for Gantt Diagrams using mermaidjs.
 * https://github.com/knsv/mermaid
 *
 * In order to use this printer you need to have
 * the Mermaid MediaWiki extension installed.
 * https://www.mediawiki.org/wiki/Extension:Mermaid
 *
 * @file Gantt.php
 * @ingroup SemanticResultFormats
 *
 * @licence GNU GPL v2+
 * @author Sebastian Schmid
 */

namespace SRF\Gantt;

use SMWOutputs;
use SMWQueryResult;
use SMWResultPrinter;
use SMWDITime;
use SMWDIBlob;
use Html;

class GanttPrinter extends SMWResultPrinter {

	protected $mParams = [];
	protected $mGantt = null;
	protected $mErrors = [];

	public function getName() {
		// Give grep a chance to find the usage
		return wfMessage( 'srf-printername-gantt' )->text();
	}

	public function getParamDefinitions( array $definitions ) {
		$params = parent::getParamDefinitions( $definitions );

		$params[] = [
			'name'    => 'diagramtitle',
			'message' => 'srf-paramdesc-gantt-diagramtitle',
			'default' => ''
		];

		$params[] = [
			'name'    => 'theme',
			'message' => 'srf-paramdesc-gantt-diagramtheme',
			'default' => 'default'
		];

		$params[] = [
			'name'    => 'axisformat',
			'message' => 'srf-paramdesc-gantt-axisformat',
			'default' => '%m/%d/%Y'
		];

		$params[] = [
			'name'    => 'statusmapping',
			'message' => 'srf-paramdesc-gantt-statusmapping',
			'default' => ''
		];

		$params[] = [
			'name'    => 'prioritymapping',
			'message' => 'srf-paramdesc-gantt-prioritymapping',
			'default' => ''
		];

		$params[] = [
			'name'    => 'titletopmargin',
			'message' => 'srf-paramdesc-gantt-titletopmargin',
			'default' => 25
		];

		$params[] = [
			'name'    => 'barheight',
			'message' => 'srf-paramdesc-gantt-barheight',
			'default' => 20
		];

		$params[] = [
			'name'    => 'leftpadding',
			'message' => 'srf-paramdesc-gantt-leftpadding',
			'default' => 75
		];

		$params[] = [
			'name'    => 'bargap',
			'message' => 'srf-paramdesc-gantt-bargap',
			'default' => 4
		];

		return $params;
	}

	/**
	 * Handle (set) the result format parameters
	 *
	 * @see SMWResultPrinter::handleParameters()
	 */
	protected function handleParameters( array $params, $outputmode ) {

		parent::handleParameters( $params, $outputmode );

		//Set header params
		$this->mParams['title'] = trim( $params['diagramtitle'] );
		$this->mParams['axisformat'] = trim( $params['axisformat'] );
		$this->mParams['statusmapping'] = trim( $params['statusmapping'] );
		$this->mParams['prioritymapping'] = trim( $params['prioritymapping'] );
		$this->mParams['theme'] = trim( $params['theme'] );

		//Validate Theme
		if ( !in_array( $this->params['theme'], [ 'default', 'neutral', 'dark', 'forest' ] ) ) {
			$this->mErrors[] = wfMessage( 'srf-error-gantt-theme' )->text();
		}

		//Validate mapping
		if ( !empty( trim( $params['statusmapping'] ) ) ) {

			$paramMapping = explode( ';', trim( $params['statusmapping'] ) );

			foreach ( $paramMapping as $pm ) {

				// if no "=>" pattern was found
				if ( !strpos( $pm, '=>' ) ) {
					$this->mErrors[] = wfMessage( 'srf-error-gantt-mapping-assignment', 'statusmapping' )->text();
				} else {
					$pmKeyVal = explode( '=>', $pm );
					// if no key value pair
					if ( count( $pmKeyVal ) % 2 !== 0 ) {
						$this->mErrors[] = wfMessage( 'srf-error-gantt-mapping-assignment', 'statusmapping' )->text();
					} else {
						$mapping[trim( $pmKeyVal[0] )] = trim( $pmKeyVal[1] );
						// check if the common status keys are used
						if ( trim( $pmKeyVal[1] ) !== 'active' && trim( $pmKeyVal[1] ) !== 'done' ) {
							$this->mErrors[] = wfMessage( 'srf-error-gantt-mapping-keywords' )->text();
						}
					}
				}
			}
		}
		if ( !empty( trim( $params['prioritymapping'] ) ) ) {

			$paramMapping = explode( ';', trim( $params['prioritymapping'] ) );

			foreach ( $paramMapping as $pm ) {

				// if no "=>" pattern was found
				if ( !strpos( $pm, '=>' ) ) {
					$this->mErrors[] = wfMessage( 'srf-error-gantt-mapping-assignment', 'prioritymapping' )->text();
				} else {
					$pmKeyVal = explode( '=>', $pm );
					// if no key value pair
					if ( count( $pmKeyVal ) % 2 !== 0 ) {
						$this->mErrors[] = wfMessage( 'srf-error-gantt-mapping-assignment', 'statusmapping' )->text();
					} else {
						$mapping[trim( $pmKeyVal[0] )] = trim( $pmKeyVal[1] );
						// check if the common status keys are used
						if ( trim( $pmKeyVal[1] ) !== 'crit' ) {
							$this->mErrors[] = wfMessage( 'srf-error-gantt-mapping-keywords' )->text();
						}
					}
				}
			}
		}

		$this->mGantt = new Gantt( $this->mParams );
	}

	/**
	 * Return serialised results in specified format.
	 * @param SMWQueryResult $queryResult
	 * @param $outputmode
	 * @return string
	 */
	protected function getResultText( SMWQueryResult $queryResult, $outputmode ) {

		// Show warning if Extension:Mermaid is not available
		if ( !class_exists( 'Mermaid' ) && !class_exists( 'Mermaid\\MermaidParserFunction' ) ) {
			//wfWarn( 'The SRF Mermaid format needs the Mermaid extension to be installed.' );
				$queryResult->addErrors( ['Error: Mermaid Extension needs to be installed.'] );
			return '';
		}

		// Load general Modules
		// First load the dependent modules of Mermaid ext
		SMWOutputs::requireResource( 'ext.mermaid' );
		SMWOutputs::requireResource( 'ext.mermaid.styles' );
		SMWOutputs::requireResource( 'ext.srf.gantt' );

		//Add Tasks & Sections
		while ( $row = $queryResult->getNext() ) {

			$status = [];
			$priority = [];
			$startDate = '';
			$endDate = '';
			$taskID = '';
			$taskTitle = '';
			$sections = [];

			// Loop through all field of a row
			foreach ( $row as $field ) {

				$fieldLabel = $field->getPrintRequest()->getLabel();

				//get values
				foreach ( $field->getContent() as $dataItem ) {

					switch ( $fieldLabel ) {
						case 'section':
							$sections[$dataItem->getTitle()->getPrefixedDBKey()] = $dataItem->getSortKey();
							break;
						case 'task':
							if ( $dataItem instanceof SMWDIBlob ) {
								$taskTitle = $dataItem->getString();
								$taskID = $field->getResultSubject()->getTitle()->getPrefixedDBKey();
							}
							break;
						case 'startdate':
							if ( $dataItem instanceof SMWDITime ) {
								$startDate = $dataItem->getMwTimestamp();
							}
							break;
						case 'enddate':
							if ( $dataItem instanceof SMWDITime ) {
								$endDate = $dataItem->getMwTimestamp();
							}
							break;
						case 'status':
							if ( $dataItem instanceof SMWDIBlob ) {
								$status[] = $dataItem->getString();
							}
							break;
						case 'priority':
							if ( $dataItem instanceof SMWDIBlob ) {
								$priority[] = $dataItem->getString();
							}
							break;
					}
				}
			}

			// Add section/Task
			// Title, TaskID, StartDate and EndDate are required
			if ( $taskID !== '' && $taskTitle !== '' && $startDate !== '' && $endDate !== '' ) {
				$this->mGantt->addTask( $taskID, $taskTitle, $status, $priority, $startDate, $endDate );

				// If no section was found, put task into a dummy section object
				// "gantt-no-section#21780240" is used to identify Tasks that with no section (dummy section)
				if ( count( $sections ) == 0 ) {
					$this->mGantt->addSection( 'gantt-no-section#21780240', '', $startDate, $endDate, $taskID );
				} else {
					foreach ( $sections as $sectionID => $sectionTitle ) {
						$this->mGantt->addSection( $sectionID, $sectionTitle, $startDate, $endDate, $taskID );
					}
				}
			}
		}

		// Improve unique id by adding a random number
		$id = uniqid( 'srf-gantt-' . rand( 1, 10000 ) );

		// Add gantt configurations
		$config = [
			'theme' => $this->params['theme'],
			'gantt' => [
				'leftPadding'    => intval( $this->params['leftpadding'] ),
				'titleTopMargin' => intval( $this->params['titletopmargin'] ),
				'barHeight'      => intval( $this->params['barheight'] ),
				'barGap'         => intval( $this->params['bargap'] )
			]
		];

		// Manage Output
		if ( !empty( $this->mErrors ) ) {
			return $queryResult->addErrors( $this->mErrors );
		} else {
			return Html::rawElement( 'div', [
				'id'           => $id,
				'class'        => 'srf-gantt',
				'data-mermaid' => json_encode( [
					'content' => $this->mGantt->getGanttOutput(),
					'config'  => $config
				], JSON_UNESCAPED_UNICODE )
			], Html::rawElement( 'div', [
				'class' => 'mermaid-dots',
			] ) );
		}
	}
}