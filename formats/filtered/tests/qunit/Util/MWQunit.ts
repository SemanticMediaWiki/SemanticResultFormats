/// <reference types="qunit" />

export interface MWQunit extends QUnitStatic {
	newMwEnvironment: () => LifecycleObject;
}