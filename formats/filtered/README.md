# Parameters

## Generic


## Format level

Supported:
* mainlabel
* sort
* order
* intro
* outro
* limit
* offset
* headers (Table view)

Unsupported:
* source
* link
* searchlabel
* default

Format specific:
* views
* filter position

### List view

* list view type
* list view template
* list view named args
* list view introtemplate
* list view outrotemplate

### Calendar view

* calendar view start
* calendar view end
* calendar view title
* calendar view title template

### Table view

* table view class

### Map view

* map view marker position property

## Printout level

* hide (List view, Calendar view only)
* align (`right`|`left`|`center`) (Table view only)

### Value filter

* value filter collapsible (`collapsed`|`uncollapsed`)
* value filter switches (`and or`)
* value filter values (list of strings)
* value filter height (HTML-compliant height value)

### Distance filter

* distance filter origin (lat lon) *Required*
* distance filter collapsible (`collapsed`|`uncollapsed`)
* distance filter initial value (number)
* distance filter max distance (number)
* distance filter unit (`m`|`km`|`mi`|`nm`)

### Number filter

* number filter collapsible (`collapsed`|`uncollapsed`)
* number filter max value (number)
* number filter min value (number)
* number filter step (number)
* number filter sliders (`min`|`max`|`select`|`range`)
* number filter label (string)

# Building

This is only required for development, not for simple installation and usage.

From the `.../SemanticResultFormats/formats/filtered` directory run
 ```
 npm install
 ```