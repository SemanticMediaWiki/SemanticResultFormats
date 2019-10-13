# Parameters

Parameters to the `#ask` function can apply to the `filtered` format as a whole
(format level) or to only one specific printout (printout level). On format
level there are some generic parameters that are common to all result formats
and some format specific parameters that are used only by the `filtered` format.

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

In this query `limit=100` is on format level (generic), `views=map` is on format
level (format specific) and `+filter=number` is on printout level.

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

This view is only available if `$srfgMapProvider` is set in `LocalSettings.php`,
e.g. `$srfgMapProvider='OpenStreetMap.HOT';`<br>
See the [list of available
providers](http://leaflet-extras.github.io/leaflet-providers/preview/index.html)

It's also possible to set alternative provider for users with dark appearance
enabled on their systems via `$srfgMapProviderDark`.

* map view marker position property
* map view marker icon property
* map view marker icons
* map view height 
* map view zoom
* map view min zoom
* map view max zoom
* map view marker cluster (`true`|`false`)
* map view marker cluster max zoom
* map view marker cluster max radius
* map view marker cluster zoom on click (`true`|`false`)

## Printout level

* filter (comma-separated list of `value`, `distance`, and/or `number`)
* hide (`yes`|`no`)
* align (`right`|`left`|`center`) (Table view only)
* show if undefined (`yes`|`no`)

### Value filter

* value filter collapsible (`collapsed`|`uncollapsed`)
* value filter switches (comma-separated list of `and or` and/or `on off`)
* value filter values (list of strings)
* value filter max checkboxes (number. Default: 5)

### Distance filter

* distance filter origin (lat lon): *Required*
* distance filter collapsible (`collapsed`|`uncollapsed`)
* distance filter switches (`on off`)
* distance filter initial value (number)
* distance filter max distance (number)
* distance filter unit (`m`|`km`|`mi`|`nm`)

### Number filter

* number filter collapsible (`collapsed`|`uncollapsed`)
* number filter switches (`on off`)
* number filter min value (number)
* number filter max value (number)
* number filter step (number)
* number filter values (`auto`|comma-separated list of values):
  If this parameter is specified, min, max and step will be ignored.
* number filter sliders (`min`|`max`|`range`|`select`)
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
 
PHP tests use PHPUnit. The easiest way to run all SRF tests including the tests
for the Filtered format is to run `composer phpunit` from the SRF directory.
However, for the tests to run it has to be ensured, that the `$srfgMapProvider`
configuration variable is not set in `LocalSettings.php`. 
   
#Credits

The Filtered format contains the following software libraries:
* Leaflet by Vladimir Agafonkin (http://leafletjs.com/)
* Leaflet Marker Cluster Plugin by Dave Leaver (https://github.com/Leaflet/Leaflet.markercluster)
* Leaflet Providers Plugin by Leaflet Providers contributors (https://github.com/leaflet-extras/leaflet-providers)
* Ion.RangeSlider by Denis Ineshin (http://ionden.com)
* Select2 by Kevin Brown, Igor Vaynberg, and Select2 contributors (https://select2.github.io/)

Several other libraries are used for the build process. See the devDependencies
in the package.json file.
