/**
 * SRF DataTables JavaScript Printer using the SMWAPI
 *
 * @see http://datatables.net/
 *
 * @licence GPL-2.0-or-later
 * @author thomas-topway-it for KM-A
 * @credits mwjames (ext.smw.tableprinter.js)
 */

(function ($, mw, srf) {
	"use strict";

	/* Private methods and objects */

	/**
	 * Helper objects
	 *
	 * @since 1.9
	 *
	 * @ignore
	 * @private
	 * @static
	 */
	var html = mw.html;

	/**
	 * Container for all non-public objects and methods
	 *
	 * @private
	 * @member srf.formats.datatables
	 */
	var _datatables = {
		/**
		 * Returns ID
		 *
		 * @private
		 * @return {string}
		 */
		getID: function (container) {
			return container.attr("id");
		},

		/**
		 * Returns container data
		 *
		 * @private
		 * @return {object}
		 */
		getData: function (container) {
			return mw.config.get(this.getID(container));
		},

		/**
		 * Adds the initial sort/order from the #ask request that is available as
		 * `data-column-sort` attribute with something like:
		 *
		 * {
		 *  "list":["","Foo","Bar"]
		 *  "sort":["Foo"],
		 *  "order":["asc"]
		 * }
		 *
		 * on
		 *
		 * {{#ask: ...
		 *  |?Foo
		 *  |?Bar
		 *  |sort=Foo
		 *  |order=asc
		 *  ...
		 * }}
		 *
		 * @since 3.0
		 *
		 * @private
		 * @static
		 *
		 * @param {Object} context
		 */
		initColumnSort: function (context) {
			var column = context.data("column-sort");

			var order = [];

			// SMW allows descending and ascending but those are unknown to DataTables
			var orderMap = {
				descending: "desc",
				ascending: "asc",
				asc: "asc",
				desc: "desc",
			};

			// https://datatables.net/reference/api/order()
			// [1, 'asc'], [2, 'desc']
			$.map(column.sort, function (val, i) {
				if (val === "") {
					i = 0;
				}

				if ($.inArray(val, column.list) < 0) {
					return;
				}

				order.push([
					$.inArray(val, column.list), // Find matchable index from the list
					column.order[i] === undefined ? "asc" : orderMap[column.order[i]],
				]);
			});

			if (order.length > 0) {
				context.data("order", order);
			} else {
				// default @see https://datatables.net/reference/option/order
				context.data("order", [[0, "asc"]]);
			}
		},

		searchPanesOptions: function (data, options, columnDefs) {
			var ret = {};

			for (var i in columnDefs) {
				if (!("searchPanes" in columnDefs[i])) {
					columnDefs[i].searchPanes = {};
				}
			}

			// filter columns
			var columns = Object.keys(data[0]).filter(function (x) {
				if (
					"show" in columnDefs[x].searchPanes &&
					columnDefs[x].searchPanes.show === false
				) {
					return false;
				}

				if (
					"columns" in options.searchPanes &&
					options.searchPanes.columns.length &&
					$.inArray(x * 1, options.searchPanes.columns) < 0
				) {
					return false;
				}

				return true;
			});

			for (var i of columns) {
				ret[i] = {};
			}

			var div = document.createElement("div");
			for (var i in data) {
				for (var ii of columns) {
					div.innerHTML = data[i][ii];
					var text = div.textContent || div.innerText || "";

					// this will exclude images as well,
					// otherwise use data[i][ii]
					if (text === "") {
						continue;
					}

					if (!(data[i][ii] in ret[ii])) {
						ret[ii][data[i][ii]] = {
							label: text,
							value: data[i][ii],
							count: 0,
						};
					}

					ret[ii][data[i][ii]].count++;
				}
			}

			for (var i in ret) {
				var threshold =
					"threshold" in columnDefs[i].searchPanes
						? columnDefs[i].searchPanes.threshold
						: options.searchPanes.threshold;

				// @see https://datatables.net/extensions/searchpanes/examples/initialisation/threshold.htm
				// @see https://github.com/DataTables/SearchPanes/blob/818900b75dba6238bf4b62a204fdd41a9b8944b7/src/SearchPane.ts#L824
				// _uniqueRatio
				var binLength = Object.keys(ret[i]).length;
				var uniqueRatio = binLength / data.length;

				if (uniqueRatio > threshold || binLength <= 1) {
					delete ret[i];
					continue;
				}

				ret[i] = Object.values(ret[i]).filter(
					(x) => x.count >= options.searchPanes.minCount
				);

				if (!ret[i].length) {
					delete ret[i];
				}
			}

			for (let i in ret) {
				// @see https://datatables.net/reference/option/columns.searchPanes.combiner
				columnDefs[i].searchPanes.combiner =
					"combiner" in columnDefs[i].searchPanes
						? columnDefs[i].searchPanes.combiner
						: "or";
				columnDefs[i].searchPanes.options = [];

				// @see https://datatables.net/reference/option/columns.searchPanes.options
				for (let ii in ret[i]) {
					columnDefs[i].searchPanes.options.push({
						label: ret[i][ii].label,
						value: function (rowData, rowIdx) {
							return rowData[i] === ret[i][ii].value;
						},
					});
				}
			}

			for (var i in columnDefs) {
				if (
					!("options" in columnDefs[i].searchPanes) ||
					!columnDefs[i].searchPanes.options.length
				) {
					columnDefs[i].searchPanes.show = false;
				}
			}
		},

		searchPanesOptionsServer: function (searchPanesOptions, options, columnDefs ) {

			var div = document.createElement("div");
			for (var i in searchPanesOptions) {
				if (!("searchPanes" in columnDefs[i])) {
					columnDefs[i].searchPanes = {};
				}
				columnDefs[i].searchPanes.show =
					Object.keys(searchPanesOptions[i]).length > 0;

				for (var ii in searchPanesOptions[i]) {

					if ( options.searchPanes.htmlLabels === false ) {
						div.innerHTML = searchPanesOptions[i][ii].label;
						searchPanesOptions[i][ii].label = div.textContent || div.innerText || "";
					}

					searchPanesOptions[i][ii].total = searchPanesOptions[i][ii].count;
				}
			}

			for (var i in columnDefs) {
				if ("searchPanes" in columnDefs[i] && !(i in searchPanesOptions)) {
					delete columnDefs[i].searchPanes;
				}
			}

			return searchPanesOptions;
		},

		parse: {
			// ...
		},

		exportlinks: function (context, data) {
			// ...
		},

		/**
		 * Internationalization
		 * @see  http://datatables.net/usage/i18n
		 *
		 * @private
		 * @return {object}
		 */
		oLanguage: {
			oAria: {
				sSortAscending: mw.msg("srf-ui-datatables-label-oAria-sSortAscending"),
				sSortDescending: mw.msg(
					"srf-ui-datatables-label-oAria-sSortDescending"
				),
			},
			oPaginate: {
				sFirst: mw.msg("srf-ui-datatables-label-oPaginate-sFirst"),
				sLast: mw.msg("srf-ui-datatables-label-oPaginate-sLast"),
				sNext: mw.msg("srf-ui-datatables-label-oPaginate-sNext"),
				sPrevious: mw.msg("srf-ui-datatables-label-oPaginate-sPrevious"),
			},
			sEmptyTable: mw.msg("srf-ui-datatables-label-sEmptyTable"),
			sInfo: mw.msg("srf-ui-datatables-label-sInfo"),
			sInfoEmpty: mw.msg("srf-ui-datatables-label-sInfoEmpty"),
			sInfoFiltered: mw.msg("srf-ui-datatables-label-sInfoFiltered"),
			sInfoPostFix: mw.msg("srf-ui-datatables-label-sInfoPostFix"),
			sInfoThousands: mw.msg("srf-ui-datatables-label-sInfoThousands"),
			sLengthMenu: mw.msg("srf-ui-datatables-label-sLengthMenu"),
			sLoadingRecords: mw.msg("srf-ui-datatables-label-sLoadingRecords"),
			sProcessing: mw.msg("srf-ui-datatables-label-sProcessing"),
			sSearch: mw.msg("srf-ui-datatables-label-sSearch"),
			sZeroRecords: mw.msg("srf-ui-datatables-label-sZeroRecords"),
		},

		/**
		 * UI components
		 *
		 * @private
		 * @param  {array} context
		 * @param  {array} container
		 * @param  {array} data
		 */
		ui: function (context, container, data) {
			// ...
		},

		showNotice: function (context, container, msg) {
			var cookieKey =
				"srf-ui-datatables-searchpanes-notice-" +
				mw.config.get("wgUserName") +
				"-" +
				mw.config.get("wgArticleId");

			if (
				mw.config.get("wgUserName") != context.data("editor") ||
				mw.cookie.get(cookieKey)
			) {
				return;
			}

			var messageWidget = new OO.ui.MessageWidget({
				type: "warning",
				label: new OO.ui.HtmlSnippet(mw.msg(msg)),
				// *** this does not work before ooui v0.43.0
				showClose: true,
			});
			var closeFunction = function () {
				// 1 month
				var expires = 1 * 30 * 24 * 3600;
				mw.cookie.set(cookieKey, true, {
					path: "/",
					expires: expires,
				});
				$(messageWidget.$element).parent().remove();
			};
			messageWidget.on("close", closeFunction);
			$(context).prepend($("<div><br/></div>").prepend(messageWidget.$element));
			if (!messageWidget.$element.hasClass("oo-ui-messageWidget-showClose")) {
				messageWidget.$element.addClass("oo-ui-messageWidget-showClose");
				var closeButton = new OO.ui.ButtonWidget({
					classes: ["oo-ui-messageWidget-close"],
					framed: false,
					icon: "close",
					label: OO.ui.msg("ooui-popup-widget-close-button-aria-label"),
					invisibleLabel: true,
				});
				closeButton.on("click", closeFunction);
				messageWidget.$element.append(closeButton.$element);
			}
		},
	};

	/**
	 * Inheritance class for the srf.formats constructor
	 *
	 * @since 1.9
	 *
	 * @class
	 * @abstract
	 */
	srf.formats = srf.formats || {};

	/**
	 * Class that contains the DataTables JavaScript result printer
	 *
	 * @since 1.9
	 *
	 * @class
	 * @constructor
	 * @extends srf.formats
	 */
	srf.formats.datatables = function () {};

	/* Public methods */

	srf.formats.datatables.prototype = {
		/**
		 * Default settings
		 *
		 * @note MW 1.21 vs MW 1.20
		 * Apparently mw.config.get( 'srf' )/mw.config.get( 'smw' ) does only work
		 * in MW 1.21 therefore instead of being customizable those settings are
		 * going to be fixed
		 *
		 * TTL (if enabled) cache for resultObject is set to be 15 min by default
		 * TTL (if enabled) cache for imageInfo is set to be 24 h
		 *
		 * @since  1.9
		 *
		 * @property
		 */
		defaults: {
			// ...
		},

		/**
		 * Initializes the DataTables instance
		 *
		 * @since 1.9
		 *
		 * @param  {array} context
		 * @param  {array} container
		 * @param  {array} data
		 */
		init: function (context, container, data) {
			var self = this;

			// Hide loading spinner
			context.find(".srf-loading-dots").hide();

			// Show container
			container.css({ display: "block" });

			_datatables.initColumnSort(context);

			var order = context.data("order");

			// Setup a raw table
			container.html(
				html.element("table", {
					style: "width: 100%",
					class:
						context.data("theme") === "bootstrap"
							? "bordered-table zebra-striped"
							: "display", // nowrap
					cellpadding: "0",
					cellspacing: "0",
					border: "0",
				})
			);

			var options = context.data("datatables");

			var arrayTypes = {
				lengthMenu: "number",
				buttons: "string",
				"searchPanes.columns": "number",
				// ...
			};

			// function isNumeric(str) {
			// 	if (typeof str != "string") {
			// 		return false;
			// 	}
			//
			// 	return !isNaN(str) && !isNaN(parseFloat(str));
			// }

			function csvToArray(str, numeric) {
				var arr = str
					.split(",")
					.map((x) => x.trim())
					.filter((x) => x !== "");

				if (!numeric) {
					return arr;
				}

				return arr.map((x) => x * 1);
			}

			// enable searchPanes if the symbol is in the dom
			// if (options.dom.indexOf("P") !== -1) {
			//   options.searchPanes = true;
			// }

			for (var i in options) {
				// transform csv to array
				if (i in arrayTypes) {
					options[i] = csvToArray(options[i], arrayTypes[i] === "number");
				}

				// convert strings like columns.searchPanes.show
				// to nested objects
				var arr = i.split(".");
				if (arr.length === 1) {
					continue;
				}

				arr.reduce(function (acc, value, index, arr) {
					// if value of parent parameter is false,
					// for istance scroller = false, then ignore
					// all children
					if (index === 0 && options[value] === false) {
						delete options[i];
						arr.splice(index + 1);
						return {};
					}

					if (index < arr.length - 1) {
						acc[value] = $.extend({}, acc[value]);
					} else {
						acc[value] = options[i];
						delete options[i];
					}

					return acc[value];
				}, options);
			}

			// add the button placeholder if any button is required
			if (options.buttons.length && options.dom.indexOf("B") === -1) {
				options.dom = "B" + options.dom;
			}

			function isObject(obj) {
				return obj !== null && typeof obj === "object" && !Array.isArray(obj);
			}

			if (options.scroller === true || isObject(options.scroller)) {
				if (!("scrollY" in options) || !options.scrollY) {
					options.scrollY = "300px";

					// expected type is string
				} else if (!isNaN(options.scrollY)) {
					options.scrollY = options.scrollY + "px";
				}
			}

			var queryResult = data.query.result;
			var useAjax = queryResult.length < context.data("count");

			var searchPanes = false;
			if (isObject(options.searchPanes)) {
				if (useAjax) {
					// remove panes because this is tricky to
					// be implemented in conjunction with SMW
					// options.searchPanes = false;
					// if (options.dom.indexOf("P") !== -1) {
					// 	options.dom = options.dom.replace("P", "");
					// }
					// _datatables.showNotice(
					// 	context,
					// 	container,
					// 	"srf-ui-datatables-searchpanes-noajax"
					// );
				} else {
					// searchPanes = true;
					// if (options.dom.indexOf("P") === -1) {
					// 	options.dom = "P" + options.dom;
					// }
				}

				searchPanes = true;
				if (options.dom.indexOf("P") === -1) {
					options.dom = "P" + options.dom;
				}
			}

			if (searchPanes === false) {
				options.dom = options.dom.replace("P", "");
			}

			// add the pagelength at the proper place in the length menu
			if ($.inArray(options.pageLength, options.lengthMenu) < 0) {
				options.lengthMenu.push(options.pageLength);
				options.lengthMenu.sort(function (a, b) {
					return a - b;
				});
			}

			var query = data.query.ask;
			var printouts = context.data("printouts");
			var queryString = query.conditions;
			var printrequests = context.data("printrequests");
			var searchPanesOptions = data.searchPanes;
			var searchPanesLog = data.searchPanesLog;

			if (mw.config.get("wgUserName") === context.data("editor")) {
				console.log("searchPanesLog", searchPanesLog);
			}

			var entityCollation = context.data("collation");

			// @see https://datatables.net/reference/option/
			var arrayTypesColumns = {
				orderable: "boolean",
				searchable: "boolean",
				visible: "boolean",
				orderData: "numeric-array",
				"searchPanes.collapse": "boolean",
				"searchPanes.controls": "boolean",
				"searchPanes.hideCount": "boolean",
				"searchPanes.orderable": "boolean",
				"searchPanes.initCollapsed": "boolean",
				"searchPanes.show": "boolean",
				"searchPanes.threshold": "number",
				"searchPanes.viewCount": "boolean",
				// ...
			};

			var columnDefs = [];
			$.map(printrequests, function (property, index) {
				// @see https://datatables.net/reference/option/columns.type
				// value for all columns
				if (!options.columns.type) {
					options.columns.type =
						entityCollation === "numeric" && property.typeid === "_wpg"
							? "any-number"
							: null;
				}

				var coulumnDatatablesOptions = {};
				if (printouts[index] && isObject(printouts[index][4])) {
					for (var i in printouts[index][4]) {
						if (i.indexOf("datatables-") === 0) {
							var printoutValue = printouts[index][4][i].trim();

							var optionKey = i.replace(/datatables-(columns\.)?/, "");

							if (
								searchPanes === false &&
								optionKey.indexOf("searchPanes.") === 0
							) {
								continue;
							}

							if (optionKey in arrayTypesColumns) {
								switch (arrayTypesColumns[optionKey]) {
									case "boolean":
										printoutValue =
											printoutValue.toLowerCase() === "true" ||
											parseInt(printoutValue) === 1;
										break;
									case "numeric-array":
										printoutValue = csvToArray(printoutValue, true);
										break;
									case "number":
										printoutValue = printoutValue * 1;
										break;
									// ...
								}
							}

							// convert strings like columns.searchPanes.show
							// to nested objects
							var arr = optionKey.split(".");

							arr.reduce(function (acc, value, index_, arr) {
								if (index_ < arr.length - 1) {
									acc[value] = $.extend({}, acc[value]);
								} else {
									acc[value] = printoutValue;
									delete options[i];
								}

								return acc[value];
							}, coulumnDatatablesOptions);
						}
					}
				}

				columnDefs.push(
					$.extend(
						{
							// https://datatables.net/reference/option/columnDefs
							/*
							data:
								property.label +
								(labelsCount[property.label] == 0
									? ""
									: "_" + labelsCount[property.label]),
*/
							title: property.label,

							// get canonical label or empty string if mainlabel
							name: printrequests[index].key !== "" ? printouts[index][1] : "",
							className: "smwtype" + property.typeid,
							targets: [index],
						},
						options.columns,
						coulumnDatatablesOptions
					)
				);

				// labelsCount[property.label]++;
			});

			if (searchPanes && !useAjax) {
				_datatables.searchPanesOptions(queryResult, options, columnDefs);
			} else {
				searchPanesOptions = _datatables.searchPanesOptionsServer(
					searchPanesOptions,
					options,
					columnDefs
				);
			}

			// console.log("columnDefs",columnDefs)

			var conf = $.extend(options, {
				columnDefs: columnDefs,
				language: _datatables.oLanguage,
				order: order,
				search: {
					caseInsensitive: context.data("nocase"),
				},
			});

			if (!useAjax) {
				conf.serverSide = false;
				conf.data = queryResult;

				// use Ajax only when required
			} else {
				var preloadData = {};

				// cache using the column index and sorting
				// method, as pseudo-multidimensional array
				// column index + dir (asc/desc)
				var cacheKey = JSON.stringify(order) + JSON.stringify({});

				preloadData[cacheKey] = queryResult;

				var payload = {
					action: "ext.srf.datatables.api",
					format: "json",
					query: queryString,
					columndefs: JSON.stringify(columnDefs),
					printouts: JSON.stringify(printouts),
					printrequests: JSON.stringify(printrequests),
					settings: JSON.stringify(
						$.extend({ count: context.data("count") }, query.parameters)
					),
				};

				conf = $.extend(conf, {
					// *** attention! deferLoading if used in conjunction with
					// ajax, expects only the first page of data, if the preloaded
					// data contain more rows, datatables will show a wrong rows
					// counter. For this reason we renounce to use deferRender, and
					// instead we use the following hack: the Ajax function returns
					// the preloaded data as long they are available for the requested
					// slice, and then it uses an ajax call for not available data.
					// deferLoading: context.data("count"),

					processing: true,
					serverSide: true,
					ajax: function (datatableData, callback, settings) {
						// must match cacheKey
						var key =
							JSON.stringify(
								datatableData.order.map((x) => [x.column, x.dir])
							) + JSON.stringify(datatableData.searchPanes);

						if (!(key in preloadData)) {
							preloadData[key] = [];
						}

						// returned cached data for the required
						// dimension (order column/dir)
						if (
							datatableData.search.value === "" &&
							datatableData.start + datatableData.length <=
								preloadData[key].length
						) {
							return callback({
								draw: datatableData.draw,
								data: preloadData[key].slice(
									datatableData.start,
									datatableData.start + datatableData.length
								),
								recordsTotal: context.data("count"),
								recordsFiltered: context.data("count"),
								searchPanes: {
									options: searchPanesOptions,
								},
							});
						}

						// flush cache each 100,000 rows
						// @TODO this is only one of the possible
						// methods !
						var totalSize = 0;
						for (var i in preloadData) {
							totalSize = preloadData[i].length;
						}

						if (totalSize > 100000) {
							console.log("flushing datatables cache!");
							preloadData = {};
						}

						new mw.Api()
							.post(
								$.extend(payload, {
									datatable: JSON.stringify(datatableData),
								})
							)
							.done(function (results) {

								var json = results["datatables-json"];

								if (mw.config.get("wgUserName") === context.data("editor")) {
									console.log("results log",json.log)
								}

								// cache all retrieved rows for each sorting
								// dimension (column/dir), up to a global
								// threshold (100,000 rows)

								if (datatableData.search.value === "") {
									preloadData[key] = preloadData[key]
										.slice(0, datatableData.start)
										.concat(json.data);
								}

								// we retrieve more than "length"
								// expected by datatables, so return the
								// sliced result
								json.data = json.data.slice(0, datatableData.length);

								json.searchPanes = {
									options: searchPanesOptions,
								};
								callback(json);
							})
							.fail(function (error) {
								console.log("error", error);
							});
					},
				});
			}
			console.log("conf", conf);
			container.find("table").DataTable(conf);
		},

		update: function (context, data) {
			// ...
		},

		test: {
			// ...
		},
	};

	/**
	 * dataTables implementation
	 *
	 * @ignore
	 */
	var datatables = new srf.formats.datatables();

	$(document).ready(function () {
		$(".srf-datatable").each(function () {
			var context = $(this),
				container = context.find(".datatables-container");

			var data = JSON.parse(_datatables.getData(container));

			datatables.init(context, container, data);
		});
	});
})(jQuery, mediaWiki, semanticFormats);
