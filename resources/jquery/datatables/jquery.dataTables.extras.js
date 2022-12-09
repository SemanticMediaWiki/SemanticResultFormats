/**
 * DataTables extras
 *
 * @see http://datatables.net/
 *
 * @since 1.9
 */
( function( $ ) {
	'use strict';

// https://github.com/SemanticMediaWiki/SemanticResultFormats/issues/185
$.fn.dataTableExt = $.fn.dataTableExt || {};
$.fn.dataTableExt.oSort = $.fn.dataTableExt.oSort || {};
$.fn.dataTableExt.aTypes = $.fn.dataTableExt.aTypes || {
    'unshift': function() {}
};

// table sorting https://datatables.net/reference/option/columns.type
var _anyNumberSort = function(a, b, high) {
	// https://datatables.net/plug-ins/sorting/any-number
    var tmpA = document.createElement("DIV");
    tmpA.innerHTML = a;
    a = tmpA.textContent || tmpA.innerText || "";
 
    var tmpB = document.createElement("DIV");
    tmpB.innerHTML = b;
    b = tmpB.textContent || tmpB.innerText || "";
 
    return a.localeCompare(b, undefined, { numeric: true, sensitivity: 'base' })

	// this returns alwasy 0 on subsequent executions after init ?
/*
	var reg = /[+-]?((\d+(\.\d*)?)|\.\d+)([eE][+-]?[0-9]+)?/;        
	a = a.replace(',','.').match(reg);
	a = a !== null ? parseFloat(a[0]) : high;
	b = b.replace(',','.').match(reg);
	b = b !== null ? parseFloat(b[0]) : high;
	return ((a < b) ? -1 : ((a > b) ? 1 : 0));
*/
}

$.extend( $.fn.dataTableExt.oSort, {
	"any-number-asc": function (a, b) {
		return _anyNumberSort(a, b, Number.POSITIVE_INFINITY);
	},
	"any-number-desc": function (a, b) {
		return _anyNumberSort(a, b, Number.NEGATIVE_INFINITY) * -1;
	}
});

// Sorting Currency Columns
$.extend( $.fn.dataTableExt.aTypes, {
    'unshift': function ( sData ) {
        var sValidChars = "0123456789.-,";
        var Char;

        if( typeof sData === "object" ) {
            /* Check the numeric part */
            for ( var i=1 ; i < sData.length ; i++ ) {
                Char = sData.charAt(i);
                if (sValidChars.indexOf(Char) == -1)
                {
                    return null;
                }
            }

            /* Check prefixed by currency */
            if ( sData.charAt(0) == '$' || sData.charAt(0) == '£' ) {
                return 'currency';
            }
            return null;
        }
    }
} );

$.fn.dataTableExt.oSort['currency-asc'] = function(a,b) {
    /* Remove any formatting */
    var x = a == "-" ? 0 : a.replace( /[^\d\-\.]/g, "" );
    var y = b == "-" ? 0 : b.replace( /[^\d\-\.]/g, "" );

    /* Parse and return */
    x = parseFloat( x );
    y = parseFloat( y );
    return x - y;
};

$.fn.dataTableExt.oSort['currency-desc'] = function(a,b) {
    var x = a === '-' ? 0 : a.replace( /[^\d\-\.]/g, '' );
    var y = b === '-' ? 0 : b.replace( /[^\d\-\.]/g, '' );

    x = parseFloat( x );
    y = parseFloat( y );
    return y - x;
};

// Sorting Formatted Numbers
$.fn.dataTableExt.aTypes.unshift(
	function ( sData ) {
		if( sData !== undefined && $.isNumeric( sData ) ) {
			// var deformatted = sData.replace(/[^\d\-\.\/a-zA-Z]/g,'');
			//if ( $.isNumeric( deformatted ) ) {
				return 'formatted-num';
			}
			return null;
		}
);

$.fn.dataTableExt.oSort['formatted-num-asc'] = function(a,b) {
    /* Remove any formatting */
    var x = a.match(/\d/) ? a.replace( /[^\d\-\.]/g, "" ) : 0;
    var y = b.match(/\d/) ? b.replace( /[^\d\-\.]/g, "" ) : 0;

    /* Parse and return */
    return parseFloat(x) - parseFloat(y);
};

$.fn.dataTableExt.oSort['formatted-num-desc'] = function(a,b) {
    var x = a.match(/\d/) ? a.replace( /[^\d\-\.]/g, "" ) : 0;
    var y = b.match(/\d/) ? b.replace( /[^\d\-\.]/g, "" ) : 0;

    return parseFloat(y) - parseFloat(x);
};

} )( jQuery );
