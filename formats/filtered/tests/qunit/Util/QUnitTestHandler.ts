import { MWQunit } from "./MWQunit";
import { QUnitTest } from "./QUnitTest";

declare let QUnit: MWQunit;

export class QUnitTestHandler {

	isInitialised: boolean = false;
	testclasses: (typeof QUnitTest)[];
	moduleName: string;

	constructor( moduleName: string, testclasses: (typeof QUnitTest)[] ) {
		this.moduleName = moduleName;
		this.testclasses = testclasses;
	}

	public init() {

		if ( this.isInitialised ) {
			return;
		}

		this.isInitialised = true;

		QUnit.testDone( ( details: TestDoneCallbackObject ) => {
			let message = `Pass: ${details.passed}  Fail: ${details.failed}  Total: ${details.total}  ${details.module} - ${details.name} (${details.duration}ms)`;
			this.reportResult( details.failed, message );
		} );

		QUnit.done( ( details: DoneCallbackObject ) => {
			let message = `All tests finished. (${details.runtime}ms)\nPass: ${details.passed}  Fail: ${details.failed}  Total: ${details.total}`;
			this.reportResult( details.failed, message );
		} );
	};

	private reportResult( failed: number, message: string ) {
		if ( failed === 0 ) {
			console.log( message );
		} else {
			console.error( message );
		}
	}

	public runTests() {

		this.init();

		QUnit.module( this.moduleName, QUnit.newMwEnvironment() );

		this.testclasses.forEach( function ( testclass ) {
			return new testclass().runTests();
		} );
	};

}