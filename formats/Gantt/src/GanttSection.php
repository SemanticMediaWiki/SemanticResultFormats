<?php
/**
 * File holding the GanttSection class
 *
 * Creats Section with params
 *
 * @author Sebastian Schmid
 * @file
 * @ingroup SemanticResultFormats
 */

namespace SRF\Gantt;

class GanttSection {

	private $mTitle;
	private $mID;
	private $mEarliestStartDate;
	private $mLatestEndDate;
	private $mTasks = [];

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

	public function setEarliestStartDate( $earliestStartDate ) {
		$this->mEarliestStartDate = $earliestStartDate;
	}

	public function getEarliestStartDate() {
		return $this->mEarliestStartDate;
	}

	public function setLatestEndDate( $latestEndDate ) {
		$this->mLatestEndDate = $latestEndDate;
	}

	public function getLatestEndDate() {
		return $this->mLatestEndDate;
	}

	public function getTasks() {
		return $this->mTasks;
	}

	// If we reorder the tasks we need to reset it with the ordered tasks
	public function setTasks( $tasks ) {
		$this->mTasks = $tasks;
	}

	public function addTask( $task ) {
		$this->mTasks[] = $task;
	}
}