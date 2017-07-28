# Parameters

Parameters to the `#ask` function can apply to the `filtered` format as a whole (format level) or to only one specific printout (printout level). On format level there are some generic parameters that are common to all result formats and some format specific parameters that are used only by the `filtered` format.

Consider the following query:
```
{{#ask:[[SomeCondition]]
|? SomePrintout |+filter=number
|? Position
|format=filtered
|limit=100
|views=map
|mapviewmarkerpositionproperty=Position
}}
```

In this query `limit=100` is on format level (generic), `views=map` is on format level (format specific) and `+filter=number` is on printout level.

## Format level - Generic:

Supported:
* format
* mainlabel
* sort
* order
* intro
* outro
* limit
* offset
* headers (Table view)

Not supported by the `filtered` format:
* source
* link
* searchlabel
* default

## Format level - Format specific:
* views (`list`|`calendar`|`table`|`map`)
* filter position (`top`|`bottom`)

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
$srfgMapProvider to be set in Localsettings.php
e. g. $srfgMapProvider='OpenStreetMap.HOT'
A list of available providers is here http://leaflet-extras.github.io/leaflet-providers/preview/index.html

* map view marker position property
* map view height 
* map view zoom
* map view min zoom
* map view max zoom
* map view marker cluster (`true`|`false`)
* map view marker cluster max zoom
* map view marker cluster max radius
* map view marker cluster zoom on click (`true`|`false`)

## Printout level

* filter (`value`|`distrance`|`number`)
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

# Running tests

## JavaScript

JavaScript tests use the QUnit test environment of MediaWiki. To enable the test
environment add the following to `LocalSettings.php`:
``` PHP
$wgEnableJavaScriptTest = true;
```

Then the easiest way to run tests is to go to
`http://127.0.0.1/wiki/Special:JavaScriptTest/qunit/plain?module=ext.srf.formats.filtered`
(with server and path modified as necessary).


To at some point allow for continuous integration testing the tests can also be
run from the command line on a headless browser. Google Chrome 59 (currently in
Beta status) allows headless execution. It will forward anything written to the
JavaScript console to the CLI standard output. So, to run tests from the command
line install Chrome Beta and run  

```
google-chrome-beta \
--headless \
--disable-gpu \
--remote-debugging-port=9222 \
'http://127.0.0.1/wiki/Special:JavaScriptTest/qunit/plain?debug=true&module=ext.srf.formats.filtered'
```

Chrome will remain running, so when the test run is finished it has to be
force-stopped.
See https://developers.google.com/web/updates/2017/04/headless-chrome for
details on headless Chrome.
 
 ## PHP
 
 Not yet available.
