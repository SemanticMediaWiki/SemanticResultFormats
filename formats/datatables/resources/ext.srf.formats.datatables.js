/*!
 * @license GNU GPL v2+
 * @since 3.0
 *
 * @author thomas-topway-it <business@topway.it>
 * @credits mwjames (ext.smw.tableprinter.js)
 */

/* global jQuery, mediaWiki, mw */
(function ($, mw) {
	"use strict";

	var dataTable = {
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

			// In case of a transposed table, don't try to match a column or its order
			if (
				column === undefined ||
				!column.hasOwnProperty("sort") ||
				column.sort.length === 0 ||
				context.attr("data-transpose")
			) {
				return;
			}

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
			}
		},

		/**
		 * @since 3.0
		 *
		 * @private
		 * @static
		 *
		 * @param {Object} context
		 */
		addHeader: function (context) {
			// console.log("addHeader");
			// Copy the thead to a position the DataTable plug-in can transform
			// and display
			if (context.find("thead").length === 0) {
				// console.log("addHeader--");

				var head = context.find("tbody tr");
				context.prepend("<thead>" + head.html() + "</thead>");
				head.eq(0).remove();

				// In case of a transposed, turn any td into a th
				context.find("thead td").wrapInner("<th />").contents().unwrap();
			}

			// Ensure that any link in the header stops the propagation of the
			// click sorting event
			context.find("thead tr a").on("click.sorting", function (event) {
				event.stopPropagation();
			});
		},

		/**
		 * @since 3.0
		 *
		 * @private
		 * @static
		 *
		 * @param {Object} context
		 */
		addFooter: function (context) {
			// console.log("addFooter");

			// As a transposed table, move the footer column to the bottom
			// and remove any footer-cell from the table matrix to
			// ensure a proper formatted table
			if (
				context.data("transpose") === 1 &&
				context.find("tbody .sortbottom").length === 1
			) {
				// console.log("addFooter--");

				var footer = context.find("tbody .sortbottom");
				context.append(
					"<tfoot><tr><td colspan=" +
						footer.index() +
						">" +
						footer.html() +
						"</td></tr></tfoot>"
				);
				footer.eq(0).remove();

				// Remove remaining footer cells to avoid an uneven table
				context.find("tbody .footer-cell").each(function () {
					$(this).remove();
				});
			}

			// Copy the tbody to a position the DataTable plug-in can transform
			// and display
			if (context.find("tbody .smwfooter").length == 1) {
				var footer = context.find("tbody .smwfooter");
				context.append("<tfoot>" + footer.html() + "</tfoot>");
				footer.eq(0).remove();
			}

			context.find(".sortbottom").addClass("plainlinks");
		},

		/**
		 * @since 3.0
		 *
		 * @param {Object} context
		 */
		addToolbarExportLinks: function (context) {
			var toolbar = context.parent().find(".smw-datatable-toolbar"),
				query = context.data("query"),
				exportFormats = {
					JSON: {
						format: "json",
						searchlabel: "JSON",
						type: "simple",
						prettyprint: true,
						unescape: true,
					},
					CSV: {
						format: "csv",
						searchlabel: "CSV",
					},
					RSS: {
						format: "rss",
						searchlabel: "RSS",
					},
					RDF: {
						format: "rdf",
						searchlabel: "RDF",
					},
				};

			if (!query instanceof Object || query === undefined) {
				return;
			}

			var items = "";

			Object.keys(exportFormats).forEach(function (key) {
				// https://stackoverflow.com/questions/122102/what-is-the-most-efficient-way-to-deep-clone-an-object-in-javascript
				var conf = exportFormats[key],
					parameters = $.extend({}, query.parameters);

				// Modify the default query with that of the configuration
				Object.keys(conf).forEach(function (k) {
					parameters[k] = conf[k];
				});

				var q = new smw.query(query.printouts, parameters, query.conditions);

				q.setLinkAttributes({
					title: key,
				});

				if (key === "RDF") {
					items += '<li class="divider"></li>';
				}

				items += '<li class="action">' + q.getLink(key) + "</li>";
			});

			toolbar.append(
				'<span class="smw-dropdown">' +
					"<button>" +
					mw.msg("smw-format-datatable-toolbar-export") +
					"</button>" +
					'<label><input type="checkbox">' +
					'<ul class="smw-dropdown-menu">' +
					items +
					"</ul></label></span>"
			);

			toolbar.find(".action a").on("click", function (e) {
				toolbar.find(".smw-dropdown input").prop("checked", false);
			});
		},

		/**
		 * @since 3.0
		 *
		 * @param {Object} context
		 */
		attach: function (context) {
			var self = this;
			context.show();

			// Remove any class that may interfere due to some external JS or CSS
			context.removeClass("jquery-tablesorter");
			context.removeClass("sortable");
			context.removeClass("is-disabled");
			context.removeClass("wikitable");

			// DataTables default display class
			context.addClass("display");

			self.initColumnSort(context);

			// MediaWiki table output is missing some standard formatting hence
			// add a footer and header
			self.addFooter(context);
			self.addHeader(context);

			// https://datatables.net/manual/tech-notes/3
			// Ensure the object initialization only happens once
			if ($.fn.dataTable.isDataTable(context)) {
				return;
			}

			// console.log("context.data", context.data());

			var options = context.data("datatables");

			var arrayTypes = [
				"LengthMenu",
				"buttons",
				// ...
			];

			// transform csv to array
			for (var i in options) {
				if (arrayTypes.indexOf(i) !== -1) {
					options[i] = options[i]
						.split(",")
						.map((x) => x.trim())
						.filter((x) => x !== "");
				}
			}

			// console.log("options", options);

			// add the button placeholder if any button is required
			if (options.buttons.length && options.dom.indexOf("B") === -1) {
				options.dom = "B" + options.dom;
			}

			if (options.scrollY === -1) {
				delete options.scrollY;
			}

			if (options.scroller === true) {
				options.scroller = { loadingIndicator: true };
			}

			// console.log("max", context.data("max"));
			// console.log("order", context.data("order"));

			// console.log("query", context.data("query"));

			var query = context.data("query");

			var printouts = context.data("printouts");

			// console.log("printouts", printouts);

			var queryString = query.conditions;
			// console.log("queryString", queryString);

			var printrequests = context.data("printrequests");

			// @see https://datatables.net/reference/option/columns.type
			var columnstypePar = context.data("columnstype") || "";
			columnstypePar = columnstypePar
				.split(",")
				.map((x) => x.trim())
				.filter((x) => x !== "");

			var entityCollation = context.data("collation");

			// use the latest set value if one or more column is missing
			var columnsType = null;

			var columnDefs = [];
			$.map(printrequests, function (property, index) {
				if (columnstypePar[index]) {
					columnsType =
						columnstypePar[index] === "auto" ? null : columnstypePar[index];
				} else if (entityCollation) {
					// html-num-fmt
					columnsType =
						entityCollation === "numeric" && property.typeid === "_wpg"
							? "any-number"
							: null;
				}

				columnDefs.push({
					// 'mData': property.label,
					// 'sTitle': property.label,
					// 'sClass': 'smwtype' + property.typeid,
					// 'aTargets': [index]

					// https://datatables.net/reference/option/columnDefs
					data: property.label,
					title: property.label,
					// type: columnsType,
					className: "smwtype" + property.typeid,
					targets: [index],
				});
			});

			// console.log("columnDefs", columnDefs);
/*
			$.ajax({
				url: mw.util.wikiScript("api"),
				dataType: "json",
				data: {
					action: "ext.srf.datatables.json",
					format: "json",
					query: queryString,
					printouts: JSON.stringify(printouts),
					datatable: JSON.stringify({ start: 0, draw: 1 }),
					settings: JSON.stringify({
						max: context.data("max"),
						deferEach: context.data("defer-each"),
					}),
				},
			})
				.done(function (results) {
					console.log("results", results);
				})
				.fail(function (error) {
					console.log("error", error);
				});
*/

			var table = context.DataTable(
				$.extend(options, {
					columnDefs: columnDefs,
					processing: true,
					order: context.data("order"),
					deferRender: true,
					deferLoading: context.data("max"),
					serverSide: true,
					ajax: {
						url: mw.util.wikiScript("api"),
						data: function (d) {
							// console.log("d", d);
							return {
								action: "ext.srf.datatables.json",
								format: "json",
								query: queryString,
								printouts: JSON.stringify(printouts),
								datatable: JSON.stringify(d),
								settings: JSON.stringify({
									max: context.data("max"),
									deferEach: context.data("defer-each"),
								}),
							};
						},
						dataFilter: function (json) {
							json = JSON.parse(json);
							// console.log("json", json["datatables-json"]);
							var json = json["datatables-json"];

							json.data = json.data.map((x) => {
								var ret = {};
								for (var i in x) {
									ret[columnDefs[i].data] = x[i];
								}
								return ret;
							});

							// console.log("json", json);
							return JSON.stringify(json);
						},
					},
				})
			);

			// console.log("options", options);
		},

		/**
		 * @since 3.0
		 *
		 * @private
		 * @static
		 *
		 * @param {Object} context
		 */
		init: function (context) {
			context.removeClass("is-disabled");
			context.removeClass("smw-flex-center");

			context.css("background-color", "transparent");
			context.css("height", "");
			context.find(".smw-overlay-spinner").hide();

			context.find(".datatable").css("opacity", "1");
			context.removeClass("smw-extra-margin");

			context.find(".smw-datatable").removeClass("smw-extra-margin");
			this.attach(context.find(".datatable"));
		},
	};

	$(document).ready(function () {
		$(".smw-datatable").each(function () {
			dataTable.init($(this));
		});
	});
})(jQuery, mediaWiki);

