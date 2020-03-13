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

	protected $mGantt = null;
	protected $mErrors = [];

	public function getName() {
		return wfMessage( 'srf-printername-gantt' )->text();
	}

	public function getParamDefinitions( array $definitions ) {
		$params = parent::getParamDefinitions( $definitions );

		$params[] = [
			'type'    => 'string',
			'name'    => 'diagramtitle',
			'message' => 'srf-paramdesc-gantt-diagramtitle',
			'default' => ''
		];

		$params[] = [
			'type'    => 'string',
			'name'    => 'theme',
			'message' => 'srf-paramdesc-gantt-diagramtheme',
			'default' => 'default'
		];

		$params[] = [
			'type'    => 'string',
			'name'    => 'axisformat',
			'message' => 'srf-paramdesc-gantt-axisformat',
			'default' => '%m/%d/%Y'
		];

		$params[] = [
			'type'    => 'string',
			'name'    => 'statusmapping',
			'message' => 'srf-paramdesc-gantt-statusmapping',
			'default' => ''
		];

		$params[] = [
			'type'    => 'string',
			'name'    => 'prioritymapping',
			'message' => 'srf-paramdesc-gantt-prioritymapping',
			'default' => ''
		];

		$params[] = [
			'type'    => 'integer',
			'name'    => 'titletopmargin',
			'message' => 'srf-paramdesc-gantt-titletopmargin',
			'default' => 25
		];

		$params[] = [
			'type'    => 'integer',
			'name'    => 'barheight',
			'message' => 'srf-paramdesc-gantt-barheight',
			'default' => 20
		];

		$params[] = [
			'type'    => 'integer',
			'name'    => 'leftpadding',
			'message' => 'srf-paramdesc-gantt-leftpadding',
			'default' => 75
		];

		$params[] = [
			'type'    => 'integer',
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

		//Set header params
		$this->params['title'] = trim( $params['diagramtitle'] );
		$this->params['axisformat'] = trim( $params['axisformat'] );
		$this->params['statusmapping'] =  $this->getValidatedMapping( $params[ 'statusmapping' ], 'statusmapping', [ 'active', 'done' ] );
		$this->params['prioritymapping'] = $this->getValidatedMapping( $params[ 'prioritymapping' ], 'prioritymapping', [ 'crit' ] );
		$this->params['theme'] = $this->getValidatedTheme($params['theme']);

		$this->mGantt = $this->getGantt();
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
			$queryResult->addErrors( [wfMessage('')->text()] );
			return '';
		}

		// Load general Modules
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
				'data-mermaid' => html_entity_decode( json_encode( [
					'content' => $this->mGantt->getGanttOutput(),
					'config'  => $config
				]))
			], Html::rawElement( 'div', [ 'class' => 'mermaid-dots' ]));
		}
	}

	private function getGantt(){
		return new Gantt( $this->params );
	}

	/**
	 * Return valid theme as string
	 * @param String $theme
	 *
	 * @return string
	 */
	private function getValidatedTheme( $theme ) {
		$theme = trim( $theme );

		if ( !in_array( $this->params['theme'], [ 'default', 'neutral', 'dark', 'forest' ] ) ) {
			$this->mErrors[] = wfMessage( 'srf-error-gantt-theme' )->text();
		}

		return $theme;
	}


	private function getValidatedMapping( $params, $mappingType, array $mappingKeys){
		//Validate mapping
		$mapping = [];

		if ( !empty( $params ) ) {
			$paramMapping = explode( ';', trim( $params ) );

			foreach ( $paramMapping as $pm ) {
				$pmKeyVal = explode( '=>', $pm, 2);

				// if no key value pair
				if ( count( $pmKeyVal ) !== 2 ) {
					$this->mErrors[] = wfMessage( 'srf-error-gantt-mapping-assignment', $mappingType )->text();
				} else {
					$mapping[trim( $pmKeyVal[0] )] = trim( $pmKeyVal[1] );

					if(!in_array(trim( $pmKeyVal[1] ), $mappingKeys)){
						$this->mErrors[] = wfMessage( 'srf-error-gantt-mapping-keywords' )->text();
					}
				}
			}
			return $mapping;
		}
		return '';
	}
}