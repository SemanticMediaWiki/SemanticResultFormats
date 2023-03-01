(function ($) {
	var _anyNumberSort = function (a, b, high) {
		// https://datatables.net/plug-ins/sorting/any-number
		var tmpA = document.createElement("DIV");
		tmpA.innerHTML = a;
		a = tmpA.textContent || tmpA.innerText || "";

		var tmpB = document.createElement("DIV");
		tmpB.innerHTML = b;
		b = tmpB.textContent || tmpB.innerText || "";

		return a.localeCompare(b, undefined, {
			numeric: true,
			sensitivity: "base",
		});
	};

	$.extend($.fn.dataTableExt.oSort, {
		"any-number-asc": function (a, b) {
			return _anyNumberSort(a, b, Number.POSITIVE_INFINITY);
		},
		"any-number-desc": function (a, b) {
			return _anyNumberSort(a, b, Number.NEGATIVE_INFINITY) * -1;
		},
	});
})(jQuery);

