(function(){function r(e,n,t){function o(i,f){if(!n[i]){if(!e[i]){var c="function"==typeof require&&require;if(!f&&c)return c(i,!0);if(u)return u(i,!0);var a=new Error("Cannot find module '"+i+"'");throw a.code="MODULE_NOT_FOUND",a}var p=n[i]={exports:{}};e[i][0].call(p.exports,function(r){var n=e[i][1][r];return o(n||r)},p,p.exports,r,e,n,t)}return n[i].exports}for(var u="function"==typeof require&&require,i=0;i<t.length;i++)o(t[i]);return o}return r})()({1:[function(require,module,exports){
"use strict";
/// <reference types="jquery" />
exports.__esModule = true;
var View_1 = require("./View/View");
var Controller = /** @class */ (function () {
    function Controller(target, data, printRequests) {
        this.target = undefined;
        this.filterSpinner = undefined;
        this.views = {};
        this.filters = {};
        this.currentView = undefined;
        this.target = target;
        if (this.target !== undefined) {
            this.filterSpinner = this.target.find('div.filtered-filter-spinner');
        }
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
        filter.init();
        return this.onFilterUpdated(filterId);
    };
    Controller.prototype.getFilter = function (filterId) {
        return this.filters[filterId];
    };
    Controller.prototype.show = function () {
        this.initializeFilters();
        this.target.children('.filtered-spinner').remove();
        this.target.children().show();
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
                this.data[rowId].visible[filterId] = this.filters[filterId].isDisabled() || this.filters[filterId].isVisible(rowId);
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
        var _this = this;
        return this.showSpinner()
            .then(function () {
            var toShow = [];
            var toHide = [];
            var disabled = _this.filters[filterId].isDisabled();
            for (var rowId in _this.data) {
                var newVisible = disabled || _this.filters[filterId].isVisible(rowId);
                if (_this.data[rowId].visible[filterId] !== newVisible) {
                    _this.data[rowId].visible[filterId] = newVisible;
                    if (newVisible && _this.isVisible(rowId)) {
                        toShow.push(rowId);
                    }
                    else {
                        toHide.push(rowId);
                    }
                }
            }
            _this.hideRows(toHide);
            _this.showRows(toShow);
        })
            .then(function () { _this.hideSpinner(); });
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
    Controller.prototype.showSpinner = function () {
        return this.animateSpinner();
    };
    Controller.prototype.hideSpinner = function () {
        return this.animateSpinner(false);
    };
    Controller.prototype.animateSpinner = function (show) {
        if (show === void 0) { show = true; }
        if (this.filterSpinner === undefined) {
            return jQuery.when();
        }
        if (show) {
            return this.filterSpinner.fadeIn(200).promise();
        }
        return this.filterSpinner.fadeOut(200).promise();
    };
    return Controller;
}());
exports.Controller = Controller;

},{"./View/View":11}],2:[function(require,module,exports){
"use strict";
var __extends = (this && this.__extends) || (function () {
    var extendStatics = function (d, b) {
        extendStatics = Object.setPrototypeOf ||
            ({ __proto__: [] } instanceof Array && function (d, b) { d.__proto__ = b; }) ||
            function (d, b) { for (var p in b) if (b.hasOwnProperty(p)) d[p] = b[p]; };
        return extendStatics(d, b);
    };
    return function (d, b) {
        extendStatics(d, b);
        function __() { this.constructor = d; }
        d.prototype = b === null ? Object.create(b) : (__.prototype = b.prototype, new __());
    };
})();
exports.__esModule = true;
var Filter_1 = require("./Filter");
var DistanceFilter = /** @class */ (function (_super) {
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
        var filtercontrols = this.buildEmptyControl();
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
        var rowdata = this.controller.getData()[rowId].data;
        if (rowdata.hasOwnProperty(this.filterId)) {
            return rowdata[this.filterId].distance <= this.filterValue;
        }
        return _super.prototype.isVisible.call(this, rowId);
    };
    DistanceFilter.earthRadius = {
        m: 6371008.8,
        km: 6371.0088,
        mi: 3958.7613,
        nm: 3440.0695,
        Å: 63710088000000000
    };
    return DistanceFilter;
}(Filter_1.Filter));
exports.DistanceFilter = DistanceFilter;

},{"./Filter":3}],3:[function(require,module,exports){
"use strict";
exports.__esModule = true;
var Filter = /** @class */ (function () {
    function Filter(filterId, target, printrequestId, controller, options) {
        this.outerTarget = undefined;
        this.target = undefined;
        this.options = undefined;
        this.disabled = false;
        this.collapsed = false;
        this.target = target;
        this.outerTarget = target;
        this.filterId = filterId;
        this.printrequestId = printrequestId;
        this.controller = controller;
        this.options = options || {};
    }
    Filter.prototype.init = function () { };
    ;
    Filter.prototype.isDisabled = function () {
        return this.disabled;
    };
    Filter.prototype.disable = function () {
        var _this = this;
        this.disabled = true;
        this.outerTarget
            .removeClass('enabled')
            .addClass('disabled');
        this.collapse();
        this.target.promise().then(function () { return _this.controller.onFilterUpdated(_this.filterId); });
    };
    Filter.prototype.enable = function () {
        var _this = this;
        this.disabled = false;
        this.outerTarget
            .removeClass('disabled')
            .addClass('enabled');
        if (!this.collapsed) {
            this.uncollapse();
        }
        this.target.promise().then(function () { return _this.controller.onFilterUpdated(_this.filterId); });
    };
    Filter.prototype.collapse = function (duration) {
        var _this = this;
        if (duration === void 0) { duration = 400; }
        if (!this.collapsed) {
            this.outerTarget.promise()
                .then(function () {
                _this.target.slideUp(duration);
                _this.outerTarget.animate({
                    'padding-top': 0,
                    'padding-bottom': 0,
                    'margin-bottom': '2em'
                }, duration);
            });
        }
    };
    Filter.prototype.uncollapse = function () {
        var _this = this;
        this.outerTarget.promise()
            .then(function () {
            _this.target.slideDown();
            var style = _this.outerTarget.attr('style');
            _this.outerTarget.removeAttr('style');
            var uncollapsedCss = _this.outerTarget.css(['padding-top', 'padding-bottom', 'margin-bottom']);
            _this.outerTarget.attr('style', style);
            _this.outerTarget.animate(uncollapsedCss);
        });
    };
    Filter.prototype.isVisible = function (rowId) {
        return this.options.hasOwnProperty('show if undefined') && this.options['show if undefined'] === true;
    };
    Filter.prototype.getId = function () {
        return this.filterId;
    };
    Filter.prototype.buildEmptyControl = function () {
        this.target = $('<div class="filtered-filter-container">');
        this.outerTarget
            .append(this.target)
            .addClass('enabled');
        this.addOnOffSwitch();
        this.addLabel();
        this.addControlForCollapsing();
        return this.target;
    };
    Filter.prototype.addLabel = function () {
        // insert the label of the printout this filter filters on
        this.target.before("<div class=\"filtered-filter-label\">" + this.options['label'] + "</div>");
    };
    Filter.prototype.addOnOffSwitch = function () {
        var _this = this;
        if (this.options.hasOwnProperty('switches')) {
            var switches = this.options['switches'];
            if (switches.length > 0 && $.inArray('on off', switches) >= 0) {
                var onOffControl = $("<div class=\"filtered-filter-onoff on\"></div>");
                this.target.before(onOffControl);
                onOffControl.click(function () {
                    if (_this.outerTarget.hasClass('enabled')) {
                        _this.disable();
                    }
                    else {
                        _this.enable();
                    }
                });
            }
        }
    };
    Filter.prototype.addControlForCollapsing = function () {
        var _this = this;
        var collapsible = this.options.hasOwnProperty('collapsible') ? this.options['collapsible'] : undefined;
        if (collapsible === 'collapsed' || collapsible === 'uncollapsed') {
            var collapseControl_1 = $('<span class="filtered-filter-collapse">');
            this.target.before(collapseControl_1);
            collapseControl_1.click(function () {
                if (collapseControl_1.hasClass('collapsed')) {
                    _this.uncollapse();
                    _this.collapsed = false;
                    collapseControl_1
                        .removeClass('collapsed')
                        .addClass('uncollapsed');
                }
                else {
                    _this.collapse();
                    _this.collapsed = true;
                    collapseControl_1
                        .removeClass('uncollapsed')
                        .addClass('collapsed');
                }
            });
            if (collapsible === 'collapsed') {
                this.collapse(0);
                this.collapsed = true;
                collapseControl_1.addClass('collapsed');
            }
            else {
                collapseControl_1.addClass('uncollapsed');
            }
        }
    };
    return Filter;
}());
exports.Filter = Filter;

},{}],4:[function(require,module,exports){
"use strict";
var __extends = (this && this.__extends) || (function () {
    var extendStatics = function (d, b) {
        extendStatics = Object.setPrototypeOf ||
            ({ __proto__: [] } instanceof Array && function (d, b) { d.__proto__ = b; }) ||
            function (d, b) { for (var p in b) if (b.hasOwnProperty(p)) d[p] = b[p]; };
        return extendStatics(d, b);
    };
    return function (d, b) {
        extendStatics(d, b);
        function __() { this.constructor = d; }
        d.prototype = b === null ? Object.create(b) : (__.prototype = b.prototype, new __());
    };
})();
exports.__esModule = true;
///<reference path="../../../../node_modules/@types/ion.rangeslider/index.d.ts"/>
var Filter_1 = require("./Filter");
var NumberFilter = /** @class */ (function (_super) {
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
        var values = this.getValues();
        var _a = this.getRangeParameters(values), minValue = _a.minValue, maxValue = _a.maxValue, precision = _a.precision;
        var sliderOptions = {
            prettify_enabled: false,
            force_edges: true,
            grid: true
        };
        if (this.options.hasOwnProperty('values')) {
            sliderOptions = this.adjustSliderOptionsFromValues(sliderOptions, values);
        }
        else {
            sliderOptions = this.adjustSliderOptionsFromRangeParameters(sliderOptions, minValue, maxValue, precision);
        }
        switch (this.options['sliders']) {
            case "min":
                this.mode = this.MODE_MIN;
                sliderOptions.type = 'single';
                break;
            case "max":
                this.mode = this.MODE_MAX;
                sliderOptions.from = sliderOptions.to;
                sliderOptions.type = 'single';
                break;
            case "select":
                this.mode = this.MODE_SELECT;
                maxValue = minValue;
                sliderOptions.type = 'single';
                break;
            default: // == case "range"
                this.mode = this.MODE_RANGE;
                sliderOptions.type = 'double';
        }
        this.buildFilterControls(sliderOptions);
        this.filterValueLower = minValue;
        this.filterValueUpper = maxValue;
        return this;
    };
    NumberFilter.prototype.adjustSliderOptionsFromRangeParameters = function (sliderOptions, minValue, maxValue, precision) {
        var _this = this;
        sliderOptions.min = minValue;
        sliderOptions.max = maxValue;
        sliderOptions.step = this.getStep(precision);
        sliderOptions.grid_num = Math.min(4, Math.round((maxValue - minValue) / sliderOptions.step));
        sliderOptions.from = minValue;
        sliderOptions.to = maxValue;
        sliderOptions.onFinish = function (data) { return _this.onFilterUpdated(data.from, data.to); };
        return sliderOptions;
    };
    NumberFilter.prototype.adjustSliderOptionsFromValues = function (sliderOptions, values) {
        var _this = this;
        sliderOptions.values = values;
        sliderOptions.from = 0;
        sliderOptions.to = values.length - 1;
        sliderOptions.onFinish = function (data) { return _this.onFilterUpdated(data.from_value, data.to_value); };
        return sliderOptions;
    };
    NumberFilter.prototype.getRangeParameters = function (values) {
        var minValue = values[0];
        var maxValue = values[values.length - 1];
        var precision = this.getPrecision(minValue, maxValue);
        if (!this.options.hasOwnProperty('values')) {
            minValue = this.getMinSliderValue(minValue, precision);
            maxValue = this.getMaxSliderValue(maxValue, precision);
        }
        return { minValue: minValue, maxValue: maxValue, precision: precision };
    };
    NumberFilter.prototype.getValues = function () {
        var values;
        if (this.options.hasOwnProperty('values') && this.options['values'][0] !== 'auto') {
            values = this.options['values'];
        }
        else {
            values = this.getSortedValues();
        }
        if (values.length === 0) {
            values = [0, 0];
        }
        else if (values.length === 1) {
            values.push(values[0]);
        }
        return values;
    };
    NumberFilter.prototype.buildFilterControls = function (sliderOptions) {
        var filterClassNames = {};
        filterClassNames[this.MODE_MIN.toString()] = "mode-min";
        filterClassNames[this.MODE_MAX] = "mode-max";
        filterClassNames[this.MODE_RANGE] = "mode-range";
        filterClassNames[this.MODE_SELECT] = "mode-select";
        var filtercontrols = this.buildEmptyControl();
        var slider = $('<input type="text" value="" />');
        var sliderContainer = $("<div class=\"filtered-number-slider " + filterClassNames[this.mode] + "\" />").append(slider);
        filtercontrols.append(sliderContainer);
        if (this.options.hasOwnProperty('caption')) {
            var caption = "<div class=\"filtered-number-caption\">" + this.options['caption'] + "</div>";
            filtercontrols.append(caption);
        }
        mw.loader.using('ext.srf.filtered.slider').then(function () { return slider.ionRangeSlider(sliderOptions); });
    };
    NumberFilter.prototype.getMinSliderValue = function (minValue, precision) {
        var requestedMin = this.options['min'];
        if (requestedMin === undefined || isNaN(Number(requestedMin))) {
            return Math.floor(minValue / precision) * precision;
        }
        return Math.min(requestedMin, minValue);
    };
    NumberFilter.prototype.getMaxSliderValue = function (maxValue, precision) {
        var requestedMax = this.options['max'];
        if (requestedMax === undefined || isNaN(Number(requestedMax))) {
            return Math.ceil(maxValue / precision) * precision;
        }
        return Math.max(requestedMax, maxValue);
    };
    NumberFilter.prototype.getPrecision = function (minValue, maxValue) {
        if (maxValue - minValue > 0) {
            return Math.pow(10, (Math.floor(Math.log(maxValue - minValue) * Math.LOG10E) - 1));
        }
        else {
            return 1;
        }
    };
    NumberFilter.prototype.getStep = function (precision) {
        var step = this.options['step'];
        if (step !== undefined) {
            step = Number(step);
            if (!isNaN(step)) {
                return step;
            }
        }
        return precision / 10;
    };
    NumberFilter.prototype.getRangeFromValues = function () {
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
    NumberFilter.prototype.getSortedValues = function () {
        var valueArray = [];
        var rows = this.controller.getData();
        for (var rowId in rows) {
            var cells = rows[rowId].data;
            if (cells.hasOwnProperty(this.filterId)) {
                var values = cells[this.filterId].values;
                for (var valueId in values) {
                    var value = Number(values[valueId]);
                    if (valueArray.indexOf(value) === -1) {
                        valueArray.push(value);
                    }
                }
            }
        }
        return valueArray.sort(function (a, b) { return a - b; });
    };
    NumberFilter.prototype.onFilterUpdated = function (from, to) {
        switch (this.mode) {
            case this.MODE_MIN:
                this.filterValueLower = from;
                break;
            case this.MODE_MAX:
                this.filterValueUpper = from;
                break;
            case this.MODE_SELECT:
                this.filterValueLower = from;
                this.filterValueUpper = from;
                break;
            default: // case this.MODE_RANGE:
                this.filterValueLower = from;
                this.filterValueUpper = to;
        }
        this.controller.onFilterUpdated(this.getId());
    };
    NumberFilter.prototype.isVisible = function (rowId) {
        var rowdata = this.controller.getData()[rowId].data;
        if (rowdata.hasOwnProperty(this.filterId) && rowdata[this.filterId].values.length > 0) {
            for (var _i = 0, _a = rowdata[this.filterId].values; _i < _a.length; _i++) {
                var value = _a[_i];
                if (value >= this.filterValueLower && value <= this.filterValueUpper) {
                    return true;
                }
            }
            return false;
        }
        return _super.prototype.isVisible.call(this, rowId);
    };
    return NumberFilter;
}(Filter_1.Filter));
exports.NumberFilter = NumberFilter;

},{"./Filter":3}],5:[function(require,module,exports){
"use strict";
var __extends = (this && this.__extends) || (function () {
    var extendStatics = function (d, b) {
        extendStatics = Object.setPrototypeOf ||
            ({ __proto__: [] } instanceof Array && function (d, b) { d.__proto__ = b; }) ||
            function (d, b) { for (var p in b) if (b.hasOwnProperty(p)) d[p] = b[p]; };
        return extendStatics(d, b);
    };
    return function (d, b) {
        extendStatics(d, b);
        function __() { this.constructor = d; }
        d.prototype = b === null ? Object.create(b) : (__.prototype = b.prototype, new __());
    };
})();
exports.__esModule = true;
var Filter_1 = require("./Filter");
var ValueFilter = /** @class */ (function (_super) {
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
        /** Map of value => sort value distinct values */
        var distinctSortValues = {};
        if (this.options.hasOwnProperty('values')) {
            return this.options['values'].map(function (item) {
                return {
                    printoutValue: item,
                    formattedValue: item
                };
            });
        }
        else {
            // build filter values from available values in result set
            var data = this.controller.getData();
            var sortedEntries = [];
            for (var id in data) {
                var printoutValues = data[id]['printouts'][this.printrequestId]['values'];
                var printoutFormattedValues = data[id]['printouts'][this.printrequestId]['formatted values'];
                var printoutSortValues = data[id]['printouts'][this.printrequestId]['sort values'];
                for (var i in printoutValues) {
                    var printoutFormattedValue = printoutFormattedValues[i];
                    if (printoutFormattedValue.indexOf('<a') > -1) {
                        printoutFormattedValue = /<a.*>(.*?)<\/a>/.exec(printoutFormattedValue)[1];
                    }
                    distinctValues[printoutValues[i]] = printoutFormattedValue;
                    distinctSortValues[printoutValues[i]] = printoutSortValues[i];
                }
            }
            for (var printoutValue in distinctSortValues) {
                sortedEntries.push({
                    printoutValue: printoutValue,
                    sortValue: distinctSortValues[printoutValue],
                    formattedValue: distinctValues[printoutValue]
                });
            }
            sortedEntries.sort(function (a, b) {
                return a.sortValue.localeCompare(b.sortValue);
            });
            return sortedEntries;
        }
    };
    ValueFilter.prototype.buildControl = function () {
        var filtercontrols = this.buildEmptyControl();
        filtercontrols = this.addControlForSwitches(filtercontrols);
        var maxCheckboxes = this.options.hasOwnProperty('max checkboxes') ? this.options['max checkboxes'] : 5;
        if (this.values.length > maxCheckboxes) {
            filtercontrols.append(this.getSelected2Control());
        }
        else {
            filtercontrols.append(this.getCheckboxesControl());
        }
    };
    ValueFilter.prototype.getCheckboxesControl = function () {
        var _this = this;
        var checkboxes = $('<div class="filtered-value-checkboxes" style="width: 100%;">');
        // insert options (checkboxes and labels)
        for (var _i = 0, _a = this.values; _i < _a.length; _i++) {
            var value = _a[_i];
            checkboxes.append("<div class=\"filtered-value-option\"><label><input type=\"checkbox\" value=\"" + value.printoutValue + "\" ><div class=\"filtered-value-option-label\">" + (value.formattedValue || value.printoutValue) + "</div></label></div>");
        }
        // attach event handler
        checkboxes
            .on('change', ':checkbox', function (eventObject) {
            var checkboxElement = eventObject.currentTarget;
            _this.onFilterUpdated(checkboxElement.value, checkboxElement.checked);
        });
        return checkboxes;
    };
    ValueFilter.prototype.getSelected2Control = function () {
        var _this = this;
        var select = $('<select class="filtered-value-select" style="width: 100%;">');
        var data = [];
        // insert options (checkboxes and labels) and attach event handlers
        for (var _i = 0, _a = this.values; _i < _a.length; _i++) {
            var value = _a[_i];
            // Try to get label, if not fall back to value id
            var label = value.formattedValue || value.printoutValue;
            data.push({ id: value.printoutValue, text: label });
        }
        mw.loader.using('ext.srf.filtered.value-filter.select').then(function () {
            select.select2({
                multiple: true,
                placeholder: mw.message('srf-filtered-value-filter-placeholder').text(),
                data: data
            });
            select.on("select2:select", function (e) {
                _this.onFilterUpdated(e.params.data.id, true);
            });
            select.on("select2:unselect", function (e) {
                _this.onFilterUpdated(e.params.data.id, false);
            });
        });
        return select;
    };
    ValueFilter.prototype.addControlForSwitches = function (filtercontrols) {
        // insert switches
        var switches = this.options.hasOwnProperty('switches') ? this.options['switches'] : undefined;
        if (switches !== undefined && $.inArray('and or', switches) >= 0) {
            var switchControls = $('<div class="filtered-value-switches">');
            var andorControl = $('<div class="filtered-value-andor">');
            var orControl = this.getRadioControl('or', true);
            var andControl = this.getRadioControl('and');
            andorControl
                .append(orControl)
                .append(andControl)
                .appendTo(switchControls);
            andorControl
                .find('input')
                .on('change', undefined, { 'filter': this }, function (eventObject) {
                return eventObject.data.filter.useOr(eventObject.target.getAttribute('value') === 'or');
            });
            filtercontrols.append(switchControls);
        }
        return filtercontrols;
    };
    ValueFilter.prototype.getRadioControl = function (type, isChecked) {
        if (isChecked === void 0) { isChecked = false; }
        var checkedAttr = isChecked ? 'checked' : '';
        var labelText = mw.message('srf-filtered-value-filter-' + type).text();
        var controlText = "<label for=\"filtered-value-" + type + "-" + this.printrequestId + "\">" +
            ("<input type=\"radio\" name=\"filtered-value-" + this.printrequestId + "\"  class=\"filtered-value-" + type + "\" id=\"filtered-value-" + type + "-" + this.printrequestId + "\" value=\"" + type + "\" " + checkedAttr + ">") +
            (labelText + "</label>");
        return $(controlText);
    };
    ValueFilter.prototype.isVisible = function (rowId) {
        if (this.visibleValues.length === 0) {
            return true;
        }
        var values = this.controller.getData()[rowId].printouts[this.printrequestId].values;
        if (values.length === 0) {
            return _super.prototype.isVisible.call(this, rowId);
        }
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
    ValueFilter.prototype.onFilterUpdated = function (value, isChecked) {
        var index = this.visibleValues.indexOf(value);
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
var Filtered = /** @class */ (function () {
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
    Filtered.prototype.run = function () {
        var controller = new Controller_1.Controller(this.target, this.config.data, this.config.printrequests);
        this.attachFilters(controller, this.target.children('div.filtered-filters'));
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
    var extendStatics = function (d, b) {
        extendStatics = Object.setPrototypeOf ||
            ({ __proto__: [] } instanceof Array && function (d, b) { d.__proto__ = b; }) ||
            function (d, b) { for (var p in b) if (b.hasOwnProperty(p)) d[p] = b[p]; };
        return extendStatics(d, b);
    };
    return function (d, b) {
        extendStatics(d, b);
        function __() { this.constructor = d; }
        d.prototype = b === null ? Object.create(b) : (__.prototype = b.prototype, new __());
    };
})();
exports.__esModule = true;
var View_1 = require("./View");
var CalendarView = /** @class */ (function (_super) {
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
    var extendStatics = function (d, b) {
        extendStatics = Object.setPrototypeOf ||
            ({ __proto__: [] } instanceof Array && function (d, b) { d.__proto__ = b; }) ||
            function (d, b) { for (var p in b) if (b.hasOwnProperty(p)) d[p] = b[p]; };
        return extendStatics(d, b);
    };
    return function (d, b) {
        extendStatics(d, b);
        function __() { this.constructor = d; }
        d.prototype = b === null ? Object.create(b) : (__.prototype = b.prototype, new __());
    };
})();
exports.__esModule = true;
var View_1 = require("./View");
var ListView = /** @class */ (function (_super) {
    __extends(ListView, _super);
    function ListView() {
        return _super !== null && _super.apply(this, arguments) || this;
    }
    ListView.prototype.getItemClassName = function () {
        return '.filtered-list-item';
    };
    return ListView;
}(View_1.View));
exports.ListView = ListView;

},{"./View":11}],9:[function(require,module,exports){
"use strict";
/// <reference types="leaflet" />
var __extends = (this && this.__extends) || (function () {
    var extendStatics = function (d, b) {
        extendStatics = Object.setPrototypeOf ||
            ({ __proto__: [] } instanceof Array && function (d, b) { d.__proto__ = b; }) ||
            function (d, b) { for (var p in b) if (b.hasOwnProperty(p)) d[p] = b[p]; };
        return extendStatics(d, b);
    };
    return function (d, b) {
        extendStatics(d, b);
        function __() { this.constructor = d; }
        d.prototype = b === null ? Object.create(b) : (__.prototype = b.prototype, new __());
    };
})();
exports.__esModule = true;
var View_1 = require("./View");
var MapView = /** @class */ (function (_super) {
    __extends(MapView, _super);
    function MapView() {
        var _this = _super !== null && _super.apply(this, arguments) || this;
        _this.map = undefined;
        _this.icon = undefined;
        _this.markers = undefined;
        _this.markerClusterGroup = undefined;
        _this.bounds = undefined;
        _this.initialized = false;
        _this.zoom = -1;
        _this.minZoom = -1;
        _this.maxZoom = -1;
        _this.leafletPromise = undefined;
        return _this;
    }
    MapView.prototype.init = function () {
        var _this = this;
        var data = this.controller.getData();
        var markers = {};
        if (this.options.hasOwnProperty('height')) {
            this.target.height(this.options.height);
        }
        this.leafletPromise = mw.loader.using('ext.srf.filtered.map-view.leaflet')
            .then(function () {
            var bounds = undefined;
            var disableClusteringAtZoom = _this.getZoomForUnclustering();
            var clusterOptions = {
                animateAddingMarkers: true,
                disableClusteringAtZoom: disableClusteringAtZoom,
                spiderfyOnMaxZoom: disableClusteringAtZoom === null
            };
            clusterOptions = _this.getOptions(['maxClusterRadius', 'zoomToBoundsOnClick'], clusterOptions);
            var markerClusterGroup = L.markerClusterGroup(clusterOptions);
            for (var rowId in data) {
                if (data[rowId]['data'].hasOwnProperty(_this.id)) {
                    var positions = data[rowId]['data'][_this.id]['positions'];
                    markers[rowId] = [];
                    for (var _i = 0, positions_1 = positions; _i < positions_1.length; _i++) {
                        var pos = positions_1[_i];
                        bounds = (bounds === undefined) ? new L.LatLngBounds(pos, pos) : bounds.extend(pos);
                        var marker = _this.getMarker(pos, data[rowId]);
                        markers[rowId].push(marker);
                        markerClusterGroup.addLayer(marker);
                    }
                }
            }
            _this.markerClusterGroup = markerClusterGroup;
            _this.markers = markers;
            _this.bounds = (bounds === undefined) ? new L.LatLngBounds([-180, -90], [180, 90]) : bounds;
        });
        return this.leafletPromise;
    };
    /**
     * Detects if user uses dark theme
     * @returns {boolean}
     */
    MapView.prototype.isUserUsesDarkMode = function () {
        return window.matchMedia("(prefers-color-scheme: dark)").matches;
    };
    MapView.prototype.getZoomForUnclustering = function () {
        if (this.options.hasOwnProperty('marker cluster') && this.options['marker cluster'] === false) {
            return 0;
        }
        if (this.options.hasOwnProperty('marker cluster max zoom')) {
            return this.options['marker cluster max zoom'] + 1;
        }
        return null;
    };
    MapView.prototype.getIcon = function (row) {
        if (this.icon === undefined) {
            this.buildIconList();
        }
        if (this.options.hasOwnProperty('marker icon property')) {
            var vals = row['printouts'][this.options['marker icon property']]['values'];
            if (vals.length > 0 && this.icon.hasOwnProperty(vals[0])) {
                return this.icon[vals[0]];
            }
        }
        return this.icon['default'];
    };
    MapView.prototype.buildIconList = function () {
        this.icon = {};
        var iconPath = this.controller.getPath() + 'css/images/';
        this.icon['default'] = new L.Icon({
            'iconUrl': iconPath + 'marker-icon.png',
            'iconRetinaUrl': iconPath + 'marker-icon-2x.png',
            'shadowUrl': iconPath + 'marker-shadow.png',
            'iconSize': [25, 41],
            'iconAnchor': [12, 41],
            'popupAnchor': [1, -34],
            // 'tooltipAnchor': [16, -28],
            'shadowSize': [41, 41]
        });
        if (this.options.hasOwnProperty('marker icons')) {
            for (var value in this.options['marker icons']) {
                this.icon[value] = new L.Icon({
                    'iconUrl': this.options['marker icons'][value],
                    // 'iconRetinaUrl': iconPath + 'marker-icon-2x.png',
                    'shadowUrl': iconPath + 'marker-shadow.png',
                    'iconSize': [32, 32],
                    'iconAnchor': [16, 32],
                    'popupAnchor': [1, -30],
                    // 'tooltipAnchor': [16, -28],
                    'shadowSize': [41, 41],
                    'shadowAnchor': [12, 41]
                });
            }
        }
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
        marker.setIcon(this.getIcon(row));
        return marker;
    };
    MapView.prototype.lateInit = function () {
        var _this = this;
        if (this.initialized) {
            return;
        }
        this.initialized = true;
        var that = this;
        this.leafletPromise.then(function () {
            var mapOptions = {
                center: _this.bounds !== undefined ? _this.bounds.getCenter() : [0, 0]
            };
            mapOptions = that.getOptions(['zoom', 'minZoom', 'maxZoom'], mapOptions);
            // TODO: Limit zoom values to map max zoom
            that.map = L.map(that.getTargetElement().get(0), mapOptions);
            that.map.addLayer(that.markerClusterGroup);
            var mapProvider = null;
            if (_this.options.hasOwnProperty('map provider')) {
                mapProvider = _this.options['map provider'];
            }
            if (_this.isUserUsesDarkMode() && _this.options.hasOwnProperty('map provider dark')) {
                mapProvider = _this.options['map provider dark'];
            }
            if (mapProvider) {
                L.tileLayer.provider(mapProvider).addTo(that.map);
            }
            if (!mapOptions.hasOwnProperty('zoom')) {
                that.map.fitBounds(that.bounds);
            }
        });
    };
    MapView.prototype.getOptions = function (keys, defaults) {
        if (defaults === void 0) { defaults = {}; }
        for (var _i = 0, keys_1 = keys; _i < keys_1.length; _i++) {
            var key = keys_1[_i];
            if (this.options.hasOwnProperty(key)) {
                defaults[key] = this.options[key];
            }
        }
        return defaults;
    };
    MapView.prototype.showRows = function (rowIds) {
        var _this = this;
        this.leafletPromise.then(function () {
            _this.manipulateLayers(rowIds, function (layers) {
                _this.markerClusterGroup.addLayers(layers);
            });
        });
    };
    MapView.prototype.hideRows = function (rowIds) {
        var _this = this;
        this.leafletPromise.then(function () {
            _this.manipulateLayers(rowIds, function (layers) {
                _this.markerClusterGroup.removeLayers(layers);
            });
        });
    };
    MapView.prototype.manipulateLayers = function (rowIds, cb) {
        var layersFromRowIds = this.getLayersFromRowIds(rowIds);
        if (layersFromRowIds.length > 0) {
            cb(layersFromRowIds);
        }
    };
    MapView.prototype.getLayersFromRowIds = function (rowIds) {
        return this.flatten(this.getLayersFromRowIdsRaw(rowIds));
    };
    MapView.prototype.getLayersFromRowIdsRaw = function (rowIds) {
        var _this = this;
        return rowIds.map(function (rowId) { return _this.markers[rowId] ? _this.markers[rowId] : []; });
    };
    MapView.prototype.flatten = function (markers) {
        return markers.reduce(function (result, layers) { return result.concat(layers); }, []);
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
    var extendStatics = function (d, b) {
        extendStatics = Object.setPrototypeOf ||
            ({ __proto__: [] } instanceof Array && function (d, b) { d.__proto__ = b; }) ||
            function (d, b) { for (var p in b) if (b.hasOwnProperty(p)) d[p] = b[p]; };
        return extendStatics(d, b);
    };
    return function (d, b) {
        extendStatics(d, b);
        function __() { this.constructor = d; }
        d.prototype = b === null ? Object.create(b) : (__.prototype = b.prototype, new __());
    };
})();
exports.__esModule = true;
var View_1 = require("./View");
var TableView = /** @class */ (function (_super) {
    __extends(TableView, _super);
    function TableView() {
        return _super !== null && _super.apply(this, arguments) || this;
    }
    TableView.prototype.getItemClassName = function () {
        return '.filtered-table-item';
    };
    return TableView;
}(View_1.View));
exports.TableView = TableView;

},{"./View":11}],11:[function(require,module,exports){
"use strict";
exports.__esModule = true;
var View = /** @class */ (function () {
    function View(id, target, c, options) {
        if (options === void 0) { options = {}; }
        this.id = undefined;
        this.target = undefined;
        this.controller = undefined;
        this.options = undefined;
        this.visible = false;
        this.rows = {};
        this.id = id;
        this.target = target;
        this.controller = c;
        this.options = options;
    }
    View.prototype.init = function () {
        var _this = this;
        var rowIds = Object.keys(this.controller.getData());
        var rows = this.target.find(this.getItemClassName());
        rows.each(function (index, elem) {
            var classes = elem.classList;
            for (var i = 0; i < classes.length; i++) {
                if (rowIds.indexOf(classes[i]) >= 0) {
                    _this.rows[classes[i]] = $(rows[index]);
                }
            }
        });
    };
    View.prototype.getItemClassName = function () {
        return '.filtered-item';
    };
    View.prototype.getTargetElement = function () {
        return this.target;
    };
    View.prototype.showRows = function (rowIds) {
        var _this = this;
        if (this.visible && rowIds.length < 200) {
            rowIds.forEach(function (rowId) {
                _this.rows[rowId].slideDown(400);
            });
        }
        else {
            rowIds.forEach(function (rowId) {
                _this.rows[rowId].css('display', '');
            });
        }
    };
    View.prototype.hideRows = function (rowIds) {
        var _this = this;
        if (this.visible && rowIds.length < 200) {
            rowIds.forEach(function (rowId) {
                _this.rows[rowId].slideUp(400);
            });
        }
        else {
            rowIds.forEach(function (rowId) {
                _this.rows[rowId].css('display', 'none');
            });
        }
    };
    View.prototype.show = function () {
        this.target.show();
        this.visible = true;
    };
    View.prototype.hide = function () {
        this.target.hide();
        this.visible = false;
    };
    return View;
}());
exports.View = View;

},{}],12:[function(require,module,exports){
"use strict";
exports.__esModule = true;
var ViewSelector = /** @class */ (function () {
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
var _loop_1 = function (id) {
    if (config.hasOwnProperty(id)) {
        var f_1 = new Filtered_1.Filtered($('#' + id), config[id]);
        mw.hook('wikipage.content').add(function () { return f_1.run(); });
    }
};
for (var id in config) {
    _loop_1(id);
}

},{"./Filtered/Filtered":6}]},{},[13])

//# sourceMappingURL=ext.srf.filtered.js.map
