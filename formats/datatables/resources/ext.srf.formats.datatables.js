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
	 * Cache results retrieved through Ajax up to
	 * a certain limit, this allows smooth navigation
	 * of pages already retrieved, without to perform
	 * an Ajax request again
	 */
	var _cacheLimit = 40000;

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

		initSearchPanesColumns(columnDefs, options) {
			for (var i in columnDefs) {
				if (!("searchPanes" in columnDefs[i])) {
					columnDefs[i].searchPanes = {};
				}

				if (
					"show" in columnDefs[i].searchPanes &&
					columnDefs[i].searchPanes.show === false
				) {
					delete columnDefs[i].searchPanes;
					continue;
				}

				if (
					"columns" in options.searchPanes &&
					options.searchPanes.columns.length &&
					$.inArray(i * 1, options.searchPanes.columns) < 0
				) {
					delete columnDefs[i].searchPanes;
				}
			}
		},

		getPanesOptions: function (data, columnDefs, options) {
			var ret = {};
			var dataLength = {};
			var div = document.createElement("div");

			for (var i in columnDefs) {
				if ("searchPanes" in columnDefs[i]) {
					ret[i] = {};
					dataLength[i] = 0;
				}
			}

			for (var i in data) {
				for (var ii in ret) {
					if (data[i][ii] === "") {
						continue;
					}
					dataLength[ii]++;
					var label;
					if (options.searchPanes.htmlLabels === false) {
						div.innerHTML = data[i][ii];
						label = div.textContent || div.innerText || "";
					} else {
						label = data[i][ii];
					}

					// this will exclude images as well if
					// options.searchPanes.htmlLabels === false
					if (label === "") {
						continue;
					}

					if (!(data[i][ii] in ret[ii])) {
						ret[ii][data[i][ii]] = {
							label: label,
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
				// data.length;
				var uniqueRatio = binLength / dataLength[i];

				//  || binLength <= 1
				if (uniqueRatio > threshold) {
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

			for (var i in columnDefs) {
				if (!(i in ret)) {
					// delete columnDefs[i].searchPanes;
					columnDefs[i].searchPanes = { show: false };
				}
			}

			return ret;
		},

		setPanesOptions: function (searchPanesOptions, columnDefs) {
			for (let i in searchPanesOptions) {
				// @see https://datatables.net/reference/option/columns.searchPanes.combiner
				columnDefs[i].searchPanes.combiner =
					"combiner" in columnDefs[i].searchPanes
						? columnDefs[i].searchPanes.combiner
						: "or";
				columnDefs[i].searchPanes.options = [];

				// @see https://datatables.net/reference/option/columns.searchPanes.options
				for (let ii in searchPanesOptions[i]) {
					columnDefs[i].searchPanes.options.push({
						label: searchPanesOptions[i][ii].label,
						value: function (rowData, rowIdx) {
							return rowData[i] === searchPanesOptions[i][ii].value;
						},
					});
				}

				// @TODO sort panes after rendering using the following
				// https://github.com/DataTables/SearchPanes/blob/master/src/SearchPane.ts
			}
		},

		searchPanesOptionsServer: function (
			searchPanesOptions,
			columnDefs,
			options
		) {
			var div = document.createElement("div");
			for (var i in searchPanesOptions) {
				if (!("searchPanes" in columnDefs[i])) {
					columnDefs[i].searchPanes = {};
				}
				columnDefs[i].searchPanes.show =
					Object.keys(searchPanesOptions[i]).length > 0;

				for (var ii in searchPanesOptions[i]) {
					if (options.searchPanes.htmlLabels === false) {
						div.innerHTML = searchPanesOptions[i][ii].label;
						searchPanesOptions[i][ii].label =
							div.textContent || div.innerText || "";
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

		// we don't need it anymore, however keep is as
		// a reference for alternate use
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

			var options = data["formattedOptions"];

			// add the button placeholder if any button is required
			if (options.buttons.length && options.dom.indexOf("B") === -1) {
				options.dom = "B" + options.dom;
			}

			function isObject(obj) {
				return obj !== null && typeof obj === "object" && !Array.isArray(obj);
			}

			if (isObject(options.scroller)) {
				if (!("scrollY" in options) || !options.scrollY) {
					options.scrollY = "300px";

					// expected type is string
				} else if (!isNaN(options.scrollY)) {
					options.scrollY = options.scrollY + "px";
				}
			}

			var queryResult = data.query.result;
			var useAjax = context.data("useAjax");

			var searchPanes = isObject(options.searchPanes);

			if (searchPanes) {
				if (options.dom.indexOf("P") === -1) {
					options.dom = "P" + options.dom;
				}
			} else {
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

			var displayLog = mw.config.get("performer") === context.data("editor");

			if (displayLog) {
				console.log("searchPanesLog", searchPanesLog);
			}

			var entityCollation = context.data("collation");

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

				columnDefs.push(
					$.extend(
						{
							// https://datatables.net/reference/option/columnDefs
							// data: ...
							title: property.label,
							// get canonical label or empty string if mainlabel
							name: printrequests[index].key !== "" ? printouts[index][1] : "",
							className: "smwtype" + property.typeid,
							targets: [index],
						},
						options.columns,
						data.printoutsParametersOptions[index]
					)
				);

				// labelsCount[property.label]++;
			});

			if (searchPanes) {
				_datatables.initSearchPanesColumns(columnDefs, options);

				// @TODO remove "useAjax" and use the following trick
				// https://github.com/Knowledge-Wiki/SemanticResultFormats/blob/2230aa3eb8e65dd33ff493ba81269689f50d2945/formats/datatables/resources/ext.srf.formats.datatables.js
				// to use searchPanesOptions created server-side when Ajax is
				// not required, unfortunately we cannot use the function
				// described here https://datatables.net/reference/option/columns.searchPanes.options
				// with the searchPanes data retrieved server-side, since
				// we cannot simply provide count, label, and value in the searchPanesOptions
				// (since is not allowed by the Api) -- however the current solution
				// works fine in most cases
				if (
					// options["searchPanes"]["forceClient"] ||
					!useAjax ||
					!Object.keys(searchPanesOptions).length
				) {
					searchPanesOptions = _datatables.getPanesOptions(
						queryResult,
						columnDefs,
						options
					);
					_datatables.setPanesOptions(searchPanesOptions, columnDefs);
				} else {
					searchPanesOptions = _datatables.searchPanesOptionsServer(
						searchPanesOptions,
						columnDefs,
						options
					);
				}
			}

			var conf = $.extend(options, {
				columnDefs: columnDefs,
				language: _datatables.oLanguage,
				order: order,
				search: {
					caseInsensitive: context.data("nocase"),
				},
			});

			// cacheKey ensures that the cached pages
			// are related to current sorting and searchPanes filters
			var getCacheKey = function (obj) {
				return (
					JSON.stringify(obj.order) +
					(!searchPanes
						? ""
						: JSON.stringify(
								Object.keys(obj.searchPanes).length
									? obj.searchPanes
									: Object.fromEntries(
											Object.keys(columnDefs).map((x) => [x, {}])
									  )
						  ))
				);
			};

			if (!useAjax) {
				conf.serverSide = false;
				conf.data = queryResult;

				// use Ajax only when required
			} else {
				var preloadData = {};

				// cache using the column index and sorting
				// method, as pseudo-multidimensional array
				// column index + dir (asc/desc) + searchPanes (empty selection)
				var cacheKey = getCacheKey({
					order: order.map((x) => {
						return { column: x[0], dir: x[1] };
					}),
					searchPanes: {},
				});

				preloadData[cacheKey] = {
					data: queryResult,
					count: context.data("count"),
				};

				var payload = {
					action: "ext.srf.datatables.api",
					format: "json",
					query: queryString,
					columndefs: JSON.stringify(columnDefs),
					printouts: JSON.stringify(printouts),
					printrequests: JSON.stringify(printrequests),
					settings: JSON.stringify(
						$.extend(
							{ count: context.data("count"), displayLog: displayLog },
							query.parameters
						)
					),
				};

				conf = $.extend(conf, {
					// *** attention! deferLoading when used in conjunction with
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
						var key = getCacheKey(datatableData);

						if (!(key in preloadData)) {
							preloadData[key] = { data: [] };
						}

						// returned cached data for the required
						// dimension (order column/dir)
						if (
							datatableData.search.value === "" &&
							datatableData.start + datatableData.length <=
								preloadData[key]["data"].length
						) {
							return callback({
								draw: datatableData.draw,
								data: preloadData[key]["data"].slice(
									datatableData.start,
									datatableData.start + datatableData.length
								),
								recordsTotal: context.data("count"),
								recordsFiltered: preloadData[key]["count"],
								searchPanes: {
									options: searchPanesOptions,
								},
							});
						}

						// flush cache each 40,000 rows
						// *** another method is to compute the actual
						// size in bytes of each row, but it takes more
						// resources
						for (var i in preloadData) {
							var totalSize = preloadData[i]["data"].length;

							if (totalSize > _cacheLimit) {
								console.log("flushing datatables cache!");
								preloadData[i] = {};
							}
						}

						new mw.Api()
							.post(
								$.extend(payload, {
									datatable: JSON.stringify(datatableData),
								})
							)
							.done(function (results) {
								var json = results["datatables-json"];

								if (displayLog) {
									console.log("results log", json.log);
								}

								// cache all retrieved rows for each sorting
								// dimension (column/dir), up to a fixed
								// threshold (_cacheLimit)

								if (datatableData.search.value === "") {
									preloadData[key] = {
										data: preloadData[key]["data"]
											.slice(0, datatableData.start)
											.concat(json.data),
										count: json.recordsFiltered,
									};
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
			// console.log("conf", conf);
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
