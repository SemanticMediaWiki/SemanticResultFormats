# Installation

These are the installation and configuration instructions for [Semantic Result Formats](README.md) (SRF).

## Versions

<table>
	<tr>
		<th></th>
		<th>Status</th>
		<th>Release date</th>
		<th>Git branch</th>
	</tr>
	<tr>
		<th><a href="https://github.com/SemanticMediaWiki/SemanticResultFormats/blob/master/docs/RELEASE-NOTES.md">SRF 3.0.1</a></th>
		<td>Stable version</td>
		<td>2019-03-27</td>
		<td><a href="https://github.com/SemanticMediaWiki/SemanticResultFormats/tree/3.0.x">3.0.x</a></td>
	</tr>
	<tr>
		<th><a href="https://github.com/SemanticMediaWiki/SemanticResultFormats/blob/master/docs/RELEASE-NOTES.md">SRF 2.5.6</a></th>
		<td>Obsolete version</td>
		<td>2018-09-07</td>
		<td><a href="https://github.com/SemanticMediaWiki/SemanticResultFormats/tree/2.5.x">2.5.x</a></td>
	</tr>
	<tr>
		<th><a href="https://github.com/SemanticMediaWiki/SemanticResultFormats/blob/2.5.x/RELEASE-NOTES.md">SRF 2.4.3</a></th>
		<td>Obsolete version</td>
		<td>2017-05-06</td>
		<td><a href="https://github.com/SemanticMediaWiki/SemanticResultFormats/tree/2.4.x">2.4.x</a></td>
	</tr>
	<tr>
		<th><a href="https://github.com/SemanticMediaWiki/SemanticResultFormats/blob/2.4.x/RELEASE-NOTES.md">SRF 2.3.0</a></th>
		<td>Obsolete version</td>
		<td>2015-09-24</td>
		<td><a href="https://github.com/SemanticMediaWiki/SemanticResultFormats/tree/2.3">2.3</a></td>
	</tr>
	<tr>
		<th><a href="https://github.com/SemanticMediaWiki/SemanticResultFormats/blob/master/docs/RELEASE-NOTES.md">SRF 2.2.0</a></th>
		<td>Obsolete version</td>
		<td>2015-07-30</td>
		<td><a href="https://github.com/SemanticMediaWiki/SemanticResultFormats/tree/2.2">2.2</a></td>
	</tr>
	<tr>
		<th><a href="https://github.com/SemanticMediaWiki/SemanticResultFormats/blob/master/docs/RELEASE-NOTES.md">SRF 2.1.2</a></th>
		<td>Obsolete version</td>
		<td>2015-02-26</td>
		<td><a href="https://github.com/SemanticMediaWiki/SemanticResultFormats/tree/2.1.2">2.1.2</a></td>
	</tr>
	<tr>
		<th><a href="https://github.com/SemanticMediaWiki/SemanticResultFormats/blob/master/docs/RELEASE-NOTES.md">SRF 2.0.0</a></th>
		<td>Obsolete version</td>
		<td>2014-08-06</td>
		<td><a href="https://github.com/SemanticMediaWiki/SemanticResultFormats/tree/2.0">2.0</a></td>
	</tr>
	<tr>
		<th><a href="https://github.com/SemanticMediaWiki/SemanticResultFormats/blob/master/docs/RELEASE-NOTES.md">SRF 1.9.1</a></th>
		<td>Obsolete version</td>
		<td>2014-04-25</td>
		<td><a href="https://github.com/SemanticMediaWiki/SemanticResultFormats/tree/1.9.1">1.9.1</a></td>
	</tr>
	<tr>
		<th><a href="https://github.com/SemanticMediaWiki/SemanticResultFormats/blob/master/docs/RELEASE-NOTES.md">SRF 1.9.0</a></th>
		<td>Obsolete version</td>
		<td>2014-01-10</td>
		<td><a href="https://github.com/SemanticMediaWiki/SemanticResultFormats/tree/1.9">1.9</a></td>
	</tr>
	<tr>
		<th><a href="https://github.com/SemanticMediaWiki/SemanticResultFormats/blob/master/docs/RELEASE-NOTES.md">SRF 1.8.0</a></th>
		<td>Obsolete version</td>
		<td>2012-12-02</td>
		<td><a href="https://github.com/SemanticMediaWiki/SemanticResultFormats/tree/1.8">1.8</a></td>
	</tr>
</table>

### Platform compatibility

The PHP and MediaWiki version ranges listed are those in which SRF is known to work. It might also
work with more recent versions of PHP and MediaWiki, though this is not guaranteed. Increases of
minimum requirements are indicated in bold.

