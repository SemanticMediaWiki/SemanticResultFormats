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
    // PhanUndeclaredClassMethod : 480+ occurrences
    // PhanUndeclaredProperty : 450+ occurrences
    // PhanUndeclaredTypeParameter : 130+ occurrences
    // PhanUndeclaredClassConstant : 95+ occurrences
    // PhanUndeclaredClassProperty : 90+ occurrences
    // PhanUndeclaredConstant : 85+ occurrences
    // PhanUndeclaredMethod : 60+ occurrences
    // PhanUndeclaredClass : 55+ occurrences
    // PhanPossiblyUndeclaredVariable : 45+ occurrences
    // PhanUndeclaredClassInstanceof : 40+ occurrences
    // PhanUndeclaredExtendedClass : 35+ occurrences
    // MediaWikiNoEmptyIfDefined : 15+ occurrences
    // PhanTypeMismatchArgument : 15+ occurrences
    // MediaWikiNoIssetIfDefined : 10+ occurrences
    // PhanTypeMismatchDimFetch : 10+ occurrences
    // PhanUndeclaredTypeProperty : 10+ occurrences
    // PhanUndeclaredVariableDim : 10+ occurrences
    // PhanTypeMismatchArgumentProbablyReal : 9 occurrences
    // PhanTypeArraySuspiciousNullable : 8 occurrences
    // PhanRedundantCondition : 7 occurrences
    // PhanUndeclaredFunction : 7 occurrences
    // PhanUndeclaredTypeReturnType : 6 occurrences
    // PhanTypeMismatchArgumentInternal : 5 occurrences
    // PhanTypeMismatchReturnProbablyReal : 5 occurrences
    // PhanNonClassMethodCall : 4 occurrences
    // PhanTypeMismatchForeach : 4 occurrences
    // PhanTypeMismatchReturn : 4 occurrences
    // PhanTypeSuspiciousStringExpression : 4 occurrences
    // PhanImpossibleConditionInLoop : 3 occurrences
    // PhanTypeInvalidLeftOperandOfNumericOp : 3 occurrences
    // PhanTypeMismatchArgumentNullable : 2 occurrences
    // PhanUndeclaredClassStaticProperty : 2 occurrences
    // SecurityCheck-DoubleEscaped : 2 occurrences
    // PhanImpossibleTypeComparison : 1 occurrence
    // PhanParamSignatureMismatch : 1 occurrence
    // PhanParamTooMany : 1 occurrence
    // PhanSuspiciousWeakTypeComparison : 1 occurrence
    // PhanTypeComparisonFromArray : 1 occurrence
    // PhanTypeInvalidLeftOperandOfAdd : 1 occurrence
    // PhanTypeInvalidModuloOperand : 1 occurrence
    // PhanTypeInvalidUnaryOperandIncOrDec : 1 occurrence
    // PhanTypeMismatchArgumentNullableInternal : 1 occurrence
    // PhanTypeMismatchDimAssignment : 1 occurrence
    // PhanUndeclaredClassCatch : 1 occurrence
    // PhanUndeclaredClassReference : 1 occurrence
    // PhanUndeclaredInterface : 1 occurrence
    // PhanUndeclaredStaticMethod : 1 occurrence
    // PhanUndeclaredStaticProperty : 1 occurrence
    // PhanUnusedPrivateMethodParameter : 1 occurrence

    'file_suppressions' => [
        'formats/Gantt/GanttPrinter.php' => [
            'MediaWikiNoEmptyIfDefined' => ['\\SRF\\Gantt\\GanttPrinter::getResultText', '\\SRF\\Gantt\\GanttPrinter::getValidatedMapping'],
            'PhanUndeclaredClass' => ['\\SRF\\Gantt\\GanttPrinter::getParamDefinitions'],
            'PhanUndeclaredClassInstanceof' => ['\\SRF\\Gantt\\GanttPrinter::getResultText'],
            'PhanUndeclaredClassMethod' => ['\\SRF\\Gantt\\GanttPrinter::getResultText'],
            'PhanUndeclaredExtendedClass' => ['formats/Gantt/GanttPrinter.php'],
            'PhanUndeclaredProperty' => ['\\SRF\\Gantt\\GanttPrinter::getGantt', '\\SRF\\Gantt\\GanttPrinter::getResultText', '\\SRF\\Gantt\\GanttPrinter::getValidatedTheme', '\\SRF\\Gantt\\GanttPrinter::handleParameters'],
            'PhanUndeclaredTypeParameter' => ['\\SRF\\Gantt\\GanttPrinter::getResultText']
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
            'PhanRedundantCondition' => ['\\SRFJitGraph::getResultText'],
            'PhanUndeclaredClass' => ['\\SRFJitGraph::getParamDefinitions', '\\SRFJitGraph::handleParameters'],
            'PhanUndeclaredClassMethod' => ['\\SRFJitGraph::getResultText', '\\SRFJitGraph::includeJS'],
            'PhanUndeclaredExtendedClass' => ['formats/JitGraph/SRF_JitGraph.php'],
            'PhanUndeclaredProperty' => ['\\SRFJitGraph::getName', '\\SRFJitGraph::getResultText'],
            'PhanUndeclaredTypeParameter' => ['\\SRFJitGraph::getResultText']
        ],
        'formats/Prolog/PrologPrinter.php' => [
            'PhanUndeclaredClass' => ['\\SRF\\Prolog\\PrologPrinter::getParamDefinitions', '\\SRF\\Prolog\\PrologPrinter::outputAsFile'],
            'PhanUndeclaredClassInstanceof' => ['\\SRF\\Prolog\\PrologPrinter::getResultFileContents'],
            'PhanUndeclaredClassMethod' => ['\\SRF\\Prolog\\PrologPrinter::getResultFileContents'],
            'PhanUndeclaredConstant' => ['\\SRF\\Prolog\\PrologPrinter::getResultFileContents', '\\SRF\\Prolog\\PrologPrinter::getResultText'],
            'PhanUndeclaredExtendedClass' => ['formats/Prolog/PrologPrinter.php'],
            'PhanUndeclaredMethod' => ['\\SRF\\Prolog\\PrologPrinter::getName', '\\SRF\\Prolog\\PrologPrinter::getResultText'],
            'PhanUndeclaredProperty' => ['\\SRF\\Prolog\\PrologPrinter::getFileName', '\\SRF\\Prolog\\PrologPrinter::getResultFileContents', '\\SRF\\Prolog\\PrologPrinter::getResultText'],
            'PhanUndeclaredTypeParameter' => ['\\SRF\\Prolog\\PrologPrinter::getFileName', '\\SRF\\Prolog\\PrologPrinter::getMimeType', '\\SRF\\Prolog\\PrologPrinter::getResultFileContents', '\\SRF\\Prolog\\PrologPrinter::getResultText', '\\SRF\\Prolog\\PrologPrinter::outputAsFile']
        ],
        'formats/boilerplate/SRF_Boilerplate.php' => [
            'PhanTypeMismatchArgumentProbablyReal' => ['\\SRFBoilerplate::getFormatOutput'],
            'PhanUndeclaredClass' => ['\\SRFBoilerplate::getParamDefinitions'],
            'PhanUndeclaredClassConstant' => ['\\SRFBoilerplate::getDataValueItem'],
            'PhanUndeclaredClassMethod' => ['\\SRFBoilerplate::getDataValueItem', '\\SRFBoilerplate::getFormatOutput', '\\SRFBoilerplate::getResultData', '\\SRFBoilerplate::getResultText'],
            'PhanUndeclaredExtendedClass' => ['formats/boilerplate/SRF_Boilerplate.php'],
            'PhanUndeclaredProperty' => ['\\SRFBoilerplate::getDataValueItem', '\\SRFBoilerplate::getFormatOutput'],
            'PhanUndeclaredTypeParameter' => ['\\SRFBoilerplate::getDataValueItem', '\\SRFBoilerplate::getLabels', '\\SRFBoilerplate::getResultData', '\\SRFBoilerplate::getResultText', '\\SRFBoilerplate::getSubjects']
        ],
        'formats/carousel/Carousel.php' => [
            'MediaWikiNoEmptyIfDefined' => ['\\SRF\\Carousel::getFirstValid', '\\SRF\\Carousel::getInlineStyles'],
            'PhanRedundantCondition' => ['\\SRF\\Carousel::getImage'],
            'PhanTypeInvalidLeftOperandOfNumericOp' => ['\\SRF\\Carousel::getInlineStyles'],
            'PhanTypeMismatchDimFetch' => ['\\SRF\\Carousel::getResultText'],
            'PhanTypeMismatchForeach' => ['\\SRF\\Carousel::getResultText'],
            'PhanUndeclaredClass' => ['\\SRF\\Carousel::getParamDefinitions'],
            'PhanUndeclaredExtendedClass' => ['formats/carousel/Carousel.php'],
            'PhanUndeclaredMethod' => ['\\SRF\\Carousel::getName'],
            'PhanUndeclaredProperty' => ['\\SRF\\Carousel::getInlineStyles', '\\SRF\\Carousel::getResultText'],
            'PhanUndeclaredTypeParameter' => ['\\SRF\\Carousel::getResultText'],
            'SecurityCheck-DoubleEscaped' => ['\\SRF\\Carousel::getResultText']
        ],
        'formats/d3/SRF_D3Chart.php' => [
            'PhanPossiblyUndeclaredVariable' => ['\\SRFD3Chart::getFormatOutput'],
            'PhanTypeMismatchArgumentProbablyReal' => ['\\SRFD3Chart::getFormatOutput'],
            'PhanUndeclaredClass' => ['\\SRFD3Chart::getParamDefinitions'],
            'PhanUndeclaredClassMethod' => ['\\SRFD3Chart::getFormatOutput'],
            'PhanUndeclaredExtendedClass' => ['formats/d3/SRF_D3Chart.php'],
            'PhanUndeclaredProperty' => ['\\SRFD3Chart::getFormatOutput'],
            'PhanUndeclaredVariableDim' => ['\\SRFD3Chart::getFormatOutput']
        ],
        'formats/dataframe/DataframePrinter.php' => [
            'PhanPossiblyUndeclaredVariable' => ['\\SRF\\dataframe\\DataframePrinter::getResultFileContents'],
            'PhanUndeclaredClass' => ['\\SRF\\dataframe\\DataframePrinter::getParamDefinitions', '\\SRF\\dataframe\\DataframePrinter::outputAsFile'],
            'PhanUndeclaredClassInstanceof' => ['\\SRF\\dataframe\\DataframePrinter::getResultFileContents'],
            'PhanUndeclaredClassMethod' => ['\\SRF\\dataframe\\DataframePrinter::getResultFileContents'],
            'PhanUndeclaredConstant' => ['\\SRF\\dataframe\\DataframePrinter::getResultFileContents', '\\SRF\\dataframe\\DataframePrinter::getResultText'],
            'PhanUndeclaredExtendedClass' => ['formats/dataframe/DataframePrinter.php'],
            'PhanUndeclaredMethod' => ['\\SRF\\dataframe\\DataframePrinter::getName', '\\SRF\\dataframe\\DataframePrinter::getResultText'],
            'PhanUndeclaredProperty' => ['\\SRF\\dataframe\\DataframePrinter::getFileName', '\\SRF\\dataframe\\DataframePrinter::getResultFileContents', '\\SRF\\dataframe\\DataframePrinter::getResultText'],
            'PhanUndeclaredTypeParameter' => ['\\SRF\\dataframe\\DataframePrinter::getFileName', '\\SRF\\dataframe\\DataframePrinter::getMimeType', '\\SRF\\dataframe\\DataframePrinter::getResultFileContents', '\\SRF\\dataframe\\DataframePrinter::getResultText', '\\SRF\\dataframe\\DataframePrinter::outputAsFile'],
            'PhanUndeclaredVariableDim' => ['\\SRF\\dataframe\\DataframePrinter::getResultFileContents']
        ],
        'formats/datatables/Api.php' => [
            'PhanParamTooMany' => ['\\SRF\\DataTables\\Api::execute'],
            'PhanTypeArraySuspiciousNullable' => ['\\SRF\\DataTables\\Api::execute', '\\closure'],
            'PhanTypeSuspiciousStringExpression' => ['\\SRF\\DataTables\\Api::getVersion'],
            'PhanUndeclaredClassConstant' => ['\\SRF\\DataTables\\Api::execute'],
            'PhanUndeclaredClassMethod' => ['\\SRF\\DataTables\\Api::execute'],
            'PhanUndeclaredConstant' => ['\\SRF\\DataTables\\Api::execute', '\\SRF\\DataTables\\Api::getVersion'],
            'PhanUndeclaredMethod' => ['\\SRF\\DataTables\\Api::execute'],
            'PhanUndeclaredVariableDim' => ['\\SRF\\DataTables\\Api::execute']
        ],
        'formats/datatables/DataTables.php' => [
            'PhanPossiblyUndeclaredVariable' => ['\\SRF\\DataTables::getPrintouts'],
            'PhanTypeMismatchArgument' => ['\\SRF\\DataTables::getCellContent'],
            'PhanTypeMismatchArgumentInternal' => ['\\SRF\\DataTables::printContainer'],
            'PhanTypeMismatchReturn' => ['\\SRF\\DataTables::expandTemplate'],
            'PhanUndeclaredClass' => ['\\SRF\\DataTables::getParamDefinitions'],
            'PhanUndeclaredClassCatch' => ['\\closure'],
            'PhanUndeclaredClassConstant' => ['\\SRF\\DataTables::getCellContent', '\\SRF\\DataTables::getResultJson', '\\SRF\\DataTables::getResultText', '\\SRF\\DataTables::handleNonFileResult'],
            'PhanUndeclaredClassInstanceof' => ['\\SRF\\DataTables::getCellContent', '\\SRF\\DataTables::getPrintouts'],
            'PhanUndeclaredClassMethod' => ['\\SRF\\DataTables::expandTemplate', '\\SRF\\DataTables::getCellContent', '\\SRF\\DataTables::getPrintouts', '\\SRF\\DataTables::getResultJson', '\\SRF\\DataTables::getResultText', '\\SRF\\DataTables::handleNonFileResult', '\\SRF\\DataTables::initializePrintoutParametersAndParser', '\\SRF\\DataTables::printContainer'],
            'PhanUndeclaredConstant' => ['\\SRF\\DataTables::buildResult', '\\SRF\\DataTables::getCellContent', '\\SRF\\DataTables::getResultJson', '\\SRF\\DataTables::handleNonFileResult', '\\SRF\\DataTables::printContainer'],
            'PhanUndeclaredExtendedClass' => ['formats/datatables/DataTables.php'],
            'PhanUndeclaredMethod' => ['\\SRF\\DataTables::buildResult', '\\SRF\\DataTables::getCellContent', '\\SRF\\DataTables::getName', '\\SRF\\DataTables::handleNonFileResult'],
            'PhanUndeclaredProperty' => ['\\SRF\\DataTables::buildResult', '\\SRF\\DataTables::getCellContent', '\\SRF\\DataTables::getResultText', '\\SRF\\DataTables::handleNonFileResult', '\\SRF\\DataTables::printContainer'],
            'PhanUndeclaredStaticProperty' => ['\\SRF\\DataTables::handleNonFileResult'],
            'PhanUndeclaredTypeParameter' => ['\\SRF\\DataTables::buildResult', '\\SRF\\DataTables::expandTemplate', '\\SRF\\DataTables::getResultJson', '\\SRF\\DataTables::getResultText', '\\SRF\\DataTables::handleNonFileResult', '\\SRF\\DataTables::initializePrintoutParametersAndParser'],
            'PhanUndeclaredTypeProperty' => ['\\SRF\\DataTables'],
            'PhanUndeclaredVariableDim' => ['\\SRF\\DataTables::getPrintouts'],
            'PhanUnusedPrivateMethodParameter' => ['\\SRF\\DataTables::getResultJson']
        ],
        'formats/datatables/Hooks.php' => [
            'PhanUndeclaredClassConstant' => ['\\SRF\\DataTables\\Hooks::getCount', '\\SRF\\DataTables\\Hooks::onSMWStoreBeforeQueryResultLookupComplete'],
            'PhanUndeclaredClassMethod' => ['\\SRF\\DataTables\\Hooks::getCount', '\\SRF\\DataTables\\Hooks::onSMWStoreBeforeQueryResultLookupComplete']
        ],
        'formats/datatables/QuerySegmentListProcessor.php' => [
            'PhanTypeMismatchDimFetch' => ['\\SRF\\DataTables\\QuerySegmentListProcessor::conjunction'],
            'PhanUndeclaredClassConstant' => ['\\SRF\\DataTables\\QuerySegmentListProcessor::disjunction', '\\SRF\\DataTables\\QuerySegmentListProcessor::hierarchy', '\\SRF\\DataTables\\QuerySegmentListProcessor::segment'],
            'PhanUndeclaredClassMethod' => ['\\SRF\\DataTables\\QuerySegmentListProcessor::cleanUp', '\\SRF\\DataTables\\QuerySegmentListProcessor::disjunction', '\\SRF\\DataTables\\QuerySegmentListProcessor::hierarchy', '\\SRF\\DataTables\\QuerySegmentListProcessor::process', '\\SRF\\DataTables\\QuerySegmentListProcessor::table'],
            'PhanUndeclaredClassProperty' => ['\\SRF\\DataTables\\QuerySegmentListProcessor::conjunction', '\\SRF\\DataTables\\QuerySegmentListProcessor::disjunction', '\\SRF\\DataTables\\QuerySegmentListProcessor::hierarchy', '\\SRF\\DataTables\\QuerySegmentListProcessor::segment', '\\SRF\\DataTables\\QuerySegmentListProcessor::table'],
            'PhanUndeclaredClassReference' => ['\\SRF\\DataTables\\QuerySegmentListProcessor::conjunction'],
            'PhanUndeclaredTypeParameter' => ['\\SRF\\DataTables\\QuerySegmentListProcessor::__construct', '\\SRF\\DataTables\\QuerySegmentListProcessor::conjunction', '\\SRF\\DataTables\\QuerySegmentListProcessor::disjunction', '\\SRF\\DataTables\\QuerySegmentListProcessor::hierarchy', '\\SRF\\DataTables\\QuerySegmentListProcessor::segment', '\\SRF\\DataTables\\QuerySegmentListProcessor::table'],
            'PhanUndeclaredTypeProperty' => ['\\SRF\\DataTables\\QuerySegmentListProcessor']
        ],
        'formats/datatables/SearchPanes.php' => [
            'MediaWikiNoEmptyIfDefined' => ['\\SRF\\DataTables\\SearchPanes::getPanesOptions'],
            'PhanPossiblyUndeclaredVariable' => ['\\SRF\\DataTables\\SearchPanes::getPanesOptions'],
            'PhanTypeMismatchArgument' => ['\\SRF\\DataTables\\SearchPanes::newQuerySegmentListProcessor'],
            'PhanUndeclaredClassConstant' => ['\\SRF\\DataTables\\SearchPanes::fetchValuesByGroup', '\\SRF\\DataTables\\SearchPanes::getPanesOptions', '\\SRF\\DataTables\\SearchPanes::getSearchPanes', '\\SRF\\DataTables\\SearchPanes::searchPanesMainlabel'],
            'PhanUndeclaredClassMethod' => ['\\SRF\\DataTables\\SearchPanes::fetchValuesByGroup', '\\SRF\\DataTables\\SearchPanes::getPanesOptions', '\\SRF\\DataTables\\SearchPanes::getSearchPanes', '\\SRF\\DataTables\\SearchPanes::newQuerySegmentListProcessor', '\\SRF\\DataTables\\SearchPanes::newTemporaryTableBuilder', '\\SRF\\DataTables\\SearchPanes::searchPanesMainlabel'],
            'PhanUndeclaredClassStaticProperty' => ['\\SRF\\DataTables\\SearchPanes::getPanesOptions', '\\SRF\\DataTables\\SearchPanes::searchPanesMainlabel'],
            'PhanUndeclaredConstant' => ['\\SRF\\DataTables\\SearchPanes::getPanesOptions', '\\SRF\\DataTables\\SearchPanes::searchPanesMainlabel'],
            'PhanUndeclaredTypeParameter' => ['\\SRF\\DataTables\\SearchPanes::fetchValuesByGroup', '\\SRF\\DataTables\\SearchPanes::getPanesOptions', '\\SRF\\DataTables\\SearchPanes::searchPanesMainlabel'],
            'PhanUndeclaredTypeProperty' => ['\\SRF\\DataTables\\SearchPanes']
        ],
        'formats/dygraphs/SRF_Dygraphs.php' => [
            'PhanPossiblyUndeclaredVariable' => ['\\SRFDygraphs::getFormatOutput', '\\SRFDygraphs::getResultData'],
            'PhanTypeMismatchArgumentProbablyReal' => ['\\SRFDygraphs::getFormatOutput'],
            'PhanUndeclaredClass' => ['\\SRFDygraphs::getParamDefinitions'],
            'PhanUndeclaredClassConstant' => ['\\SRFDygraphs::getResultData'],
            'PhanUndeclaredClassMethod' => ['\\SRFDygraphs::getFormatOutput', '\\SRFDygraphs::getResultData', '\\SRFDygraphs::getResultText', '\\SRFDygraphs::makePageFromTitle'],
            'PhanUndeclaredConstant' => ['\\SRFDygraphs::getResultText'],
            'PhanUndeclaredExtendedClass' => ['formats/dygraphs/SRF_Dygraphs.php'],
            'PhanUndeclaredMethod' => ['\\SRFDygraphs::getResultData', '\\SRFDygraphs::getResultText'],
            'PhanUndeclaredProperty' => ['\\SRFDygraphs::getFormatOutput', '\\SRFDygraphs::getResultData'],
            'PhanUndeclaredTypeParameter' => ['\\SRFDygraphs::getResultData', '\\SRFDygraphs::getResultText'],
            'PhanUndeclaredVariableDim' => ['\\SRFDygraphs::getFormatOutput', '\\SRFDygraphs::getResultText']
        ],
        'formats/filtered/src/Filtered.php' => [
            'PhanUndeclaredClass' => ['\\SRF\\Filtered\\Filtered::addError', '\\SRF\\Filtered\\Filtered::getLinker', '\\SRF\\Filtered\\Filtered::getParamDefinitions', '\\SRF\\Filtered\\Filtered::handleParameters'],
            'PhanUndeclaredClassInstanceof' => ['\\SRF\\Filtered\\Filtered::getFilterHtml'],
            'PhanUndeclaredClassMethod' => ['\\SRF\\Filtered\\Filtered::getFilterHtml', '\\SRF\\Filtered\\Filtered::getResultText', '\\SRF\\Filtered\\Filtered::getViewHtml', '\\SRF\\Filtered\\Filtered::registerResourceModules'],
            'PhanUndeclaredExtendedClass' => ['formats/filtered/src/Filtered.php'],
            'PhanUndeclaredMethod' => ['\\SRF\\Filtered\\Filtered::getViewHtml'],
            'PhanUndeclaredProperty' => ['\\SRF\\Filtered\\Filtered::getLinker', '\\SRF\\Filtered\\Filtered::getResultText', '\\SRF\\Filtered\\Filtered::handleParameters', '\\SRF\\Filtered\\Filtered::hasTemplates'],
            'PhanUndeclaredTypeParameter' => ['\\SRF\\Filtered\\Filtered::getFilterHtml', '\\SRF\\Filtered\\Filtered::getResultText', '\\SRF\\Filtered\\Filtered::getViewHtml']
        ],
        'formats/filtered/src/Filters/DistanceFilter.php' => [
            'PhanTypeSuspiciousStringExpression' => ['\\SRF\\Filtered\\Filter\\DistanceFilter::getJsDataForRow'],
            'PhanUndeclaredClassInstanceof' => ['\\SRF\\Filtered\\Filter\\DistanceFilter::getJsDataForRow'],
            'PhanUndeclaredClassMethod' => ['\\SRF\\Filtered\\Filter\\DistanceFilter::buildJsConfig', '\\SRF\\Filtered\\Filter\\DistanceFilter::getJsDataForRow', '\\SRF\\Filtered\\Filter\\DistanceFilter::isValidFilterForPropertyType', '\\closure']
        ],
        'formats/filtered/src/Filters/Filter.php' => [
            'PhanTypeMismatchReturnProbablyReal' => ['\\SRF\\Filtered\\Filter\\Filter::getResourceModules'],
            'PhanUndeclaredClassMethod' => ['\\SRF\\Filtered\\Filter\\Filter::getActualParameters'],
            'PhanUndeclaredTypeParameter' => ['\\SRF\\Filtered\\Filter\\Filter::__construct'],
            'PhanUndeclaredTypeReturnType' => ['\\SRF\\Filtered\\Filter\\Filter::getPrintRequest']
        ],
        'formats/filtered/src/Filters/NumberFilter.php' => [
            'PhanUndeclaredClassInstanceof' => ['\\SRF\\Filtered\\Filter\\NumberFilter::getJsDataForRow'],
            'PhanUndeclaredClassMethod' => ['\\SRF\\Filtered\\Filter\\NumberFilter::buildJsConfig', '\\SRF\\Filtered\\Filter\\NumberFilter::getJsDataForRow', '\\SRF\\Filtered\\Filter\\NumberFilter::isValidFilterForPropertyType']
        ],
        'formats/filtered/src/ResultItem.php' => [
            'PhanUndeclaredClassInstanceof' => ['\\SRF\\Filtered\\ResultItem::getArrayRepresentation'],
            'PhanUndeclaredClassMethod' => ['\\SRF\\Filtered\\ResultItem::getArrayRepresentation'],
            'PhanUndeclaredTypeParameter' => ['\\SRF\\Filtered\\ResultItem::__construct'],
            'PhanUndeclaredTypeReturnType' => ['\\SRF\\Filtered\\ResultItem::getValue']
        ],
        'formats/filtered/src/View/CalendarView.php' => [
            'PhanUndeclaredClassInstanceof' => ['\\SRF\\Filtered\\View\\CalendarView::getJsDataForRow'],
            'PhanUndeclaredClassMethod' => ['\\SRF\\Filtered\\View\\CalendarView::getJsDataForRow', '\\SRF\\Filtered\\View\\CalendarView::getParamHashes'],
            'PhanUndeclaredConstant' => ['\\SRF\\Filtered\\View\\CalendarView::getJsDataForRow']
        ],
        'formats/filtered/src/View/ListView.php' => [
            'PhanUndeclaredClassMethod' => ['\\SRF\\Filtered\\View\\ListView::printRow'],
            'PhanUndeclaredConstant' => ['\\SRF\\Filtered\\View\\ListView::printRow'],
            'PhanUndeclaredTypeParameter' => ['\\SRF\\Filtered\\View\\ListView::printRow']
        ],
        'formats/filtered/src/View/MapView.php' => [
            'PhanParamSignatureMismatch' => ['\\SRF\\Filtered\\View\\MapView::getInitError'],
            'PhanTypeMismatchForeach' => ['\\SRF\\Filtered\\View\\MapView::getMarkerIcons'],
            'PhanTypeMismatchReturn' => ['\\SRF\\Filtered\\View\\MapView::getInitError', '\\SRF\\Filtered\\View\\MapView::getMapProvider'],
            'PhanTypeMismatchReturnProbablyReal' => ['\\SRF\\Filtered\\View\\MapView::getInitError', '\\SRF\\Filtered\\View\\MapView::getPropertyId'],
            'PhanTypeSuspiciousStringExpression' => ['\\SRF\\Filtered\\View\\MapView::getJsDataForRow'],
            'PhanUndeclaredClassInstanceof' => ['\\SRF\\Filtered\\View\\MapView::getJsDataForRow'],
            'PhanUndeclaredClassMethod' => ['\\SRF\\Filtered\\View\\MapView::getJsDataForRow'],
            'PhanUndeclaredMethod' => ['\\SRF\\Filtered\\View\\MapView::getMarkerIcons']
        ],
        'formats/filtered/src/View/TableView.php' => [
            'PhanTypeMismatchArgumentNullable' => ['\\SRF\\Filtered\\View\\TableView::getCellForPropVals'],
            'PhanUndeclaredClassConstant' => ['\\SRF\\Filtered\\View\\TableView::getCellForPropVals'],
            'PhanUndeclaredClassMethod' => ['\\SRF\\Filtered\\View\\TableView::getCellContent', '\\SRF\\Filtered\\View\\TableView::getCellForPropVals', '\\SRF\\Filtered\\View\\TableView::getColumnClass', '\\SRF\\Filtered\\View\\TableView::getTableHeader', '\\SRF\\Filtered\\View\\TableView::getTableHeaders', '\\SRF\\Filtered\\View\\TableView::getTableRows'],
            'PhanUndeclaredConstant' => ['\\SRF\\Filtered\\View\\TableView::getColumnClass', '\\SRF\\Filtered\\View\\TableView::getResultText', '\\SRF\\Filtered\\View\\TableView::getTableHeader', '\\SRF\\Filtered\\View\\TableView::getTableRowsHTML', '\\SRF\\Filtered\\View\\TableView::handleParameters'],
            'PhanUndeclaredTypeParameter' => ['\\SRF\\Filtered\\View\\TableView::getCellContent', '\\SRF\\Filtered\\View\\TableView::getCellForPropVals', '\\SRF\\Filtered\\View\\TableView::getColumnClass', '\\SRF\\Filtered\\View\\TableView::getTableHeader']
        ],
        'formats/gallery/Gallery.php' => [
            'PhanRedundantCondition' => ['\\SRF\\Gallery::addImageToGallery'],
            'PhanSuspiciousWeakTypeComparison' => ['\\SRF\\Gallery::getResultText'],
            'PhanUndeclaredClass' => ['\\SRF\\Gallery::getParamDefinitions'],
            'PhanUndeclaredClassConstant' => ['\\SRF\\Gallery::addImageProperties'],
            'PhanUndeclaredClassMethod' => ['\\SRF\\Gallery::addImagePages', '\\SRF\\Gallery::addImageProperties', '\\SRF\\Gallery::buildResult', '\\SRF\\Gallery::getCarouselWidget', '\\SRF\\Gallery::getImageOverlay', '\\SRF\\Gallery::getResultText', '\\SRF\\Gallery::getSlideshowWidget'],
            'PhanUndeclaredConstant' => ['\\SRF\\Gallery::getResultText'],
            'PhanUndeclaredExtendedClass' => ['formats/gallery/Gallery.php'],
            'PhanUndeclaredMethod' => ['\\SRF\\Gallery::addImagePages', '\\SRF\\Gallery::addImageProperties', '\\SRF\\Gallery::buildResult', '\\SRF\\Gallery::getName', '\\SRF\\Gallery::getResultText'],
            'PhanUndeclaredProperty' => ['\\SRF\\Gallery::addImagePages', '\\SRF\\Gallery::addImageToGallery', '\\SRF\\Gallery::buildResult', '\\SRF\\Gallery::getCarouselWidget', '\\SRF\\Gallery::getImageOverlay', '\\SRF\\Gallery::getResultText', '\\SRF\\Gallery::getSlideshowWidget'],
            'PhanUndeclaredTypeParameter' => ['\\SRF\\Gallery::addImagePages', '\\SRF\\Gallery::addImageProperties', '\\SRF\\Gallery::buildResult', '\\SRF\\Gallery::getResultText']
        ],
        'formats/googlecharts/SRF_GoogleBar.php' => [
            'PhanUndeclaredClass' => ['\\SRFGoogleBar::getParamDefinitions', '\\SRFGoogleBar::handleParameters'],
            'PhanUndeclaredClassMethod' => ['\\SRFGoogleBar::getResultText'],
            'PhanUndeclaredExtendedClass' => ['formats/googlecharts/SRF_GoogleBar.php'],
            'PhanUndeclaredProperty' => ['\\SRFGoogleBar::getResultText', '\\SRFGoogleBar::handleParameters'],
            'PhanUndeclaredTypeParameter' => ['\\SRFGoogleBar::getResultText']
        ],
        'formats/googlecharts/SRF_GooglePie.php' => [
            'PhanUndeclaredClass' => ['\\SRFGooglePie::getParamDefinitions', '\\SRFGooglePie::handleParameters'],
            'PhanUndeclaredClassMethod' => ['\\SRFGooglePie::getResultText'],
            'PhanUndeclaredExtendedClass' => ['formats/googlecharts/SRF_GooglePie.php'],
            'PhanUndeclaredProperty' => ['\\SRFGooglePie::getResultText', '\\SRFGooglePie::handleParameters'],
            'PhanUndeclaredTypeParameter' => ['\\SRFGooglePie::getResultText']
        ],
        'formats/graphviz/SRF_Process.php' => [
            'MediaWikiNoIssetIfDefined' => ['\\ProcessEdge::getId', '\\ProcessEdge::getUUID', '\\ProcessElement::getUUID', '\\ProcessGraph::makeNode', '\\ProcessNode::getGraphVizCode', '\\ProcessNode::getSucc', '\\SplitConditionalOrEdge::getGraphVizCode'],
            'PhanImpossibleConditionInLoop' => ['\\SRFProcess::getResultText'],
            'PhanNonClassMethodCall' => ['\\SequentialEdge::getGraphVizCode', '\\SplitConditionalOrEdge::getGraphVizCode', '\\SplitExclusiveOrEdge::getGraphVizCode', '\\SplitParallelEdge::getGraphVizCode'],
            'PhanPossiblyUndeclaredVariable' => ['\\SRFProcess::getResultText', '\\SequentialEdge::setFrom', '\\SplitConditionalOrEdge::setFrom', '\\SplitEdge::setFrom'],
            'PhanUndeclaredClass' => ['\\SRFProcess::getParamDefinitions', '\\SRFProcess::handleParameters'],
            'PhanUndeclaredClassConstant' => ['\\SRFProcess::getResultText'],
            'PhanUndeclaredClassMethod' => ['\\SRFProcess::getResultText'],
            'PhanUndeclaredExtendedClass' => ['formats/graphviz/SRF_Process.php'],
            'PhanUndeclaredFunction' => ['\\SRFProcess::getResultText'],
            'PhanUndeclaredMethod' => ['\\SRFProcess::getResultText'],
            'PhanUndeclaredProperty' => ['\\SRFProcess::getResultText'],
            'PhanUndeclaredTypeParameter' => ['\\SRFProcess::getResultText']
        ],
        'formats/incoming/SRF_Incoming.php' => [
            'PhanUndeclaredClass' => ['\\SRFIncoming::getParamDefinitions'],
            'PhanUndeclaredClassMethod' => ['\\SRFIncoming::getResultData'],
            'PhanUndeclaredClassProperty' => ['\\SRFIncoming::getResultData'],
            'PhanUndeclaredExtendedClass' => ['formats/incoming/SRF_Incoming.php'],
            'PhanUndeclaredMethod' => ['\\SRFIncoming::getResultText'],
            'PhanUndeclaredProperty' => ['\\SRFIncoming::getFormatOutput', '\\SRFIncoming::getResultData', '\\SRFIncoming::getResultText', '\\SRFIncoming::initTemplateOutput'],
            'PhanUndeclaredTypeParameter' => ['\\SRFIncoming::getResultData', '\\SRFIncoming::getResultText']
        ],
        'formats/jqplot/SRF_jqPlot.php' => [
            'PhanTypeMismatchReturnProbablyReal' => ['\\SRFjqPlot::getNumbersTicks'],
            'PhanUndeclaredExtendedClass' => ['formats/jqplot/SRF_jqPlot.php']
        ],
        'formats/jqplot/SRF_jqPlotChart.php' => [
            'PhanPossiblyUndeclaredVariable' => ['\\SRFjqPlotChart::preparePieData'],
            'PhanTypeMismatchArgumentProbablyReal' => ['\\SRFjqPlotChart::getFormatOutput'],
            'PhanUndeclaredClassMethod' => ['\\SRFjqPlotChart::getFormatOutput', '\\SRFjqPlotChart::prepareBarData', '\\SRFjqPlotChart::preparePieData'],
            'PhanUndeclaredProperty' => ['\\SRFjqPlotChart::addCommonOptions', '\\SRFjqPlotChart::getFormatOutput', '\\SRFjqPlotChart::prepareBarData', '\\SRFjqPlotChart::preparePieData'],
            'PhanUndeclaredStaticMethod' => ['\\SRFjqPlotChart::getParamDefinitions'],
            'PhanUndeclaredVariableDim' => ['\\SRFjqPlotChart::prepareBarData', '\\SRFjqPlotChart::preparePieData']
        ],
        'formats/jqplot/SRF_jqPlotSeries.php' => [
            'PhanPossiblyUndeclaredVariable' => ['\\SRFjqPlotSeries::getFormatSettings'],
            'PhanTypeMismatchArgumentProbablyReal' => ['\\SRFjqPlotSeries::getFormatOutput'],
            'PhanUndeclaredClass' => ['\\SRFjqPlotSeries::getParamDefinitions'],
            'PhanUndeclaredClassConstant' => ['\\SRFjqPlotSeries::getResultData'],
            'PhanUndeclaredClassMethod' => ['\\SRFjqPlotSeries::addResources', '\\SRFjqPlotSeries::getFormatOutput', '\\SRFjqPlotSeries::getResultData', '\\SRFjqPlotSeries::getResultText'],
            'PhanUndeclaredConstant' => ['\\SRFjqPlotSeries::getResultText'],
            'PhanUndeclaredExtendedClass' => ['formats/jqplot/SRF_jqPlotSeries.php'],
            'PhanUndeclaredMethod' => ['\\SRFjqPlotSeries::getResultText'],
            'PhanUndeclaredProperty' => ['\\SRFjqPlotSeries::addResources', '\\SRFjqPlotSeries::getFormatOutput', '\\SRFjqPlotSeries::getFormatSettings', '\\SRFjqPlotSeries::getNumbersTicks', '\\SRFjqPlotSeries::getResultData'],
            'PhanUndeclaredTypeParameter' => ['\\SRFjqPlotSeries::getResultData', '\\SRFjqPlotSeries::getResultText'],
            'PhanUndeclaredVariableDim' => ['\\SRFjqPlotSeries::getFormatSettings', '\\SRFjqPlotSeries::getResultText']
        ],
        'formats/media/MediaPlayer.php' => [
            'PhanPossiblyUndeclaredVariable' => ['\\SRF\\MediaPlayer::getResultData'],
            'PhanTypeMismatchArgumentNullable' => ['\\SRF\\MediaPlayer::getResultData'],
            'PhanTypeMismatchArgumentProbablyReal' => ['\\SRF\\MediaPlayer::getFormatOutput'],
            'PhanUndeclaredClass' => ['\\SRF\\MediaPlayer::getParamDefinitions'],
            'PhanUndeclaredClassConstant' => ['\\SRF\\MediaPlayer::getDataValueItem'],
            'PhanUndeclaredClassMethod' => ['\\SRF\\MediaPlayer::getDataValueItem', '\\SRF\\MediaPlayer::getFormatOutput', '\\SRF\\MediaPlayer::getResultData', '\\SRF\\MediaPlayer::getResultText'],
            'PhanUndeclaredExtendedClass' => ['formats/media/MediaPlayer.php'],
            'PhanUndeclaredMethod' => ['\\SRF\\MediaPlayer::getName', '\\SRF\\MediaPlayer::getResultText'],
            'PhanUndeclaredProperty' => ['\\SRF\\MediaPlayer::getFormatOutput', '\\SRF\\MediaPlayer::getResultText'],
            'PhanUndeclaredTypeParameter' => ['\\SRF\\MediaPlayer::getDataValueItem', '\\SRF\\MediaPlayer::getResultData', '\\SRF\\MediaPlayer::getResultText']
        ],
        'formats/slideshow/SRF_SlideShow.php' => [
            'PhanUndeclaredClass' => ['\\SRFSlideShow::getParamDefinitions'],
            'PhanUndeclaredClassInstanceof' => ['\\SRFSlideShow::getResultText'],
            'PhanUndeclaredClassMethod' => ['\\SRFSlideShow::getResultText'],
            'PhanUndeclaredExtendedClass' => ['formats/slideshow/SRF_SlideShow.php'],
            'PhanUndeclaredProperty' => ['\\SRFSlideShow::getResultText'],
            'PhanUndeclaredTypeParameter' => ['\\SRFSlideShow::getResultText', '\\SRFSlideShow::linkFurtherResults']
        ],
        'formats/slideshow/SRF_SlideShowApi.php' => [
            'PhanTypeSuspiciousStringExpression' => ['\\SRFSlideShowApi::getVersion'],
            'PhanUndeclaredClassConstant' => ['\\SRFSlideShowApi::execute'],
            'PhanUndeclaredClassMethod' => ['\\SRFSlideShowApi::execute'],
            'PhanUndeclaredConstant' => ['\\SRFSlideShowApi::execute', '\\SRFSlideShowApi::getVersion']
        ],
        'formats/sparkline/SRF_Sparkline.php' => [
            'PhanTypeMismatchArgumentProbablyReal' => ['\\SRFSparkline::getFormatOutput'],
            'PhanUndeclaredClass' => ['\\SRFSparkline::getParamDefinitions'],
            'PhanUndeclaredClassMethod' => ['\\SRFSparkline::getFormatOutput'],
            'PhanUndeclaredExtendedClass' => ['formats/sparkline/SRF_Sparkline.php'],
            'PhanUndeclaredProperty' => ['\\SRFSparkline::getFormatOutput']
        ],
        'formats/spreadsheet/SpreadsheetPrinter.php' => [
            'PhanTypeMismatchArgumentProbablyReal' => ['\\SRF\\SpreadsheetPrinter::createSpreadsheetFromTemplate'],
            'PhanUndeclaredClass' => ['\\SRF\\SpreadsheetPrinter::getParamDefinitions', '\\SRF\\SpreadsheetPrinter::outputAsFile'],
            'PhanUndeclaredClassInstanceof' => ['\\SRF\\SpreadsheetPrinter::populateCellAccordingToType'],
            'PhanUndeclaredClassMethod' => ['\\SRF\\SpreadsheetPrinter::getResultFileContents', '\\SRF\\SpreadsheetPrinter::populateCell', '\\SRF\\SpreadsheetPrinter::populateHeaderRow', '\\SRF\\SpreadsheetPrinter::populateWorksheet', '\\SRF\\SpreadsheetPrinter::setNumberDataValue', '\\SRF\\SpreadsheetPrinter::setQuantityDataValue', '\\SRF\\SpreadsheetPrinter::setStringDataValue', '\\SRF\\SpreadsheetPrinter::setTimeDataValue'],
            'PhanUndeclaredConstant' => ['\\SRF\\SpreadsheetPrinter::getResultText', '\\SRF\\SpreadsheetPrinter::populateCell'],
            'PhanUndeclaredExtendedClass' => ['formats/spreadsheet/SpreadsheetPrinter.php'],
            'PhanUndeclaredMethod' => ['\\SRF\\SpreadsheetPrinter::getResultText'],
            'PhanUndeclaredProperty' => ['\\SRF\\SpreadsheetPrinter::createSpreadsheet', '\\SRF\\SpreadsheetPrinter::getFileName', '\\SRF\\SpreadsheetPrinter::getResultText', '\\SRF\\SpreadsheetPrinter::populateWorksheet'],
            'PhanUndeclaredTypeParameter' => ['\\SRF\\SpreadsheetPrinter::getFileName', '\\SRF\\SpreadsheetPrinter::getMimeType', '\\SRF\\SpreadsheetPrinter::getResultFileContents', '\\SRF\\SpreadsheetPrinter::getResultText', '\\SRF\\SpreadsheetPrinter::outputAsFile', '\\SRF\\SpreadsheetPrinter::populateCell', '\\SRF\\SpreadsheetPrinter::populateCellAccordingToType', '\\SRF\\SpreadsheetPrinter::populateHeaderRow', '\\SRF\\SpreadsheetPrinter::populateWorksheet', '\\SRF\\SpreadsheetPrinter::setNumberDataValue', '\\SRF\\SpreadsheetPrinter::setQuantityDataValue', '\\SRF\\SpreadsheetPrinter::setStringDataValue', '\\SRF\\SpreadsheetPrinter::setTimeDataValue']
        ],
        'formats/tagcloud/TagCloud.php' => [
            'PhanPossiblyUndeclaredVariable' => ['\\SRF\\TagCloud::getTagSizes', '\\SRF\\TagCloud::getTags'],
            'PhanUndeclaredClass' => ['\\SRF\\TagCloud::getParamDefinitions'],
            'PhanUndeclaredClassConstant' => ['\\SRF\\TagCloud::getTags'],
            'PhanUndeclaredClassMethod' => ['\\SRF\\TagCloud::getResultText', '\\SRF\\TagCloud::getTagCloud', '\\SRF\\TagCloud::getTags'],
            'PhanUndeclaredExtendedClass' => ['formats/tagcloud/TagCloud.php'],
            'PhanUndeclaredMethod' => ['\\SRF\\TagCloud::getName', '\\SRF\\TagCloud::getResultText', '\\SRF\\TagCloud::getTags'],
            'PhanUndeclaredProperty' => ['\\SRF\\TagCloud::addTemplateOutput', '\\SRF\\TagCloud::getResultText', '\\SRF\\TagCloud::getTagCloud', '\\SRF\\TagCloud::getTagSizes', '\\SRF\\TagCloud::getTags', '\\SRF\\TagCloud::isHTML'],
            'PhanUndeclaredTypeParameter' => ['\\SRF\\TagCloud::getResultText', '\\SRF\\TagCloud::getTags']
        ],
        'formats/time/SRF_Time.php' => [
            'MediaWikiNoEmptyIfDefined' => ['\\SRFTime::getResultText'],
            'PhanPossiblyUndeclaredVariable' => ['\\SRFTime::getResultText'],
            'PhanUndeclaredClass' => ['\\SRFTime::getParamDefinitions'],
            'PhanUndeclaredClassConstant' => ['\\SRFTime::getSortKeys'],
            'PhanUndeclaredClassMethod' => ['\\SRFTime::getResultText', '\\SRFTime::getSortKeys'],
            'PhanUndeclaredExtendedClass' => ['formats/time/SRF_Time.php'],
            'PhanUndeclaredProperty' => ['\\SRFTime::getName', '\\SRFTime::getResultText'],
            'PhanUndeclaredTypeParameter' => ['\\SRFTime::getResultText', '\\SRFTime::getSortKeys']
        ],
        'formats/timeline/SRF_Timeline.php' => [
            'PhanPossiblyUndeclaredVariable' => ['\\SRFTimeline::getEventsHTML'],
            'PhanTypeMismatchArgument' => ['\\SRFTimeline::getEventsHTML'],
            'PhanTypeMismatchDimFetch' => ['\\SRFTimeline::getEventsHTML'],
            'PhanUndeclaredClass' => ['\\SRFTimeline::getParamDefinitions', '\\SRFTimeline::handleParameters'],
            'PhanUndeclaredClassConstant' => ['\\SRFTimeline::getResultText', '\\SRFTimeline::handlePropertyValue'],
            'PhanUndeclaredClassMethod' => ['\\SRFTimeline::getEventsHTML', '\\SRFTimeline::getResultText', '\\SRFTimeline::handlePropertyValue'],
            'PhanUndeclaredConstant' => ['\\SRFTimeline::getEventsHTML', '\\SRFTimeline::getResultText'],
            'PhanUndeclaredExtendedClass' => ['formats/timeline/SRF_Timeline.php'],
            'PhanUndeclaredFunction' => ['\\SRFTimeline::handleParameters'],
            'PhanUndeclaredMethod' => ['\\SRFTimeline::getEventsHTML', '\\SRFTimeline::handlePropertyValue'],
            'PhanUndeclaredProperty' => ['\\SRFTimeline::getEventsHTML', '\\SRFTimeline::getName', '\\SRFTimeline::getResultText', '\\SRFTimeline::handlePropertyValue'],
            'PhanUndeclaredTypeParameter' => ['\\SRFTimeline::getEventsHTML', '\\SRFTimeline::getResultText', '\\SRFTimeline::handlePropertyValue']
        ],
        'formats/timeseries/SRF_Timeseries.php' => [
            'PhanPossiblyUndeclaredVariable' => ['\\SRFTimeseries::getFormatOutput'],
            'PhanTypeMismatchArgumentProbablyReal' => ['\\SRFTimeseries::getFormatOutput'],
            'PhanUndeclaredClass' => ['\\SRFTimeseries::getParamDefinitions'],
            'PhanUndeclaredClassConstant' => ['\\SRFTimeseries::getAggregatedTimeSeries'],
            'PhanUndeclaredClassMethod' => ['\\SRFTimeseries::getAggregatedTimeSeries', '\\SRFTimeseries::getFormatOutput', '\\SRFTimeseries::getResultText'],
            'PhanUndeclaredConstant' => ['\\SRFTimeseries::getResultText'],
            'PhanUndeclaredExtendedClass' => ['formats/timeseries/SRF_Timeseries.php'],
            'PhanUndeclaredMethod' => ['\\SRFTimeseries::getResultText'],
            'PhanUndeclaredProperty' => ['\\SRFTimeseries::getAggregatedTimeSeries', '\\SRFTimeseries::getFormatOutput'],
            'PhanUndeclaredTypeParameter' => ['\\SRFTimeseries::getAggregatedTimeSeries', '\\SRFTimeseries::getResultText'],
            'PhanUndeclaredVariableDim' => ['\\SRFTimeseries::getFormatOutput', '\\SRFTimeseries::getResultText']
        ],
        'formats/tree/TreeNode.php' => [
            'PhanUndeclaredClass' => ['\\SRF\\Formats\\Tree\\TreeNode::__construct', '\\SRF\\Formats\\Tree\\TreeNode::addChild'],
            'PhanUndeclaredClassMethod' => ['\\SRF\\Formats\\Tree\\TreeNode::addChild', '\\SRF\\Formats\\Tree\\TreeNode::getHash'],
            'PhanUndeclaredExtendedClass' => ['formats/tree/TreeNode.php'],
            'PhanUndeclaredMethod' => ['\\SRF\\Formats\\Tree\\TreeNode::addChild', '\\SRF\\Formats\\Tree\\TreeNode::getResultSubject'],
            'PhanUndeclaredTypeParameter' => ['\\SRF\\Formats\\Tree\\TreeNode::__construct', '\\SRF\\Formats\\Tree\\TreeNode::addChild'],
            'PhanUndeclaredTypeReturnType' => ['\\SRF\\Formats\\Tree\\TreeNode::addChild', '\\SRF\\Formats\\Tree\\TreeNode::getResultSubject']
        ],
        'formats/tree/TreeNodeVisitor.php' => [
            'PhanTypeMismatchArgument' => ['\\SRF\\Formats\\Tree\\TreeNodePrinter::visit'],
            'PhanUndeclaredClassMethod' => ['\\SRF\\Formats\\Tree\\TreeNodePrinter::getLabelForCell', '\\SRF\\Formats\\Tree\\TreeNodePrinter::getParamNameForCell', '\\SRF\\Formats\\Tree\\TreeNodePrinter::getValuesTextForCell', '\\SRF\\Formats\\Tree\\TreeNodePrinter::visit'],
            'PhanUndeclaredConstant' => ['\\SRF\\Formats\\Tree\\TreeNodePrinter::getLabelForCell', '\\SRF\\Formats\\Tree\\TreeNodePrinter::getValuesTextForCell'],
            'PhanUndeclaredInterface' => ['formats/tree/TreeNodeVisitor.php'],
            'PhanUndeclaredMethod' => ['\\SRF\\Formats\\Tree\\TreeNodePrinter::getTextForNode'],
            'PhanUndeclaredTypeParameter' => ['\\SRF\\Formats\\Tree\\TreeNodePrinter::getLabelForCell', '\\SRF\\Formats\\Tree\\TreeNodePrinter::getParamNameForCell', '\\SRF\\Formats\\Tree\\TreeNodePrinter::getTextForRowNoTemplate', '\\SRF\\Formats\\Tree\\TreeNodePrinter::getTextForRowWithTemplate', '\\SRF\\Formats\\Tree\\TreeNodePrinter::getValuesTextForCell', '\\SRF\\Formats\\Tree\\TreeNodePrinter::visit']
        ],
        'formats/tree/TreeResultPrinter.php' => [
            'MediaWikiNoEmptyIfDefined' => ['\\SRF\\Formats\\Tree\\TreeResultPrinter::buildTreeFromNodeList'],
            'PhanTypeMismatchArgument' => ['\\SRF\\Formats\\Tree\\TreeResultPrinter::buildTreeFromNodeList'],
            'PhanUndeclaredClass' => ['\\SRF\\Formats\\Tree\\TreeResultPrinter::addError', '\\SRF\\Formats\\Tree\\TreeResultPrinter::getLinkerForColumn', '\\SRF\\Formats\\Tree\\TreeResultPrinter::getParamDefinitions', '\\SRF\\Formats\\Tree\\TreeResultPrinter::postProcessParameters'],
            'PhanUndeclaredClassMethod' => ['\\SRF\\Formats\\Tree\\TreeResultPrinter::buildTreeFromNodeList', '\\SRF\\Formats\\Tree\\TreeResultPrinter::getHashOfNodes', '\\SRF\\Formats\\Tree\\TreeResultPrinter::getRootHash', '\\SRF\\Formats\\Tree\\TreeResultPrinter::initalizeStandardTemplateParameters'],
            'PhanUndeclaredExtendedClass' => ['formats/tree/TreeResultPrinter.php'],
            'PhanUndeclaredMethod' => ['\\SRF\\Formats\\Tree\\TreeResultPrinter::buildLinesFromTree'],
            'PhanUndeclaredProperty' => ['\\SRF\\Formats\\Tree\\TreeResultPrinter::buildLinesFromTree', '\\SRF\\Formats\\Tree\\TreeResultPrinter::buildTreeFromNodeList', '\\SRF\\Formats\\Tree\\TreeResultPrinter::getLinker', '\\SRF\\Formats\\Tree\\TreeResultPrinter::getName', '\\SRF\\Formats\\Tree\\TreeResultPrinter::getResultText', '\\SRF\\Formats\\Tree\\TreeResultPrinter::getRootHash', '\\SRF\\Formats\\Tree\\TreeResultPrinter::initalizeStandardTemplateParameters', '\\SRF\\Formats\\Tree\\TreeResultPrinter::postProcessParameters'],
            'PhanUndeclaredTypeParameter' => ['\\SRF\\Formats\\Tree\\TreeResultPrinter::getResultText', '\\SRF\\Formats\\Tree\\TreeResultPrinter::setQueryResult'],
            'PhanUndeclaredTypeProperty' => ['\\SRF\\Formats\\Tree\\TreeResultPrinter'],
            'PhanUndeclaredTypeReturnType' => ['\\SRF\\Formats\\Tree\\TreeResultPrinter::getQueryResult']
        ],
        'formats/valuerank/SRF_ValueRank.php' => [
            'PhanUndeclaredClass' => ['\\SRFValueRank::getParamDefinitions'],
            'PhanUndeclaredClassConstant' => ['\\SRFValueRank::getResultValues'],
            'PhanUndeclaredClassMethod' => ['\\SRFValueRank::getResultValues'],
            'PhanUndeclaredConstant' => ['\\SRFValueRank::getResultText'],
            'PhanUndeclaredExtendedClass' => ['formats/valuerank/SRF_ValueRank.php'],
            'PhanUndeclaredMethod' => ['\\SRFValueRank::getResultValues'],
            'PhanUndeclaredProperty' => ['\\SRFValueRank::addTemplateOutput', '\\SRFValueRank::getFormatOutput', '\\SRFValueRank::getResultText', '\\SRFValueRank::getResultValues', '\\SRFValueRank::getValueRank'],
            'PhanUndeclaredTypeParameter' => ['\\SRFValueRank::getResultText', '\\SRFValueRank::getResultValues']
        ],
        'formats/widget/SRF_ListWidget.php' => [
            'PhanUndeclaredClass' => ['\\SRFListWidget::getParamDefinitions'],
            'PhanUndeclaredClassMethod' => ['\\SRFListWidget::getResultText'],
            'PhanUndeclaredExtendedClass' => ['formats/widget/SRF_ListWidget.php'],
            'PhanUndeclaredProperty' => ['\\SRFListWidget::getResultText'],
            'PhanUndeclaredTypeParameter' => ['\\SRFListWidget::getResultText']
        ],
        'formats/widget/SRF_PageWidget.php' => [
            'PhanUndeclaredClass' => ['\\SRFPageWidget::getParamDefinitions', '\\SRFPageWidget::getResultText'],
            'PhanUndeclaredClassMethod' => ['\\SRFPageWidget::getResultText'],
            'PhanUndeclaredExtendedClass' => ['formats/widget/SRF_PageWidget.php'],
            'PhanUndeclaredProperty' => ['\\SRFPageWidget::getResultText'],
            'PhanUndeclaredTypeParameter' => ['\\SRFPageWidget::getResultText']
        ],
        'src/ArrayFormat/ArrayPrinter.php' => [
            'MediaWikiNoEmptyIfDefined' => ['\\SRF\\ArrayFormat\\ArrayPrinter::deliverPageProperties', '\\SRF\\ArrayFormat\\ArrayPrinter::deliverPropertiesManyValues', '\\SRF\\ArrayFormat\\ArrayPrinter::deliverSingleManyValuesData', '\\SRF\\ArrayFormat\\ArrayPrinter::getResultText'],
            'MediaWikiNoIssetIfDefined' => ['\\SRF\\ArrayFormat\\ArrayPrinter::createArray', '\\SRF\\ArrayFormat\\ArrayPrinter::initializeCfgValue'],
            'PhanPossiblyUndeclaredVariable' => ['\\SRF\\ArrayFormat\\ArrayPrinter::getCfgSepText'],
            'PhanUndeclaredClass' => ['\\SRF\\ArrayFormat\\ArrayPrinter::__construct', '\\SRF\\ArrayFormat\\ArrayPrinter::getParamDefinitions', '\\SRF\\ArrayFormat\\ArrayPrinter::handleParameters'],
            'PhanUndeclaredClassConstant' => ['\\SRF\\ArrayFormat\\ArrayPrinter::createArray', '\\SRF\\ArrayFormat\\ArrayPrinter::getCfgSepText', '\\SRF\\ArrayFormat\\ArrayPrinter::getQueryMode'],
            'PhanUndeclaredClassInstanceof' => ['\\SRF\\ArrayFormat\\ArrayPrinter::getResultText'],
            'PhanUndeclaredClassMethod' => ['\\SRF\\ArrayFormat\\ArrayPrinter::createArray', '\\SRF\\ArrayFormat\\ArrayPrinter::deliverPropertiesManyValues', '\\SRF\\ArrayFormat\\ArrayPrinter::getResultText'],
            'PhanUndeclaredConstant' => ['\\SRF\\ArrayFormat\\ArrayPrinter::deliverPropertiesManyValues'],
            'PhanUndeclaredExtendedClass' => ['src/ArrayFormat/ArrayPrinter.php'],
            'PhanUndeclaredMethod' => ['\\SRF\\ArrayFormat\\ArrayPrinter::getCfgSepText'],
            'PhanUndeclaredProperty' => ['\\SRF\\ArrayFormat\\ArrayPrinter::__construct', '\\SRF\\ArrayFormat\\ArrayPrinter::applyArrayParameters', '\\SRF\\ArrayFormat\\ArrayPrinter::deliverPropertiesManyValues', '\\SRF\\ArrayFormat\\ArrayPrinter::getName', '\\SRF\\ArrayFormat\\ArrayPrinter::getResultText'],
            'PhanUndeclaredTypeParameter' => ['\\SRF\\ArrayFormat\\ArrayPrinter::deliverMissingProperty', '\\SRF\\ArrayFormat\\ArrayPrinter::deliverPropertiesManyValues', '\\SRF\\ArrayFormat\\ArrayPrinter::getResultText']
        ],
        'src/ArrayFormat/HashPrinter.php' => [
            'MediaWikiNoIssetIfDefined' => ['\\SRF\\ArrayFormat\\HashPrinter::createArray'],
            'PhanUndeclaredClassConstant' => ['\\SRF\\ArrayFormat\\HashPrinter::createArray'],
            'PhanUndeclaredClassMethod' => ['\\SRF\\ArrayFormat\\HashPrinter::createArray']
        ],
        'src/BibTex/BibTexFileExportPrinter.php' => [
            'PhanTypeMismatchReturn' => ['\\SRF\\BibTex\\BibTexFileExportPrinter::newItem'],
            'PhanUndeclaredClass' => ['\\SRF\\BibTex\\BibTexFileExportPrinter::getParamDefinitions'],
            'PhanUndeclaredClassInstanceof' => ['\\SRF\\BibTex\\BibTexFileExportPrinter::newItem'],
            'PhanUndeclaredClassMethod' => ['\\SRF\\BibTex\\BibTexFileExportPrinter::getResultText', '\\SRF\\BibTex\\BibTexFileExportPrinter::newItem'],
            'PhanUndeclaredConstant' => ['\\SRF\\BibTex\\BibTexFileExportPrinter::getBibTexLink', '\\SRF\\BibTex\\BibTexFileExportPrinter::getFileName', '\\SRF\\BibTex\\BibTexFileExportPrinter::getResultText'],
            'PhanUndeclaredExtendedClass' => ['src/BibTex/BibTexFileExportPrinter.php'],
            'PhanUndeclaredMethod' => ['\\SRF\\BibTex\\BibTexFileExportPrinter::getBibTexLink', '\\SRF\\BibTex\\BibTexFileExportPrinter::getFileName'],
            'PhanUndeclaredProperty' => ['\\SRF\\BibTex\\BibTexFileExportPrinter::getBibTexLink', '\\SRF\\BibTex\\BibTexFileExportPrinter::getFileName'],
            'PhanUndeclaredTypeParameter' => ['\\SRF\\BibTex\\BibTexFileExportPrinter::getBibTexLink', '\\SRF\\BibTex\\BibTexFileExportPrinter::getFileName', '\\SRF\\BibTex\\BibTexFileExportPrinter::getMimeType', '\\SRF\\BibTex\\BibTexFileExportPrinter::getResultText'],
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
            'PhanUndeclaredClass' => ['\\SRF\\Calendar\\Calendar::getParamDefinitions', '\\SRF\\Calendar\\Calendar::handleParameters'],
            'PhanUndeclaredClassConstant' => ['\\SRF\\Calendar\\Calendar::getResultText'],
            'PhanUndeclaredClassMethod' => ['\\SRF\\Calendar\\Calendar::getResultText'],
            'PhanUndeclaredConstant' => ['\\SRF\\Calendar\\Calendar::buildResult', '\\SRF\\Calendar\\Calendar::getResultText'],
            'PhanUndeclaredExtendedClass' => ['src/Calendar/Calendar.php'],
            'PhanUndeclaredFunction' => ['\\SRF\\Calendar\\Calendar::getResultText'],
            'PhanUndeclaredProperty' => ['\\SRF\\Calendar\\Calendar::buildResult', '\\SRF\\Calendar\\Calendar::getResultText'],
            'PhanUndeclaredTypeParameter' => ['\\SRF\\Calendar\\Calendar::buildResult', '\\SRF\\Calendar\\Calendar::getResultText']
        ],
        'src/Calendar/EventCalendar.php' => [
            'PhanTypeMismatchDimAssignment' => ['\\SRF\\Calendar\\EventCalendar::getResultText'],
            'PhanUndeclaredClass' => ['\\SRF\\Calendar\\EventCalendar::getParamDefinitions'],
            'PhanUndeclaredExtendedClass' => ['src/Calendar/EventCalendar.php'],
            'PhanUndeclaredMethod' => ['\\SRF\\Calendar\\EventCalendar::getName'],
            'PhanUndeclaredProperty' => ['\\SRF\\Calendar\\EventCalendar::getResultText'],
            'PhanUndeclaredTypeParameter' => ['\\SRF\\Calendar\\EventCalendar::getResultText']
        ],
        'src/Calendar/HistoricalDate.php' => [
            'PhanTypeInvalidModuloOperand' => ['\\SRF\\Calendar\\HistoricalDate::getDayOfWeek'],
            'PhanTypeInvalidUnaryOperandIncOrDec' => ['\\SRF\\Calendar\\HistoricalDate::createFromJulian']
        ],
        'src/Graph/GraphFormatter.php' => [
            'MediaWikiNoEmptyIfDefined' => ['\\SRF\\Graph\\GraphFormatter::buildGraph'],
            'PhanUndeclaredClassMethod' => ['\\SRF\\Graph\\GraphFormatter::buildGraph'],
            'PhanUndeclaredTypeParameter' => ['\\SRF\\Graph\\GraphFormatter::buildGraph'],
            'SecurityCheck-DoubleEscaped' => ['\\SRF\\Graph\\GraphFormatter::buildGraph']
        ],
        'src/Graph/GraphPrinter.php' => [
            'PhanTypeMismatchArgument' => ['\\SRF\\Graph\\GraphPrinter::getResultText'],
            'PhanUndeclaredClass' => ['\\SRF\\Graph\\GraphPrinter::getParamDefinitions', '\\SRF\\Graph\\GraphPrinter::handleParameters'],
            'PhanUndeclaredClassConstant' => ['\\SRF\\Graph\\GraphPrinter::processResultRow'],
            'PhanUndeclaredClassMethod' => ['\\SRF\\Graph\\GraphPrinter::getResultText', '\\SRF\\Graph\\GraphPrinter::processResultRow'],
            'PhanUndeclaredConstant' => ['\\SRF\\Graph\\GraphPrinter::getResultText'],
            'PhanUndeclaredExtendedClass' => ['src/Graph/GraphPrinter.php'],
            'PhanUndeclaredMethod' => ['\\SRF\\Graph\\GraphPrinter::getName'],
            'PhanUndeclaredTypeParameter' => ['\\SRF\\Graph\\GraphPrinter::getResultText', '\\SRF\\Graph\\GraphPrinter::processResultRow']
        ],
        'src/Math/Math.php' => [
            'PhanRedundantCondition' => ['\\SRF\\Math\\MathFormats::quartillowerExcFunction', '\\SRF\\Math\\MathFormats::quartillowerIncFunction', '\\SRF\\Math\\MathFormats::quartilupperExcFunction', '\\SRF\\Math\\MathFormats::quartilupperIncFunction'],
            'PhanTypeMismatchDimFetch' => ['\\SRF\\Math\\MathFormats::medianFunction', '\\SRF\\Math\\MathFormats::quartillowerExcFunction', '\\SRF\\Math\\MathFormats::quartillowerIncFunction', '\\SRF\\Math\\MathFormats::quartilupperExcFunction', '\\SRF\\Math\\MathFormats::quartilupperIncFunction'],
            'PhanUndeclaredClassConstant' => ['\\SRF\\Math\\Math::addNumbersForDataItem'],
            'PhanUndeclaredClassMethod' => ['\\SRF\\Math\\Math::addNumbersForDataItem', '\\SRF\\Math\\Math::buildResult', '\\SRF\\Math\\Math::getNumbers'],
            'PhanUndeclaredConstant' => ['\\SRF\\Math\\Math::buildResult'],
            'PhanUndeclaredExtendedClass' => ['src/Math/Math.php'],
            'PhanUndeclaredProperty' => ['\\SRF\\Math\\Math::getName', '\\SRF\\Math\\Math::getResultText'],
            'PhanUndeclaredTypeParameter' => ['\\SRF\\Math\\Math::addNumbersForDataItem', '\\SRF\\Math\\Math::buildResult', '\\SRF\\Math\\Math::getNumbers', '\\SRF\\Math\\Math::getResultText']
        ],
        'src/Outline/ListTreeBuilder.php' => [
            'PhanUndeclaredClassConstant' => ['\\SRF\\Outline\\ListTreeBuilder::item'],
            'PhanUndeclaredConstant' => ['\\SRF\\Outline\\ListTreeBuilder::item'],
            'PhanUndeclaredTypeParameter' => ['\\SRF\\Outline\\ListTreeBuilder::setLinker'],
            'PhanUndeclaredTypeProperty' => ['\\SRF\\Outline\\ListTreeBuilder']
        ],
        'src/Outline/OutlineResultPrinter.php' => [
            'PhanUndeclaredClass' => ['\\SRF\\Outline\\OutlineResultPrinter::getParamDefinitions'],
            'PhanUndeclaredClassMethod' => ['\\SRF\\Outline\\OutlineResultPrinter::getResultText'],
            'PhanUndeclaredConstant' => ['\\SRF\\Outline\\OutlineResultPrinter::newOutlineItem'],
            'PhanUndeclaredExtendedClass' => ['src/Outline/OutlineResultPrinter.php'],
            'PhanUndeclaredMethod' => ['\\SRF\\Outline\\OutlineResultPrinter::getResultText', '\\SRF\\Outline\\OutlineResultPrinter::newOutlineItem'],
            'PhanUndeclaredProperty' => ['\\SRF\\Outline\\OutlineResultPrinter::getResultText', '\\SRF\\Outline\\OutlineResultPrinter::newOutlineItem'],
            'PhanUndeclaredTypeParameter' => ['\\SRF\\Outline\\OutlineResultPrinter::getResultText']
        ],
        'src/Outline/TemplateBuilder.php' => [
            'PhanUndeclaredClassConstant' => ['\\SRF\\Outline\\TemplateBuilder::itemText'],
            'PhanUndeclaredConstant' => ['\\SRF\\Outline\\TemplateBuilder::item', '\\SRF\\Outline\\TemplateBuilder::itemRaw', '\\SRF\\Outline\\TemplateBuilder::itemText'],
            'PhanUndeclaredTypeParameter' => ['\\SRF\\Outline\\TemplateBuilder::setLinker'],
            'PhanUndeclaredTypeProperty' => ['\\SRF\\Outline\\TemplateBuilder']
        ],
        'src/ResourceFormatter.php' => [
            'PhanTypeMismatchArgument' => ['\\SRF\\ResourceFormatter::encode'],
            'PhanTypeMismatchReturnProbablyReal' => ['\\SRF\\ResourceFormatter::getData'],
            'PhanUndeclaredClassMethod' => ['\\SRF\\ResourceFormatter::appendPreferredPropertyLabel', '\\SRF\\ResourceFormatter::encode', '\\SRF\\ResourceFormatter::getData', '\\SRF\\ResourceFormatter::placeholder', '\\SRF\\ResourceFormatter::registerResources'],
            'PhanUndeclaredTypeParameter' => ['\\SRF\\ResourceFormatter::appendPreferredPropertyLabel', '\\SRF\\ResourceFormatter::getData']
        ],
        'src/iCalendar/DateParser.php' => [
            'PhanUndeclaredClassMethod' => ['\\SRF\\iCalendar\\DateParser::parseDate'],
            'PhanUndeclaredTypeParameter' => ['\\SRF\\iCalendar\\DateParser::parseDate']
        ],
        'src/iCalendar/IcalTimezoneFormatter.php' => [
            'PhanTypeComparisonFromArray' => ['\\SRF\\iCalendar\\IcalTimezoneFormatter::calcTransitions']
        ],
        'src/iCalendar/iCalendarFileExportPrinter.php' => [
            'PhanUndeclaredClass' => ['\\SRF\\iCalendar\\iCalendarFileExportPrinter::getParamDefinitions', '\\SRF\\iCalendar\\iCalendarFileExportPrinter::handleParameters'],
            'PhanUndeclaredClassConstant' => ['\\SRF\\iCalendar\\iCalendarFileExportPrinter::getQueryMode'],
            'PhanUndeclaredClassMethod' => ['\\SRF\\iCalendar\\iCalendarFileExportPrinter::filterField', '\\SRF\\iCalendar\\iCalendarFileExportPrinter::getEventParams', '\\SRF\\iCalendar\\iCalendarFileExportPrinter::getIcal', '\\SRF\\iCalendar\\iCalendarFileExportPrinter::getIcalLink'],
            'PhanUndeclaredConstant' => ['\\SRF\\iCalendar\\iCalendarFileExportPrinter::getIcalLink', '\\SRF\\iCalendar\\iCalendarFileExportPrinter::getResultText'],
            'PhanUndeclaredExtendedClass' => ['src/iCalendar/iCalendarFileExportPrinter.php'],
            'PhanUndeclaredMethod' => ['\\SRF\\iCalendar\\iCalendarFileExportPrinter::getIcalLink'],
            'PhanUndeclaredProperty' => ['\\SRF\\iCalendar\\iCalendarFileExportPrinter::getIcal', '\\SRF\\iCalendar\\iCalendarFileExportPrinter::getIcalLink'],
            'PhanUndeclaredTypeParameter' => ['\\SRF\\iCalendar\\iCalendarFileExportPrinter::getEventParams', '\\SRF\\iCalendar\\iCalendarFileExportPrinter::getFileName', '\\SRF\\iCalendar\\iCalendarFileExportPrinter::getIcal', '\\SRF\\iCalendar\\iCalendarFileExportPrinter::getIcalLink', '\\SRF\\iCalendar\\iCalendarFileExportPrinter::getMimeType', '\\SRF\\iCalendar\\iCalendarFileExportPrinter::getResultText']
        ],
        'src/vCard/vCardFileExportPrinter.php' => [
            'PhanTypeMismatchArgument' => ['\\SRF\\vCard\\vCardFileExportPrinter::newVCard'],
            'PhanUndeclaredClass' => ['\\SRF\\vCard\\vCardFileExportPrinter::getParamDefinitions'],
            'PhanUndeclaredClassConstant' => ['\\SRF\\vCard\\vCardFileExportPrinter::getQueryMode', '\\SRF\\vCard\\vCardFileExportPrinter::mapField'],
            'PhanUndeclaredClassMethod' => ['\\SRF\\vCard\\vCardFileExportPrinter::getVCardContent', '\\SRF\\vCard\\vCardFileExportPrinter::getVCardLink'],
            'PhanUndeclaredConstant' => ['\\SRF\\vCard\\vCardFileExportPrinter::getFileName', '\\SRF\\vCard\\vCardFileExportPrinter::getResultText', '\\SRF\\vCard\\vCardFileExportPrinter::getVCardLink'],
            'PhanUndeclaredExtendedClass' => ['src/vCard/vCardFileExportPrinter.php'],
            'PhanUndeclaredMethod' => ['\\SRF\\vCard\\vCardFileExportPrinter::getFileName', '\\SRF\\vCard\\vCardFileExportPrinter::getVCardLink'],
            'PhanUndeclaredProperty' => ['\\SRF\\vCard\\vCardFileExportPrinter::getFileName', '\\SRF\\vCard\\vCardFileExportPrinter::getVCardLink'],
            'PhanUndeclaredTypeParameter' => ['\\SRF\\vCard\\vCardFileExportPrinter::getFileName', '\\SRF\\vCard\\vCardFileExportPrinter::getMimeType', '\\SRF\\vCard\\vCardFileExportPrinter::getResultText', '\\SRF\\vCard\\vCardFileExportPrinter::getVCardContent', '\\SRF\\vCard\\vCardFileExportPrinter::getVCardLink'],
            'PhanUndeclaredVariableDim' => ['\\SRF\\vCard\\vCardFileExportPrinter::newVCard']
        ],
    ],
    // 'directory_suppressions' => ['src/directory_name' => ['PhanIssueName1', 'PhanIssueName2']] can be manually added if needed.
    // (directory_suppressions will currently be ignored by subsequent calls to --save-baseline, but may be preserved in future Phan releases)
];
