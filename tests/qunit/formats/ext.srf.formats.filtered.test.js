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

},{"./View/View":5}],2:[function(require,module,exports){
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

},{}],3:[function(require,module,exports){
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
        for (var _i = 0, _a = this.values; _i < _a.length; _i++) {
            var value = _a[_i];
            var option = $('<div class="filtered-value-option">');
            var checkbox = $('<input type="checkbox" class="filtered-value-value" value="' + value.printoutValue + '"  >');
            // attach event handler
            checkbox
                .on('change', undefined, { 'filter': this }, function (eventObject) {
                eventObject.data.filter.onFilterUpdated(eventObject);
            });
            // Try to get label, if not fall back to value id
            var label = value.formattedValue || value.printoutValue; //this.values[ value ] || value;
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

},{"./Filter":2}],4:[function(require,module,exports){
"use strict";
/// <reference types="leaflet" />
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
        return _super.prototype.init.call(this);
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

},{}],6:[function(require,module,exports){
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

},{}],7:[function(require,module,exports){
"use strict";
/// <reference types="qunit" />
exports.__esModule = true;
var Controller_1 = require("../../../resources/ts/Filtered/Controller");
var MockedFilter_1 = require("../Util/MockedFilter");
var View_1 = require("../../../resources/ts/Filtered/View/View");
var ControllerTest = (function () {
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
        targetElement.show = function () {
            targetShown = true;
            return targetElement;
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
        filterIds.forEach(function (filterId) {
            var visibilityWasQueried = false;
            var filter = new MockedFilter_1.MockedFilter(filterId, undefined, undefined, controller);
            filter.isVisible = function (rowId) {
                visibilityWasQueried = true;
                return true;
            };
            // Run
            controller.attachFilter(filter);
            // Assert: Filter was queried for the visibility of result items
            assert.ok(visibilityWasQueried, "Filter \"" + filterId + "\" was queried after attaching.");
            // Assert: Filter correctly attached and retained.
            assert.deepEqual(controller.getFilter(filterId), filter, "Controller knows \"" + filterId + "\" filter.");
        });
    };
    return ControllerTest;
}());
exports.ControllerTest = ControllerTest;

},{"../../../resources/ts/Filtered/Controller":1,"../../../resources/ts/Filtered/View/View":5,"../Util/MockedFilter":12}],8:[function(require,module,exports){
"use strict";
/// <reference types="qunit" />
/// <reference types="jquery" />
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
var ValueFilter_1 = require("../../../../resources/ts/Filtered/Filter/ValueFilter");
var Controller_1 = require("../../../../resources/ts/Filtered/Controller");
var QUnitTest_1 = require("../../Util/QUnitTest");
var ValueFilterTest = (function (_super) {
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
        assert.strictEqual(target.find('.filtered-collapsible').length, 1, 'Added container for collapsable content.');
        assert.strictEqual(target.find('.filtered-value-andor').length, 1, 'Added container for and/or switch.');
        // Assert: One input added per value
        for (var _i = 0, _a = options.values; _i < _a.length; _i++) {
            var value = _a[_i];
            assert.strictEqual(target.find("input[value=\"" + value + "\"]").length, 1, "Added input for value \"" + value + "\".");
        }
    };
    ;
    ValueFilterTest.prototype.testUseOr = function (assert) {
        // Setup
        var controller = new Controller_1.Controller($(), {}, {});
        controller.onFilterUpdated = function (filterId) {
            // Assert
            assert.ok(true, 'Filter updated.');
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
var ViewTest_1 = require("./ViewTest");
var MapView_1 = require("../../../../resources/ts/Filtered/View/MapView");
var Controller_1 = require("../../../../resources/ts/Filtered/Controller");
var MapViewTest = (function (_super) {
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
        if (c === undefined) {
            c = new Controller_1.Controller(undefined, undefined, undefined);
        }
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
var QUnitTest_1 = require("../../Util/QUnitTest");
var View_1 = require("../../../../resources/ts/Filtered/View/View");
var ViewTest = (function (_super) {
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
        v.init();
        // Assert
        assert.ok(v instanceof View_1.View, 'Can construct View.');
        assert.strictEqual(v.getTargetElement(), target, 'View retains target element.');
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

},{"../../../../resources/ts/Filtered/View/View":5,"../../Util/QUnitTest":13}],11:[function(require,module,exports){
"use strict";
/// <reference types="qunit" />
/// <reference types="jquery" />
exports.__esModule = true;
var ViewSelector_1 = require("../../../resources/ts/Filtered/ViewSelector");
var Controller_1 = require("../../../resources/ts/Filtered/Controller");
var ViewSelectorTest = (function () {
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
var Filter_1 = require("../../../resources/ts/Filtered/Filter/Filter");
var MockedFilter = (function (_super) {
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
var QUnitTest = (function () {
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
var QUnitTestHandler = (function () {
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
