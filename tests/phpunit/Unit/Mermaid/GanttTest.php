<?php
/**
 * Created by PhpStorm.
 * User: Sebastian
 * Date: 30.01.2019
 * Time: 13:47
 */

namespace SRF\Tests\Mermaid;

use SRF\Mermaid\Gantt;


class GanttTest extends \PHPUnit_Framework_TestCase{

	public function testCanConstruct() {

		$this->assertInstanceOf(
			Gantt::class,
			new Gantt( [] )
		);
	}
}