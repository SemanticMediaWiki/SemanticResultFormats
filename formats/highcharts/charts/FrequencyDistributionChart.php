<?php

namespace SRF\HighCharts;

use SMWDINumber;
use SMWNumberValue;
use SMWQueryResult;

class FrequencyDistributionChart implements ChartTemplate {

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
		$params['numcols'] = array(
			'message' => 'srf-hc-paramdesc-numcols',
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
		$numCols = $this->params['numcols'];

		$total = $this->queryResult->getCount();
		$frequencyTable = $this->getFrequencyDistributionTable();
		$categories = array();

		$series = array(
			'type' => 'column',
			'data' => array(),
		);

		foreach($frequencyTable as $value => $occurence){
			$series['data'][] = array(
				'y' => $occurence/$total,
				'name' => $value,
			);
		}

		if($numCols > 0){
			$lastKey = 0;
			$numGrouped = 0;
			$maxKey = sizeof($series['data'])-1;
			$mod = ceil($maxKey/$numCols);

			foreach($series['data'] as $key => $data){
				$newKey = intval($key/$mod)*$mod;
				if($key%$mod === 0 && $maxKey !== $key){
					$numGrouped = 0;
					continue;
				}

				$series['data'][$newKey]['y'] += $data['y'];
				$numGrouped++;
				if(($key+1)%$mod === 0 || $maxKey === $key){
					$series['data'][$newKey]['name'] .= ' - '.$data['name']." ($numGrouped)";
					$categories[] = $series['data'][$newKey]['name'];
				}
				unset($series['data'][$key]);

				$lastKey = $newKey;
			}
			$series['data'] = $array = array_values($series['data']);

		}

		$categories = json_encode($categories);
		$series = json_encode($series);
		$template = <<<EOT
{
    chart: {
        type: 'column'
    },
    title: {
        text: '$title'
    },
    subtitle: {
        text: '$subtitle'
    },
    tooltip: {
        enabled: false
    },
    plotOptions:{
        column:{
	        groupPadding: 0,
			pointPadding: 0,
			borderWidth: 1,
		},
        series: {
            dataLabels: {
                enabled: true,
                formatter: function(){
                    return Math.round(this.y * 10000)/100+'%';
                }
            }
        }
    },
    xAxis: {
        title: {
            text: '$xtitle'
        },
        categories: $categories,
    },
    yAxis: {
        min: 0,
        title: {
            text: '$ytitle'
        },
		labels: {
		    formatter: function() {
		        return this.value*100+'%';
		    }
		}
    },
    series: [$series]
}
EOT;
		return $template;
	}

	public function setQueryResult( SMWQueryResult $res ) {
		$this->queryResult = $res;
	}
}