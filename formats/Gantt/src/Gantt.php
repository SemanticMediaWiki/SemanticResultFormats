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
	private $mSortKey;
	private $mPriorityMapping;
	private $mStatusMapping;

	public function __construct( $headerParam ) {
		$this->setTitle( $headerParam['title'] );
		$this->setAxisFormat( $headerParam['axisformat'] );
		$this->setSortKey( $headerParam['sortkey'] );
		$this->setStatusMapping( $headerParam['statusmapping'] );
		$this->setPriorityMapping( $headerParam['prioritymapping'] );
	}

	private function setSortKey( $sortkey ) {
		$this->mSortKey = strtolower( $sortkey );
	}

	private function getSortKey() {
		return $this->mSortKey;
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
		$task->setTaskParam( $status, $this->getStatusMapping(), "status" );
		$task->setTaskParam( $priority, $this->getPriorityMapping(), "priority" );
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
		if ( count( $sections ) != 0 ) {

			if ( array_key_exists( $sectionID, $sections ) ) {

				foreach ( $sections as $sectionObj ) {
					if ( $sectionObj->getID() == $sectionID ) {
						if ( $sectionObj->getEarliestStartDate() > $startDate ) {
							$sectionObj->setEarliestStartDate( $startDate );
						}
						if ( $sectionObj->getLatestEndDate() < $endDate ) {
							$sectionObj->setLatestEndDate( $endDate );
						}
						$sectionObj->addTask( $taskID );
					}
				}
			} else {
				$this->createNewSection( $sectionID, $sectionTitle, $startDate, $endDate, $taskID );
			}

		} else {
			// initialize GanttSection Array
			$this->createNewSection( $sectionID, $sectionTitle, $startDate, $endDate, $taskID );
		}
	}

	/**
	 * Sort Tasks based on the sortkey
	 *
	 * @param GanttTask $a
	 * @param GanttTask $b
	 *
	 * @return integer
	 */
	public function sortTasks( $a, $b ) {

		$sortKey = $this->getSortKey();

		// sort based on start/end date or title
		if ( $sortKey == 'title' ) {
			// sort based on title
			return strcmp( $a->getTitle(), $b->getTitle() );
		} else {
			if ( strpos( $sortKey, 'date' ) !== false ) {
				if ( strpos( $sortKey, 'start' ) !== false ) {
					// sort based on startDate
					return strcmp( $a->getStartDate(), $b->getStartDate() );
				} else {
					// sort based on endDate
					return strcmp( $b->getEndDate(), $a->getEndDate() );
				}
			}
		}
	}


	/**
	 * Sort Sections based on the sortkey
	 *
	 * @param GanttSection $a
	 * @param GanttSection $b
	 *
	 */
	public function sortSections( $a, $b ) {

		$sortKey = $this->getSortKey();

		// sort based on start/end date or title
		if ( $sortKey == 'title' ) {
			// sort based on title
			return strcmp( $a->getTitle(), $b->getTitle() );
		} else {
			if ( strpos( $sortKey, 'date' ) !== false ) {
				if ( strpos( $sortKey, 'start' ) !== false ) {
					// sort based on startDate
					return strcmp( $a->getEarliestStartDate(), $b->getEarliestStartDate() );
				} else {
					// sort based on endDate
					return strcmp( $b->getLatestEndDate(), $a->getLatestEndDate() );
				}
			}
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
		$sortKey = $this->getSortKey();

		if ( !empty( $sortKey ) ) {
			// Order Sections
			usort( $sections, [ $this, "sortSections" ] );
			usort( $tasks, [ $this, "sortTasks" ] );

			// reorder TaskArray of current section
			foreach ( $sections as $section ) {
				$orderedTasks = [];
				foreach ( $tasks as $task ) {

					// check if $section->getTasks() holds ID of current task
					if ( in_array( $task->getID(), $section->getTasks() ) ) {
						$sectionTasks = $section->getTasks();
						//loop through tasks of current section
						foreach ( $sectionTasks as $taskKey => $sectionTask ) {
							if ( $task->getID() == $sectionTask ) {
								array_push( $orderedTasks, $sectionTask );
							}
						}
					}
				}
				$section->setTasks( $orderedTasks );
			}
		}

		/*
		 * Bring the "section" with no title to the first position.
		 * This "section" is the one that hold tasks without any section.
		 * If we don't display it at the beginning we have to put them into a dummy section
		 */
		foreach ( $sections as $key => $section ) {
			if ( $section->getTitle() == "" ) {
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
		$mermaidOut .= ( !empty( $title ) ) ? "title $title\n" : "";
		$mermaidOut .= "axisFormat $axisFormat\n";

		// Output section and all related Issues
		foreach ( $sections as $section ) {
			if ( $section->getTitle() != "" ) {
				$mermaidOut .= "section " . $section->getTitle() . "\n";
			}

			//loop through related section tasks
			foreach ( $section->getTasks() as $sectionTaskValue ) {
				foreach ( $tasks as $taskObj ) {
					if ( $taskObj->getID() === $sectionTaskValue ) {

						$status = $taskObj->getStatus();

						// Get Date from timestamp
						$date = date_create();
						date_timestamp_set( $date, $taskObj->getStartDate() );
						$startDate = date_format( $date, 'Y-m-d' ) . ", ";
						date_timestamp_set( $date, $taskObj->getEndDate() );
						$endDate = date_format( $date, 'Y-m-d' );

						//get Priority
						$priority = $taskObj->getPriority();

						$mermaidOut .= $taskObj->getTitle() . "\t :" . $priority . $status . $startDate . $endDate .
									   "\n";
					}
				}
			}
		}

		//Hashtags mark a Comment in Mermaid, so we need to replace it with <esc>35</esc> to replace it again after rendering
		$mermaidOut = str_replace( "#", "<esc>35</esc>", $mermaidOut );

		return $mermaidOut;
	}
}