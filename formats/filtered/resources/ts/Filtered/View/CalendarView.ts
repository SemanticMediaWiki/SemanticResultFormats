import { View } from "./View";
declare let mw: any;

export class CalendarView extends View {

	private getI18N() {
		return {
			monthNames: [ mw.msg( 'january' ), mw.msg( 'february' ), mw.msg( 'march' ),
				mw.msg( 'april' ), mw.msg( 'may_long' ), mw.msg( 'june' ),
				mw.msg( 'july' ), mw.msg( 'august' ), mw.msg( 'september' ),
				mw.msg( 'october' ), mw.msg( 'november' ), mw.msg( 'december' )
			],
			monthNamesShort: [ mw.msg( 'jan' ), mw.msg( 'feb' ), mw.msg( 'mar' ),
				mw.msg( 'apr' ), mw.msg( 'may' ), mw.msg( 'jun' ),
				mw.msg( 'jul' ), mw.msg( 'aug' ), mw.msg( 'sep' ),
				mw.msg( 'oct' ), mw.msg( 'nov' ), mw.msg( 'dec' )
			],
			dayNames: [ mw.msg( 'sunday' ), mw.msg( 'monday' ), mw.msg( 'tuesday' ),
				mw.msg( 'wednesday' ), mw.msg( 'thursday' ), mw.msg( 'friday' ), mw.msg( 'saturday' )
			],
			dayNamesShort: [ mw.msg( 'sun' ), mw.msg( 'mon' ), mw.msg( 'tue' ),
				mw.msg( 'wed' ), mw.msg( 'thu' ), mw.msg( 'fri' ), mw.msg( 'sat' )
			],
			buttonText: {
				today: mw.msg( 'srf-ui-eventcalendar-label-today' ),
				month: mw.msg( 'srf-ui-eventcalendar-label-month' ),
				week: mw.msg( 'srf-ui-eventcalendar-label-week' ),
				day: mw.msg( 'srf-ui-eventcalendar-label-day' )
			}
			,
			allDayText: mw.msg( 'srf-ui-eventcalendar-label-allday' ),
			timeFormat: {
				'': mw.msg( 'srf-ui-eventcalendar-format-time' ),
				agenda: mw.msg( 'srf-ui-eventcalendar-format-time-agenda' )
			}
			,
			axisFormat: mw.msg( 'srf-ui-eventcalendar-format-axis' ),
			titleFormat: {
				month: mw.msg( 'srf-ui-eventcalendar-format-title-month' ),
				week: mw.msg( 'srf-ui-eventcalendar-format-title-week' ),
				day: mw.msg( 'srf-ui-eventcalendar-format-title-day' )
			}
			,
			columnFormat: {
				month: mw.msg( 'srf-ui-eventcalendar-format-column-month' ),
				week: mw.msg( 'srf-ui-eventcalendar-format-column-week' ),
				day: mw.msg( 'srf-ui-eventcalendar-format-column-day' )
			}
		};
	}

	public init() {

		let _i18n = this.getI18N();

		// initialize the calendar
		this.target.fullCalendar( {

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
		} );
	}

	private getEvent( rowId: any, rowData: any ) {

		let eventdata: any = {
			id: rowId,
			title: rowData[ 'title' ],
			start: rowData[ 'start' ],
			className: rowId
		};

		if ( rowData.hasOwnProperty( 'end' ) ) {
			eventdata[ 'end' ] = rowData[ 'end' ];
		}

		if ( rowData.hasOwnProperty( 'url' ) ) {
			eventdata[ 'url' ] = rowData[ 'url' ];
		}

		return eventdata;
	}

	public showRows( rowIds: string[] ): any {

		let events: any[] = [];

		rowIds.forEach( ( rowId: string ) => {

			let rowData = this.controller.getData()[ rowId ].data[ this.id ];

			if ( rowData.hasOwnProperty( 'start' ) ) {
				events.push( this.getEvent( rowId, rowData ) );
			}
		} );

		this.target.fullCalendar( 'addEventSource', events );
	}

	public hideRows( rowIds: string[] ): any {
		this.target.fullCalendar( 'removeEvents', ( e: any ) => ( rowIds.indexOf( e._id ) >= 0 ) );
	}

	public show(): any {
		super.show();
		this.target.fullCalendar( 'render' );
	}

	public hide(): any {
		return super.hide();
	}

}
