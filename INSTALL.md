# SRF installation

These are the installation and configuration instructions for [Semantic Result Formats](README.md).

## Versions

<table>
	<tr>
		<th></th>
		<th>Status</th>
		<th>Release date</th>
		<th>Git branch</th>
	</tr>
	<tr>
		<th><a href="https://github.com/SemanticMediaWiki/SemanticResultFormats/blob/master/docs/RELEASE-NOTES.md">SRF 2.3.x</a></th>
		<td>Development version</td>
		<td>-</td>
		<td><a href="https://github.com/SemanticMediaWiki/SemanticResultFormats/tree/master">master</a></td>
	</tr>
	<tr>
		<th><a href="https://github.com/SemanticMediaWiki/SemanticResultFormats/blob/master/docs/RELEASE-NOTES.md">SRF 2.3.0</a></th>
		<td>Stable version</td>
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
		<th><a href="https://github.com/SemanticMediaWiki/SemanticResultFormats/blob/master/docs/RELEASE-NOTES.md">SRF 2.0</a></th>
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

<table>
	<tr>
		<th></th>
		<th>PHP</th>
		<th>MediaWiki</th>
		<th>Semantic MediaWiki</th>
		<th>Composer</th>
	</tr>
	<tr>
		<th>SRF 2.3.x</th>
		<td>5.3.2 - 5.6.x</td>
		<td>1.19 - 1.25</td>
		<td>2.x</td>
		<td>Required</td>
	</tr>
	<tr>
		<th>SRF 2.2.x</th>
		<td>5.3.2 - 5.6.x</td>
		<td>1.19 - 1.25</td>
		<td>2.x</td>
		<td>Required</td>
	</tr>
	<tr>
		<th>SRF 2.1.x</th>
		<td>5.3.2 - 5.6.x</td>
		<td>1.19 - 1.24</td>
		<td>2.x</td>
		<td>Required</td>
	</tr>
	<tr>
		<th>SRF 2.0.x</th>
		<td>5.3.2 - 5.5.x</td>
		<td>1.19 - 1.23</td>
		<td>2.x</td>
		<td>Required</td>
	</tr>
	<tr>
		<th>SRF 1.9.x</th>
		<td>5.3.2 - 5.5.x</td>
		<td>1.19 - 1.23</td>
		<td>1.9.x</td>
		<td>Required</td>
	</tr>
	<tr>
		<th>SRF 1.8.x</th>
		<td>5.2.0 - 5.5.x</td>
		<td>1.17 - 1.22</td>
		<td>1.8.x</td>
		<td>Not supported</td>
	</tr>
	<tr>
		<th>SRF 1.7.x</th>
		<td>5.2.0 - 5.4.x</td>
		<td>1.16 - 1.19</td>
		<td>1.7.x</td>
		<td>Not supported</td>
	</tr>
</table>

The PHP and MediaWiki version ranges listed are those in which SRF is known to work. It might also
work with more recent versions of PHP and MediaWiki, though this is not guaranteed.

## Download and installation

### Composer Installation

The recommended way to install Semantic Result Formats is with [Composer](http://getcomposer.org) using
[MediaWiki 1.22 built-in support for Composer](https://www.mediawiki.org/wiki/Composer). MediaWiki
versions prior to 1.22 can use Composer via the
[Extension Installer](https://github.com/JeroenDeDauw/ExtensionInstaller/blob/master/README.md)
extension.

##### Step 1

If you have MediaWiki 1.22 or later, go to the root directory of your MediaWiki installation,
and go to step 2. You do not need to install any extensions to support composer.

For MediaWiki 1.21.x and earlier you need to install the
[Extension Installer](https://github.com/JeroenDeDauw/ExtensionInstaller/blob/master/README.md) extension.

Once you are done installing the Extension Installer extension, go to its directory so composer.phar
is installed in the right place.

    cd extensions/ExtensionInstaller

##### Step 2

If you have previously installed Composer skip to step 3.

To install Composer:

    wget http://getcomposer.org/composer.phar

##### Step 3
    
Now using Composer, install Semantic Result Formats.

If you do not have a composer.json file yet, copy the composer-example.json file to composer.json.
If you are using the Extension Installer extension, the file to copy will be named example.json,
rather than composer-example.json. When this is done, run:
    
    php composer.phar require mediawiki/semantic-result-formats "~2.3"

##### Verify installation success

As final step, you can verify SRF got installed by looking at the "Special:Version" page on your wiki and verifying the
Semantic Result Formats section is listed.

## Configuration

A default set of formats is enabled. These are the
the formats that satisfy the following criteria:

* they do not require further software to be installed (besides SMW),
* they do not transmit any data to external websites, not even by making client
  browsers request any static external resources (such as an externally hosted
  image file),
* they are considered reasonably stable and secure.

Currently, these default formats thus are:
'vcard', 'icalendar', 'calendar', 'timeline', 'eventline', 'bibtex', 'outline',
'gallery', 'jqplotbar', 'jqplotpie', 'sum', 'average', 'min', 'max', 'tagcloud',
'median', 'product', 'valuerank', 'array', 'tree', 'ultree', 'oltree',
'D3Line'¹, 'D3Bar'¹, 'D3Treemap'¹, 'hash'².

¹ from MediaWiki 1.17 onwards
² with HashTables extension installed

To add more formats to this list, you can add lines like:

    $srfgFormats[] = 'googlebar';

... or you can override the set of formats entirely, with a call like:

    $srfgFormats = array( 'calendar', 'timeline' );

There are some formats that you may not want to include because they may
not follow certain policies within your wiki; the formats 'googlebar' and
'googlepie', for instance, send data to external web services for rendering,
which may be considered a data leak.

Notes on specific formats:
* eventline: requires Javascript to render.
* exhibit: requires Javascript to render; requires access to Javascript files
  hosted by MIT (not locally included), but does not send any data to MIT
  (besides the requester's IP and the URL of the site with the query). Some
  subformats of Exhibit, like the Google Maps view, send data to Google for
  rendering.
* googlebar: sends data to Google for rendering.  Googlebar requires
  access to the Google servers in order to render.
* googlepie: sends data to Google for rendering.  Googlepie requires
  access to the Google servers in order to render.
* graph: in order to get the graph format to run, you first must have
  the MediaWiki Graph extension running.
* jqplotbar: requires Javascript to render.
* jqplotpie: requires Javascript to render.
* process: in order to get the process format to run, you first must
  have the MediaWiki Graph extension running
* ploticus: requires that the Ploticus application be installed on the
  server.
* timeline: requires Javascript to render.
