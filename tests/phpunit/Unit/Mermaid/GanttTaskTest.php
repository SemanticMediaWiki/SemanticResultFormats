<?php
/**
 * Created by PhpStorm.
 * User: Sebastian
 * Date: 30.01.2019
 * Time: 13:47
 */

namespace SRF\Tests\Mermaid;

use SRF\Mermaid\GanttTask;


class GanttTaskTest extends \PHPUnit_Framework_TestCase {

	public function testCanConstruct() {

		$this->assertInstanceOf( GanttTask::class, new GanttTask( [] ) );
	}

	public function testPropertyAccess() {

		$instance = new GanttTask();

		$instance->setTitle( 'GanttTask' );
		$this->assertEquals( $instance->getTitle(), 'GanttTask' );

		$instance->setID( '298043571780240' );
		$this->assertEquals( $instance->getID(), '298043571780240' );

	}
}