<?php
/**
 * This is an automatically generated baseline for Phan issues.
 * When Phan is invoked with --load-baseline=path/to/baseline.php,
 * The pre-existing issues listed in this file won't be emitted.
 *
 * This file can be updated by invoking Phan with --save-baseline=path/to/baseline.php
 * (can be combined with --load-baseline)
 */
return [
    // # Issue statistics:
    // PhanUndeclaredClassMethod : 55+ occurrences
    // PhanUndeclaredMethod : 55+ occurrences
    // PhanPossiblyUndeclaredVariable : 45+ occurrences
    // MediaWikiNoEmptyIfDefined : 15+ occurrences
    // PhanTypeMismatchArgument : 15+ occurrences
    // MediaWikiNoIssetIfDefined : 10+ occurrences
    // PhanTypeMismatchArgumentProbablyReal : 10+ occurrences
    // PhanUndeclaredVariableDim : 10+ occurrences
    // PhanTypeMismatchDimFetch : 9 occurrences
    // PhanTypeArraySuspiciousNullable : 8 occurrences
    // PhanTypeMismatchArgumentNullable : 8 occurrences
    // PhanTypeMismatchReturnProbablyReal : 7 occurrences
    // PhanUndeclaredTypeParameter : 6 occurrences
    // PhanTypeMismatchArgumentInternal : 5 occurrences
    // PhanNonClassMethodCall : 4 occurrences
    // PhanUndeclaredClassConstant : 4 occurrences
    // PhanUndeclaredTypeProperty : 4 occurrences
    // PhanImpossibleConditionInLoop : 3 occurrences
    // PhanRedundantCondition : 3 occurrences
    // PhanTypeInvalidLeftOperandOfNumericOp : 3 occurrences
    // PhanTypeMismatchArgumentNullableInternal : 3 occurrences
    // PhanTypeMismatchArgumentSuperType : 3 occurrences
    // PhanTypeMismatchForeach : 3 occurrences
    // PhanParamSignatureMismatch : 2 occurrences
    // PhanTypeMismatchReturn : 2 occurrences
    // PhanTypeNoAccessiblePropertiesForeach : 2 occurrences
    // PhanTypeSuspiciousStringExpression : 2 occurrences
    // PhanUndeclaredConstant : 2 occurrences
    // PhanImpossibleTypeComparison : 1 occurrence
    // PhanImpossibleValueComparisonInLoop : 1 occurrence
    // PhanParamTooMany : 1 occurrence
    // PhanTypeComparisonFromArray : 1 occurrence
    // PhanTypeInvalidLeftOperandOfAdd : 1 occurrence
    // PhanTypeInvalidModuloOperand : 1 occurrence
    // PhanTypeInvalidUnaryOperandIncOrDec : 1 occurrence
    // PhanTypeMismatchArgumentReal : 1 occurrence
    // PhanTypeMismatchDimAssignment : 1 occurrence
    // PhanTypeMismatchReturnNullable : 1 occurrence
    // PhanUndeclaredClassCatch : 1 occurrence
    // PhanUndeclaredFunction : 1 occurrence
    // PhanUndeclaredTypeReturnType : 1 occurrence
    // PhanUnusedPrivateMethodParameter : 1 occurrence
    // SecurityCheck-DoubleEscaped : 1 occurrence

    'file_suppressions' => [
        'formats/Gantt/GanttPrinter.php' => [
            'MediaWikiNoEmptyIfDefined' => ['\\SRF\\Gantt\\GanttPrinter::getResultText', '\\SRF\\Gantt\\GanttPrinter::getValidatedMapping'],
            'PhanTypeMismatchReturnProbablyReal' => ['\\SRF\\Gantt\\GanttPrinter::getResultText'],
            'PhanUndeclaredMethod' => ['\\SRF\\Gantt\\GanttPrinter::getResultText']
        ],
        'formats/Gantt/src/Gantt.php' => [
            'MediaWikiNoEmptyIfDefined' => ['\\SRF\\Gantt\\Gantt::getGanttOutput'],
            'PhanTypeMismatchArgumentInternal' => ['\\SRF\\Gantt\\Gantt::getGanttOutput']
        ],
        'formats/Gantt/src/GanttTask.php' => [
            'MediaWikiNoEmptyIfDefined' => ['\\SRF\\Gantt\\GanttTask::setTaskParam'],
            'PhanTypeMismatchForeach' => ['\\SRF\\Gantt\\GanttTask::setTaskParam']
        ],
        'formats/JitGraph/SRF_JitGraph.php' => [
            'PhanPossiblyUndeclaredVariable' => ['\\SRFJitGraph::getResultText'],
            'PhanRedundantCondition' => ['\\SRFJitGraph::getResultText']
        ],
        'formats/boilerplate/SRF_Boilerplate.php' => [
            'PhanTypeMismatchArgumentProbablyReal' => ['\\SRFBoilerplate::getFormatOutput'],
            'PhanTypeMismatchReturnProbablyReal' => ['\\SRFBoilerplate::getResultText'],
            'PhanTypeNoAccessiblePropertiesForeach' => ['\\SRFBoilerplate::getLabels', '\\SRFBoilerplate::getSubjects'],
            'PhanUndeclaredClassMethod' => ['\\SRFBoilerplate::getFormatOutput'],
            'PhanUndeclaredMethod' => ['\\SRFBoilerplate::getDataValueItem']
        ],
        'formats/carousel/Carousel.php' => [
            'MediaWikiNoEmptyIfDefined' => ['\\SRF\\Carousel::getFirstValid', '\\SRF\\Carousel::getInlineStyles'],
            'PhanRedundantCondition' => ['\\SRF\\Carousel::getImage'],
            'PhanTypeInvalidLeftOperandOfNumericOp' => ['\\SRF\\Carousel::getInlineStyles'],
            'PhanTypeMismatchDimFetch' => ['\\SRF\\Carousel::getResultText'],
            'PhanTypeMismatchForeach' => ['\\SRF\\Carousel::getResultText'],
            'SecurityCheck-DoubleEscaped' => ['\\SRF\\Carousel::getResultText']
        ],
        'formats/d3/SRF_D3Chart.php' => [
            'PhanPossiblyUndeclaredVariable' => ['\\SRFD3Chart::getFormatOutput'],
            'PhanTypeMismatchArgumentProbablyReal' => ['\\SRFD3Chart::getFormatOutput'],
            'PhanUndeclaredClassMethod' => ['\\SRFD3Chart::getFormatOutput'],
            'PhanUndeclaredVariableDim' => ['\\SRFD3Chart::getFormatOutput']
        ],
        'formats/dataframe/DataframePrinter.php' => [
            'PhanPossiblyUndeclaredVariable' => ['\\SRF\\dataframe\\DataframePrinter::getResultFileContents'],
            'PhanUndeclaredVariableDim' => ['\\SRF\\dataframe\\DataframePrinter::getResultFileContents']
        ],
        'formats/datatables/Api.php' => [
            'PhanTypeArraySuspiciousNullable' => ['\\SRF\\DataTables\\Api::execute', '\\closure'],
            'PhanTypeMismatchArgument' => ['\\SRF\\DataTables\\Api::execute'],
            'PhanTypeSuspiciousStringExpression' => ['\\SRF\\DataTables\\Api::getVersion'],
            'PhanUndeclaredConstant' => ['\\SRF\\DataTables\\Api::getVersion'],
            'PhanUndeclaredVariableDim' => ['\\SRF\\DataTables\\Api::execute']
        ],
        'formats/datatables/DataTables.php' => [
            'PhanParamSignatureMismatch' => ['\\SRF\\DataTables::buildResult', '\\SRF\\DataTables::handleNonFileResult'],
            'PhanPossiblyUndeclaredVariable' => ['\\SRF\\DataTables::getPrintouts'],
            'PhanTypeMismatchArgument' => ['\\SRF\\DataTables::getCellContent'],
            'PhanTypeMismatchArgumentInternal' => ['\\SRF\\DataTables::printContainer'],
            'PhanTypeMismatchReturn' => ['\\SRF\\DataTables::expandTemplate'],
            'PhanUndeclaredClassCatch' => ['\\closure'],
            'PhanUndeclaredClassMethod' => ['\\SRF\\DataTables::expandTemplate'],
            'PhanUndeclaredMethod' => ['\\SRF\\DataTables::getPrintouts'],
            'PhanUndeclaredTypeParameter' => ['\\SRF\\DataTables::expandTemplate'],
            'PhanUndeclaredVariableDim' => ['\\SRF\\DataTables::getPrintouts'],
            'PhanUnusedPrivateMethodParameter' => ['\\SRF\\DataTables::getResultJson']
        ],
        'formats/datatables/Hooks.php' => [
            'PhanTypeMismatchArgument' => ['\\SRF\\DataTables\\Hooks::getCount']
        ],
        'formats/datatables/QuerySegmentListProcessor.php' => [
            'PhanImpossibleValueComparisonInLoop' => ['\\SRF\\DataTables\\QuerySegmentListProcessor::disjunction'],
            'PhanUndeclaredClassMethod' => ['\\SRF\\DataTables\\QuerySegmentListProcessor::cleanUp', '\\SRF\\DataTables\\QuerySegmentListProcessor::disjunction', '\\SRF\\DataTables\\QuerySegmentListProcessor::hierarchy', '\\SRF\\DataTables\\QuerySegmentListProcessor::process', '\\SRF\\DataTables\\QuerySegmentListProcessor::table'],
            'PhanUndeclaredTypeParameter' => ['\\SRF\\DataTables\\QuerySegmentListProcessor::__construct'],
            'PhanUndeclaredTypeProperty' => ['\\SRF\\DataTables\\QuerySegmentListProcessor']
        ],
        'formats/datatables/SearchPanes.php' => [
            'MediaWikiNoEmptyIfDefined' => ['\\SRF\\DataTables\\SearchPanes::getPanesOptions'],
            'PhanPossiblyUndeclaredVariable' => ['\\SRF\\DataTables\\SearchPanes::getPanesOptions'],
            'PhanTypeMismatchArgument' => ['\\SRF\\DataTables\\SearchPanes::newQuerySegmentListProcessor'],
            'PhanTypeMismatchArgumentReal' => ['\\SRF\\DataTables\\SearchPanes::getPanesOptions'],
            'PhanTypeMismatchArgumentSuperType' => ['\\SRF\\DataTables\\SearchPanes::getSearchPanes'],
            'PhanUndeclaredMethod' => ['\\SRF\\DataTables\\SearchPanes::fetchValuesByGroup', '\\SRF\\DataTables\\SearchPanes::getPanesOptions', '\\SRF\\DataTables\\SearchPanes::newQuerySegmentListProcessor', '\\SRF\\DataTables\\SearchPanes::searchPanesMainlabel']
        ],
        'formats/dygraphs/SRF_Dygraphs.php' => [
            'PhanPossiblyUndeclaredVariable' => ['\\SRFDygraphs::getFormatOutput', '\\SRFDygraphs::getResultData'],
            'PhanTypeMismatchArgumentNullable' => ['\\SRFDygraphs::getResultData'],
            'PhanTypeMismatchArgumentProbablyReal' => ['\\SRFDygraphs::getFormatOutput', '\\SRFDygraphs::getResultData'],
            'PhanTypeMismatchReturnProbablyReal' => ['\\SRFDygraphs::getResultText'],
            'PhanUndeclaredClassMethod' => ['\\SRFDygraphs::getFormatOutput', '\\SRFDygraphs::getResultText'],
            'PhanUndeclaredMethod' => ['\\SRFDygraphs::getResultData'],
            'PhanUndeclaredVariableDim' => ['\\SRFDygraphs::getFormatOutput', '\\SRFDygraphs::getResultText']
        ],
        'formats/gallery/Gallery.php' => [
            'PhanRedundantCondition' => ['\\SRF\\Gallery::addImageToGallery'],
            'PhanUndeclaredClassMethod' => ['\\SRF\\Gallery::getResultText'],
            'PhanUndeclaredMethod' => ['\\SRF\\Gallery::addImagePages', '\\SRF\\Gallery::addImageProperties']
        ],
        'formats/graphviz/SRF_Process.php' => [
            'MediaWikiNoIssetIfDefined' => ['\\ProcessEdge::getId', '\\ProcessEdge::getUUID', '\\ProcessElement::getUUID', '\\ProcessGraph::makeNode', '\\ProcessNode::getGraphVizCode', '\\ProcessNode::getSucc', '\\SplitConditionalOrEdge::getGraphVizCode'],
            'PhanImpossibleConditionInLoop' => ['\\SRFProcess::getResultText'],
            'PhanNonClassMethodCall' => ['\\SequentialEdge::getGraphVizCode', '\\SplitConditionalOrEdge::getGraphVizCode', '\\SplitExclusiveOrEdge::getGraphVizCode', '\\SplitParallelEdge::getGraphVizCode'],
            'PhanPossiblyUndeclaredVariable' => ['\\SRFProcess::getResultText', '\\SequentialEdge::setFrom', '\\SplitConditionalOrEdge::setFrom', '\\SplitEdge::setFrom'],
            'PhanUndeclaredFunction' => ['\\SRFProcess::getResultText'],
            'PhanUndeclaredMethod' => ['\\SRFProcess::getResultText']
        ],
        'formats/incoming/SRF_Incoming.php' => [
            'PhanTypeMismatchReturnNullable' => ['\\SRFIncoming::getResultText'],
            'PhanUndeclaredMethod' => ['\\SRFIncoming::getResultData']
        ],
        'formats/jqplot/SRF_jqPlot.php' => [
            'PhanTypeMismatchReturnProbablyReal' => ['\\SRFjqPlot::getNumbersTicks']
        ],
        'formats/jqplot/SRF_jqPlotChart.php' => [
            'PhanPossiblyUndeclaredVariable' => ['\\SRFjqPlotChart::preparePieData'],
            'PhanTypeMismatchArgumentProbablyReal' => ['\\SRFjqPlotChart::getFormatOutput'],
            'PhanUndeclaredClassMethod' => ['\\SRFjqPlotChart::getFormatOutput'],
            'PhanUndeclaredVariableDim' => ['\\SRFjqPlotChart::prepareBarData', '\\SRFjqPlotChart::preparePieData']
        ],
        'formats/jqplot/SRF_jqPlotSeries.php' => [
            'PhanPossiblyUndeclaredVariable' => ['\\SRFjqPlotSeries::getFormatSettings'],
            'PhanTypeMismatchArgumentProbablyReal' => ['\\SRFjqPlotSeries::getFormatOutput'],
            'PhanTypeMismatchReturnProbablyReal' => ['\\SRFjqPlotSeries::getResultText'],
            'PhanUndeclaredClassMethod' => ['\\SRFjqPlotSeries::getFormatOutput', '\\SRFjqPlotSeries::getResultText'],
            'PhanUndeclaredMethod' => ['\\SRFjqPlotSeries::getResultData'],
            'PhanUndeclaredVariableDim' => ['\\SRFjqPlotSeries::getFormatSettings', '\\SRFjqPlotSeries::getResultText']
        ],
        'formats/media/MediaPlayer.php' => [
            'PhanTypeMismatchArgumentNullable' => ['\\SRF\\MediaPlayer::getResultData'],
            'PhanTypeMismatchArgumentProbablyReal' => ['\\SRF\\MediaPlayer::getFormatOutput'],
            'PhanUndeclaredClassMethod' => ['\\SRF\\MediaPlayer::getFormatOutput'],
            'PhanUndeclaredMethod' => ['\\SRF\\MediaPlayer::getDataValueItem']
        ],
        'formats/slideshow/SRF_SlideShow.php' => [
            'PhanUndeclaredMethod' => ['\\SRFSlideShow::getResultText']
        ],
        'formats/slideshow/SRF_SlideShowApi.php' => [
            'PhanTypeSuspiciousStringExpression' => ['\\SRFSlideShowApi::getVersion'],
            'PhanUndeclaredConstant' => ['\\SRFSlideShowApi::getVersion']
        ],
        'formats/sparkline/SRF_Sparkline.php' => [
            'PhanTypeMismatchArgumentProbablyReal' => ['\\SRFSparkline::getFormatOutput'],
            'PhanUndeclaredClassMethod' => ['\\SRFSparkline::getFormatOutput']
        ],
        'formats/spreadsheet/SpreadsheetPrinter.php' => [
            'PhanTypeMismatchArgumentProbablyReal' => ['\\SRF\\SpreadsheetPrinter::createSpreadsheetFromTemplate']
        ],
        'formats/tagcloud/TagCloud.php' => [
            'PhanPossiblyUndeclaredVariable' => ['\\SRF\\TagCloud::getTagSizes', '\\SRF\\TagCloud::getTags'],
            'PhanUndeclaredClassMethod' => ['\\SRF\\TagCloud::getTagCloud'],
            'PhanUndeclaredMethod' => ['\\SRF\\TagCloud::getTags']
        ],
        'formats/time/SRF_Time.php' => [
            'MediaWikiNoEmptyIfDefined' => ['\\SRFTime::getResultText'],
            'PhanPossiblyUndeclaredVariable' => ['\\SRFTime::getResultText']
        ],
        'formats/timeline/SRF_Timeline.php' => [
            'PhanPossiblyUndeclaredVariable' => ['\\SRFTimeline::getEventsHTML'],
            'PhanTypeMismatchArgument' => ['\\SRFTimeline::getEventsHTML'],
            'PhanTypeMismatchDimFetch' => ['\\SRFTimeline::getEventsHTML'],
            'PhanUndeclaredMethod' => ['\\SRFTimeline::handlePropertyValue']
        ],
        'formats/timeseries/SRF_Timeseries.php' => [
            'PhanPossiblyUndeclaredVariable' => ['\\SRFTimeseries::getFormatOutput'],
            'PhanTypeMismatchArgumentProbablyReal' => ['\\SRFTimeseries::getFormatOutput'],
            'PhanTypeMismatchReturnProbablyReal' => ['\\SRFTimeseries::getResultText'],
            'PhanUndeclaredClassMethod' => ['\\SRFTimeseries::getFormatOutput', '\\SRFTimeseries::getResultText'],
            'PhanUndeclaredMethod' => ['\\SRFTimeseries::getAggregatedTimeSeries'],
            'PhanUndeclaredVariableDim' => ['\\SRFTimeseries::getFormatOutput', '\\SRFTimeseries::getResultText']
        ],
        'formats/tree/TreeNode.php' => [
            'PhanUndeclaredMethod' => ['\\SRF\\Formats\\Tree\\TreeNode::addChild']
        ],
        'formats/tree/TreeNodeVisitor.php' => [
            'PhanTypeMismatchArgumentSuperType' => ['\\SRF\\Formats\\Tree\\TreeNodePrinter::visit']
        ],
        'formats/tree/TreeResultPrinter.php' => [
            'MediaWikiNoEmptyIfDefined' => ['\\SRF\\Formats\\Tree\\TreeResultPrinter::buildTreeFromNodeList']
        ],
        'formats/valuerank/SRF_ValueRank.php' => [
            'PhanUndeclaredMethod' => ['\\SRFValueRank::getResultValues']
        ],
        'formats/widget/SRF_ListWidget.php' => [
            'PhanParamTooMany' => ['\\SRFListWidget::getResultText'],
            'PhanUndeclaredClassMethod' => ['\\SRFListWidget::getResultText']
        ],
        'formats/widget/SRF_PageWidget.php' => [
            'PhanUndeclaredClassMethod' => ['\\SRFPageWidget::getResultText']
        ],
        'src/ArrayFormat/ArrayPrinter.php' => [
            'MediaWikiNoEmptyIfDefined' => ['\\SRF\\ArrayFormat\\ArrayPrinter::deliverPageProperties', '\\SRF\\ArrayFormat\\ArrayPrinter::deliverPropertiesManyValues', '\\SRF\\ArrayFormat\\ArrayPrinter::deliverSingleManyValuesData', '\\SRF\\ArrayFormat\\ArrayPrinter::getResultText'],
            'MediaWikiNoIssetIfDefined' => ['\\SRF\\ArrayFormat\\ArrayPrinter::createArray', '\\SRF\\ArrayFormat\\ArrayPrinter::initializeCfgValue'],
            'PhanPossiblyUndeclaredVariable' => ['\\SRF\\ArrayFormat\\ArrayPrinter::getCfgSepText'],
            'PhanUndeclaredClassConstant' => ['\\SRF\\ArrayFormat\\ArrayPrinter::createArray', '\\SRF\\ArrayFormat\\ArrayPrinter::getCfgSepText'],
            'PhanUndeclaredMethod' => ['\\SRF\\ArrayFormat\\ArrayPrinter::getCfgSepText']
        ],
        'src/ArrayFormat/HashPrinter.php' => [
            'MediaWikiNoIssetIfDefined' => ['\\SRF\\ArrayFormat\\HashPrinter::createArray'],
            'PhanUndeclaredClassConstant' => ['\\SRF\\ArrayFormat\\HashPrinter::createArray'],
            'PhanUndeclaredClassMethod' => ['\\SRF\\ArrayFormat\\HashPrinter::createArray']
        ],
        'src/BibTex/BibTexFileExportPrinter.php' => [
            'PhanTypeMismatchArgumentNullableInternal' => ['\\SRF\\BibTex\\BibTexFileExportPrinter::getFileName'],
            'PhanTypeMismatchReturn' => ['\\SRF\\BibTex\\BibTexFileExportPrinter::newItem'],
            'PhanUndeclaredClassMethod' => ['\\SRF\\BibTex\\BibTexFileExportPrinter::getResultText'],
            'PhanUndeclaredTypeReturnType' => ['\\SRF\\BibTex\\BibTexFileExportPrinter::newItem']
        ],
        'src/BibTex/Item.php' => [
            'MediaWikiNoEmptyIfDefined' => ['\\SRF\\BibTex\\Item::buildURI']
        ],
        'src/Calendar/Calendar.php' => [
            'MediaWikiNoEmptyIfDefined' => ['\\SRF\\Calendar\\Calendar::displayCalendar'],
            'PhanImpossibleTypeComparison' => ['\\SRF\\Calendar\\Calendar::getResultText'],
            'PhanPossiblyUndeclaredVariable' => ['\\SRF\\Calendar\\Calendar::displayCalendar', '\\SRF\\Calendar\\Calendar::getResultText'],
            'PhanTypeInvalidLeftOperandOfAdd' => ['\\SRF\\Calendar\\Calendar::displayCalendar'],
            'PhanTypeInvalidLeftOperandOfNumericOp' => ['\\SRF\\Calendar\\Calendar::displayCalendar'],
            'PhanTypeMismatchArgumentNullableInternal' => ['\\SRF\\Calendar\\Calendar::intToMonth'],
            'PhanTypeMismatchDimFetch' => ['\\SRF\\Calendar\\Calendar::intToMonth'],
            'PhanUndeclaredMethod' => ['\\SRF\\Calendar\\Calendar::formatDateStr']
        ],
        'src/Calendar/EventCalendar.php' => [
            'PhanTypeMismatchDimAssignment' => ['\\SRF\\Calendar\\EventCalendar::getResultText']
        ],
        'src/Calendar/HistoricalDate.php' => [
            'PhanTypeInvalidModuloOperand' => ['\\SRF\\Calendar\\HistoricalDate::getDayOfWeek'],
            'PhanTypeInvalidUnaryOperandIncOrDec' => ['\\SRF\\Calendar\\HistoricalDate::createFromJulian']
        ],
        'src/Graph/GraphFormatter.php' => [
            'MediaWikiNoEmptyIfDefined' => ['\\SRF\\Graph\\GraphFormatter::buildGraph'],
            'PhanUndeclaredClassMethod' => ['\\SRF\\Graph\\GraphFormatter::buildGraph'],
            'PhanUndeclaredTypeParameter' => ['\\SRF\\Graph\\GraphFormatter::buildGraph']
        ],
        'src/Graph/GraphPrinter.php' => [
            'PhanTypeMismatchArgument' => ['\\SRF\\Graph\\GraphPrinter::getResultText'],
            'PhanUndeclaredMethod' => ['\\SRF\\Graph\\GraphPrinter::collectEdgesAndFields', '\\SRF\\Graph\\GraphPrinter::determineNode', '\\SRF\\Graph\\GraphPrinter::readRowValues']
        ],
        'src/Math/Math.php' => [
            'PhanUndeclaredMethod' => ['\\SRF\\Math\\Math::addNumbersForDataItem', '\\SRF\\Math\\Math::buildResult']
        ],
        'src/Math/MathFormats.php' => [
            'PhanTypeMismatchDimFetch' => ['\\SRF\\Math\\MathFormats::medianFunction']
        ],
        'src/Outline/ListTreeBuilder.php' => [
            'PhanTypeMismatchArgument' => ['\\SRF\\Outline\\ListTreeBuilder::item'],
            'PhanUndeclaredMethod' => ['\\SRF\\Outline\\ListTreeBuilder::item'],
            'PhanUndeclaredTypeParameter' => ['\\SRF\\Outline\\ListTreeBuilder::setLinker'],
            'PhanUndeclaredTypeProperty' => ['\\SRF\\Outline\\ListTreeBuilder']
        ],
        'src/Outline/OutlineResultPrinter.php' => [
            'PhanTypeMismatchArgument' => ['\\SRF\\Outline\\OutlineResultPrinter::getResultText']
        ],
        'src/Outline/TemplateBuilder.php' => [
            'PhanUndeclaredTypeParameter' => ['\\SRF\\Outline\\TemplateBuilder::setLinker'],
            'PhanUndeclaredTypeProperty' => ['\\SRF\\Outline\\TemplateBuilder']
        ],
        'src/ResourceFormatter.php' => [
            'PhanTypeMismatchArgument' => ['\\SRF\\ResourceFormatter::encode'],
            'PhanTypeMismatchReturnProbablyReal' => ['\\SRF\\ResourceFormatter::getData'],
            'PhanUndeclaredClassMethod' => ['\\SRF\\ResourceFormatter::placeholder']
        ],
        'src/iCalendar/IcalTimezoneFormatter.php' => [
            'PhanTypeComparisonFromArray' => ['\\SRF\\iCalendar\\IcalTimezoneFormatter::calcTransitions']
        ],
        'src/iCalendar/iCalendarFileExportPrinter.php' => [
            'PhanTypeMismatchArgumentNullable' => ['\\SRF\\iCalendar\\iCalendarFileExportPrinter::getEventParams', '\\SRF\\iCalendar\\iCalendarFileExportPrinter::getIcalLink'],
            'PhanTypeMismatchArgumentSuperType' => ['\\SRF\\iCalendar\\iCalendarFileExportPrinter::filterField']
        ],
        'src/vCard/vCardFileExportPrinter.php' => [
            'PhanTypeMismatchArgument' => ['\\SRF\\vCard\\vCardFileExportPrinter::newVCard'],
            'PhanTypeMismatchArgumentNullable' => ['\\SRF\\vCard\\vCardFileExportPrinter::getVCardContent', '\\SRF\\vCard\\vCardFileExportPrinter::getVCardLink'],
            'PhanTypeMismatchArgumentNullableInternal' => ['\\SRF\\vCard\\vCardFileExportPrinter::getFileName'],
            'PhanUndeclaredMethod' => ['\\SRF\\vCard\\vCardFileExportPrinter::mapField'],
            'PhanUndeclaredVariableDim' => ['\\SRF\\vCard\\vCardFileExportPrinter::newVCard']
        ],
    ],
    // 'directory_suppressions' => ['src/directory_name' => ['PhanIssueName1', 'PhanIssueName2']] can be manually added if needed.
    // (directory_suppressions will currently be ignored by subsequent calls to --save-baseline, but may be preserved in future Phan releases)
];
