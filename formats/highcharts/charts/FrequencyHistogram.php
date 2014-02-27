<?php

namespace SRF\HighCharts;

use SMWDINumber;
use SMWNumberValue;
use SMWQueryResult;

class FrequencyHistogram implements Chart {

	/**
	 * @var $params array
	 */
	protected $params;

	/**
	 * @var $queryResult SMWQueryResult
	 */
	protected $queryResult;

	public function setParameters($params = array()) {
		$this->params = $params;
	}

	public function getParameterDefinitions() {
		$params['bins'] = array(
			'message' => 'srf-hc-paramdesc-bins',
			'default' => 0,
			'type'    => 'integer',
		);

		$params['binrange'] = array(
			'message' => 'srf-hc-paramdesc-binrange',
			'default' => 0,
			'type'    => 'integer',
		);

		$params['xlrotation'] = array(
			'message' => 'srf-hc-paramdesc-xlrotation',
			'default' => 0,
			'type'    => 'integer',
		);

		return $params;
	}

	private function getFrequencyDistributionTable(){
		$freqTable = array();
		if(!is_null($this->queryResult)){
			while(true){
				$resultArray = $this->queryResult->getNext();
				if($resultArray === false){
					break;
				}

				if($resultArray && sizeof($resultArray) === 2){
					$dv = $resultArray[1]->getNextDataValue();
					if($dv === false){
						continue;
					}
					if(!($dv instanceof SMWNumberValue)){
						throw new \InvalidArgumentException("Illegal input for this chart.");
					}
					$key = "".$dv->getNumber(); //force into a string
					if (!array_key_exists($key,$freqTable)){
						$freqTable[$key] = 0;
					}
					$freqTable[$key]++;
				}
			}
		}
		ksort($freqTable);
		return $freqTable;
	}

	public function getChartJSON() {
		$title = $this->params['title'];
		$subtitle = $this->params['subtitle'];
		$ytitle = $this->params['ytitle'];
		$xtitle = $this->params['xtitle'];
		$binRange = $this->params['binrange'];
		$numBins = $this->params['bins'];
		$xAxisLabelsRotation = $this->params['xlrotation'];

		$frequencyTable = $this->getFrequencyDistributionTable();
		$count = count($frequencyTable);

		$total = array_sum($frequencyTable);

		$series = array(
			array(
				'name' => 'Frequency',
				'type' => 'column',
				'data' => array(),
			),

			array(
				'name' => 'Exceedens',
				'type' => 'spline',
				'data' => array(),
			),

		);


		$mi = $min = 2147483647;
		$ma = $max = -1*$min;
		$mean = 0;
		$median = 0;
		foreach($frequencyTable as $value => $occurence){
			$mi = $value < $mi ? $value : $mi;
			$ma = $value > $ma ? $value : $ma;
			$min = $occurence < $min ? $occurence : $min;
			$max = $occurence > $max ? $occurence : $max;
			$mean += $occurence;
			$median += $occurence;
		}
		$mean /= $total;
		$mean /= 100;

		$median = $me = $median / 2;


		$offset = $mi;


		if($binRange > 0){
			$range = $binRange;
			$numBins = ceil($ma/$binRange);
			$offset = 0;
		}

		if($numBins === 0){
			$numBins = $count;
		}

		$mode = 0;
		$range = isset($range) ? $range : ($ma-$mi)/$numBins;
		for($x = 0; $x < $numBins; $x++){
			$bmin = ceil($offset + ($range*$x));
			$bmax = floor($offset + $range*($x+1));

			$sumOccurenceInRange = $sumOccurenceAboveCurrent = 0;
			foreach($frequencyTable as $value => $occurence){
				if ($value >= $bmin){
					$sumOccurenceAboveCurrent += $occurence;
					if($value <= $bmax){
						$sumOccurenceInRange += $occurence;
						if ($me > 0){
							$me -= $occurence;
							if ($me <= 0){
								$median = $value + $me;
							}
						}
					}
				}
			}
			$mode = max($sumOccurenceInRange/$total,$mode);

			$series[0]['data'][] = array(
				'y' => $sumOccurenceInRange/$total,
				'name' => $numBins === $count ? $bmin : $bmin . '-'. ($bmax > $ma ? $ma : $bmax),
			);

			$series[1]['data'][] = array(
				'y' => $sumOccurenceAboveCurrent/$total,
			);

		}



		$series = json_encode($series);
		$template = <<<EOT
{
    chart: {
        type: 'column',
        marginTop: 80
    },
    labels: {
        items: [{
            html: '<b>Min: </b>$min <b>Max: </b>$max <b>Mode: </b>$mode <b>Median (P50): </b>$median <b>Mean: </b>$mean  <b>Data points:</b> $total',
            style: {
                top: '-20px',
                left: '0px',
                whiteSpace: 'nowrap'
            }
        }],
    },
    title: {
        text: '$title'
    },
    subtitle: {
        text: '$subtitle'
    },
    tooltip: {
        formatter: function(){
            return Math.round(this.y * 10000)/100+'%';
        }
    },
    plotOptions:{
        column:{
	        groupPadding: 0,
			pointPadding: 0,
			borderWidth: 1,
		},
    },
    xAxis: {
        title: {
            text: '$xtitle'
        },
        categories: [],
	    labels:{
	        rotation: $xAxisLabelsRotation,
	    },
    },
    yAxis: {
        tickWidth: 1,
        lineWidth:1,
        offset: 2,
        min: 0,
        max: 1,
        title: {
            text: '$ytitle'
        },
		labels: {
		    formatter: function() {
		        return this.value*100+'%';
		    }
		}
    },
    series: $series
}
EOT;
		return $template;
	}

	public function setQueryResult( SMWQueryResult $res ) {
		$this->queryResult = $res;
	}
}