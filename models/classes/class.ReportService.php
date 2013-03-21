<?php
/*  
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 * 
 * Copyright (c) 2002-2008 (original work) Public Research Centre Henri Tudor & University of Luxembourg (under the project TAO & TAO2);
 *               2008-2010 (update and modification) Deutsche Institut für Internationale Pädagogische Forschung (under the project TAO-TRANSFER);\n *               2009-2012 (update and modification) Public Research Centre Henri Tudor (under the project TAO-SUSTAIN & TAO-DEV);
 * 
 */

/**
 * TAO - taoResults/models/classes/class.ReportService.php
 *
 * $Id$
 *
 *
 * Automatically generated on 20.08.2012, 15:22:19 with ArgoUML PHP module 
 * (last revised $Date: 2010-01-12 20:14:42 +0100 (Tue, 12 Jan 2010) $)
 *
 * @author Patrick Plichart, <patrick.plichart@taotesting.com>
 * @package taoResults
 * @subpackage models_classes
 */

if (0 > version_compare(PHP_VERSION, '5')) {
    die('This file was generated for PHP 5');
}


class taoResults_models_classes_ReportService
extends taoResults_models_classes_StatisticsService
{
	protected $deliveryDataSet = null;
	
	protected $contextClass;
	
	public function setDataSet($deliveryDataSet) {
	$this->deliveryDataSet = $deliveryDataSet;
	}
	
	public function setContextClass($contextClass) {
	$this->contextClass = $contextClass;
	}
	
	public function buildSimpleReport(){	

		$deliveryDataSet = $this->deliveryDataSet;
		$urlDeliverybarChart = $this->computeBarChart($this->deliveryDataSet["statisticsPerDelivery"]["splitData"], "Average and Total Scores by deciles of the population (".$this->contextClass->getLabel().")");
		
		$reportData['deliveryBarChart'] = $urlDeliverybarChart;
		
		$reportData['reportTitle'] = 'Statistical Report ('.$this->contextClass->getLabel().')';
		$reportData['average'] =  $this->deliveryDataSet["statisticsPerDelivery"]["avg"];
		$reportData['std'] =  $this->deliveryDataSet["statisticsPerDelivery"]["std"];
		$reportData['nbExecutions'] =  $this->deliveryDataSet["nbExecutions"];
		$reportData['#'] =  $this->deliveryDataSet["statisticsPerDelivery"]["#"];
		$reportData['numberVariables'] =  $this->deliveryDataSet["statisticsPerDelivery"]["numberVariables"];	
		
		foreach ($this->deliveryDataSet["statisticsPerVariable"] as $predicateUri => $struct){
		$scoreVariable = new core_kernel_classes_Resource($predicateUri);
		$scoreVariableLabel = $scoreVariable->getlabel();
		//compute every single distribution for each variable
		$urlDeliveryVariablebarChart = $this->computeBarChart($this->deliveryDataSet["statisticsPerVariable"][$predicateUri]["splitData"], "Average and Total Scores by deciles of the population (".$scoreVariableLabel.")");
		
		//build UX data structure		
		$listOfVariables[]= array("label" => $scoreVariableLabel, "url" => $urlDeliveryVariablebarChart, "infos" => array("#" => $struct["#"], "sum" => $struct["sum"], "avg" => $struct["avg"]));
		
		//build parallel arrays to maintain values for the graph computation showing all variables
		$labels[] = $scoreVariableLabel;
		$sums[] = $struct["sum"];
		$avgs[] = $struct["avg"];
		}
		
		$reportData['listOfVariables'] =  $listOfVariables;	
		//$urlDeliveryVariableRadarPlot = $this->computeRadarPlot($sums,$avgs,$labels, "Scores by variables");
		//$this->setData('compareVariablesPlot', $urlDeliveryVariableRadarPlot);
		return $reportData;
		
	}
	/**
	* TODO should be moved in a helper 
	*compute a bar chart PNG picture, stores it and return its url
	*/
	private function computebarChart($dataSet, $title){
		
		$data1y = $this->flattenQuantiles($dataSet, "avg");
		//print_r($data1y);
		$data2y = $this->flattenQuantiles($dataSet, "sum");
		
		// Create the graph. These two calls are always required
		$graph = new Graph(550,200,'auto');
		$graph->SetScale("textlin");
		$graph->SetBox(false);
		$graph->xaxis->SetTickLabels(array('0-10 %',' 0-20 %','20-30 %','30-40 %','40-50 %','50-60 %','60-70 %','70-80 %','80-90 %','90-100 %'));
		// Create the bar plots
		$b1plot = new BarPlot($data1y);
		// Create the bar plots
		$b2plot = new BarPlot($data2y);
		// Create the grouped bar plot
		$gbplot = new GroupBarPlot(array($b1plot, $b2plot));
		// ...and add it to the graPH
		$graph->Add($gbplot);
		$b1plot->SetColor("white");
		$b1plot->SetFillColor("#cc1111");
		$b2plot->SetColor("white");
		$b2plot->SetFillColor("#1111cc");
		$b1plot->SetLegend("Average Score for each decile");
		$b2plot->SetLegend("Total Score for each decile");

		$graph->title->Set($title);
		$url = $this->getUniqueMediaFileName("png");
		// Display the graph
		$graph->Stroke(ROOT_PATH.$url);
		return ROOT_URL.$url;
		}
		
	/**
	*TODO move to an helper, attempt to get a unique file name
	*/
	private function getUniqueMediaFileName($fileExtension="")
		{	$fileName = base64_encode("sid_".session_id()."c_".$this->contextClass->getUri()).'.'.$fileExtension;
			return "taoResults/views/genpics/".$fileName;
		}
	private function computeRadarPlot($sums,$avgs, $labels, $title)
		{
		// Some data
		$data1 = $sums ;
		$data2 = $avgs ;
		
		// Setup a basic radar graph
		$graph = new RadarGraph(880,400,'auto');

		// Add a title to the graph
		$graph->title->Set('Total and average Score for each variables');
		 
		// Create the first radar plot with formatting
		$plot1 = new RadarPlot($data1);
		$plot1->SetLegend('Total Score');
		$plot1->SetColor('red', 'lightred');
		 
		$plot2 = new RadarPlot($data2);
		$plot2->SetLegend('Average Score');
		$plot2->SetColor('blue', 'lightblue');
		 
		// Add the plots to the graph
		$graph->Add($plot1);
		$graph->Add($plot2);
		 $graph->SetTitles($labels);

		$graph->title->Set($title);
		$url = $this->getUniqueMediaFileName("png");
		// Display the graph
		$graph->Stroke(ROOT_PATH.$url);
		return ROOT_URL.$url;

		}

} /* end of class taoResults_models_classes_ResultsService */

?>