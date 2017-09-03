(function e(t,n,r){function s(o,u){if(!n[o]){if(!t[o]){var a=typeof require=="function"&&require;if(!u&&a)return a(o,!0);if(i)return i(o,!0);var f=new Error("Cannot find module '"+o+"'");throw f.code="MODULE_NOT_FOUND",f}var l=n[o]={exports:{}};t[o][0].call(l.exports,function(e){var n=t[o][1][e];return s(n?n:e)},l,l.exports,e,t,n,r)}return n[o].exports}var i=typeof require=="function"&&require;for(var o=0;o<r.length;o++)s(r[o]);return s})({1:[function(require,module,exports){
"use strict";
exports.__esModule = true;
var View_1 = require("./View/View");
var Controller = (function () {
    function Controller(target, data, printRequests) {
        this.target = undefined;
        this.views = {};
        this.filters = {};
        this.currentView = undefined;
        this.target = target;
        this.data = data;
        this.printRequests = printRequests;
        for (var rowId in this.data) {
            if (!this.data[rowId].hasOwnProperty('visible')) {
                this.data[rowId].visible = {};
            }
        }
    }
    Controller.prototype.getData = function () {
        return this.data;
    };
    Controller.prototype.getPrintRequests = function () {
        return this.printRequests;
    };
    Controller.prototype.getPath = function () {
        return srf.settings.get('srfgScriptPath') + '/formats/filtered/resources/';
    };
    Controller.prototype.attachView = function (viewid, view) {
        this.views[viewid] = view;
        if (this.currentView === undefined) {
            this.currentView = view;
            view.show();
        }
        else {
            view.hide();
        }
        return this;
    };
    Controller.prototype.getView = function (viewId) {
        return this.views[viewId];
    };
    Controller.prototype.attachFilter = function (filter) {
        var filterId = filter.getId();
        this.filters[filterId] = filter;
        this.onFilterUpdated(filterId);
        return this;
    };
    Controller.prototype.getFilter = function (filterId) {
        return this.filters[filterId];
    };
    Controller.prototype.show = function () {
        this.initializeFilters();
        this.target.show();
        this.switchToView(this.currentView);
    };
    Controller.prototype.switchToView = function (view) {
        if (this.currentView instanceof View_1.View) {
            this.currentView.hide();
        }
        this.currentView = view;
        if (this.currentView instanceof View_1.View) {
            view.show();
        }
    };
    Controller.prototype.initializeFilters = function () {
        var toShow = [];
        var toHide = [];
        for (var rowId in this.data) {
            for (var filterId in this.filters) {
                this.data[rowId].visible[filterId] = this.filters[filterId].isVisible(rowId);
            }
            if (this.isVisible(rowId)) {
                toShow.push(rowId);
            }
            else {
                toHide.push(rowId);
            }
        }
        this.hideRows(toHide);
        this.showRows(toShow);
    };
    Controller.prototype.onViewSelected = function (viewID) {
        this.switchToView(this.views[viewID]);
    };
    Controller.prototype.onFilterUpdated = function (filterId) {
        var toShow = [];
        var toHide = [];
        for (var rowId in this.data) {
            var oldVisible = this.data[rowId].visible[filterId];
            var newVisible = this.filters[filterId].isVisible(rowId);
            if (oldVisible !== newVisible) {
                this.data[rowId].visible[filterId] = newVisible;
                if (newVisible && this.isVisible(rowId)) {
                    toShow.push(rowId);
                    // controller.showRow( rowId );
                }
                else {
                    toHide.push(rowId);
                    // controller.hideRow( rowId );
                }
            }
        }
        this.hideRows(toHide);
        this.showRows(toShow);
    };
    Controller.prototype.isVisible = function (rowId) {
        for (var filterId in this.data[rowId].visible) {
            if (!this.data[rowId].visible[filterId]) {
                return false;
            }
        }
        return true;
    };
    Controller.prototype.hideRows = function (rowIds) {
        if (rowIds.length === 0) {
            return;
        }
        for (var viewId in this.views) {
            this.views[viewId].hideRows(rowIds);
        }
    };
    Controller.prototype.showRows = function (rowIds) {
        if (rowIds.length === 0) {
            return;
        }
        for (var viewId in this.views) {
            this.views[viewId].showRows(rowIds);
        }
    };
    return Controller;
}());
exports.Controller = Controller;

},{"./View/View":11}],2:[function(require,module,exports){
"use strict";
var __extends = (this && this.__extends) || (function () {
    var extendStatics = Object.setPrototypeOf ||
        ({ __proto__: [] } instanceof Array && function (d, b) { d.__proto__ = b; }) ||
        function (d, b) { for (var p in b) if (b.hasOwnProperty(p)) d[p] = b[p]; };
    return function (d, b) {
        extendStatics(d, b);
        function __() { this.constructor = d; }
        d.prototype = b === null ? Object.create(b) : (__.prototype = b.prototype, new __());
    };
})();
exports.__esModule = true;
var Filter_1 = require("./Filter");
var DistanceFilter = (function (_super) {
    __extends(DistanceFilter, _super);
    function DistanceFilter() {
        var _this = _super !== null && _super.apply(this, arguments) || this;
        _this.earthRadiusValue = DistanceFilter.earthRadius.km;
        _this.filterValue = 0;
        return _this;
    }
    DistanceFilter.prototype.init = function () {
        var values = this.controller.getData();
        var origin = this.options['origin'];
        if (!(origin !== undefined && origin.hasOwnProperty('lat') && origin.hasOwnProperty('lng'))) {
            this.target.detach();
            return;
        }
        var unit = 'km';
        if (this.options['unit'] && DistanceFilter.earthRadius[this.options['unit']]) {
            unit = this.options['unit'];
        }
        this.earthRadiusValue = DistanceFilter.earthRadius[unit];
        var maxValue = this.updateDistances(origin);
        var precision = Math.pow(10, (Math.floor(Math.log(maxValue) * Math.LOG10E) - 1));
        if (this.options['max'] !== undefined && this.options['max'] > maxValue) {
            maxValue = this.options['max'];
        }
        else {
            maxValue = Math.ceil(maxValue / precision) * precision;
        }
        this.filterValue = this.options['initial value'] ? Math.min(this.options['initial value'], maxValue) : maxValue;
        // build filter controls
        var filtercontrols = this.target;
        filtercontrols
            .append('<div class="filtered-distance-label"><span>' + this.options['label'] + '</span></div>');
        filtercontrols = this.addControlForCollapsing(filtercontrols);
        var readout = $('<div class="filtered-distance-readout">' + this.filterValue + '</div>');
        var table = $('<table class="filtered-distance-table"><tbody><tr><td class="filtered-distance-min-cell">0</td>' +
            '<td class="filtered-distance-slider-cell"><div class="filtered-distance-slider"></div></td>' +
            '<td class="filtered-distance-max-cell">' + maxValue + '</td></tr>' +
            '<tr><td colspan=3 class="filtered-distance-unit-cell">' + unit + '</td></tr></tbody></table>');
        filtercontrols.append(table);
        var that = this;
        mw.loader.using('jquery.ui.slider').then(function () {
            table.find('.filtered-distance-slider')
                .slider({
                animate: true,
                max: maxValue,
                value: that.filterValue,
                step: precision / 100
            })
                .on('slidechange', undefined, { 'filter': that }, function (eventObject, ui) {
                eventObject.data.ui = ui;
                eventObject.data.filter.onFilterUpdated(eventObject);
            })
                .on('slide', undefined, { 'filter': that }, function (eventObject, ui) {
                readout.text(ui.value);
            })
                .find('.ui-slider-handle')
                .append(readout);
        });
        return this;
    };
    DistanceFilter.prototype.updateDistances = function (origin) {
        var _this = this;
        var values = this.controller.getData();
        var max = 1;
        var prId = this.printrequestId;
        for (var rowId in values) {
            if (values[rowId].data.hasOwnProperty(this.filterId)) {
                var distances = values[rowId].data[this.filterId].positions.map(function (pos) { return _this.distance(origin, pos); });
                var dist = Math.min.apply(Math, distances);
                values[rowId].data[this.filterId].distance = dist;
                max = Math.max(max, dist);
            }
            else {
                values[rowId].data[this.filterId].distance = Infinity;
            }
        }
        return max;
    };
    DistanceFilter.prototype.onFilterUpdated = function (eventObject) {
        this.filterValue = eventObject.data.ui.value;
        this.controller.onFilterUpdated(this.getId());
    };
    DistanceFilter.prototype.distance = function (a, b) {
        var DEG2RAD = Math.PI / 180.0;
        function squared(x) {
            return x * x;
        }
        var f = squared(Math.sin((b.lat - a.lat) * DEG2RAD / 2.0)) +
            Math.cos(a.lat * DEG2RAD) * Math.cos(b.lat * DEG2RAD) *
                squared(Math.sin((b.lng - a.lng) * DEG2RAD / 2.0));
        return this.earthRadiusValue * 2 * Math.atan2(Math.sqrt(f), Math.sqrt(1 - f));
    };
    DistanceFilter.prototype.isVisible = function (rowId) {
        return this.controller.getData()[rowId].data[this.filterId].distance <= this.filterValue;
    };
    return DistanceFilter;
}(Filter_1.Filter));
DistanceFilter.earthRadius = {
    m: 6371008.8,
    km: 6371.0088,
    mi: 3958.7613,
    nm: 3440.0695,
    Å: 63710088000000000
};
exports.DistanceFilter = DistanceFilter;

},{"./Filter":3}],3:[function(require,module,exports){
"use strict";
exports.__esModule = true;
var Filter = (function () {
    function Filter(filterId, target, printrequestId, controller, options) {
        this.target = undefined;
        this.options = undefined;
        this.target = target;
        this.filterId = filterId;
        this.printrequestId = printrequestId;
        this.controller = controller;
        this.options = options || {};
    }
    Filter.prototype.init = function () { };
    ;
    Filter.prototype.isVisible = function (rowId) {
        return true;
    };
    Filter.prototype.getId = function () {
        return this.filterId;
    };
    Filter.prototype.addControlForCollapsing = function (filtercontrols) {
        var collapsible = this.options.hasOwnProperty('collapsible') ? this.options['collapsible'] : undefined;
        if (collapsible === 'collapsed' || collapsible === 'uncollapsed') {
            var showControl_1 = $('<span class="filtered-show">');
            var hideControl_1 = $('<span class="filtered-hide">');
            filtercontrols
                .prepend(showControl_1)
                .prepend(hideControl_1);
            filtercontrols = $('<div class="filtered-collapsible">')
                .appendTo(filtercontrols);
            var outercontrols_1 = filtercontrols;
            showControl_1.click(function () {
                outercontrols_1.slideDown();
                showControl_1.hide();
                hideControl_1.show();
            });
            hideControl_1.click(function () {
                outercontrols_1.slideUp();
                showControl_1.show();
                hideControl_1.hide();
            });
            if (collapsible === 'collapsed') {
                hideControl_1.hide();
                outercontrols_1.slideUp(0);
            }
            else {
                showControl_1.hide();
            }
        }
        return filtercontrols;
    };
    return Filter;
}());
exports.Filter = Filter;

},{}],4:[function(require,module,exports){
"use strict";
var __extends = (this && this.__extends) || (function () {
    var extendStatics = Object.setPrototypeOf ||
        ({ __proto__: [] } instanceof Array && function (d, b) { d.__proto__ = b; }) ||
        function (d, b) { for (var p in b) if (b.hasOwnProperty(p)) d[p] = b[p]; };
    return function (d, b) {
        extendStatics(d, b);
        function __() { this.constructor = d; }
        d.prototype = b === null ? Object.create(b) : (__.prototype = b.prototype, new __());
    };
})();
exports.__esModule = true;
var Filter_1 = require("./Filter");
var NumberFilter = (function (_super) {
    __extends(NumberFilter, _super);
    function NumberFilter() {
        var _this = _super !== null && _super.apply(this, arguments) || this;
        _this.MODE_RANGE = 0;
        _this.MODE_MIN = 1;
        _this.MODE_MAX = 2;
        _this.MODE_SELECT = 3;
        _this.filterValueUpper = 0;
        _this.filterValueLower = 0;
        _this.mode = _this.MODE_RANGE;
        return _this;
    }
    NumberFilter.prototype.init = function () {
        var _a = this.getRange(), minValue = _a[0], maxValue = _a[1];
        var precision = Math.pow(10, (Math.floor(Math.log(maxValue - minValue) * Math.LOG10E) - 1));
        var requestedMax = this.options['max'];
        if (requestedMax !== undefined && !isNaN(Number(requestedMax))) {
            maxValue = Math.max(requestedMax, maxValue);
        }
        else {
            maxValue = Math.ceil(maxValue / precision) * precision;
        }
        var requestedMin = this.options['min'];
        if (requestedMin !== undefined && !isNaN(Number(requestedMin))) {
            minValue = Math.min(requestedMin, minValue);
        }
        else {
            minValue = Math.floor(minValue / precision) * precision;
        }
        var step = this.options['step'];
        if (step === undefined || isNaN(Number(step))) {
            step = precision / 10;
        }
        this.filterValueUpper = maxValue;
        this.filterValueLower = minValue;
        // build filter controls
        var filtercontrols = this.target;
        filtercontrols
            .append('<div class="filtered-number-label"><span>' + this.options['label'] + '</span></div>');
        filtercontrols = this.addControlForCollapsing(filtercontrols);
        var readoutLeft = $('<div class="filtered-number-readout">');
        var readoutRight = $('<div class="filtered-number-readout">');
        var caption = '';
        if (this.options['caption']) {
            caption = '<tr><td colspan=3 class="filtered-number-caption-cell">' + this.options['caption'] + '</td></tr>';
        }
        var table = $('<table class="filtered-number-table"><tbody><tr>' +
            '<td class="filtered-number-min-cell">' + minValue + '</td>' +
            '<td class="filtered-number-slider-cell"></td>' +
            '<td class="filtered-number-max-cell">' + maxValue + '</td></tr>' +
            caption +
            '</tbody></table>');
        var sliderContainer = $('<div class="filtered-number-slider">');
        var lowerHandle = $('<div class="ui-slider-handle ui-slider-handle-lower">');
        var upperHandle = $('<div class="ui-slider-handle ui-slider-handle-upper">');
        var selectHandle = $('<div class="ui-slider-handle ui-slider-handle-select">');
        var slideroptions = {
            animate: true,
            min: minValue,
            max: maxValue,
            step: step
        };
        switch (this.options['sliders']) {
            case 'max':
                this.mode = this.MODE_MAX;
                slideroptions.range = 'min';
                slideroptions.value = maxValue;
                readoutLeft.text(maxValue);
                upperHandle.append(readoutLeft);
                sliderContainer.append(upperHandle);
                break;
            case 'min':
                this.mode = this.MODE_MIN;
                slideroptions.range = 'max';
                slideroptions.value = minValue;
                readoutLeft.text(minValue);
                lowerHandle.append(readoutLeft);
                sliderContainer.append(lowerHandle);
                break;
            case 'select':
                this.mode = this.MODE_SELECT;
                slideroptions.value = maxValue;
                readoutLeft.text(maxValue);
                selectHandle.append(readoutLeft);
                sliderContainer.append(selectHandle);
                this.filterValueUpper = maxValue;
                this.filterValueLower = maxValue;
                break;
            default:
                this.mode = this.MODE_RANGE;
                slideroptions.range = true;
                slideroptions.values = [minValue, maxValue];
                readoutLeft.text(minValue);
                lowerHandle.append(readoutLeft);
                readoutRight.text(maxValue);
                upperHandle.append(readoutRight);
                sliderContainer.append(lowerHandle).append(upperHandle);
        }
        filtercontrols.append(table);
        table
            .find('.filtered-number-slider-cell')
            .append(sliderContainer);
        var that = this;
        mw.loader.using('jquery.ui.slider').then(function () {
            sliderContainer.slider(slideroptions)
                .on('slidechange', undefined, { 'filter': that }, function (eventObject, ui) {
                eventObject.data.ui = ui;
                eventObject.data.filter.onFilterUpdated(eventObject);
            })
                .on('slide', undefined, { 'filter': that }, function (eventObject, ui) {
                ui.handle.firstElementChild.innerHTML = ui.value.toString();
            });
        });
        return this;
    };
    NumberFilter.prototype.getRange = function () {
        var rows = this.controller.getData();
        var min = Infinity;
        var max = -Infinity;
        for (var rowId in rows) {
            if (rows[rowId].data.hasOwnProperty(this.filterId)) {
                var values = rows[rowId].data[this.filterId].values;
                min = Math.min.apply(Math, [min].concat(values));
                max = Math.max.apply(Math, [max].concat(values));
            }
        }
        return [min, max];
    };
    NumberFilter.prototype.onFilterUpdated = function (eventObject) {
        switch (this.mode) {
            case this.MODE_RANGE:
                this.filterValueLower = eventObject.data.ui.values[0];
                this.filterValueUpper = eventObject.data.ui.values[1];
                break;
            case this.MODE_MIN:
                this.filterValueLower = eventObject.data.ui.value;
                break;
            case this.MODE_MAX:
                this.filterValueUpper = eventObject.data.ui.value;
                break;
            case this.MODE_SELECT:
                this.filterValueLower = eventObject.data.ui.value;
                this.filterValueUpper = eventObject.data.ui.value;
                break;
        }
        this.controller.onFilterUpdated(this.getId());
    };
    NumberFilter.prototype.isVisible = function (rowId) {
        var rowdata = this.controller.getData()[rowId].data;
        if (rowdata.hasOwnProperty(this.filterId)) {
            for (var _i = 0, _a = rowdata[this.filterId].values; _i < _a.length; _i++) {
                var value = _a[_i];
                if (value >= this.filterValueLower && value <= this.filterValueUpper) {
                    return true;
                }
            }
        }
        return false;
    };
    return NumberFilter;
}(Filter_1.Filter));
exports.NumberFilter = NumberFilter;

},{"./Filter":3}],5:[function(require,module,exports){
"use strict";
var __extends = (this && this.__extends) || (function () {
    var extendStatics = Object.setPrototypeOf ||
        ({ __proto__: [] } instanceof Array && function (d, b) { d.__proto__ = b; }) ||
        function (d, b) { for (var p in b) if (b.hasOwnProperty(p)) d[p] = b[p]; };
    return function (d, b) {
        extendStatics(d, b);
        function __() { this.constructor = d; }
        d.prototype = b === null ? Object.create(b) : (__.prototype = b.prototype, new __());
    };
})();
exports.__esModule = true;
var Filter_1 = require("./Filter");
var ValueFilter = (function (_super) {
    __extends(ValueFilter, _super);
    function ValueFilter() {
        var _this = _super !== null && _super.apply(this, arguments) || this;
        _this.values = {};
        _this.visibleValues = [];
        _this._useOr = true;
        return _this;
    }
    ValueFilter.prototype.init = function () {
        this.values = this.getSortedValues();
        this.buildControl();
    };
    ValueFilter.prototype.useOr = function (useOr) {
        this._useOr = useOr;
        this.controller.onFilterUpdated(this.getId());
    };
    ValueFilter.prototype.getSortedValues = function () {
        /** Map of value => label distinct values */
        var distinctValues = {};
        if (this.options.hasOwnProperty('values')) {
            return this.options['values'].reduce(function (values, item) {
                values[item] = item;
                return values;
            }, {});
        }
        else {
            // build filter values from available values in result set
            var data = this.controller.getData();
            for (var id in data) {
                var printoutValues = data[id]['printouts'][this.printrequestId]['values'];
                var printoutFormattedValues = data[id]['printouts'][this.printrequestId]['formatted values'];
                for (var i in printoutValues) {
                    var printoutFormattedValue = printoutFormattedValues[i];
                    if (printoutFormattedValue.indexOf('<a') > -1) {
                        printoutFormattedValue = /<a.*>(.*?)<\/a>/.exec(printoutFormattedValue)[1];
                    }
                    distinctValues[printoutValues[i]] = printoutFormattedValue;
                }
            }
        }
        return distinctValues;
    };
    ValueFilter.prototype.buildControl = function () {
        var filtercontrols = this.target;
        // insert the label of the printout this filter filters on
        filtercontrols.append('<div class="filtered-value-label"><span>' + this.options['label'] + '</span></div>');
        filtercontrols = this.addControlForCollapsing(filtercontrols);
        this.addControlForSwitches(filtercontrols);
        var height = this.options.hasOwnProperty('height') ? this.options['height'] : undefined;
        if (height !== undefined) {
            filtercontrols = $('<div class="filtered-value-scrollable">')
                .appendTo(filtercontrols);
            filtercontrols.height(height);
        }
        // insert options (checkboxes and labels) and attach event handlers
        for (var _i = 0, _a = Object.keys(this.values).sort(); _i < _a.length; _i++) {
            var value = _a[_i];
            var option = $('<div class="filtered-value-option">');
            var checkbox = $('<input type="checkbox" class="filtered-value-value" value="' + value + '"  >');
            // attach event handler
            checkbox
                .on('change', undefined, { 'filter': this }, function (eventObject) {
                eventObject.data.filter.onFilterUpdated(eventObject);
            });
            // Try to get label, if not fall back to value id
            var label = this.values[value] || value;
            option.append(checkbox).append(label);
            filtercontrols.append(option);
        }
    };
    ValueFilter.prototype.addControlForSwitches = function (filtercontrols) {
        // insert switches
        var switches = this.options.hasOwnProperty('switches') ? this.options['switches'] : undefined;
        if (switches !== undefined && switches.length > 0) {
            var switchControls = $('<div class="filtered-value-switches">');
            if ($.inArray('and or', switches) >= 0) {
                var andorControl = $('<div class="filtered-value-andor">');
                var andControl = $('<input type="radio" name="filtered-value-' +
                    this.printrequestId + '"  class="filtered-value-and ' + this.printrequestId + '" value="and">');
                var orControl_1 = $('<input type="radio" name="filtered-value-' +
                    this.printrequestId + '"  class="filtered-value-or ' + this.printrequestId + '" value="or" checked>');
                andControl
                    .add(orControl_1)
                    .on('change', undefined, { 'filter': this }, function (eventObject) {
                    eventObject.data.filter.useOr(orControl_1.is(':checked'));
                });
                andorControl
                    .append(orControl_1)
                    .append(' OR ')
                    .append(andControl)
                    .append(' AND ')
                    .appendTo(switchControls);
            }
            filtercontrols.append(switchControls);
        }
    };
    ValueFilter.prototype.isVisible = function (rowId) {
        if (this.visibleValues.length === 0) {
            return true;
        }
        var values = this.controller.getData()[rowId].printouts[this.printrequestId].values;
        if (this._useOr) {
            for (var _i = 0, _a = this.visibleValues; _i < _a.length; _i++) {
                var expectedValue = _a[_i];
                if (values.indexOf(expectedValue) >= 0) {
                    return true;
                }
            }
            return false;
        }
        else {
            for (var _b = 0, _c = this.visibleValues; _b < _c.length; _b++) {
                var expectedValue = _c[_b];
                if (values.indexOf(expectedValue) < 0) {
                    return false;
                }
            }
            return true;
        }
    };
    ValueFilter.prototype.onFilterUpdated = function (eventObject) {
        var target = $(eventObject.target);
        var value = target.val();
        var index = this.visibleValues.indexOf(value);
        var isChecked = target.is(':checked');
        if (isChecked && index === -1) {
            this.visibleValues.push(value);
        }
        else if (!isChecked && index >= 0) {
            this.visibleValues.splice(index, 1);
        }
        this.controller.onFilterUpdated(this.getId());
    };
    return ValueFilter;
}(Filter_1.Filter));
exports.ValueFilter = ValueFilter;

},{"./Filter":3}],6:[function(require,module,exports){
"use strict";
exports.__esModule = true;
var Controller_1 = require("./Controller");
var ViewSelector_1 = require("./ViewSelector");
var View_1 = require("./View/View");
var ListView_1 = require("./View/ListView");
var TableView_1 = require("./View/TableView");
var MapView_1 = require("./View/MapView");
var CalendarView_1 = require("./View/CalendarView");
var ValueFilter_1 = require("./Filter/ValueFilter");
var DistanceFilter_1 = require("./Filter/DistanceFilter");
var NumberFilter_1 = require("./Filter/NumberFilter");
/**
 * Central Filtered class
 *
 * Factory to setup everyhting else
 */
var Filtered = (function () {
    /**
     *
     * @param target
     * @param config
     */
    function Filtered(target, config) {
        this.viewTypes = {
            table: TableView_1.TableView,
            list: ListView_1.ListView,
            map: MapView_1.MapView,
            calendar: CalendarView_1.CalendarView
        };
        this.filterTypes = {
            value: ValueFilter_1.ValueFilter,
            distance: DistanceFilter_1.DistanceFilter,
            number: NumberFilter_1.NumberFilter
        };
        this.config = config;
        this.target = target;
    }
    /**
     *
     */
    Filtered.prototype.run = function () {
        this.showStartupMessage();
        this.startDeferred();
    };
    /**
     *
     */
    Filtered.prototype.showStartupMessage = function () {
        // this.target.text( "Loading..." );
        // TODO: Use spinner from srf.util
    };
    /**
     *
     */
    Filtered.prototype.startDeferred = function () {
        setTimeout(this.start(), 0);
    };
    Filtered.prototype.start = function () {
        var controller = new Controller_1.Controller(this.target, this.config.data, this.config.printrequests);
        this.attachFilters(controller, this.target.find('div.filtered-filters'));
        this.attachViewSelector(controller, this.target.find('div.filtered-views-selectors-container'));
        this.attachViews(controller, this.target.find('div.filtered-views-container'));
        // lift-off
        controller.show();
    };
    Filtered.prototype.attachFilters = function (controller, filtersContainer) {
        for (var prId in this.config.printrequests) {
            var pr = this.config.printrequests[prId];
            if (pr.hasOwnProperty('filters')) {
                for (var filterid in pr.filters) {
                    if (pr.filters.hasOwnProperty(filterid) &&
                        pr.filters[filterid].hasOwnProperty('type') &&
                        this.filterTypes.hasOwnProperty(pr.filters[filterid].type)) {
                        //  target: JQuery, printrequest: string,
                        // controller: Controller, options?: Options
                        var filter = new this.filterTypes[pr.filters[filterid].type](filterid, filtersContainer.children('#' + filterid), prId, controller, pr.filters[filterid]);
                        filter.init();
                        controller.attachFilter(filter);
                    }
                }
            }
        }
    };
    Filtered.prototype.attachViewSelector = function (controller, viewSelectorContainer) {
        var viewSelector = new ViewSelector_1.ViewSelector(viewSelectorContainer, Object.keys(this.config.views), controller);
        viewSelector.init();
    };
    Filtered.prototype.attachViews = function (controller, viewsContainer) {
        // attach views
        for (var viewid in this.config.views) {
            var viewtype = this.config.views[viewid]['type'];
            var viewHandlerClass = this.viewTypes.hasOwnProperty(viewtype) ? this.viewTypes[viewtype] : View_1.View;
            var view = new viewHandlerClass(viewid, viewsContainer.children('#' + viewid), controller, this.config.views[viewid]);
            view.init();
            controller.attachView(viewid, view);
        }
    };
    return Filtered;
}());
exports.Filtered = Filtered;

},{"./Controller":1,"./Filter/DistanceFilter":2,"./Filter/NumberFilter":4,"./Filter/ValueFilter":5,"./View/CalendarView":7,"./View/ListView":8,"./View/MapView":9,"./View/TableView":10,"./View/View":11,"./ViewSelector":12}],7:[function(require,module,exports){
"use strict";
var __extends = (this && this.__extends) || (function () {
    var extendStatics = Object.setPrototypeOf ||
        ({ __proto__: [] } instanceof Array && function (d, b) { d.__proto__ = b; }) ||
        function (d, b) { for (var p in b) if (b.hasOwnProperty(p)) d[p] = b[p]; };
    return function (d, b) {
        extendStatics(d, b);
        function __() { this.constructor = d; }
        d.prototype = b === null ? Object.create(b) : (__.prototype = b.prototype, new __());
    };
})();
exports.__esModule = true;
var View_1 = require("./View");
var CalendarView = (function (_super) {
    __extends(CalendarView, _super);
    function CalendarView() {
        return _super !== null && _super.apply(this, arguments) || this;
    }
    CalendarView.prototype.getI18N = function () {
        return {
            monthNames: [mw.msg('january'), mw.msg('february'), mw.msg('march'),
                mw.msg('april'), mw.msg('may_long'), mw.msg('june'),
                mw.msg('july'), mw.msg('august'), mw.msg('september'),
                mw.msg('october'), mw.msg('november'), mw.msg('december')
            ],
            monthNamesShort: [mw.msg('jan'), mw.msg('feb'), mw.msg('mar'),
                mw.msg('apr'), mw.msg('may'), mw.msg('jun'),
                mw.msg('jul'), mw.msg('aug'), mw.msg('sep'),
                mw.msg('oct'), mw.msg('nov'), mw.msg('dec')
            ],
            dayNames: [mw.msg('sunday'), mw.msg('monday'), mw.msg('tuesday'),
                mw.msg('wednesday'), mw.msg('thursday'), mw.msg('friday'), mw.msg('saturday')
            ],
            dayNamesShort: [mw.msg('sun'), mw.msg('mon'), mw.msg('tue'),
                mw.msg('wed'), mw.msg('thu'), mw.msg('fri'), mw.msg('sat')
            ],
            buttonText: {
                today: mw.msg('srf-ui-eventcalendar-label-today'),
                month: mw.msg('srf-ui-eventcalendar-label-month'),
                week: mw.msg('srf-ui-eventcalendar-label-week'),
                day: mw.msg('srf-ui-eventcalendar-label-day')
            },
            allDayText: mw.msg('srf-ui-eventcalendar-label-allday'),
            timeFormat: {
                '': mw.msg('srf-ui-eventcalendar-format-time'),
                agenda: mw.msg('srf-ui-eventcalendar-format-time-agenda')
            },
            axisFormat: mw.msg('srf-ui-eventcalendar-format-axis'),
            titleFormat: {
                month: mw.msg('srf-ui-eventcalendar-format-title-month'),
                week: mw.msg('srf-ui-eventcalendar-format-title-week'),
                day: mw.msg('srf-ui-eventcalendar-format-title-day')
            },
            columnFormat: {
                month: mw.msg('srf-ui-eventcalendar-format-column-month'),
                week: mw.msg('srf-ui-eventcalendar-format-column-week'),
                day: mw.msg('srf-ui-eventcalendar-format-column-day')
            }
        };
    };
    CalendarView.prototype.init = function () {
        var _i18n = this.getI18N();
        // initialize the calendar
        this.target.fullCalendar({
            firstDay: this.options.firstDay,
            isRTL: this.options.isRTL,
            monthNames: _i18n.monthNames,
            monthNamesShort: _i18n.monthNamesShort,
            dayNames: _i18n.dayNames,
            dayNamesShort: _i18n.dayNamesShort,
            buttonText: _i18n.buttonText,
            allDayText: _i18n.allDayText,
            timeFormat: _i18n.timeFormat,
            titleFormat: _i18n.titleFormat,
            columnFormat: _i18n.columnFormat
        });
        return this;
    };
    CalendarView.prototype.getEvent = function (rowId, rowData) {
        var eventdata = {
            id: rowId,
            title: rowData['title'],
            start: rowData['start'],
            className: rowId
        };
        if (rowData.hasOwnProperty('end')) {
            eventdata['end'] = rowData['end'];
        }
        if (rowData.hasOwnProperty('url')) {
            eventdata['url'] = rowData['url'];
        }
        return eventdata;
    };
    CalendarView.prototype.showRows = function (rowIds) {
        var _this = this;
        var events = [];
        rowIds.forEach(function (rowId) {
            var rowData = _this.controller.getData()[rowId].data[_this.id];
            if (rowData.hasOwnProperty('start')) {
                events.push(_this.getEvent(rowId, rowData));
            }
        });
        this.target.fullCalendar('addEventSource', events);
    };
    CalendarView.prototype.hideRows = function (rowIds) {
        this.target.fullCalendar('removeEvents', function (e) { return (rowIds.indexOf(e._id) >= 0); });
    };
    CalendarView.prototype.show = function () {
        _super.prototype.show.call(this);
        this.target.fullCalendar('render');
    };
    CalendarView.prototype.hide = function () {
        return _super.prototype.hide.call(this);
    };
    return CalendarView;
}(View_1.View));
exports.CalendarView = CalendarView;

},{"./View":11}],8:[function(require,module,exports){
"use strict";
var __extends = (this && this.__extends) || (function () {
    var extendStatics = Object.setPrototypeOf ||
        ({ __proto__: [] } instanceof Array && function (d, b) { d.__proto__ = b; }) ||
        function (d, b) { for (var p in b) if (b.hasOwnProperty(p)) d[p] = b[p]; };
    return function (d, b) {
        extendStatics(d, b);
        function __() { this.constructor = d; }
        d.prototype = b === null ? Object.create(b) : (__.prototype = b.prototype, new __());
    };
})();
exports.__esModule = true;
var View_1 = require("./View");
var ListView = (function (_super) {
    __extends(ListView, _super);
    function ListView() {
        return _super !== null && _super.apply(this, arguments) || this;
    }
    return ListView;
}(View_1.View));
exports.ListView = ListView;

},{"./View":11}],9:[function(require,module,exports){
"use strict";
var __extends = (this && this.__extends) || (function () {
    var extendStatics = Object.setPrototypeOf ||
        ({ __proto__: [] } instanceof Array && function (d, b) { d.__proto__ = b; }) ||
        function (d, b) { for (var p in b) if (b.hasOwnProperty(p)) d[p] = b[p]; };
    return function (d, b) {
        extendStatics(d, b);
        function __() { this.constructor = d; }
        d.prototype = b === null ? Object.create(b) : (__.prototype = b.prototype, new __());
    };
})();
exports.__esModule = true;
var View_1 = require("./View");
var MapView = (function (_super) {
    __extends(MapView, _super);
    function MapView() {
        var _this = _super !== null && _super.apply(this, arguments) || this;
        _this.map = undefined;
        _this.icon = undefined;
        _this.markers = undefined;
        _this.markerClusterGroup = undefined;
        _this.bounds = undefined;
        _this.initialized = false;
        return _this;
    }
    MapView.prototype.init = function () {
        var data = this.controller.getData();
        var markers = {};
        var bounds = undefined;
        var markerClusterGroup = L.markerClusterGroup({
            animateAddingMarkers: true
        });
        for (var rowId in data) {
            var positions = data[rowId]['data'][this.id]['positions'];
            markers[rowId] = [];
            for (var _i = 0, positions_1 = positions; _i < positions_1.length; _i++) {
                var pos = positions_1[_i];
                bounds = (bounds === undefined) ? new L.LatLngBounds(pos, pos) : bounds.extend(pos);
                var marker = this.getMarker(pos, data[rowId]);
                markers[rowId].push(marker);
                markerClusterGroup.addLayer(marker);
            }
        }
        this.markerClusterGroup = markerClusterGroup;
        this.markers = markers;
        this.bounds = bounds;
        return _super.prototype.init.call(this);
    };
    MapView.prototype.getIcon = function () {
        if (this.icon === undefined) {
            var iconPath = this.controller.getPath() + 'css/images/';
            this.icon = new L.Icon({
                'iconUrl': iconPath + 'marker-icon.png',
                'iconRetinaUrl': iconPath + 'marker-icon-2x.png',
                'shadowUrl': iconPath + 'marker-shadow.png',
                'iconSize': [25, 41],
                'iconAnchor': [12, 41],
                'popupAnchor': [1, -34],
                // 'tooltipAnchor': [16, -28],
                'shadowSize': [41, 41]
            });
        }
        return this.icon;
    };
    MapView.prototype.getMarker = function (latLng, row) {
        var title = undefined;
        var popup = [];
        // TODO: Use <div> instead of <b> and do CSS styling
        for (var prId in row['printouts']) {
            var printrequest = (this.controller.getPrintRequests())[prId];
            if (!printrequest.hasOwnProperty('hide') || printrequest.hide === false) {
                var printouts = row['printouts'][prId];
                if (title === undefined) {
                    title = printouts['values'].join(', ');
                    popup.push('<b>' + printouts['formatted values'].join(', ') + '</b>');
                }
                else {
                    popup.push((printouts.label ? '<b>' + printouts.label + ':</b> ' : '') + printouts['formatted values'].join(', '));
                }
            }
        }
        var marker = L.marker(latLng, { title: title, alt: title });
        marker.bindPopup(popup.join('<br>'));
        marker.setIcon(this.getIcon());
        return marker;
    };
    MapView.prototype.lateInit = function () {
        if (this.initialized) {
            return;
        }
        this.initialized = true;
        var that = this;
        $(function () {
            setTimeout(function () {
                that.map = L.map(that.getTargetElement().get(0))
                    .addLayer(L.tileLayer('http://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: ''
                }))
                    .addLayer(that.markerClusterGroup)
                    .fitBounds(that.bounds);
            }, 0);
        });
    };
    MapView.prototype.showRows = function (rowIds) {
        var _this = this;
        var markers = rowIds.map(function (rowId) { return _this.markers[rowId]; });
        this.markerClusterGroup.addLayers(markers.reduce(function (result, layers) { return result.concat(layers); }));
    };
    MapView.prototype.hideRows = function (rowIds) {
        var _this = this;
        var markers = rowIds.map(function (rowId) { return _this.markers[rowId]; });
        this.markerClusterGroup.removeLayers(markers.reduce(function (result, layers) { return result.concat(layers); }));
    };
    MapView.prototype.show = function () {
        _super.prototype.show.call(this);
        this.lateInit();
    };
    return MapView;
}(View_1.View));
exports.MapView = MapView;

},{"./View":11}],10:[function(require,module,exports){
"use strict";
var __extends = (this && this.__extends) || (function () {
    var extendStatics = Object.setPrototypeOf ||
        ({ __proto__: [] } instanceof Array && function (d, b) { d.__proto__ = b; }) ||
        function (d, b) { for (var p in b) if (b.hasOwnProperty(p)) d[p] = b[p]; };
    return function (d, b) {
        extendStatics(d, b);
        function __() { this.constructor = d; }
        d.prototype = b === null ? Object.create(b) : (__.prototype = b.prototype, new __());
    };
})();
exports.__esModule = true;
var View_1 = require("./View");
var TableView = (function (_super) {
    __extends(TableView, _super);
    function TableView() {
        return _super !== null && _super.apply(this, arguments) || this;
    }
    return TableView;
}(View_1.View));
exports.TableView = TableView;

},{"./View":11}],11:[function(require,module,exports){
"use strict";
exports.__esModule = true;
var View = (function () {
    function View(id, target, c, options) {
        if (options === void 0) { options = {}; }
        this.id = undefined;
        this.target = undefined;
        this.controller = undefined;
        this.options = undefined;
        this.id = id;
        this.target = target;
        this.controller = c;
        this.options = options;
    }
    View.prototype.init = function () { };
    View.prototype.getTargetElement = function () {
        return this.target;
    };
    View.prototype.showRows = function (rowIds) {
        var _this = this;
        rowIds.forEach(function (rowId) {
            _this.target.find('.' + rowId).slideDown(400);
        });
    };
    View.prototype.hideRows = function (rowIds) {
        var _this = this;
        rowIds.forEach(function (rowId) {
            _this.target.find('.' + rowId).slideUp(400);
        });
    };
    View.prototype.show = function () {
        this.target.show();
    };
    View.prototype.hide = function () {
        this.target.hide();
    };
    return View;
}());
exports.View = View;

},{}],12:[function(require,module,exports){
"use strict";
exports.__esModule = true;
var ViewSelector = (function () {
    function ViewSelector(target, viewIDs, controller) {
        this.target = undefined;
        this.viewIDs = undefined;
        this.controller = undefined;
        this.target = target;
        this.viewIDs = viewIDs;
        this.controller = controller;
    }
    ViewSelector.prototype.init = function () {
        var _this = this;
        if (this.viewIDs.length > 1) {
            this.viewIDs.forEach(function (id) { _this.target.on('click', '.' + id, { 'target': id, 'controller': _this.controller }, ViewSelector.onSelectorSelected); });
            this.target.children().first().addClass('selected');
            this.target.show();
        }
    };
    ViewSelector.onSelectorSelected = function (event) {
        event.data.controller.onViewSelected(event.data.target);
        $(event.target)
            .addClass('selected')
            .siblings().removeClass('selected');
        event.stopPropagation();
        event.preventDefault();
    };
    return ViewSelector;
}());
exports.ViewSelector = ViewSelector;

},{}],13:[function(require,module,exports){
"use strict";
exports.__esModule = true;
var Filtered_1 = require("./Filtered/Filtered");
var config = mw.config.get('srfFilteredConfig');
for (var id in config) {
    if (config.hasOwnProperty(id)) {
        var f = new Filtered_1.Filtered($('#' + id), config[id]);
        f.run();
    }
}

},{"./Filtered/Filtered":6}]},{},[13])

//# sourceMappingURL=ext.srf.filtered.js.map
