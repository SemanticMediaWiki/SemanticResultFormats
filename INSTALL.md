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
		<th><a href="https://github.com/SemanticMediaWiki/SemanticResultFormats/blob/master/docs/RELEASE-NOTES.md">SRF 3.0.x</a></th>
		<td>Development version</td>
		<td>-</td>
		<td><a href="https://github.com/SemanticMediaWiki/SemanticResultFormats/tree/master">master</a></td>
	</tr>
	<tr>
		<th><a href="https://github.com/SemanticMediaWiki/SemanticResultFormats/blob/master/docs/RELEASE-NOTES.md">SRF 2.5.6</a></th>
		<td>Stable version</td>
		<td>2018-09-07</td>
		<td><a href="https://github.com/SemanticMediaWiki/SemanticResultFormats/tree/2.5.x">2.5.x</a></td>
	</tr>
	<tr>
		<th><a href="https://github.com/SemanticMediaWiki/SemanticResultFormats/blob/master/docs/RELEASE-NOTES.md">SRF 2.4.2</a></th>
		<td>Obsolete version</td>
		<td>2017-02-25</td>
		<td><a href="https://github.com/SemanticMediaWiki/SemanticResultFormats/tree/2.4.x">2.4.x</a></td>
	</tr>
	<tr>
		<th><a href="https://github.com/SemanticMediaWiki/SemanticResultFormats/blob/master/docs/RELEASE-NOTES.md">SRF 2.3.0</a></th>
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
		<td>3.x</td>
	<tr>
	<tr>
		<th>SRF 2.5.x</th>
		<td><strong>5.5.x</strong> - 7.0.x</td>
		<td><strong>1.23</strong> - 1.29</td>
		<td>2.1.x</td>
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
		<td>2.x</td>
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
* It is strongly recommended to also always upgrade the underlying MediaWiki software to supported versions. See the [version lifecycle](https://www.mediawiki.org/wiki/Version_lifecycle) for current information on supported versions.
* It is strongly recommended to also always upgrade the underlying Semantic MediaWiki software to supported versions. See the page on [compatibility](https://www.semantic-mediawiki.org/wiki/Help:Compatibility) for current information on supported versions.

## Download and installation

### Composer Installation

The recommended way to install Semantic Result Formats is with
[Composer](https://getcomposer.org) using [MediaWiki's built-in support for
Composer](https://www.mediawiki.org/wiki/Composer).

#### Step 1

Change to the root directory of your MediaWiki installation. This is where the
"LocalSettings.php" file is located.

#### Step 2

If you already have Composer installed continue to step 3. If not install
Composer now:
``` bash
    wget https://getcomposer.org/composer.phar
```

#### Step 3

Add the following line to the end of the "require" section in your "composer.local.json" file:
``` json
    "mediawiki/semantic-result-formats": "~2.5"
```

   * Remark 1: Remember to add a comma to the end of the preceding line in this 
     section.

   * Remark 2: If you do not have a `composer.local.json` file (MediaWiki <1.25),
     use `composer.json` instead.

   * Remark 3: If you do not have a `composer.json` file (MediaWiki <1.24),
     copy `composer.json.example` to `composer.json` first.

#### Step 4

When this is done run in your shell:
``` bash
    php composer.phar update --no-dev --prefer-source "mediawiki/semantic-result-formats"
```

#### Verify installation success

As final step, you can verify SRF got installed by looking at the "Special:Version" page on your
wiki and verifying the Semantic Result Formats section is listed.

## Configuration

A default set of formats is enabled. These are the formats that satisfy the following criteria:

* they do not require further software to be installed (besides Semantic MediaWiki),
* they do not transmit any data to external websites, not even by making client browsers request
  any static external resources (such as an externally hosted image file),
* they are considered reasonably stable and secure.

Currently, these default formats thus are:  
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