<table>
	<tr>
		<th></th>
		<th>PHP</th>
		<th>MediaWiki</th>
		<th>Semantic MediaWiki</th>
	</tr>
	<tr>
		<th>SRF 3.0.x</th>
		<td><strong>5.6.x</strong> - latest</td>
		<td><strong>1.27</strong> - latest</td>
		<td><strong>3.0.x</strong></td>
	<tr>
	<tr>
		<th>SRF 2.5.x</th>
		<td><strong>5.5.x</strong> - 7.0.x</td>
		<td><strong>1.23</strong> - 1.29</td>
		<td>2.1.x - 2.5.x</td>
	<tr>
		<th>SRF 2.4.x</th>
		<td>5.3.2 - 7.0.x</td>
		<td>1.19 - 1.28</td>
		<td>2.1.x</td>
	</tr>
	<tr>
		<th>SRF 2.3.x</th>
		<td>5.3.2 - 5.6.x</td>
		<td>1.19 - 1.25</td>
		<td>2.1.x</td>
	</tr>
	<tr>
		<th>SRF 2.2.x</th>
		<td>5.3.2 - 5.6.x</td>
		<td>1.19 - 1.25</td>
		<td>2.1.x</td>
	</tr>
	<tr>
		<th>SRF 2.1.x</th>
		<td>5.3.2 - 5.6.x</td>
		<td>1.19 - 1.24</td>
		<td>2.1.x</td>
	</tr>
	<tr>
		<th>SRF 2.0.x</th>
		<td>5.3.2 - 5.5.x</td>
		<td>1.19 - 1.23</td>
		<td>2.0.x</td>
	</tr>
	<tr>
		<th>SRF 1.9.x</th>
		<td><strong>5.3.2</strong> - 5.5.x</td>
		<td><strong>1.19</strong> - 1.23</td>
		<td>1.9.x</td>
	</tr>
	<tr>
		<th>SRF 1.8.x</th>
		<td>5.2.0 - 5.5.x</td>
		<td><strong>1.17</strong> - 1.22</td>
		<td>1.8.x</td>
	</tr>
	<tr>
		<th>SRF 1.7.x</th>
		<td>5.2.0 - 5.4.x</td>
		<td>1.16 - 1.19</td>
		<td>1.7.x</td>
	</tr>
</table>

**Note:**
* It is strongly recommended to also always upgrade the underlying MediaWiki software to supported versions.
See the [version lifecycle](https://www.mediawiki.org/wiki/Version_lifecycle) for current information on
supported versions.
* It is strongly recommended to also always upgrade the underlying Semantic MediaWiki software to supported
versions. See the page on [compatibility](https://www.semantic-mediawiki.org/wiki/Help:Compatibility) for
current information on supported versions.

## Installation

The recommended way to install Semantic Result Formats is using [Composer](http://getcomposer.org) with
[MediaWiki's built-in support for Composer](https://www.mediawiki.org/wiki/Composer).

Note that the required extension Semantic MediaWiki must be installed first according to the installation
instructions provided.

### Step 1

Change to the base directory of your MediaWiki installation. This is where the "LocalSettings.php"
file is located. If you have not yet installed Composer do it now by running the following command
in your shell:

    wget https://getcomposer.org/composer.phar

### Step 2
    
If you do not have a "composer.local.json" file yet, create one and add the following content to it:

```
{
	"require": {
		"mediawiki/semantic-result-formats": "~3.0"
	}
}
```

If you already have a "composer.local.json" file add the following line to the end of the "require"
section in your file:

    "mediawiki/semantic-result-formats": "~3.0"

Remember to add a comma to the end of the preceding line in this section.

### Step 3

Run the following command in your shell:

    php composer.phar update --no-dev

Note if you have Git installed on your system add the `--prefer-source` flag to the above command. Also
note that it may be necessary to run this command twice. If unsure do it twice right away.

### Step 4

Add the following line to the end of your "LocalSettings.php" file:

    wfLoadExtension( 'SemanticResultFormats' );
    
### Verify installation success

As final step, you can verify SRF got installed by looking at the "Special:Version" page on your wiki and
check that it is listed in the semantic extensions section.

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
* excel: requires the phpexcel library from phpoffice to work.
* googlebar: sends data to Google for rendering. It also requires
  access to the Google servers in order to render.
* googlepie: sends data to Google for rendering. It also requires
  access to the Google servers in order to render.
* graph: requires the MediaWiki GraphViz extension to work.
* hash: requires the MediaWiki HashTables extensions to work.
* process: requires the MediaWiki GraphViz extension to work.
