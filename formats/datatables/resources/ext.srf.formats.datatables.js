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
				}
			}

			var useAjax = data.query.result.length < context.data("count");

			if (options.searchPanes === true || isObject(options.searchPanes)) {
				if (useAjax) {
					// remove panes because this is tricky to
					// be implemented in conjunction with SMW

					// @TODO show a notice to the editor
					// conf.dom = conf.dom.replace("P", "");
					options.searchPanes = false;

					if (options.dom.indexOf("P") !== -1) {
						options.dom = options.dom.replace("P", "");
						// @TODO show notice to editor
					}
				} else if (options.dom.indexOf("P") === -1) {
					options.dom = "P" + options.dom;
				}
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

			var entityCollation = context.data("collation");

			// @see https://datatables.net/reference/option/
			var arrayTypesColumns = {
				orderable: "boolean",
				searchable: "boolean",
				visible: "boolean",
				orderData: "numeric-array",
				// ...
			};

			var columnDefs = [];
			var labelsCount = {};
			$.map(printrequests, function (property, index) {
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
							var optionKey = i.replace(/datatables-(columns\.)?/, "");

							coulumnDatatablesOptions[optionKey] =
								printouts[index][4][i].trim();

							if (optionKey in arrayTypesColumns) {
								switch (arrayTypesColumns[optionKey]) {
									case "boolean":
										coulumnDatatablesOptions[optionKey] =
											coulumnDatatablesOptions[optionKey].toLowerCase() ===
												"true" ||
											parseInt(coulumnDatatablesOptions[optionKey]) === 1;
										break;
									case "numeric-array":
										coulumnDatatablesOptions[optionKey] = csvToArray(
											coulumnDatatablesOptions[optionKey],
											true
										);
										break;
									// ...
								}
							}
						}
					}
				}

				// this is to avoid duplicate data/keys
				if (!(property.label in labelsCount)) {
					labelsCount[property.label] = 0;
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

				labelsCount[property.label]++;
			});

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
				conf.data = data.query.result;

				// use Ajax only when required
			} else {
				var preloadData = {};

				// cache using the column index and sorting
				// method, as pseudo-multidimensional array
				// column index + dir (asc/desc)
				var cacheKey = JSON.stringify(order);

				preloadData[cacheKey] = data.query.result;

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
						var key = JSON.stringify(
							datatableData.order.map((x) => [x.column, x.dir])
						);

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
							});
						}

						// flush cache each 1 million row
						// @TODO this is only one of the possible
						// methods !
						var totalSize = 0;
						for (var i in preloadData) {
							totalSize = preloadData[i].length;
						}

						if (totalSize > 1000000) {
							console.log("flushing datatables cache!");
							preloadData = {};
						}

						$.ajax({
							url: mw.util.wikiScript("api"),
							dataType: "json",
							data: $.extend(payload, {
								datatable: JSON.stringify(datatableData),
							}),
						})
							.done(function (results) {
								var json = results["datatables-json"];

								// cache all retrieved rows for each sorting
								// dimension (column/dir), up to a global
								// threshold (1,000,000 rows)
								preloadData[key] = preloadData[key]
									.slice(0, datatableData.start)
									.concat(json.data);

								// we retrieve more than "length"
								// expected by datatables, so return the
								// sliced result
								json.data = json.data.slice(0, datatableData.length);
								callback(json);
							})
							.fail(function (error) {
								console.log("error", error);
							});
					},
				});
			}

			data.table = container.find("table").DataTable(conf);
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

