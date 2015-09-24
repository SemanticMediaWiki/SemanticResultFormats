These are the release notes for the [Semantic Result Formats]
(https://semantic-mediawiki.org/wiki/Semantic_Result_Formats) MediaWiki extension.

## SRF 2.3 (2015-09-24)

* Added table view to filtered format
* Fixed eventcalendar format to return a truncated version of strings in tooltips
* Internal code cleanup concerning the calendar format
* Internal code cleanup concerning the tagcould format

## SRF 2.2 (2015-07-30)

* Fixed filtered format so that filters work for ol/ul type lists
* Fixed gallery format by adding a required dependency for the carousel option to the widget parameter
* Enhanced calendar format by adding the startmonth and startyear parameters

## SRF 2.1.2 (2015-02-26)

* Fixed bug in the slideshow format API

## SRF 2.1.1 (2015-02-04)

* Fixed various jQuery 1.9+ issues that appeared in connection with MW 1.24+ including `jquery.jqplot`, `jquery.fancybox`, and`jquery.jgrid`
* Added replacement pattern for `%3A` in `gallery` overlay format (65abda9)
* Fixed the usage of plain-text title attribute in `gallery` overlay format (f18f3ea8)
* Added support for apostrophes in title text `gallery` overlay format (8dec4106)
* #79 Fixed `event calender` class parameter usage
* #73 Fixed `icalendar` escaping issues (as per RFC)

## SRF 2.0 (2014-08-06)

* #26 Fixed not showing up of graphvis legend when it should
* #35 Fixed error in the gallary format for a null object
* #37 Fixed error in the timeline format for named arguments
* #43 Fixed graphname parameter in the graphviz format
* [14daff1](https://github.com/SemanticMediaWiki/SemanticResultFormats/commit/14daff10350190634b96f644961beb15d0b29e09)
commit added support for date/time values to the [excel format](https://semantic-mediawiki.org/wiki/Help:Excel_format)
* #46 Added support for `format=graph` using Composer `mediawiki/graph-viz` package
* #47 Added parameters 'filename' (the download file name for the generated file) and 'templatefile' (a template file
      from the NS_FILE namespace used for formatting the generated file) to [excel format](https://semantic-mediawiki.org/wiki/Help:Excel_format)
* #51 Fixed null title issue in Gallery.php for MW 1.23+
* #52 Fixed `format=process` exception that was caused by missing message parameters
* #53 Updated jQuery blockUI plugin to v.2.66.0-2013.10.09

## SRF 1.9.1 (2014-04-25)

* #13 Fixed PHP warning when running PHP >=5.1 in strict mode
* #16 Improved handling of empty values in the filtered format
* #19 Fixed duplicate headers bug in the excel format
* #22 The excel format is now enabled by default when PHPExcel is loaded
* #23 The PHPUnit bootstrap now works on Windows
* #24 Added support for the new MediaWiki i18n JSON system
* #25 Fixed resource path issue occurring on some installations
* #27 Fixed error in the tagcloud format occurring when referencing a non-existing page
* #31 Added template parameter to the timeline format

## SRF 1.9.0.1 (2014-01-17)

### Bug fixes

* #7 Fix tagcloud rendering on special pages and when using templates

## SRF 1.9 (2014-01-10)

### Compatibility changes

* Changed minimum MediaWiki version from 1.17 to 1.19.
* Changed minimum PHP version from 5.2.x. to 5.3.x.
* Changed Semantic MediaWiki compatibility from 1.8.x to 1.9.x.
* Full compatibility with MediaWiki 1.19, 1.20, 1.21, 1.22 and forward-compatibility with 1.23.
* Deleted SRF_Settings.php entry point, the main entry point is SemanticResultFormats.php
* [Installation](INSTALL.md) is now done via the [Composer](http://getcomposer.org/) dependency manager.

### New formats

* [media](https://semantic-mediawiki.org/wiki/Help:Media_format) (Added by mwjames)
* [excel](https://semantic-mediawiki.org/wiki/Help:Excel_format) (Requires PHPExcel, disabled by default) (Added by Kim Eik)

### New features

* [EventCalendar](https://semantic-mediawiki.org/wiki/Help:Eventcalendar_format) SMWAPI/Ajax integration
* [tree format](https://semantic-mediawiki.org/wiki/Help:Tree_format): new parameters 'root' and 'start level'

### Other improvements and changes

* jquery.tagcanvas increase from 1.18 to 1.20
* jquery.responsiveslides increase from v1.32 to v1.53
* jquery.sparkline increase from 2.0 to 2.1
* d3 increase from d3.vs to d3.v3
* Introduce PHP SRF\ namespaces

### Bug fixes

* tree format: root elements not included

## SRF 1.8 (2012-12-02)

### Compatibility changes

* Changed minimum MediaWiki version from 1.16 to 1.17.
* Changed minimum Semantic MediaWiki version from 1.7 to 1.8.
* Full compatibility with MediaWiki 1.19 and forward-compatibility with 1.20.
* Changed minimum Validator version from 0.4 to 1.0.
* jqplotbar and jqplotpie format are replaced by jqplotchart format
* SRF_Settings.php has been deprecated (will be removed in 1.9) as entry point, use SemanticResultFormats.php instead

### New formats

* slideshow (written by Stephan Gambke)
* listwidget (bug 37721, I54660c15) (mwjames)
* sparkline format (I911862ce) (mwjames)
* timeseries printer (Ibad00690) (mwjames)
* d3chart format (I4baa7df8) (mwjames)
* jqplotseries format (I3c8847aa) (mwjames)
* jqplotchart format (I3c8847aa) (mwjames)
* incoming format (Ie5be9196) (mwjames)
* syndication feed (atom, rss) (bug 38636, Ia3cdc243) (mwjames)
* dygraphs chart format (Ibac4b753) (mwjames)
* event calendar (Iaff44b71) (mwjames)
* earliest format (written by Jeroen De Dauw, Nischay Nahata)
* latest format (written by Jeroen De Dauw, Nischay Nahata)

### New features

* (Ice7ba7ea) Enable tableview plugin support for timeseries, jqplotseries, and dygraphs format
* (bug 38094) Tag cloud format added 'sphere widget' (mwjames)
* (I6920ae49) Tag cloud format added 'wordcloud widget' (mwjames)
* (bug 37695) Tag cloud format added template support (mwjames)
* (bug 38184) Gallery format added 'slideshow widget' (mwjames)
* (bug 38357) Gallery format added 'overlay' parameter enabling gallery slideshow/carousel image overlay  (mwjames)
* (I7c49a644) Gallery format added redirects to enable images to be redirect to another target (mwjames)
* (bug 38296, Ic9f5e186) Gallery format fixed Special:Ask gallery display error (mwjames)
* (I338b6b19, I7a0e663b) Gallery format added support for pointing to the subject property in the gallery property parameters using "-"
* (I762cde6a) Value rank format added template support (mwjames)

### Other improvements

* Added test file support (see SemanticResultFormats/tests/...)
* All formats have been moved (see SemanticResultFormats/formats/...)
* Added new folder (SemanticResultFormats/resources/...) where all external plug-ins will be successively been moved
* Introduce a new array-based syntax to define parameters (see Validator/IParameterDefinition class)

### Bug fixes

* (bug 38258, I10be92c9) Fix authors/editors in bibtex

## SRF 1.7.1 (2012-03-08)

* Fixed issue with the graphlenegd parameter in the graph format (bug 33745).
* Added 'default' parameter to math formats (bug 34983).
* Added 'galleryformat' parameter with carousel option (bug 34411) (mwjames)

New formats in this version are:
* tree, ultree, oltree (written by Stephan Gambke)
* JitGraph (still in alpha, disabled by default) (written by Alex Shapovalov) (bug 32877)
* filtered (still in alpha, disabled by default) (written by Stephan Gambke)

## SRF 1.7 (2012-01-01)

* Compatibility with SMW 1.7 and later.
* Dropped support for MediaWiki 1.15.x and SMW < 1.7.
* Added warning message to jqplotpie and jqplotbar shown when there are no results instead of a non-working chart.
* Added value distribution support to jqplotpie and jqplotbar.
* Added min parameter to jqplotbar to set the minimun value for the Y-axis.
* Added pointlabel parameter to jqplotbar and chartlegend, legendlocation,
  datalabels and datalabeltype parameters to jqplotpie based on a patches by mwjames.
* Made array and hash formats compatible with 'Array' extension 2.0 and 'HashTables' 1.0.
* Added summary parameter to the icalendar format.

New formats in this version are:
* valuerank (written by DaSch)
* D3Line, D3Bar and D3Treemap (written by mwjames) (requires MW 1.17 or later)

## SRF 1.6.2 ##

Released on September 18, 2011.

* Fixed error in math printer when there are no numerical results.
* Fixed vCard compatibility with SMW 1.6 and later.
* Fixed array compatibility with SMW 1.6 and later.
* Added median and product formats to the list of default enabled formats.

## SRF 1.6.1 ##

Released on August 20, 2011.

* Fixed rendering bug in the tagcloud format occuring for inline queries.
* Fixed jqPlotBar and jqPlotPie rendering on Special:Ask and other special pages.
* Cleaned up the jqPlotBar format somewhat.
* Dropped compatibility with SMW < 1.6 for the tagcloud format.

## SRF 1.6 ##

Released on July 30, 2011.

Changes in this version:
* Added compatibility with SMW 1.6.
* Rewrote math formats for efficiency, correct recursion and handling of multiple numerical properties.
* Cleaned up the graph format.
* Fixed division by zero issue (oh shii~) in the tagcloud format.
* Added parameter descriptions to the graph and ploticus formats.
* Added support for SMW 1.6 style parameter handling to the tagcloud format.
* Somewhat cleaned up the BibTeX format.
* Fixed double HTML escaping issue in the tagcloud format.
* Added fileextensions parameter to the Gallery format and added missing parameter description messages.

New formats in this version are:
* product (written by Jeroen De Dauw)
* median (written by Jeroen De Dauw)

## SRF 1.5.3 ##

Released on February 9, 2011.

Changes in this version:
* Support for images specified by properties in the gallery format.
* Fixes to the calendar and jqplot formats.
* Improvements to the timeline and eventline formats.

New formats in this version are:
* tagcloud (written by Jeroen De Dauw)

## SRF 1.5.2 ##

Released on January 11 2011.

Changes in this version:
* Handling for ResourceLoader in MediaWiki 1.17+ added for
  'timeline', 'eventline', 'jqplotbar' and 'jqplotpie' formats.
* Visualization improvements for 'process' format.

## SRF 1.5.1 ##

Released on August 26 2010.

New formats in this version are:
* jqplotbar (written by Sanyam Goyal and Yaron Koren)
* jqplotpie (written by Sanyam Goyal and Yaron Koren)

Other changes:
* Added support for 'semantic' extension type, added by SMW 1.5.2 and above.

## SRF 1.5 ##

Released on June 22 2010.

New formats in this version are:
* gallery (written by Rowan Rodrik van der Molen)

Changes in this version:
* the functions getName() and getParameters() were added to most formats, for use in Special:Ask
* a 'lang' parameter was added to the 'calendar' format
* improvements in 'exhibit' result format
** new facet styles (slider and search)

## SRF 1.4.5 ##

Released on June 3 2009.

New formats in this version are:
* outline (written by Yaron Koren)

Other changes:
* the 'ploticus' format was disabled, due to a security hole
* the 'calendar' format no longer requires disabling of caching
* imagemap links were fixed for the 'graph' format
* handling was added for the Admin Links extension

## SRF 1.4.4 ##

Released on April 16 2009.

* improvements in 'exhibit' result format:
** required scripts, styles, images largely included (no remote server access needed)
** fixes for Timeline
** Usage of Google Maps now requires to set a Google Maps key (as obtained from Google)
   in LocalSettings.php:
   $wgGoogleMapsKey = 'yourkey';
   If this is not set, the "maps" view will be disabled.
** many formatting improvements
** improved compatibility with Internet Explorer (esp. IE8)
** only Timeline and Map will access a remote server now

* other changes:
** a getName() method was added to many of the formats
** SRF_ParserFunctions.php file added, holding parser functions for use by
'calendar' format.

## SRF 1.4.3 ##

Released on March 2 2009.

New formats in this version are:
* bibtex (written by Steren Giannini)

Also, handling of templates was added to the 'calendar' format by David
Loomer.

## SRF 1.4.2 ##

Released on February 10 2009.

The initialization of formats was changed to use the global $srfgFormats
variable, instead of the srfInit() function.

New formats in this version are:
* ploticus (written by Joel Natividad)
* exhibit (written by Fabian Howahl and using code from MIT CSAIL)
* average (written by Yaron Koren)
* min (written by Yaron Koren)
* max (written by Yaron Koren)
* sum (written by Nathan Yergler)
* moved existing formats 'vcard' and 'icalendar' from SMW

## SRF 1.4.0 ##

Released on November 26 2008.

This is the initial release of Semantic Result Formats. The version number
was chosen in order to be aligned to the Semantic MediaWiki core distribution.
SRF 1.4.0 is compatible to SMW 1.4.0 and thus equally versioned.

The initial sets of Semantic Result Formats are:
* calendar (written by Yaron Koren)
* eventline (written by Markus Krötzsch and based on code by MIT's Simile group)
* googlebar (written by Denny Vrandecic)
* googlepie (written by Denny Vrandecic)
* graph (written by Frank Dengler)
* timeline (written by Markus Krötzsch and based on code by MIT's Simile group)
