import { Filtered } from "./Filtered/Filtered";

declare let mw: any;
let config = mw.config.get( 'srfFilteredConfig' );

for ( let id in config ) {
	if ( config.hasOwnProperty( id ) ) {
		let f = new Filtered( $( '#' + id ), config[ id ] );
		f.run();
	}
}