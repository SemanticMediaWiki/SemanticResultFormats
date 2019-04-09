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

},{"./View/View":5}],2:[function(require,module,exports){
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

},{}],3:[function(require,module,exports){
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

},{"./Filter":2}],4:[function(require,module,exports){
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
            if (_this.options.hasOwnProperty('map provider')) {
                L.tileLayer.provider(_this.options['map provider']).addTo(that.map);
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

},{"./View":5}],5:[function(require,module,exports){
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

},{}],6:[function(require,module,exports){
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

},{}],7:[function(require,module,exports){
"use strict";
/// <reference types="qunit" />
exports.__esModule = true;
var Controller_1 = require("../../../resources/ts/Filtered/Controller");
var MockedFilter_1 = require("../Util/MockedFilter");
var View_1 = require("../../../resources/ts/Filtered/View/View");
var ControllerTest = /** @class */ (function () {
    function ControllerTest() {
    }
    ControllerTest.prototype.runTests = function () {
        QUnit.test('Controller: Can construct and attach data', this.testConstructAndAttachData);
        QUnit.test('Controller: Attaching 3 views (foo, bar, baz) and switch between them', this.testAttachViewsAndSwitchToViews);
        QUnit.test('Controller: Show', this.testShow);
        QUnit.test('Controller: Attaching 3 filters (foo, bar, baz)', this.testAttachFilter);
        return true;
    };
    /**
     * @covers Controller.constructor
     * @covers Controller.getData
     */
    ControllerTest.prototype.testConstructAndAttachData = function (assert) {
        // Setup
        var data = { 'foo': {} };
        // Run
        var c = new Controller_1.Controller(undefined, data, {});
        // Assert: Can construct
        assert.ok(c instanceof Controller_1.Controller, 'Can construct Controller.');
        // Assert: Data correctly attached and retained
        assert.deepEqual(c.getData(), data, 'Returns result data as given to constructor.');
    };
    /**
     * @covers Controller.attachView
     * @covers Controller.getView
     * @covers Controller.onViewSelected
     */
    ControllerTest.prototype.testAttachViewsAndSwitchToViews = function (assert) {
        // Setup
        var c = new Controller_1.Controller(undefined, undefined, undefined);
        var viewIds = ['foo', 'bar', 'baz'];
        var viewsShown = [];
        var viewsHidden = [];
        var views = {};
        viewIds.forEach(function (viewId) {
            var v = new View_1.View(viewId, undefined, c, {});
            v.show = function () {
                if (viewsShown.indexOf(v) === -1) {
                    viewsShown.push(v);
                }
                var index = viewsHidden.indexOf(v);
                if (index >= 0) {
                    viewsHidden.splice(index, 1);
                }
            };
            v.hide = function () {
                if (viewsHidden.indexOf(v) === -1) {
                    viewsHidden.push(v);
                }
                var index = viewsShown.indexOf(v);
                if (index >= 0) {
                    viewsShown.splice(index, 1);
                }
            };
            views[viewId] = v;
            // Run
            c.attachView(viewId, v);
        });
        // Assert: One view visible, all others hidden, i.e. none has undefined
        // visibility
        assert.strictEqual(viewsShown.length, 1, 'One view visible.');
        assert.strictEqual(viewsHidden.length, viewIds.length - 1, 'All but one view hidden.');
        for (var viewId in views) {
            // Assert: View correctly attached and retained
            assert.deepEqual(c.getView(viewId), views[viewId], "Controller knows \"" + viewId + "\" view.");
        }
        for (var viewId in views) {
            // Run: Select view
            c.onViewSelected(viewId);
            // Assert: Only selected view visible, all others hidden, i.e. none
            // has undefined visibility
            assert.ok(viewsShown.length === 1 && viewsShown.indexOf(views[viewId]) >= 0, 'Selected view visible.');
            assert.strictEqual(viewsHidden.length, viewIds.length - 1, 'All other views hidden.');
        }
    };
    /**
     * @covers Controller.show
     */
    ControllerTest.prototype.testShow = function (assert) {
        // Setup
        var targetElement = $();
        var targetShown = false;
        targetElement.children = function (selector) {
            var targetChild = $();
            targetChild.show = function () {
                targetShown = true;
                return targetChild;
            };
            return targetChild;
        };
        // Run
        new Controller_1.Controller(targetElement, undefined, undefined).show();
        // Assert
        assert.ok(targetShown, 'Container made visible.');
    };
    /**
     * @covers Controller.attachFilter
     * @covers Controller.getFilter
     */
    ControllerTest.prototype.testAttachFilter = function (assert) {
        // Setup
        var data = { 'foo': {} };
        var controller = new Controller_1.Controller(undefined, data, {});
        var filterIds = ['foo', 'bar', 'baz'];
        var done = assert.async();
        var promises = [];
        filterIds.forEach(function (filterId) {
            var visibilityWasQueried = false;
            var filter = new MockedFilter_1.MockedFilter(filterId, undefined, undefined, controller);
            filter.isVisible = function (rowId) {
                visibilityWasQueried = true;
                return true;
            };
            // Run
            var promise = controller.attachFilter(filter)
                .then(function () {
                // Assert: Filter was queried for the visibility of result items
                assert.ok(visibilityWasQueried, "Filter \"" + filterId + "\" was queried after attaching.");
            });
            promises.push(promise);
            // Assert: Filter correctly attached and retained.
            assert.deepEqual(controller.getFilter(filterId), filter, "Controller knows \"" + filterId + "\" filter.");
        });
        jQuery.when.apply(jQuery, promises).then(done);
    };
    return ControllerTest;
}());
exports.ControllerTest = ControllerTest;

},{"../../../resources/ts/Filtered/Controller":1,"../../../resources/ts/Filtered/View/View":5,"../Util/MockedFilter":12}],8:[function(require,module,exports){
"use strict";
/// <reference types="qunit" />
/// <reference types="jquery" />
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
var ValueFilter_1 = require("../../../../resources/ts/Filtered/Filter/ValueFilter");
var Controller_1 = require("../../../../resources/ts/Filtered/Controller");
var QUnitTest_1 = require("../../Util/QUnitTest");
var ValueFilterTest = /** @class */ (function (_super) {
    __extends(ValueFilterTest, _super);
    function ValueFilterTest() {
        return _super !== null && _super.apply(this, arguments) || this;
    }
    // TODO:
    // 	public isVisible( rowId: string ): boolean {
    //	public onFilterUpdated( eventObject: JQueryEventObject ) {
    ValueFilterTest.prototype.runTests = function () {
        QUnit.test('ValueFilter: Can construct', this.testCanConstruct);
        QUnit.test('ValueFilter: Init', this.testInit);
        QUnit.test('ValueFilter: Update on and/or switch.', this.testUseOr);
        return true;
    };
    ;
    ValueFilterTest.prototype.testCanConstruct = function (assert) {
        var controller = undefined;
        var options = {};
        var f = new ValueFilter_1.ValueFilter('foo', $(), 'fooPR', controller, options);
        assert.ok(f instanceof ValueFilter_1.ValueFilter, 'Can construct ValueFilter.');
    };
    ;
    ValueFilterTest.prototype.testInit = function (assert) {
        // Setup
        var controller = new Controller_1.Controller($(), {}, {});
        var options = {
            'switches': [
                'and or'
            ],
            'values': [
                'foo',
                'bar'
            ],
            'collapsible': 'uncollapsed',
            'type': 'value',
            'label': 'FooLabel'
        };
        var target = $('<div>');
        var f = new ValueFilter_1.ValueFilter('foo', target, 'fooPR', controller, options);
        // Run
        f.init();
        // Assert
        assert.strictEqual(target.find('.filtered-filter-container').length, 1, 'Added container for collapsable content.');
        assert.strictEqual(target.find('.filtered-value-andor').length, 1, 'Added container for and/or switch.');
        var done = assert.async();
        setTimeout(function () {
            // Assert: One input added per value
            for (var _i = 0, _a = options.values; _i < _a.length; _i++) {
                var value = _a[_i];
                assert.strictEqual(target.find("input[value=\"" + value + "\"]").length, 1, "Added option for value \"" + value + "\".");
            }
            done();
        }, 100);
    };
    ;
    ValueFilterTest.prototype.testUseOr = function (assert) {
        // Setup
        var controller = new Controller_1.Controller($(), {}, {});
        controller.onFilterUpdated = function (filterId) {
            // Assert
            assert.ok(true, 'Filter updated.');
            var d = jQuery.Deferred();
            d.resolve();
            return d.promise();
        };
        var f = new ValueFilter_1.ValueFilter('foo', $(), 'fooPR', controller, {});
        assert.expect(1);
        // Run
        f.useOr(true);
    };
    ;
    return ValueFilterTest;
}(QUnitTest_1.QUnitTest));
exports.ValueFilterTest = ValueFilterTest;

},{"../../../../resources/ts/Filtered/Controller":1,"../../../../resources/ts/Filtered/Filter/ValueFilter":3,"../../Util/QUnitTest":13}],9:[function(require,module,exports){
"use strict";
/// <reference types="qunit" />
/// <reference types="jquery" />
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
var ViewTest_1 = require("./ViewTest");
var MapView_1 = require("../../../../resources/ts/Filtered/View/MapView");
var Controller_1 = require("../../../../resources/ts/Filtered/Controller");
var MapViewTest = /** @class */ (function (_super) {
    __extends(MapViewTest, _super);
    function MapViewTest() {
        return _super !== null && _super.apply(this, arguments) || this;
    }
    // TODO:
    MapViewTest.prototype.getTestObject = function (id, target, c, options) {
        if (id === void 0) { id = 'foo'; }
        if (target === void 0) { target = undefined; }
        if (c === void 0) { c = undefined; }
        if (options === void 0) { options = {}; }
        c = c || new Controller_1.Controller(undefined, {}, undefined);
        return new MapView_1.MapView(id, target, c, options);
    };
    ;
    MapViewTest.prototype.runTests = function () {
        _super.prototype.runTests.call(this);
        return true;
    };
    ;
    return MapViewTest;
}(ViewTest_1.ViewTest));
exports.MapViewTest = MapViewTest;

},{"../../../../resources/ts/Filtered/Controller":1,"../../../../resources/ts/Filtered/View/MapView":4,"./ViewTest":10}],10:[function(require,module,exports){
"use strict";
/// <reference types="qunit" />
/// <reference types="jquery" />
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
var QUnitTest_1 = require("../../Util/QUnitTest");
var View_1 = require("../../../../resources/ts/Filtered/View/View");
var Controller_1 = require("../../../../resources/ts/Filtered/Controller");
var ViewTest = /** @class */ (function (_super) {
    __extends(ViewTest, _super);
    function ViewTest() {
        return _super !== null && _super.apply(this, arguments) || this;
    }
    // Coverage:
    // [x] public constructor( id: string, target: JQuery, c: Controller, options: Options = {} )
    // [x] public init()
    // [x] public getTargetElement(): JQuery
    // [ ] public showRows( rowIds: string[] )
    // [ ] public hideRows( rowIds: string[] )
    // [x] public show()
    // [x] public hide()
    ViewTest.prototype.getTestObject = function (id, target, c, options) {
        if (id === void 0) { id = 'foo'; }
        if (target === void 0) { target = undefined; }
        if (c === void 0) { c = undefined; }
        if (options === void 0) { options = {}; }
        c = c || new Controller_1.Controller(undefined, {}, undefined);
        return new View_1.View(id, target, c, options);
    };
    ;
    ViewTest.prototype.runTests = function () {
        var className = this.getTestObject().constructor['name'];
        var that = this;
        QUnit.test(className + ": Can construct, init and knows target element", function (assert) { that.testBasics(assert, that); });
        QUnit.test(className + ": Show and Hide", function (assert) { that.testShowAndHide(assert, that); });
        return true;
    };
    ;
    ViewTest.prototype.testBasics = function (assert, that) {
        //Setup
        var target = $('<div>');
        // Run
        var v = that.getTestObject('foo', target);
        var ret = v.init();
        if (ret !== undefined) {
            var done_1 = assert.async();
            ret.then(function () {
                assert.ok(v instanceof View_1.View, 'Can construct View. (P)');
                assert.strictEqual(v.getTargetElement(), target, 'View retains target element. (P)');
                done_1();
            });
        }
        else {
            // Assert
            assert.ok(v instanceof View_1.View, 'Can construct View.');
            assert.strictEqual(v.getTargetElement(), target, 'View retains target element.');
        }
    };
    ;
    ViewTest.prototype.testShowAndHide = function (assert, that) {
        // Setup
        var target = $('<div>');
        target.show = function () { assert.ok(true, 'Target element shown.'); return target; };
        target.hide = function () { assert.ok(true, 'Target element hidden.'); return target; };
        var v = that.getTestObject('foo', target);
        v.init();
        v.show();
        v.hide();
        assert.expect(2);
    };
    ;
    return ViewTest;
}(QUnitTest_1.QUnitTest));
exports.ViewTest = ViewTest;

},{"../../../../resources/ts/Filtered/Controller":1,"../../../../resources/ts/Filtered/View/View":5,"../../Util/QUnitTest":13}],11:[function(require,module,exports){
"use strict";
/// <reference types="qunit" />
/// <reference types="jquery" />
exports.__esModule = true;
var ViewSelector_1 = require("../../../resources/ts/Filtered/ViewSelector");
var Controller_1 = require("../../../resources/ts/Filtered/Controller");
var ViewSelectorTest = /** @class */ (function () {
    function ViewSelectorTest() {
    }
    ViewSelectorTest.prototype.runTests = function () {
        QUnit.test('ViewSelector: Can construct', this.testCanConstruct);
        QUnit.test('ViewSelector: Init for 1 view', this.testInitSingleView);
        QUnit.test('ViewSelector: Init for 2 views', this.testInitMultipleViews);
        QUnit.test('ViewSelector: Selecting views when clicked (3 views: foo, bar, baz)', this.testSelectViews);
        return true;
    };
    ViewSelectorTest.prototype.testCanConstruct = function (assert) {
        var v = new ViewSelector_1.ViewSelector(undefined, [], undefined);
        assert.ok(v instanceof ViewSelector_1.ViewSelector, 'Can construct ViewSelector.');
    };
    ViewSelectorTest.prototype.testInitSingleView = function (assert) {
        // Setup
        var callCount = 0;
        var viewName = 'foo';
        var target = $('<div style="display:none">');
        target.append('<div class="' + viewName + '">');
        target.on = function () {
            var args = [];
            for (var _a = 0; _a < arguments.length; _a++) {
                args[_a] = arguments[_a];
            }
            callCount++;
            return target;
        };
        target.appendTo('body');
        var v = new ViewSelector_1.ViewSelector(target, [viewName], undefined);
        // Run
        v.init();
        // Assert
        assert.strictEqual(callCount, 0, 'Registers no Click events.');
        assert.ok(target.is(':hidden'), 'Target element is NOT visible.');
        // Tear down
        target.remove();
    };
    ViewSelectorTest.prototype.testInitMultipleViews = function (assert) {
        // Setup
        var target = $('<div style="display:none">');
        var viewSelectors = {};
        var viewIDs = ['foo', 'bar'];
        for (var _a = 0, viewIDs_1 = viewIDs; _a < viewIDs_1.length; _a++) {
            var id = viewIDs_1[_a];
            viewSelectors[id] = $('<div class="' + id + '">');
            target.append(viewSelectors[id]);
        }
        var eventRegistrationCount = 0;
        target.origOn = target.on;
        target.on = function () {
            var args = [];
            for (var _a = 0; _a < arguments.length; _a++) {
                args[_a] = arguments[_a];
            }
            eventRegistrationCount++;
            return target.origOn.apply(target, args);
        };
        target.appendTo('body');
        var v = new ViewSelector_1.ViewSelector(target, viewIDs, undefined);
        // Run test: Initialize ViewSelector
        v.init();
        // Assert
        assert.strictEqual(eventRegistrationCount, viewIDs.length, "Registers " + viewIDs.length + " Click events.");
        assert.ok(target.children().first().hasClass('selected'), 'First view selector is marked as selected.');
        assert.ok(target.is(':visible'), 'Target element is visible.');
        // Tear down
        target.remove();
    };
    ViewSelectorTest.prototype.testSelectViews = function (assert) {
        // Setup
        var target = $('<div style="display:none">');
        var viewSelectors = {};
        var viewIDs = ['foo', 'bar', 'baz'];
        for (var _i = 0, viewIDs_2 = viewIDs; _i < viewIDs_2.length; _i++) {
            var id = viewIDs_2[_i];
            viewSelectors[id] = $('<div class="' + id + '">');
            target.append(viewSelectors[id]);
        }
        target.appendTo('body');
        var c = new Controller_1.Controller(undefined, undefined, undefined);
        c.onViewSelected = function (viewID) {
            // Assert that the ViewSelector called the Controller when clicked
            assert.ok(true, "Controller was called to select view \"" + viewID + "\".");
        };
        var v = new ViewSelector_1.ViewSelector(target, viewIDs, c);
        v.init();
        // Run test: Select view
        assert.expect(6);
        for (var id in viewSelectors) {
            viewSelectors[id].click();
            // Assert: Only the clicked ViewController has class 'selected'
            assert.ok(viewSelectors[id].hasClass('selected') && !viewSelectors[id].siblings().hasClass('selected'), "View selector \"" + id + "\" marked as selected, siblings NOT marked as selected.");
        }
        // Tear down
        target.remove();
    };
    return ViewSelectorTest;
}());
exports.ViewSelectorTest = ViewSelectorTest;

},{"../../../resources/ts/Filtered/Controller":1,"../../../resources/ts/Filtered/ViewSelector":6}],12:[function(require,module,exports){
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
var Filter_1 = require("../../../resources/ts/Filtered/Filter/Filter");
var MockedFilter = /** @class */ (function (_super) {
    __extends(MockedFilter, _super);
    function MockedFilter() {
        return _super !== null && _super.apply(this, arguments) || this;
    }
    return MockedFilter;
}(Filter_1.Filter));
exports.MockedFilter = MockedFilter;

},{"../../../resources/ts/Filtered/Filter/Filter":2}],13:[function(require,module,exports){
"use strict";
exports.__esModule = true;
var QUnitTest = /** @class */ (function () {
    function QUnitTest() {
    }
    QUnitTest.prototype.runTests = function () { };
    ;
    return QUnitTest;
}());
exports.QUnitTest = QUnitTest;

},{}],14:[function(require,module,exports){
"use strict";
exports.__esModule = true;
var QUnitTestHandler = /** @class */ (function () {
    function QUnitTestHandler(moduleName, testclasses) {
        this.isInitialised = false;
        this.moduleName = moduleName;
        this.testclasses = testclasses;
    }
    QUnitTestHandler.prototype.init = function () {
        var _this = this;
        if (this.isInitialised) {
            return;
        }
        this.isInitialised = true;
        QUnit.testDone(function (details) {
            var message = "Pass: " + details.passed + "  Fail: " + details.failed + "  Total: " + details.total + "  " + details.module + " - " + details.name + " (" + details.duration + "ms)";
            _this.reportResult(details.failed, message);
        });
        QUnit.done(function (details) {
            var message = "All tests finished. (" + details.runtime + "ms)\nPass: " + details.passed + "  Fail: " + details.failed + "  Total: " + details.total;
            _this.reportResult(details.failed, message);
        });
    };
    ;
    QUnitTestHandler.prototype.reportResult = function (failed, message) {
        if (failed === 0) {
            console.log(message);
        }
        else {
            console.error(message);
        }
    };
    QUnitTestHandler.prototype.runTests = function () {
        this.init();
        QUnit.module(this.moduleName, QUnit.newMwEnvironment());
        this.testclasses.forEach(function (testclass) {
            return new testclass().runTests();
        });
    };
    ;
    return QUnitTestHandler;
}());
exports.QUnitTestHandler = QUnitTestHandler;

},{}],15:[function(require,module,exports){
"use strict";
/// <reference types="qunit" />
exports.__esModule = true;
var ViewSelectorTest_1 = require("./Filtered/ViewSelectorTest");
var ControllerTest_1 = require("./Filtered/ControllerTest");
var ValueFilterTest_1 = require("./Filtered/Filter/ValueFilterTest");
var QUnitTestHandler_1 = require("./Util/QUnitTestHandler");
var ViewTest_1 = require("./Filtered/View/ViewTest");
var MapViewTest_1 = require("./Filtered/View/MapViewTest");
var testclasses = [
    ViewSelectorTest_1.ViewSelectorTest,
    ControllerTest_1.ControllerTest,
    ValueFilterTest_1.ValueFilterTest,
    ViewTest_1.ViewTest,
    MapViewTest_1.MapViewTest,
];
var testhandler = new QUnitTestHandler_1.QUnitTestHandler('ext.srf.formats.filtered', testclasses);
testhandler.runTests();

},{"./Filtered/ControllerTest":7,"./Filtered/Filter/ValueFilterTest":8,"./Filtered/View/MapViewTest":9,"./Filtered/View/ViewTest":10,"./Filtered/ViewSelectorTest":11,"./Util/QUnitTestHandler":14}]},{},[15]);
