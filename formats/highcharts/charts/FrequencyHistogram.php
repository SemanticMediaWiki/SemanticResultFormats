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

	/**
	 * @param $percent float A value greater than 0, less than 1.
	 * @param $values Array ordered array from lowest to highest
	 * @throws \InvalidArgumentException
	 * @return float percentile
	 */
	private function getPercentile($percent,$values){
		if($percent > 1 || $percent < 0){
			throw new \InvalidArgumentException();
		}

		$index = $percent * count($values);

		if($index % 1 === 0){
			//whole number
			return $values[$index-1];
		}else{
			return ($values[$index-1] + $values[$index])/2;
		}

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


		$min = 2147483647;
		$max = -1*$min;
		$mean = 0;
		foreach($frequencyTable as $value => $occurence){
			$min = $value < $min ? $value : $min;
			$max = $value > $max ? $value : $max;
			$mean += $value;
		}

		$mean /= $total;


		$offset = $min;
		if($binRange > 0){
			$range = $binRange;
			$numBins = ceil($max/$binRange);
			$offset = 0;
		}

		if($numBins === 0){
			$numBins = $count;
		}

		$mode = 0;
		$range = isset($range) ? $range : ($max-$min)/$numBins;
		for($x = 0; $x < $numBins; $x++){
			$bmin = ceil($offset + ($range*$x));
			$bmax = floor($offset + $range*($x+1));

			$sumOccurenceInRange = $sumOccurenceAboveCurrent = 0;
			foreach($frequencyTable as $value => $occurence){
				if ($value >= $bmin){
					$sumOccurenceAboveCurrent += $occurence;
					if($value <= $bmax){
						$sumOccurenceInRange += $occurence;
					}
				}
			}
			$mode = max($sumOccurenceInRange/$total,$mode);

			$series[0]['data'][] = array(
				'y' => $sumOccurenceInRange/$total,
				'name' => $numBins === $count ? $bmin : $bmin . '-'. ($bmax > $max ? $max : $bmax),
			);

			$series[1]['data'][] = array(
				'y' => $sumOccurenceAboveCurrent/$total,
			);

		}
		$series = json_encode($series);


		$mode = round($mode*100,2).'%';
		$mean = round($mean,2);

		$sortedFrequencyTableValues = array_keys($frequencyTable);
		$p10 = $this->getPercentile(0.1,$sortedFrequencyTableValues);
		$p50 = $this->getPercentile(0.5,$sortedFrequencyTableValues);
		$p90 = $this->getPercentile(0.9,$sortedFrequencyTableValues);
		$template = <<<EOT
{
    chart: {
        type: 'column',
        marginTop: 80
    },
    labels: {
        items: [{
            html: '<b>Min: </b>$min <b>Max: </b>$max <b>Mode: </b>$mode <b>P10: </b>$p10 <b>Median/P50: </b>$p50 <b>P90: </b>$p90 <b>Mean: </b>$mean  <b>Data points:</b> $total',
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