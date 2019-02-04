<?php
/**
 * Created by PhpStorm.
 * User: Sebastian
 * Date: 30.01.2019
 * Time: 13:47
 */

namespace SRF\Tests\Mermaid;

use SRF\Mermaid\GanttSection;


class GanttSectionTest extends \PHPUnit_Framework_TestCase{

	public function testCanConstruct() {

		$this->assertInstanceOf(
			GanttSection::class,
			new GanttSection( [] )
		);
	}

	public function testPropertyAccess() {

		$instance = new GanttSection();

		$instance->setTitle('GanttSection');
		$this->assertEquals( $instance->getTitle(), 'GanttSection' );

		$instance->setID('mermaid-no-section#21780240');
		$this->assertEquals( $instance->getID(), 'mermaid-no-section#21780240' );

		$instance->setTasks( array( 'task1', 'task2' ));
		$this->assertEquals( $instance->getTasks(), array( 'task1', 'task2' ) );

		$instance->addTask( 'task3' );
		$this->assertEquals( $instance->getTasks(), array( 'task1', 'task2', 'task3' ) );

		$instance->setEarliestStartDate( '2019-02-01' );
		$this->assertEquals( $instance->getEarliestStartDate(), '2019-02-01' );

		$instance->setLatestEndDate( '2019-02-28' );
		$this->assertEquals( $instance->getLatestEndDate(), '2019-02-28' );
	}


}