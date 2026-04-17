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
    // PhanUndeclaredClassMethod : 500+ occurrences
    // PhanUndeclaredProperty : 450+ occurrences
    // PhanUndeclaredTypeParameter : 140+ occurrences
    // PhanUndeclaredClassConstant : 100+ occurrences
    // PhanUndeclaredClassProperty : 90+ occurrences
    // PhanUndeclaredConstant : 80+ occurrences
    // PhanUndeclaredMethod : 60+ occurrences
    // PhanUndeclaredClass : 55+ occurrences
    // PhanPossiblyUndeclaredVariable : 45+ occurrences
    // PhanUndeclaredClassInstanceof : 40+ occurrences
    // PhanUndeclaredExtendedClass : 35+ occurrences
    // MediaWikiNoEmptyIfDefined : 15+ occurrences
    // PhanTypeMismatchArgument : 15+ occurrences
    // PhanUndeclaredTypeThrowsType : 15+ occurrences
    // PhanUndeclaredVariableDim : 15+ occurrences
    // PhanNonClassMethodCall : 10+ occurrences
    // PhanTypeMismatchDimFetch : 10+ occurrences
    // PhanTypeMismatchReturn : 10+ occurrences
    // PhanUndeclaredTypeProperty : 10+ occurrences
    // PhanTypeMismatchArgumentProbablyReal : 9 occurrences
    // PhanUndeclaredTypeReturnType : 9 occurrences
    // PhanRedundantCondition : 8 occurrences
    // PhanTypeArraySuspiciousNullable : 8 occurrences
    // PhanTypePossiblyInvalidDimOffset : 7 occurrences
    // PhanUndeclaredFunction : 7 occurrences
    // PhanCompatibleUnionType : 6 occurrences
    // PhanPluginDuplicateConditionalTernaryDuplication : 5 occurrences
    // PhanPluginSimplifyExpressionBool : 5 occurrences
    // PhanTypeMismatchArgumentInternal : 5 occurrences
    // PhanTypeMismatchReturnProbablyReal : 5 occurrences
    // PhanPluginDuplicateExpressionAssignmentOperation : 4 occurrences
    // PhanTypeInvalidLeftOperandOfNumericOp : 4 occurrences
    // PhanTypeMismatchForeach : 4 occurrences
    // PhanTypeSuspiciousStringExpression : 4 occurrences
    // PhanImpossibleConditionInLoop : 3 occurrences
    // PhanTypeMismatchArgumentNullable : 3 occurrences
    // PhanParamSignatureMismatch : 2 occurrences
    // PhanPluginDuplicateConditionalNullCoalescing : 2 occurrences
    // PhanPluginUseReturnValueInternalKnown : 2 occurrences
    // PhanTypeInvalidUnaryOperandIncOrDec : 2 occurrences
    // PhanTypeMismatchArgumentNullableInternal : 2 occurrences
    // PhanTypeMismatchProperty : 2 occurrences
    // PhanUndeclaredClassStaticProperty : 2 occurrences
    // SecurityCheck-DoubleEscaped : 2 occurrences
    // MediaWikiNoBaseException : 1 occurrence
    // PhanImpossibleTypeComparison : 1 occurrence
    // PhanParamSpecial1 : 1 occurrence
    // PhanParamTooMany : 1 occurrence
    // PhanPluginDuplicateAdjacentStatement : 1 occurrence
    // PhanPluginDuplicateExpressionAssignment : 1 occurrence
    // PhanSuspiciousBinaryAddLists : 1 occurrence
    // PhanSuspiciousWeakTypeComparison : 1 occurrence
    // PhanTypeComparisonFromArray : 1 occurrence
    // PhanTypeInvalidLeftOperandOfAdd : 1 occurrence
    // PhanTypeMismatchDimAssignment : 1 occurrence
    // PhanTypeMismatchReturnNullable : 1 occurrence
    // PhanTypeMissingReturn : 1 occurrence
    // PhanUndeclaredClassCatch : 1 occurrence
    // PhanUndeclaredClassInCallable : 1 occurrence
    // PhanUndeclaredClassReference : 1 occurrence
    // PhanUndeclaredFunctionInCallable : 1 occurrence
    // PhanUndeclaredInterface : 1 occurrence
    // PhanUndeclaredStaticMethod : 1 occurrence
    // PhanUndeclaredStaticProperty : 1 occurrence

    // Currently, file_suppressions and directory_suppressions are the only supported suppressions
    'file_suppressions' => [
        'formats/Gantt/GanttPrinter.php' => ['MediaWikiNoEmptyIfDefined', 'PhanUndeclaredClass', 'PhanUndeclaredClassInstanceof', 'PhanUndeclaredClassMethod', 'PhanUndeclaredExtendedClass', 'PhanUndeclaredProperty', 'PhanUndeclaredTypeParameter'],
        'formats/Gantt/src/Gantt.php' => ['MediaWikiNoEmptyIfDefined', 'PhanTypeMismatchArgumentInternal'],
        'formats/Gantt/src/GanttTask.php' => ['MediaWikiNoEmptyIfDefined', 'PhanTypeMismatchForeach'],
        'formats/JitGraph/SRF_JitGraph.php' => ['PhanPluginUseReturnValueInternalKnown', 'PhanPossiblyUndeclaredVariable', 'PhanRedundantCondition', 'PhanTypeMismatchArgumentNullableInternal', 'PhanUndeclaredClass', 'PhanUndeclaredClassMethod', 'PhanUndeclaredExtendedClass', 'PhanUndeclaredProperty', 'PhanUndeclaredTypeParameter'],
        'formats/Prolog/PrologPrinter.php' => ['PhanCompatibleUnionType', 'PhanUndeclaredClass', 'PhanUndeclaredClassInstanceof', 'PhanUndeclaredClassMethod', 'PhanUndeclaredConstant', 'PhanUndeclaredExtendedClass', 'PhanUndeclaredMethod', 'PhanUndeclaredProperty', 'PhanUndeclaredTypeParameter'],
        'formats/array/SRF_Array.php' => ['MediaWikiNoEmptyIfDefined', 'PhanPossiblyUndeclaredVariable', 'PhanTypeMismatchArgumentNullable', 'PhanUndeclaredClass', 'PhanUndeclaredClassConstant', 'PhanUndeclaredClassInstanceof', 'PhanUndeclaredClassMethod', 'PhanUndeclaredConstant', 'PhanUndeclaredExtendedClass', 'PhanUndeclaredMethod', 'PhanUndeclaredProperty', 'PhanUndeclaredTypeParameter'],
        'formats/array/SRF_Hash.php' => ['PhanUndeclaredClassConstant', 'PhanUndeclaredClassMethod', 'PhanUndeclaredTypeParameter'],
        'formats/boilerplate/SRF_Boilerplate.php' => ['PhanTypeMismatchArgumentProbablyReal', 'PhanUndeclaredClass', 'PhanUndeclaredClassConstant', 'PhanUndeclaredClassMethod', 'PhanUndeclaredExtendedClass', 'PhanUndeclaredProperty', 'PhanUndeclaredTypeParameter'],
        'formats/calendar/EventCalendar.php' => ['PhanPluginDuplicateConditionalTernaryDuplication', 'PhanTypeMismatchDimAssignment', 'PhanUndeclaredClass', 'PhanUndeclaredExtendedClass', 'PhanUndeclaredMethod', 'PhanUndeclaredProperty', 'PhanUndeclaredTypeParameter'],
        'formats/calendar/SRFC_HistoricalDate.php' => ['PhanTypeInvalidUnaryOperandIncOrDec'],
        'formats/calendar/SRF_Calendar.php' => ['MediaWikiNoEmptyIfDefined', 'PhanImpossibleTypeComparison', 'PhanPossiblyUndeclaredVariable', 'PhanTypeInvalidLeftOperandOfAdd', 'PhanTypeInvalidLeftOperandOfNumericOp', 'PhanTypeMismatchArgumentNullableInternal', 'PhanTypeMismatchDimFetch', 'PhanUndeclaredClass', 'PhanUndeclaredClassConstant', 'PhanUndeclaredClassMethod', 'PhanUndeclaredConstant', 'PhanUndeclaredExtendedClass', 'PhanUndeclaredFunction', 'PhanUndeclaredProperty', 'PhanUndeclaredTypeParameter', 'PhanUndeclaredVariableDim'],
        'formats/carousel/Carousel.php' => ['MediaWikiNoEmptyIfDefined', 'PhanRedundantCondition', 'PhanTypeInvalidLeftOperandOfNumericOp', 'PhanTypeMismatchDimFetch', 'PhanTypeMismatchForeach', 'PhanUndeclaredClass', 'PhanUndeclaredExtendedClass', 'PhanUndeclaredMethod', 'PhanUndeclaredProperty', 'PhanUndeclaredTypeParameter', 'SecurityCheck-DoubleEscaped'],
        'formats/d3/SRF_D3Chart.php' => ['PhanPluginDuplicateConditionalTernaryDuplication', 'PhanPossiblyUndeclaredVariable', 'PhanTypeMismatchArgumentProbablyReal', 'PhanUndeclaredClass', 'PhanUndeclaredClassMethod', 'PhanUndeclaredExtendedClass', 'PhanUndeclaredProperty', 'PhanUndeclaredVariableDim'],
        'formats/dataframe/DataframePrinter.php' => ['PhanCompatibleUnionType', 'PhanParamSpecial1', 'PhanPossiblyUndeclaredVariable', 'PhanUndeclaredClass', 'PhanUndeclaredClassInstanceof', 'PhanUndeclaredClassMethod', 'PhanUndeclaredConstant', 'PhanUndeclaredExtendedClass', 'PhanUndeclaredMethod', 'PhanUndeclaredProperty', 'PhanUndeclaredTypeParameter', 'PhanUndeclaredVariableDim'],
        'formats/datatables/Api.php' => ['PhanParamTooMany', 'PhanTypeArraySuspiciousNullable', 'PhanTypePossiblyInvalidDimOffset', 'PhanTypeSuspiciousStringExpression', 'PhanUndeclaredClassConstant', 'PhanUndeclaredClassMethod', 'PhanUndeclaredConstant', 'PhanUndeclaredMethod', 'PhanUndeclaredVariableDim'],
        'formats/datatables/DataTables.php' => ['PhanPluginDuplicateExpressionAssignmentOperation', 'PhanPluginSimplifyExpressionBool', 'PhanPossiblyUndeclaredVariable', 'PhanTypeInvalidLeftOperandOfNumericOp', 'PhanTypeMismatchArgument', 'PhanTypeMismatchArgumentInternal', 'PhanTypeMismatchReturn', 'PhanTypeMismatchReturnNullable', 'PhanUndeclaredClass', 'PhanUndeclaredClassCatch', 'PhanUndeclaredClassConstant', 'PhanUndeclaredClassInstanceof', 'PhanUndeclaredClassMethod', 'PhanUndeclaredConstant', 'PhanUndeclaredExtendedClass', 'PhanUndeclaredMethod', 'PhanUndeclaredProperty', 'PhanUndeclaredStaticProperty', 'PhanUndeclaredTypeParameter', 'PhanUndeclaredTypeProperty', 'PhanUndeclaredVariableDim'],
        'formats/datatables/Hooks.php' => ['PhanUndeclaredClassConstant', 'PhanUndeclaredClassMethod'],
        'formats/datatables/QuerySegmentListProcessor.php' => ['PhanPluginDuplicateConditionalTernaryDuplication', 'PhanTypeMismatchDimFetch', 'PhanUndeclaredClassConstant', 'PhanUndeclaredClassMethod', 'PhanUndeclaredClassProperty', 'PhanUndeclaredClassReference', 'PhanUndeclaredTypeParameter', 'PhanUndeclaredTypeProperty'],
        'formats/datatables/SearchPanes.php' => ['MediaWikiNoEmptyIfDefined', 'PhanPossiblyUndeclaredVariable', 'PhanTypeMismatchArgument', 'PhanTypePossiblyInvalidDimOffset', 'PhanUndeclaredClassConstant', 'PhanUndeclaredClassMethod', 'PhanUndeclaredClassStaticProperty', 'PhanUndeclaredConstant', 'PhanUndeclaredTypeParameter', 'PhanUndeclaredTypeProperty'],
        'formats/dygraphs/SRF_Dygraphs.php' => ['PhanPossiblyUndeclaredVariable', 'PhanTypeMismatchArgumentProbablyReal', 'PhanUndeclaredClass', 'PhanUndeclaredClassConstant', 'PhanUndeclaredClassMethod', 'PhanUndeclaredConstant', 'PhanUndeclaredExtendedClass', 'PhanUndeclaredMethod', 'PhanUndeclaredProperty', 'PhanUndeclaredTypeParameter', 'PhanUndeclaredVariableDim'],
        'formats/filtered/src/Filtered.php' => ['PhanPluginUseReturnValueInternalKnown', 'PhanUndeclaredClass', 'PhanUndeclaredClassInCallable', 'PhanUndeclaredClassInstanceof', 'PhanUndeclaredClassMethod', 'PhanUndeclaredExtendedClass', 'PhanUndeclaredMethod', 'PhanUndeclaredProperty', 'PhanUndeclaredTypeParameter'],
        'formats/filtered/src/Filters/DistanceFilter.php' => ['PhanTypeSuspiciousStringExpression', 'PhanUndeclaredClassInstanceof', 'PhanUndeclaredClassMethod'],
        'formats/filtered/src/Filters/Filter.php' => ['PhanTypeMismatchReturnProbablyReal', 'PhanUndeclaredClassMethod', 'PhanUndeclaredTypeParameter', 'PhanUndeclaredTypeReturnType'],
        'formats/filtered/src/Filters/NumberFilter.php' => ['PhanUndeclaredClassInstanceof', 'PhanUndeclaredClassMethod'],
        'formats/filtered/src/ResultItem.php' => ['PhanUndeclaredClassInstanceof', 'PhanUndeclaredClassMethod', 'PhanUndeclaredTypeParameter', 'PhanUndeclaredTypeReturnType'],
        'formats/filtered/src/View/CalendarView.php' => ['PhanParamSignatureMismatch', 'PhanUndeclaredClassInstanceof', 'PhanUndeclaredClassMethod', 'PhanUndeclaredConstant'],
        'formats/filtered/src/View/ListView.php' => ['PhanUndeclaredClassMethod', 'PhanUndeclaredConstant', 'PhanUndeclaredTypeParameter'],
        'formats/filtered/src/View/MapView.php' => ['PhanParamSignatureMismatch', 'PhanPluginDuplicateConditionalNullCoalescing', 'PhanTypeMismatchForeach', 'PhanTypeMismatchReturn', 'PhanTypeMismatchReturnProbablyReal', 'PhanTypeSuspiciousStringExpression', 'PhanUndeclaredClassInstanceof', 'PhanUndeclaredClassMethod', 'PhanUndeclaredMethod'],
        'formats/filtered/src/View/TableView.php' => ['PhanTypeMismatchArgumentNullable', 'PhanUndeclaredClassConstant', 'PhanUndeclaredClassMethod', 'PhanUndeclaredConstant', 'PhanUndeclaredTypeParameter'],
        'formats/gallery/Gallery.php' => ['MediaWikiNoEmptyIfDefined', 'PhanRedundantCondition', 'PhanSuspiciousWeakTypeComparison', 'PhanUndeclaredClass', 'PhanUndeclaredClassConstant', 'PhanUndeclaredClassMethod', 'PhanUndeclaredConstant', 'PhanUndeclaredExtendedClass', 'PhanUndeclaredMethod', 'PhanUndeclaredProperty', 'PhanUndeclaredTypeParameter'],
        'formats/googlecharts/SRF_GoogleBar.php' => ['PhanUndeclaredClass', 'PhanUndeclaredClassMethod', 'PhanUndeclaredExtendedClass', 'PhanUndeclaredProperty', 'PhanUndeclaredTypeParameter'],
        'formats/googlecharts/SRF_GooglePie.php' => ['PhanUndeclaredClass', 'PhanUndeclaredClassMethod', 'PhanUndeclaredExtendedClass', 'PhanUndeclaredProperty', 'PhanUndeclaredTypeParameter'],
        'formats/graphviz/SRF_Process.php' => ['PhanImpossibleConditionInLoop', 'PhanNonClassMethodCall', 'PhanPossiblyUndeclaredVariable', 'PhanUndeclaredClass', 'PhanUndeclaredClassConstant', 'PhanUndeclaredClassMethod', 'PhanUndeclaredExtendedClass', 'PhanUndeclaredFunction', 'PhanUndeclaredFunctionInCallable', 'PhanUndeclaredMethod', 'PhanUndeclaredProperty', 'PhanUndeclaredTypeParameter'],
        'formats/incoming/SRF_Incoming.php' => ['PhanUndeclaredClass', 'PhanUndeclaredClassMethod', 'PhanUndeclaredClassProperty', 'PhanUndeclaredExtendedClass', 'PhanUndeclaredMethod', 'PhanUndeclaredProperty', 'PhanUndeclaredTypeParameter'],
        'formats/jqplot/SRF_jqPlot.php' => ['PhanTypeMismatchReturnProbablyReal', 'PhanUndeclaredExtendedClass'],
        'formats/jqplot/SRF_jqPlotChart.php' => ['PhanPluginDuplicateConditionalTernaryDuplication', 'PhanPluginDuplicateExpressionAssignmentOperation', 'PhanPossiblyUndeclaredVariable', 'PhanTypeMismatchArgumentProbablyReal', 'PhanUndeclaredClassMethod', 'PhanUndeclaredProperty', 'PhanUndeclaredStaticMethod', 'PhanUndeclaredVariableDim'],
        'formats/jqplot/SRF_jqPlotSeries.php' => ['PhanPluginDuplicateConditionalTernaryDuplication', 'PhanPossiblyUndeclaredVariable', 'PhanTypeMismatchArgumentProbablyReal', 'PhanTypeMissingReturn', 'PhanUndeclaredClass', 'PhanUndeclaredClassConstant', 'PhanUndeclaredClassMethod', 'PhanUndeclaredConstant', 'PhanUndeclaredExtendedClass', 'PhanUndeclaredMethod', 'PhanUndeclaredProperty', 'PhanUndeclaredTypeParameter', 'PhanUndeclaredVariableDim'],
        'formats/math/SRF_Math.php' => ['PhanPluginSimplifyExpressionBool', 'PhanRedundantCondition', 'PhanSuspiciousBinaryAddLists', 'PhanTypeMismatchDimFetch', 'PhanUndeclaredClassConstant', 'PhanUndeclaredClassMethod', 'PhanUndeclaredConstant', 'PhanUndeclaredExtendedClass', 'PhanUndeclaredProperty', 'PhanUndeclaredTypeParameter'],
        'formats/media/MediaPlayer.php' => ['PhanPossiblyUndeclaredVariable', 'PhanTypeMismatchArgumentNullable', 'PhanTypeMismatchArgumentProbablyReal', 'PhanUndeclaredClass', 'PhanUndeclaredClassConstant', 'PhanUndeclaredClassMethod', 'PhanUndeclaredExtendedClass', 'PhanUndeclaredMethod', 'PhanUndeclaredProperty', 'PhanUndeclaredTypeParameter'],
        'formats/slideshow/SRF_SlideShow.php' => ['PhanUndeclaredClass', 'PhanUndeclaredClassInstanceof', 'PhanUndeclaredClassMethod', 'PhanUndeclaredExtendedClass', 'PhanUndeclaredProperty', 'PhanUndeclaredTypeParameter'],
        'formats/slideshow/SRF_SlideShowApi.php' => ['PhanTypeSuspiciousStringExpression', 'PhanUndeclaredClassConstant', 'PhanUndeclaredClassMethod', 'PhanUndeclaredConstant'],
        'formats/sparkline/SRF_Sparkline.php' => ['PhanTypeMismatchArgumentProbablyReal', 'PhanUndeclaredClass', 'PhanUndeclaredClassMethod', 'PhanUndeclaredExtendedClass', 'PhanUndeclaredProperty'],
        'formats/spreadsheet/SpreadsheetPrinter.php' => ['PhanCompatibleUnionType', 'PhanTypeMismatchArgumentProbablyReal', 'PhanUndeclaredClass', 'PhanUndeclaredClassConstant', 'PhanUndeclaredClassInstanceof', 'PhanUndeclaredClassMethod', 'PhanUndeclaredConstant', 'PhanUndeclaredExtendedClass', 'PhanUndeclaredMethod', 'PhanUndeclaredProperty', 'PhanUndeclaredTypeParameter', 'PhanUndeclaredTypeReturnType', 'PhanUndeclaredTypeThrowsType'],
        'formats/tagcloud/TagCloud.php' => ['PhanPossiblyUndeclaredVariable', 'PhanUndeclaredClass', 'PhanUndeclaredClassConstant', 'PhanUndeclaredClassMethod', 'PhanUndeclaredExtendedClass', 'PhanUndeclaredMethod', 'PhanUndeclaredProperty', 'PhanUndeclaredTypeParameter'],
        'formats/time/SRF_Time.php' => ['MediaWikiNoEmptyIfDefined', 'PhanPossiblyUndeclaredVariable', 'PhanUndeclaredClass', 'PhanUndeclaredClassConstant', 'PhanUndeclaredClassMethod', 'PhanUndeclaredExtendedClass', 'PhanUndeclaredProperty', 'PhanUndeclaredTypeParameter'],
        'formats/timeline/SRF_Timeline.php' => ['PhanPossiblyUndeclaredVariable', 'PhanTypeInvalidUnaryOperandIncOrDec', 'PhanTypeMismatchArgument', 'PhanTypeMismatchDimFetch', 'PhanUndeclaredClass', 'PhanUndeclaredClassConstant', 'PhanUndeclaredClassMethod', 'PhanUndeclaredConstant', 'PhanUndeclaredExtendedClass', 'PhanUndeclaredFunction', 'PhanUndeclaredMethod', 'PhanUndeclaredProperty', 'PhanUndeclaredTypeParameter'],
        'formats/timeseries/SRF_Timeseries.php' => ['PhanPossiblyUndeclaredVariable', 'PhanTypeMismatchArgumentProbablyReal', 'PhanUndeclaredClass', 'PhanUndeclaredClassConstant', 'PhanUndeclaredClassMethod', 'PhanUndeclaredConstant', 'PhanUndeclaredExtendedClass', 'PhanUndeclaredMethod', 'PhanUndeclaredProperty', 'PhanUndeclaredTypeParameter', 'PhanUndeclaredVariableDim'],
        'formats/tree/TreeNode.php' => ['PhanUndeclaredClass', 'PhanUndeclaredClassMethod', 'PhanUndeclaredExtendedClass', 'PhanUndeclaredMethod', 'PhanUndeclaredTypeParameter', 'PhanUndeclaredTypeReturnType'],
        'formats/tree/TreeNodeVisitor.php' => ['PhanTypeMismatchArgument', 'PhanUndeclaredClassMethod', 'PhanUndeclaredConstant', 'PhanUndeclaredInterface', 'PhanUndeclaredMethod', 'PhanUndeclaredTypeParameter'],
        'formats/tree/TreeResultPrinter.php' => ['MediaWikiNoBaseException', 'MediaWikiNoEmptyIfDefined', 'PhanTypeMismatchArgument', 'PhanUndeclaredClass', 'PhanUndeclaredClassMethod', 'PhanUndeclaredExtendedClass', 'PhanUndeclaredMethod', 'PhanUndeclaredProperty', 'PhanUndeclaredTypeParameter', 'PhanUndeclaredTypeProperty', 'PhanUndeclaredTypeReturnType'],
        'formats/valuerank/SRF_ValueRank.php' => ['PhanUndeclaredClass', 'PhanUndeclaredClassConstant', 'PhanUndeclaredClassMethod', 'PhanUndeclaredConstant', 'PhanUndeclaredExtendedClass', 'PhanUndeclaredMethod', 'PhanUndeclaredProperty', 'PhanUndeclaredTypeParameter'],
        'formats/widget/SRF_ListWidget.php' => ['PhanUndeclaredClass', 'PhanUndeclaredClassMethod', 'PhanUndeclaredExtendedClass', 'PhanUndeclaredProperty', 'PhanUndeclaredTypeParameter'],
        'formats/widget/SRF_PageWidget.php' => ['PhanUndeclaredClass', 'PhanUndeclaredClassMethod', 'PhanUndeclaredExtendedClass', 'PhanUndeclaredProperty', 'PhanUndeclaredTypeParameter'],
        'src/BibTex/BibTexFileExportPrinter.php' => ['PhanCompatibleUnionType', 'PhanTypeMismatchReturn', 'PhanUndeclaredClass', 'PhanUndeclaredClassInstanceof', 'PhanUndeclaredClassMethod', 'PhanUndeclaredConstant', 'PhanUndeclaredExtendedClass', 'PhanUndeclaredMethod', 'PhanUndeclaredProperty', 'PhanUndeclaredTypeParameter', 'PhanUndeclaredTypeReturnType'],
        'src/BibTex/Item.php' => ['MediaWikiNoEmptyIfDefined'],
        'src/Graph/GraphFormatter.php' => ['MediaWikiNoEmptyIfDefined', 'PhanUndeclaredClassMethod', 'PhanUndeclaredTypeParameter', 'SecurityCheck-DoubleEscaped'],
        'src/Graph/GraphOptions.php' => ['PhanTypeMismatchProperty', 'PhanTypeMismatchReturn'],
        'src/Graph/GraphPrinter.php' => ['PhanTypeMismatchArgument', 'PhanUndeclaredClass', 'PhanUndeclaredClassConstant', 'PhanUndeclaredClassMethod', 'PhanUndeclaredConstant', 'PhanUndeclaredExtendedClass', 'PhanUndeclaredMethod', 'PhanUndeclaredTypeParameter'],
        'src/Outline/ListTreeBuilder.php' => ['PhanUndeclaredClassConstant', 'PhanUndeclaredConstant', 'PhanUndeclaredTypeParameter', 'PhanUndeclaredTypeProperty'],
        'src/Outline/OutlineResultPrinter.php' => ['PhanUndeclaredClass', 'PhanUndeclaredClassMethod', 'PhanUndeclaredConstant', 'PhanUndeclaredExtendedClass', 'PhanUndeclaredMethod', 'PhanUndeclaredProperty', 'PhanUndeclaredTypeParameter'],
        'src/Outline/TemplateBuilder.php' => ['PhanPluginDuplicateAdjacentStatement', 'PhanUndeclaredClassConstant', 'PhanUndeclaredConstant', 'PhanUndeclaredTypeParameter', 'PhanUndeclaredTypeProperty'],
        'src/ResourceFormatter.php' => ['PhanTypeMismatchArgument', 'PhanTypeMismatchReturnProbablyReal', 'PhanUndeclaredClassMethod', 'PhanUndeclaredTypeParameter'],
        'src/iCalendar/DateParser.php' => ['PhanUndeclaredClassMethod', 'PhanUndeclaredTypeParameter'],
        'src/iCalendar/IcalTimezoneFormatter.php' => ['PhanPluginDuplicateExpressionAssignment', 'PhanTypeComparisonFromArray'],
        'src/iCalendar/iCalendarFileExportPrinter.php' => ['PhanCompatibleUnionType', 'PhanUndeclaredClass', 'PhanUndeclaredClassConstant', 'PhanUndeclaredClassMethod', 'PhanUndeclaredConstant', 'PhanUndeclaredExtendedClass', 'PhanUndeclaredMethod', 'PhanUndeclaredProperty', 'PhanUndeclaredTypeParameter'],
        'src/vCard/vCard.php' => ['PhanTypePossiblyInvalidDimOffset'],
        'src/vCard/vCardFileExportPrinter.php' => ['PhanCompatibleUnionType', 'PhanTypeMismatchArgument', 'PhanUndeclaredClass', 'PhanUndeclaredClassConstant', 'PhanUndeclaredClassMethod', 'PhanUndeclaredConstant', 'PhanUndeclaredExtendedClass', 'PhanUndeclaredMethod', 'PhanUndeclaredProperty', 'PhanUndeclaredTypeParameter', 'PhanUndeclaredVariableDim'],
    ],
    // 'directory_suppressions' => ['src/directory_name' => ['PhanIssueName1', 'PhanIssueName2']] can be manually added if needed.
    // (directory_suppressions will currently be ignored by subsequent calls to --save-baseline, but may be preserved in future Phan releases)
];
