<?php
namespace SRF;
use SMWOutputs;
use SMWQueryResult;
use SMWResultPrinter;

/**
 * Class SRFHighCharts
 * @package SRF
 * @author Kim Eik
 */
class HighCharts extends SMWResultPrinter {

	/**
	 * @var \SRF\HighCharts\Chart[]
	 */
	protected $graphs = array(
		'frequency histogram' => 'SRF\HighCharts\FrequencyHistogram',
	);

	function __construct() {
		foreach ($this->graphs as &$graph) {
			$graph = new $graph;
		}
	}

	/**
	 * Return serialised results in specified format.
	 * Implemented by subclasses.
	 */
	protected function getResultText( SMWQueryResult $res, $outputmode ) {
		SMWOutputs::requireResource( 'ext.srf.highcharts');
		$id = uniqid ('hc');
		$graph = $this->graphs['frequency histogram'];
		$graph->setQueryResult($res);
		$graph->setParameters($this->params);
		$js = sprintf(
			'if (window.srfhighcharts === undefined) {window.srfhighcharts = {}};window.srfhighcharts[\'%s\'] = %s;',
		$id,$graph->getChartJSON());

		$html = \Html::rawElement( 'div', array(
				'id' => $id,
				'style' => "min-width:{$this->params['min-width']}px; height:{$this->params['height']}px; margin 0 auto"
			)
		);
		$this->getOutput()->addInlineScript($js);
		return $html;
	}

	/**
	 * @see SMWResultPrinter::getParamDefinitions
	 *
	 * @since 1.8
	 *
	 * @param $definitions array of IParamDefinition
	 *
	 * @return array of IParamDefinition|array
	 */
	public function getParamDefinitions( array $definitions ) {
		$params = parent::getParamDefinitions( $definitions );

		$params['graph-format'] = array(
			'message' => 'srf-hc-paramdesc-graph',
			'default' => '',
			'values' => array('frequency histogram'),
		);

		$params['yscale'] = array(
			'message' => 'srf-hc-paramdesc-yscale',
			'default' => 'normal',
			'values' => array('normal','log'),
		);

		$params['xscale'] = array(
			'message' => 'srf-hc-paramdesc-xscale',
			'default' => 'normal',
			'values' => array('normal','log'),
		);

		$params['ytitle'] = array(
			'message' => 'srf-hc-paramdesc-ytitle',
			'default' => 'Y-axis',
		);

		$params['xtitle'] = array(
			'message' => 'srf-hc-paramdesc-xtitle',
			'default' => 'X-axis',
		);

		$params['title'] = array(
			'message' => 'srf-hc-paramdesc-title',
			'default' => 'Title',
		);

		$params['subtitle'] = array(
			'message' => 'srf-hc-paramdesc-subtitle',
			'default' => 'Subtitle',
		);

		$params['min-width'] = array(
			'message' => 'srf-hc-paramdesc-minwidth',
			'default' => '0',
		);

		$params['height'] = array(
			'message' => 'srf-hc-paramdesc-height',
			'default' => '400',
		);

		foreach ($this->graphs as $graph) {
			$params = array_merge($params,$graph->getParameterDefinitions());
		}

		return $params;
	}


}
