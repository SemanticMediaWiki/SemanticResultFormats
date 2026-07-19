'use strict';

const path = require( 'path' );

require( 'jquery-ui/ui/widgets/datepicker.js' );
require( path.resolve( __dirname, '../../resources/ext.srf.util.html.js' ) );
require( path.resolve( __dirname, '../../formats/calendar/resources/ext.srf.widgets.calendarparameters.js' ) );

QUnit.module( 'ext.srf.widgets.calendarparameters', () => {

	QUnit.test( 'instance', ( assert ) => {
		const context = $( '<div class="test"></div>' ).appendTo( document.body );

		const result = context.calendarparameters();

		assert.true( result.is( context ), 'the srf.calendarparameters widget returned the context element (its _init() creates no wrapper)' );
	} );

	QUnit.test( 'dateSelection: building the portlet content', ( assert ) => {
		const context = $( '<div class="test"></div>' ).appendTo( document.body );
		const list = [ 'Start date', 'End date' ];

		context.calendarparameters();
		context.calendarparameters( 'dateSelection', {
			list: list,
			browser: 'firefox',
			dateFormat: 'yy-mm-dd'
		} );

		assert.strictEqual( context.find( '.datepicker' ).length, 1, 'a .datepicker container was added' );
		assert.strictEqual( context.find( '.options' ).length, 1, 'an .options container was added' );
		assert.strictEqual( context.find( 'input:radio[name=option]' ).length, 2, 'the from/to radio inputs were added' );
		assert.strictEqual( context.find( '#printouts' ).length, 1, 'the printouts dropdown was added' );
		assert.true( context.find( '#printouts' ).prop( 'disabled' ), 'the printouts dropdown starts disabled' );
		assert.strictEqual( context.find( '#printouts option' ).length, list.length + 1, 'the dropdown has one option per list entry plus the empty default option' );
		assert.strictEqual( context.find( '#mini-calendar-from' ).length, 1, 'the from date input was added' );
		assert.strictEqual( context.find( '#mini-calendar-to' ).length, 1, 'the to date input was added' );
	} );

	QUnit.test( 'dateSelection: printouts dropdown change stores the property index on the checked radio target', ( assert ) => {
		const context = $( '<div class="test"></div>' ).appendTo( document.body );
		const list = [ 'Start date', 'End date' ];

		context.calendarparameters();
		context.calendarparameters( 'dateSelection', {
			list: list,
			browser: 'firefox',
			dateFormat: 'yy-mm-dd'
		} );

		context.find( '#from' ).prop( 'checked', true );
		context.find( '#printouts' ).val( '1' ).trigger( 'change' );
		assert.strictEqual( context.find( '#mini-calendar-from' ).data( 'property' ), '1', 'the from input stored the selected printout index' );

		context.find( 'input:radio[name=option]' ).prop( 'checked', false );
		context.find( '#to' ).prop( 'checked', true );
		context.find( '#printouts' ).val( '0' ).trigger( 'change' );
		assert.strictEqual( context.find( '#mini-calendar-to' ).data( 'property' ), '0', 'the to input stored the selected printout index' );
	} );

	QUnit.test( 'dateSelection: radio click restores the datepicker to the stored from/to date and re-enables the dropdown', ( assert ) => {
		const context = $( '<div class="test"></div>' ).appendTo( document.body );
		const list = [ 'Start date', 'End date' ];

		context.calendarparameters();
		context.calendarparameters( 'dateSelection', {
			list: list,
			browser: 'firefox',
			dateFormat: 'yy-mm-dd'
		} );

		context.find( '#mini-calendar-from' ).val( '2024-01-01' ).data( 'property', '0' );
		context.find( '#from' ).prop( 'checked', true ).trigger( 'click' );

		assert.strictEqual( context.find( '.datepicker' ).datepicker( 'getDate' ).getFullYear(), 2024, 'the datepicker jumped to the stored from-date' );
		assert.false( context.find( '#printouts' ).prop( 'disabled' ), 'the dropdown is re-enabled while selecting a from/to option' );
		assert.strictEqual( context.find( '#printouts' ).val(), '0', 'the dropdown is reset to the property stored for the from option' );
	} );

	QUnit.test( 'dateSelection: reset-link click clears from/to state and invokes onReset', ( assert ) => {
		const context = $( '<div class="test"></div>' ).appendTo( document.body );
		const list = [ 'Start date', 'End date' ];
		let resetCalled = false;

		context.calendarparameters();
		context.calendarparameters( 'dateSelection', {
			list: list,
			browser: 'firefox',
			dateFormat: 'yy-mm-dd',
			onReset: () => {
				resetCalled = true;
			}
		} );

		context.find( '#mini-calendar-from' ).val( '2024-01-01' ).data( 'property', '0' );
		context.find( '#mini-calendar-to' ).val( '2024-02-01' ).data( 'property', '1' );
		context.find( '#printouts' ).val( '0' ).prop( 'disabled', false );
		context.find( '#from' ).prop( 'checked', true );

		context.find( '.reset-link' ).trigger( 'click' );

		assert.strictEqual( context.find( '#mini-calendar-from' ).val(), '', 'the from date input was cleared' );
		assert.strictEqual( context.find( '#mini-calendar-to' ).val(), '', 'the to date input was cleared' );
		assert.strictEqual( context.find( '#mini-calendar-from' ).data( 'property' ), '', 'the from property was cleared' );
		assert.strictEqual( context.find( '#printouts' ).val(), '', 'the dropdown selection was cleared' );
		assert.true( context.find( '#printouts' ).prop( 'disabled' ), 'the dropdown was disabled again' );
		assert.strictEqual( context.find( 'input:radio[name=option]:checked' ).length, 0, 'the radio buttons were unchecked' );
		assert.true( resetCalled, 'the onReset callback was invoked' );
	} );

	QUnit.test( 'dateSelection: selecting a from-date invokes onSelect with the resolved property and disables the dropdown', ( assert ) => {
		const context = $( '<div class="test"></div>' ).appendTo( document.body );
		const list = [ 'Start date', 'End date' ];
		let onSelectResult = null;

		context.calendarparameters();
		context.calendarparameters( 'dateSelection', {
			list: list,
			browser: 'firefox',
			dateFormat: 'yy-mm-dd',
			onSelect: ( ui ) => {
				onSelectResult = ui;
			}
		} );

		context.find( '#mini-calendar-from' ).data( 'property', 0 );
		context.find( '#from' ).prop( 'checked', true );

		context.find( '.datepicker' ).datepicker( 'option', 'onSelect' ).call(
			context.find( '.datepicker' )[ 0 ], '2024-03-15'
		);

		assert.strictEqual( context.find( '#mini-calendar-from' ).val(), '2024-03-15', 'the from date input was updated with the selected date' );
		assert.strictEqual( context.find( 'input:radio[name=option]:checked' ).length, 0, 'the radio buttons were unchecked after selection' );
		assert.true( context.find( '#printouts' ).prop( 'disabled' ), 'the dropdown was disabled after selection' );
		assert.deepEqual( onSelectResult, { fromProperty: list[ 0 ], fromDate: '2024-03-15' }, 'onSelect received the resolved fromProperty and the selected date' );
	} );

	QUnit.test( 'dateSelection: selecting a to-date invokes onSelect with the resolved property', ( assert ) => {
		const context = $( '<div class="test"></div>' ).appendTo( document.body );
		const list = [ 'Start date', 'End date' ];
		let onSelectResult = null;

		context.calendarparameters();
		context.calendarparameters( 'dateSelection', {
			list: list,
			browser: 'firefox',
			dateFormat: 'yy-mm-dd',
			onSelect: ( ui ) => {
				onSelectResult = ui;
			}
		} );

		context.find( '#mini-calendar-to' ).data( 'property', 1 );
		context.find( '#to' ).prop( 'checked', true );

		context.find( '.datepicker' ).datepicker( 'option', 'onSelect' ).call(
			context.find( '.datepicker' )[ 0 ], '2024-04-20'
		);

		assert.strictEqual( context.find( '#mini-calendar-to' ).val(), '2024-04-20', 'the to date input was updated with the selected date' );
		assert.deepEqual( onSelectResult, { toProperty: list[ 1 ], toDate: '2024-04-20' }, 'onSelect received the resolved toProperty and the selected date' );
	} );

	QUnit.test( 'dateSelection: selecting a date with no from/to option checked invokes gotoDate instead', ( assert ) => {
		const context = $( '<div class="test"></div>' ).appendTo( document.body );
		const list = [ 'Start date', 'End date' ];
		let gotoDateResult = null;

		context.calendarparameters();
		context.calendarparameters( 'dateSelection', {
			list: list,
			browser: 'firefox',
			dateFormat: 'yy-mm-dd',
			gotoDate: ( date ) => {
				gotoDateResult = date;
			},
			onSelect: () => {
				assert.true( false, 'onSelect must not be called when no from/to option is checked' );
			}
		} );

		context.find( '.datepicker' ).datepicker( 'option', 'onSelect' ).call(
			context.find( '.datepicker' )[ 0 ], '2024-05-01'
		);

		assert.strictEqual( gotoDateResult.getFullYear(), 2024, 'gotoDate was invoked with the parsed selected date' );
	} );

	QUnit.test( 'limit: renders the initial limit/count and updates on slider slide', ( assert ) => {
		const context = $( '<div class="test"></div>' ).appendTo( document.body );

		context.calendarparameters();
		context.calendarparameters( 'limit', {
			limit: 10,
			count: 5,
			max: 20,
			step: 1
		} );

		assert.strictEqual( context.find( '.value' ).text(), '10', 'the initial limit value is shown' );
		assert.strictEqual( context.find( '.count' ).text(), '[ 5 ]', 'the initial count is shown' );

		context.find( '.slider' ).slider( 'option', 'slide' ).call(
			context.find( '.slider' )[ 0 ], {}, { value: 8 }
		);

		assert.strictEqual( context.find( '.value' ).text(), '7', 'sliding to 8 displays the constrained value (8 - 1)' );
	} );

	QUnit.test( 'limit: slider change invokes the change callback with the constrained value', ( assert ) => {
		const context = $( '<div class="test"></div>' ).appendTo( document.body );
		let changeResult = null;

		context.calendarparameters();
		context.calendarparameters( 'limit', {
			limit: 10,
			count: 5,
			max: 20,
			step: 1,
			change: ( event, value ) => {
				changeResult = value;
			}
		} );

		context.find( '.slider' ).slider( 'option', 'change' ).call(
			context.find( '.slider' )[ 0 ], {}, { value: 4 }
		);

		assert.strictEqual( changeResult, 3, 'the change callback received the constrained value (4 - 1)' );
	} );

	QUnit.test( 'limit: a value of 1 is not constrained', ( assert ) => {
		const context = $( '<div class="test"></div>' ).appendTo( document.body );

		context.calendarparameters();
		context.calendarparameters( 'limit', {
			limit: 1,
			count: 0,
			max: 20,
			step: 1
		} );

		assert.strictEqual( context.find( '.value' ).text(), '1', 'the initial value of 1 is shown as-is' );
		assert.strictEqual( context.find( '.count' ).text(), '', 'a falsy count renders no count text' );

		context.find( '.slider' ).slider( 'option', 'slide' ).call(
			context.find( '.slider' )[ 0 ], {}, { value: 1 }
		);

		assert.strictEqual( context.find( '.value' ).text(), '1', 'sliding to 1 leaves the value unconstrained' );
	} );

	QUnit.test( 'option: limit triggers a display update without rebuilding the slider', ( assert ) => {
		const context = $( '<div class="test"></div>' ).appendTo( document.body );

		context.calendarparameters();
		context.calendarparameters( 'limit', {
			limit: 10,
			count: 5,
			max: 20,
			step: 1
		} );

		context.calendarparameters( 'option', 'limit', {
			limit: 15,
			count: 9
		} );

		assert.strictEqual( context.find( '.value' ).text(), '15', 'the value display was updated via the limit option' );
		assert.strictEqual( context.find( '.count' ).text(), '[ 9 ]', 'the count display was updated via the limit option' );
	} );

	QUnit.test( 'eventStart: pre-selects the radio matching the given type and reacts to change/reset', ( assert ) => {
		const context = $( '<div class="test"></div>' ).appendTo( document.body );
		let changeResult = null;
		let resetCalled = false;

		context.calendarparameters();
		context.calendarparameters( 'eventStart', {
			type: 'earliest',
			change: ( type ) => {
				changeResult = type;
			},
			reset: () => {
				resetCalled = true;
			}
		} );

		assert.true( context.find( '#min' ).prop( 'checked' ), 'the "earliest" radio is pre-checked for type=earliest' );
		assert.false( context.find( '#max' ).prop( 'checked' ), 'the "latest" radio is not checked for type=earliest' );

		context.find( '#max' ).prop( 'checked', true ).trigger( 'change' );
		assert.strictEqual( changeResult, 'latest', 'the change callback received the newly checked value' );

		context.find( '.reset-link' ).trigger( 'click' );
		assert.strictEqual( context.find( 'input:radio[name=minmax]:checked' ).length, 0, 'the radio buttons were unchecked on reset' );
		assert.true( resetCalled, 'the reset callback was invoked' );
	} );

	QUnit.test( 'eventStart: pre-selects the "latest" radio for type=latest', ( assert ) => {
		const context = $( '<div class="test"></div>' ).appendTo( document.body );

		context.calendarparameters();
		context.calendarparameters( 'eventStart', {
			type: 'latest'
		} );

		assert.true( context.find( '#max' ).prop( 'checked' ), 'the "latest" radio is pre-checked for type=latest' );
		assert.false( context.find( '#min' ).prop( 'checked' ), 'the "earliest" radio is not checked for type=latest' );
	} );

	QUnit.test( 'colorFilter: building the portlet content', ( assert ) => {
		const context = $( '<div class="test"></div>' ).appendTo( document.body );
		const list = [ 'Category', 'Status' ];

		context.calendarparameters();
		context.calendarparameters( 'colorFilter', {
			list: list,
			browser: 'firefox'
		} );

		assert.strictEqual( context.find( 'input:radio[name=filterType]' ).length, 2, 'the legend/filter radio inputs were added' );
		assert.strictEqual( context.find( '#filterproperty' ).length, 1, 'the filter property dropdown was added' );
		assert.true( context.find( '#filterproperty' ).prop( 'disabled' ), 'the filter property dropdown starts disabled' );
		assert.strictEqual( context.find( '#filterproperty option' ).length, list.length + 1, 'the dropdown has one option per list entry plus the empty default option' );
	} );

	QUnit.test( 'colorFilter: changing the dropdown/radio invokes onChange with the current selection', ( assert ) => {
		const context = $( '<div class="test"></div>' ).appendTo( document.body );
		const list = [ 'Category', 'Status' ];
		let onChangeResult = null;

		context.calendarparameters();
		context.calendarparameters( 'colorFilter', {
			list: list,
			browser: 'firefox',
			onChange: ( event, ui ) => {
				onChangeResult = ui;
			}
		} );

		context.find( '#legend' ).prop( 'checked', true );
		context.find( '#filterproperty' ).val( '1' ).trigger( 'change' );

		assert.false( context.find( '#filterproperty' ).prop( 'disabled' ), 'the dropdown is (re-)enabled on change' );
		assert.deepEqual( onChangeResult, { propertyIndex: '1', filterType: 'legend' }, 'onChange received the selected propertyIndex and filterType' );
	} );

	QUnit.test( 'colorFilter: reset-link clears the selection and invokes onReset only when a selection was made', ( assert ) => {
		const context = $( '<div class="test"></div>' ).appendTo( document.body );
		const list = [ 'Category', 'Status' ];
		let onResetCalls = 0;

		context.calendarparameters();
		context.calendarparameters( 'colorFilter', {
			list: list,
			browser: 'firefox',
			onReset: () => {
				onResetCalls++;
			}
		} );

		// No selection made yet: reset-link click must be a no-op.
		context.find( '.reset-link' ).trigger( 'click' );
		assert.strictEqual( onResetCalls, 0, 'onReset is not invoked when nothing was selected' );

		context.find( '#filterproperty' ).val( '0' ).prop( 'disabled', false );
		context.find( '#filter' ).prop( 'checked', true );

		context.find( '.reset-link' ).trigger( 'click' );

		assert.strictEqual( context.find( '#filterproperty' ).val(), '', 'the dropdown selection was cleared' );
		assert.true( context.find( '#filterproperty' ).prop( 'disabled' ), 'the dropdown was disabled again' );
		assert.strictEqual( context.find( 'input:radio[name=filterType]:checked' ).length, 0, 'the radio buttons were unchecked' );
		assert.strictEqual( onResetCalls, 1, 'onReset was invoked once a selection existed to clear' );
	} );

	QUnit.test( 'option: colorFilter toggles the filter portlet visibility', ( assert ) => {
		const context = $( '<div class="test"></div>' ).appendTo( document.body );

		context.calendarparameters();
		context.calendarparameters( 'colorFilter', {
			list: [ 'Category' ],
			browser: 'firefox'
		} );

		context.calendarparameters( 'option', 'colorFilter', { hide: true } );
		assert.strictEqual( context.find( '.filterparam' ).css( 'display' ), 'none', 'the filter portlet was hidden' );

		context.calendarparameters( 'option', 'colorFilter', { hide: false } );
		assert.notStrictEqual( context.find( '.filterparam' ).css( 'display' ), 'none', 'the filter portlet was shown again' );
	} );

	QUnit.test( 'option: eventStart re-invokes eventStart with the new value', ( assert ) => {
		const context = $( '<div class="test"></div>' ).appendTo( document.body );

		context.calendarparameters();
		context.calendarparameters( 'eventStart', {
			type: 'earliest'
		} );

		context.calendarparameters( 'option', 'eventStart', {
			type: 'latest'
		} );

		assert.strictEqual( context.find( '.minmax' ).length, 2, 'a second eventStart portlet was appended (eventStart rebuilds rather than updating in place)' );
		assert.true( context.find( '.minmax' ).last().find( 'input[value=latest]' ).prop( 'checked' ), 'the newly appended portlet reflects the new type' );
	} );

	QUnit.test( 'destroy: removing by class only removes matching elements within this widget instance', ( assert ) => {
		const context = $( '<div class="test"></div>' ).appendTo( document.body );
		const unrelated = $( '<div class="unrelated-widget"></div>' ).appendTo( document.body );
		$( '<span class="shared-marker"></span>' ).appendTo( unrelated );

		context.calendarparameters();
		context.calendarparameters( 'colorFilter', {
			list: [ 'Category' ],
			browser: 'firefox'
		} );
		$( '<span class="shared-marker"></span>' ).appendTo( context.find( '.filterparam' ) );

		context.calendarparameters( 'destroy', { class: 'shared-marker' } );

		assert.strictEqual( unrelated.find( '.shared-marker' ).length, 1, 'destroy( { class } ) must not remove same-class elements belonging to an unrelated widget instance' );
	} );

} );
