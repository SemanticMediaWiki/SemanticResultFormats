<?php
/**
 * File holding the Gantt class
 *
 * - Add sections and tasks and
 *   manage the relations between them
 * - Sort elements based on the sortkey
 * - Creates config for gantt diagram
 *
 * @author Sebastian Schmid
 * @file
 * @ingroup SemanticResultFormats
 */

namespace SRF\Gantt;

class Gantt {

	private $mTitle;
	private $mAxisFormat;
	private $mSections = [];
	private $mTasks = [];
	private $mPriorityMapping;
	private $mStatusMapping;

	public function __construct( $headerParam ) {
		$this->setTitle( $headerParam['title'] );
		$this->setAxisFormat( $headerParam['axisformat'] );
		$this->setStatusMapping( $headerParam['statusmapping'] );
		$this->setPriorityMapping( $headerParam['prioritymapping'] );
	}

	private function setPriorityMapping( $priorityMapping ) {
		$this->mPriorityMapping = $priorityMapping;
	}

	private function getPriorityMapping() {
		return $this->mPriorityMapping;
	}

	private function setStatusMapping( $statusMapping ) {
		$this->mStatusMapping = $statusMapping;
	}

	private function getStatusMapping() {
		return $this->mStatusMapping;
	}

	private function setAxisFormat( $axisFormat ) {
		$this->mAxisFormat = $axisFormat;
	}

	private function getAxisFormat() {
		return $this->mAxisFormat;
	}

	private function setTitle( $title ) {
		$this->mTitle = $title;
	}

	private function getTitle() {
		return $this->mTitle;
	}

	private function getSections() {
		return $this->mSections;
	}

	private function getTasks() {
		return $this->mTasks;
	}

	/**
	 * Adds a new Task to array
	 *
	 * @param string $taskID
	 * @param string $taskTitle
	 * @param array $status
	 * @param array $priority
	 * @param string $startDate
	 * @param string $endDate
	 *
	 */

	public function addTask( $taskID, $taskTitle, $status, $priority, $startDate, $endDate ) {
		$task = new GanttTask();
		$task->setID( $taskID );
		$task->setTitle( $taskTitle );
		$task->setTaskParam( $status, $this->getStatusMapping(), 'status' );
		$task->setTaskParam( $priority, $this->getPriorityMapping(), 'priority' );
		$task->setStartDate( $startDate );
		$task->setEndDate( $endDate );
		$this->mTasks[$taskID] = $task;
	}


	/**
	 * Creats a new Section with related tasks
	 *
	 * @param string $sectionID
	 * @param string $sectionTitle
	 * @param string $startDate
	 * @param string $endDate
	 * @param string $taskID
	 *
	 */
	public function addSection( $sectionID, $sectionTitle, $startDate, $endDate, $taskID ) {

		$sections = $this->getSections();

		if ( array_key_exists( $sectionID, $sections ) ) {

			if ( $sections[$sectionID]->getEarliestStartDate() > $startDate ) {
				$sections[$sectionID]->setEarliestStartDate( $startDate );
			}
			if ( $sections[$sectionID]->getLatestEndDate() < $endDate ) {
				$sections[$sectionID]->setLatestEndDate( $endDate );
			}
			$sections[$sectionID]->addTask( $taskID );

		} else {
			$this->createNewSection( $sectionID, $sectionTitle, $startDate, $endDate, $taskID );
		}
	}

	private function createNewSection( $sectionID, $sectionTitle, $startDate, $endDate, $taskID ) {
		$ganttSection = new GanttSection();
		//check if the id in the object is realy needed or is it enough to have it as array key
		$ganttSection->setID( $sectionID );
		$ganttSection->setTitle( $sectionTitle );
		$ganttSection->setEarliestStartDate( $startDate );
		$ganttSection->setLatestEndDate( $endDate );
		$ganttSection->addTask( $taskID );

		$this->mSections[$sectionID] = $ganttSection;
	}

	/**
	 * Creates output for mermaidjs
	 *
	 * @return string
	 */
	public function getGanttOutput() {

		$sections = $this->getSections();
		$tasks = $this->getTasks();

		/*
		 * Bring the "section" with no title to the first position.
		 * This "section" is the one that hold tasks without any section.
		 * If we don't display it at the beginning we have to put them into a dummy section
		 */
		foreach ( $sections as $key => $section ) {
			if ( $section->getTitle() === '' ) {
				$noSection = $section;
				unset( $sections[$key] );
			}
		}
		// push now the dummy task to the first place of the array
		if ( isset( $noSection ) ) {
			array_unshift( $sections, $noSection );
		}

		$title = $this->getTitle();
		$axisFormat = $this->getAxisFormat();

		$mermaidOut = "gantt\n";
		$mermaidOut .= "dateFormat YYYY-MM-DD\n";
		$mermaidOut .= ( !empty( $title ) ) ? "title $title\n" : '';
		$mermaidOut .= "axisFormat $axisFormat\n";

		// Output section and all related Issues
		foreach ( $sections as $section ) {
			if ( $section->getTitle() !== "" ) {
				$mermaidOut .= 'section ' . $section->getTitle() . "\n";
			}

			//loop through related section tasks
			foreach ( $section->getTasks() as $sectionTask ) {

						$status = $tasks[$sectionTask]->getStatus();

						// Get Date from timestamp
						$date = date_create();
						date_timestamp_set( $date, $tasks[$sectionTask]->getStartDate() );
						$startDate = date_format( $date, 'Y-m-d' ) . ', ';
						date_timestamp_set( $date, $tasks[$sectionTask]->getEndDate() );
						$endDate = date_format( $date, 'Y-m-d' );

						//get Priority
						$priority = $tasks[$sectionTask]->getPriority();

						$mermaidOut .= $tasks[$sectionTask]->getTitle() . "\t :" . $priority . $status . $startDate . $endDate .
									   "\n";
			}
		}

		//Hashtags mark a Comment in Mermaid, so we need to replace it with <esc>35</esc> to replace it again after rendering
		$mermaidOut = str_replace( '#', '<esc>35</esc>', $mermaidOut );

		return $mermaidOut;
	}
}