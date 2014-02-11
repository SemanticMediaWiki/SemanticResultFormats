<?php


namespace SRF\HighCharts;

use SMWQueryResult;

interface Chart {
	public function setQueryResult(SMWQueryResult $res);
	public function setParameters($params = array());
	public function getParameterDefinitions();
	public function getChartJSON();
} 