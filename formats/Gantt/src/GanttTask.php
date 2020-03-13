<?php
/**
 * File holding the GanttTask class
 *
 * Creates Tasks with params
 *
 * @author Sebastian Schmid
 * @file
 * @ingroup SemanticResultFormats
 */

namespace SRF\Gantt;


class GanttTask {

	private $mTitle;
	private $mID;
	private $mStatus = '';
	private $mPriority = '';
	private $mStartDate;
	private $mEndDate;


	public function setTitle( $title ) {
		$this->mTitle = $title;
	}

	public function getTitle() {
		return $this->mTitle;
	}

	public function setID( $id ) {
		$this->mID = $id;
	}

	public function getID() {
		return $this->mID;
	}

	public function setStatus($status){
		$this->mStatus = $this->mStatus . $status . ', ';
	}

	public function setPriority($priority){
		$this->mPriority = $this->mPriority . $priority . ', ';
	}

	/**
	 * Either set the status or priority of the task
	 *
	 * @param array $params
	 * @param string $paramMapping
	 * @param string $type
	 *
	 */
	public function setTaskParam( $params, $paramMapping, $type ) {

		// skip if $paramMapping is empty and
		// output errormessage if wrong mapping
		if ( !empty( $paramMapping ) ) {

			foreach ( $paramMapping as $pKey => $pVal ) {
				if ( in_array( $pKey, $params ) ) {
					if ( $type === 'status' ) {
						$this->setStatus( trim( $pVal ) );
					}
					if ( $type === 'priority' ) {
						$this->setPriority( trim( $pVal ) );
					}
				}
			}
		}
	}

	public function getStatus() {
		return $this->mStatus;
	}

	public function getPriority() {
		return $this->mPriority;
	}

	public function setStartDate( $startDate ) {
		$this->mStartDate = $startDate;
	}

	public function getStartDate() {
		return $this->mStartDate;
	}

	public function setEndDate( $endDate ) {
		$this->mEndDate = $endDate;
	}

	public function getEndDate() {
		return $this->mEndDate;
	}
}