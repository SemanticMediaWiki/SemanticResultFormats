This document holds the **installation and configuration instructions** for the [Semantic Result Formats](README.md) (SRF) extension.

- For information on the release series, see the [version overview](https://github.com/SemanticMediaWiki/SemanticResultFormats/blob/master/docs/COMPATIBILITY.md).
- For a full list of changes in each release, see the [release notes](https://github.com/SemanticMediaWiki/SemanticResultFormats/blob/master/RELEASE-NOTES.md).
- For instructions on how to install the latest version, see the [installation instructions](https://github.com/SemanticMediaWiki/SemanticResultFormats/blob/master/docs/INSTALL.md).

# Installation

The recommended way to install Semantic Result Formats is using [Composer](http://getcomposer.org) with
[MediaWiki's built-in support for Composer](https://www.mediawiki.org/wiki/Composer).

Note that the required extension Semantic MediaWiki must be installed first according to the installation
instructions provided.

### Step 1

Change to the base directory of your MediaWiki installation. If you do not have a "composer.local.json" file yet,
create one and add the following content to it:

```
{
	"require": {
		"mediawiki/semantic-result-formats": "~3.1"
	}
}
```

If you already have a "composer.local.json" file add the following line to the end of the "require"
section in your file:

    "mediawiki/semantic-result-formats": "~3.1"

Remember to add a comma to the end of the preceding line in this section.

### Step 2

Run the following command in your shell:

    php composer.phar update --no-dev

Note if you have Git installed on your system add the `--prefer-source` flag to the above command.

### Step 3

Add the following line to the end of your "LocalSettings.php" file:

    wfLoadExtension( 'SemanticResultFormats' );
    
## Configuration

A default set of formats is enabled. These are the formats that satisfy the following criteria:

* they do not require further software to be installed (besides Semantic MediaWiki),
* they do not transmit any data to external websites, not even by making client browsers request
  any static external resources (such as an externally hosted image file),
* they are considered reasonably stable and secure.

Currently, these default formats are:  

'icalendar', 'vcard', 'bibtex', 'calendar', 'eventcalendar', 'eventline', 'timeline', 'outline',
'gallery', 'jqplotchart', 'jqplotseries', 'sum', 'average', 'min', 'max', 'median', 'product',
'tagcloud', 'valuerank', 'array', 'tree', 'ultree', 'oltree', 'd3chart', 'latest', 'earliest',
'filtered', 'slideshow', 'timeseries', 'sparkline', 'listwidget', 'pagewidget', 'dygraphs', 'media',
'datatables'

To add more formats to this list, you can add lines like:

    $srfgFormats[] = 'googlebar';

... or you can override the set of formats entirely, with a call like:

    $srfgFormats = [ 'calendar', 'timeline' ];

There are some formats that you may not want to include because they may not follow certain policies
within your wiki; the formats 'googlebar' and 'googlepie', for instance, send data to external web
services for rendering, which may be considered a data leak.

Notes on specific formats:
* array: requires the MediaWiki Arrays extension to work.
* gantt: requires the MediaWiki Mermaid extension to work.
* googlebar: sends data to Google for rendering. It also requires
  access to the Google servers in order to render.
* googlepie: sends data to Google for rendering. It also requires
  access to the Google servers in order to render.
* graph: requires the MediaWiki GraphViz extension to work.
* hash: requires the MediaWiki HashTables extensions to work.
* process: requires the MediaWiki GraphViz extension to work.
* spreadsheet: requires the phpspreadsheet library from phpoffice to work.
