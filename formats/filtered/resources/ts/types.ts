export type Options = { [key: string]: any };

/**
 * One printout's values in the compact per-item schema. "f" (formatted values)
 * and "s" (sort values) are omitted when identical to "v" (plain values); the
 * accessors below fall back to "v" in that case. A printout with no values is
 * serialized as null.
 */
export interface PrintoutSlot {
	v: any[];
	f?: any[];
	s?: any[];
}

export interface Row {
	// Positional array aligned with the config-level printrequests order.
	p: ( PrintoutSlot | null )[];
	// Per-row view/filter data, keyed by view or filter id. Absent when empty.
	d?: { [ id: string ]: any };
	// Runtime-only per-filter visibility state, added by the Controller.
	visible?: { [ filterId: string ]: boolean };
}

export type ResultData = { [ rowId: string ]: Row };

export function printoutValues( slot: PrintoutSlot | null | undefined ): any[] {
	return slot ? slot.v : [];
}

export function printoutFormattedValues( slot: PrintoutSlot | null | undefined ): any[] {
	return slot ? ( slot.f !== undefined ? slot.f : slot.v ) : [];
}

export function printoutSortValues( slot: PrintoutSlot | null | undefined ): any[] {
	return slot ? ( slot.s !== undefined ? slot.s : slot.v ) : [];
}
