These are the release notes for the [Semantic Result Formats](https://www.semantic-mediawiki.org/wiki/Extension:Semantic_Result_Formats) (a.k.a SRF) MediaWiki extension.

## SRF 4.2.0

Released on December 7, 2023.

* Improved compatibility with recent versions of MediaWiki [792](https://github.com/SemanticMediaWiki/SemanticResultFormats/pull/792), [780](https://github.com/SemanticMediaWiki/SemanticResultFormats/pull/780) (by @Seb35 and @D-Groenewegen)
* Improved test coverage [789](https://github.com/SemanticMediaWiki/SemanticResultFormats/pull/791), [791](https://github.com/SemanticMediaWiki/SemanticResultFormats/pull/791) (by [gesinn.it](https://gesinn.it))
* [790](https://github.com/SemanticMediaWiki/SemanticResultFormats/pull/790) removed datatables-legacy format (by @YvarRavy)
* [786](https://github.com/SemanticMediaWiki/SemanticResultFormats/pull/786) improved datatables format (by @alistair3149)
* [788](https://github.com/SemanticMediaWiki/SemanticResultFormats/pull/788) resource loading spinner (by @thomas-topway-it for ([KM-A](https://km-a.net))
* [782](https://github.com/SemanticMediaWiki/SemanticResultFormats/pull/783) fixes jqplotchart issue (by @thomas-topway-it for ([KM-A](https://km-a.net))
* [777](https://github.com/SemanticMediaWiki/SemanticResultFormats/pull/777) fixes eventcalender issue (by @thomas-topway-it for ([KM-A](https://km-a.net))
* Further improvements of datatables format: [775](https://github.com/SemanticMediaWiki/SemanticResultFormats/pull/775), [774](https://github.com/SemanticMediaWiki/SemanticResultFormats/pull/774), [773](https://github.com/SemanticMediaWiki/SemanticResultFormats/pull/773) (by @thomas-topway-it for ([KM-A](https://km-a.net))
* Fixed NONCEs on inline scripts in order to support CSP-Header
* Fixed GraphViz extension support in the Graph format (by [Professional Wiki](https://professional.wiki))

## SRF 4.1.0

Released on October 12, 2023.

* [Complete rewrite of datatables format](https://www.semantic-mediawiki.org/w/index.php?title=Help:Datatables_format) (by @thomas-topway-it, [KM-A](https://knowledge.wiki)). [761](https://github.com/SemanticMediaWiki/SemanticResultFormats/issues/761), [750](https://github.com/SemanticMediaWiki/SemanticResultFormats/issues/750), [725](https://github.com/SemanticMediaWiki/SemanticResultFormats/issues/725), [571](https://github.com/SemanticMediaWiki/SemanticResultFormats/issues/571), [721](https://github.com/SemanticMediaWiki/SemanticResultFormats/issues/721)...
* Fixed jqplotchart label error (by @thomas-topway-it)
* Fixed preferred label issue in eventcalendar (by @thomas-topway-it)

## SRF 4.0.2

Released on March 9, 2023.

* Improved compatibility with recent versions of MediaWiki, especially 1.37, 1.38 and 1.39
* [724](https://github.com/SemanticMediaWiki/SemanticResultFormats/issues/724) Fix problem in pagewidget carousel when bootstrap is used (by @thomas-topway-it, [KM-A](https://knowledge.wiki))
* [188](https://github.com/SemanticMediaWiki/SemanticResultFormats/issues/188#) Fix for datatables format (by @harugon)
* Fix minor error in filtered format (by @Semantisch, [KM-A](https://knowledge.wiki))
* Fix error in slideshow format (by @Semantisch, [KM-A](https://knowledge.wiki))
* Fix Outline format sometimes not showing all values (by @Seb35)
* Solved potential `symfony/css-selector` package conflict (by @rvogel)
* Bump moment from 2.24.0 to 2.29.2 in /formats/filtered
* Bump tar from 4.4.8 to 4.4.19 in /formats/filtered

## SRF 4.0.1

Released on January 26, 2022.

## SRF 4.0.0

Released on January 25, 2022.

* Minimum PHP version changed from 7.0 to 7.3
* Minimum MediaWiki version changed from 1.31 to 1.35
* Added compatibility with Semantic MediaWiki 4.x
* Improved compatibility with recent versions of MediaWiki
* [Filtered] added "list view userparam" (by [gesinn.it](https://gesinn.it))
* [Gallery] added "captiontemplate" parameter to allow wrapping of image captions with a template
* [GraphViz] fields of data type other than 'page' are now displayed not as separate nodes connected by edges but as parts of labels of nodes of the type 'record' and similar

## SRF 3.2.0

Released on August 27, 2020.

* Added new statistical result formats `mode`, `variance`, `samplevariance`, `standarddeviation`, `samplestandarddeviation`, `quartilupper`, `quartilupper.exc`, `quartillower`, `quartillower.exc`, `interquartilerange`, `interquartilerange.exc`, `interquartilemean` (by [KDZ](https://www.kdz.eu))
* Added `hidezeroes` parameter to the `jqplotseries` format (by [Professional.Wiki][ProWiki])
* Added support for `introtemplate`, `outrotemplate` and `{{#itemsubjectraw}}` for Outline format (by [gesinn.it](https://gesinn.it))
* Added `graphfontsize` parameter to the `graph` format
* Improved compatibility with upcoming MediaWiki versions
* Updated translations (by translatewiki.net community)

## SRF 3.1.0

Released on August 18, 2019.

* Minimum requirement for
  * PHP changed to version 7.0 and later
  * MediaWiki changed to version 1.31 and later
* Added compatibility with Semantic MediaWiki 3.1.x
* Improved compatibility with PHP 7.2+
* Added `spreadsheet` format (by Stephan Gambke)
* Deprecated `excel` format (by Stephan Gambke)
* Added `gantt` result format (by Sebastian Schmid)
* Added `filename` parameter to the `vcard` format (by James Hong Kong)
* Added `template` parameter to the `outline` format (by James Hong Kong)
* Added css `class` parameter to the `tree` format (by Stephan Gambke)
* Improved `timeseries` format (by Christian Zagrodnick)
  * Fixed `uncaught exception: Invalid dimensions for plot`
  * Only correct plot height when there are tabs
* Other bug fixes and code improvements
* Made the extension installable without the `php-gd` PHP extension
* Updated translations (by translatewiki.net community)

## SRF 3.0.1

Released on March 27, 2019.

* [#391](https://github.com/SemanticMediaWiki/SemanticResultFormats/issues/391) Updates build tools, thus fixing a security issue for the "filtered" format (by Stephan Gambke)
* [#444](https://github.com/SemanticMediaWiki/SemanticResultFormats/pull/444) Removed a feature switch, now always using `TraditionalImageGallery`; fixes a potential "method not found" warning both for the "gallery" format (by Stephan Gambke)
* [#462](https://github.com/SemanticMediaWiki/SemanticResultFormats/pull/462) Removed usage of `$this->mFormat` which was no longer taken into account thus fixing the "listwidget" format (by Stephan Gambke)
* [#471](https://github.com/SemanticMediaWiki/SemanticResultFormats/pull/471) Updated to secure versions of dependencies for the "filtered" format (by Stephan Gambke)
* Updated translations (by translatewiki.net community)

## SRF 3.0.0

Released on October 12, 2018.

* Minimum requirement for
  * PHP changed to version 5.6 and later
  * MediaWiki changed to version 1.27 and later
  * Semantic MediaWiki changed to version 3.0 and later
* #438 Added support for extension registration via "extension.json" (by James Hong Kong)
  → Now you have to use `wfLoadExtension( 'SemanticResultFormats' );` in the "LocalSettings.php" file to invoke the extension
* Improved filtered format: More options, better test coverage, re-enabled by default (by Stephan Gambke)
* Refactored vcard format: Mostly code improvements (by James Hong Kong)
* [#248](https://github.com/SemanticMediaWiki/SemanticResultFormats/pull/248) Fixed localization of numbers in the math result formats (by James Hong Kong)
* [#311](https://github.com/SemanticMediaWiki/SemanticResultFormats/pull/365) Improved display of user preference options on special page "Preferences" (by James Hong Kong)
* [#365](https://github.com/SemanticMediaWiki/SemanticResultFormats/pull/365) Added support for the latest versions of the GraphViz extension (by Sam Wilson)
* [#375](https://github.com/SemanticMediaWiki/SemanticResultFormats/pull/375) Fixed exposing of file dimensions in captions for the "gallery" format (by James Hong Kong)
* [#384](https://github.com/SemanticMediaWiki/SemanticResultFormats/pull/384) Updated "fullcalendar" library as well as added the "list views" feature for the "eventcalendar" format (by Nischay Nahata and James Hong Kong)
* [#435](https://github.com/SemanticMediaWiki/SemanticResultFormats/pull/435) Fixed time zone transitions (by James Hong Kong)
* [#436](https://github.com/SemanticMediaWiki/SemanticResultFormats/pull/436) Removed the `template arguments` parameter of the "gallery" format (by Stephan Gambke)
* Added support for installation together with the latest versions of the Maps extension (by Jeroen De Dauw)
* Provided general code improvements as well as additional integrations tests for several result formats
* Updated translations (by translatewiki.net community)

## SRF 2.5.6

Released on September 7, 2018.

* 399: Fixes columnsearchinput field being always disabled for the "datatables" format (by Matthew A.Thompson)
* 410 Added support for installation together with the latest versions of the Maps extension (by Jeroen De Dauw)

## SRF 2.5.5

Released on April 4, 2018.

* #354: Fixed value filter labels for the "filtered" format (by Stephan Gambke)
* #355: Fixed missing namespace in the "tagcloud" format (by Cindy Cicalese)
* #361: Set stacking context for srf-gallery-slideshow in the "gallery" format (by Stephan Gambke)
* #374: Fixed "select2" list elements rendering outside of containing element for the "filtered" format (by Matthew A.Thompson)
* #379: Fixed result printers still using the Google Chart API ("googlechart", "googlepie") via http (by Karsten Hoffmeyer)
* #383: Updated some "leaflet" and "select2" modules and fixed "package-lock.json" for the "fitered" format (by Stephan Gambke)

## SRF 2.5.4

Released on November 13, 2017.

* #337: Fixed style issues when collapsing filters for the "filtered" format (by Stephan Gambke)
* #343: Fixed style and layout fixes and optimise performance of the "filtered" format (by Stephan Gambke)
* #346: Brings more performance improvements and adds missing system messages for the value filter of the "filtered" format (by Stephan Gambke)
* #349: Removed `default` parameter from "earliest" and "latest" formats (by James Hong Kong)
* #351: Added `map view marker icon property` and `map view marker icons` parameters to allow map icons depending on a printout value to the "filtered" format (by Stephan Gambke)
* Made map assets load over HTTPS for the "exhibit" format (by Máté Szabó)

## SRF 2.5.3

Released on October 25, 2017.

* #293: Fixes resource loading for the "timeline" format (by James Hong Kong)
* #295: Fixes issues with subobject for the "timeline" format (by James Hong Kong)
* #299: Brings improvements to the "filtered" format as authored with the following pull requests: (by Stephan Gambke)
    - #224: Makes radio buttons belong to the same button group
    - #278: Adds a multi-select dropdown or similar for value filters
    - #286: Brings a reworked number filter
    - #291: Fixes `list view template` to actually show the template instead of defaulting to a table
* #300: Brings improvements and fixes to the "filtered" format: (by Stephan Gambke)
    - Brings back checkboxes for value filter with only few values
    - Brings new query parameter ` |+value filter max checkboxes`
    - Allows for easier installation of "data-values/geo"
* #302: Fixes error messages shown in the instance language instead of the user language for the "filtered" format (by Stephan Gambke)
* #305: Fixes "SRF\Filtered\Filtered::setParser() must be an instance of Parser..." for the "filtered" format (by Stephan Gambke)
* Fixes issues with HTML-encoded values sent by JavaScript for the "filtered" format (by Stephan Gambke)
* #324: Brings improvements to the "filtered" format as authored with the following pull requests: (by Stephan Gambke)
    - #318: Wrap input elements of the Value filter (checkboxes and radioboxes) in label elements. This way they will also be triggered when only the label text is clicked.
    - #322: Show a spinner while filtering. This will block users from triggering further filter events while filtering is still ongoing.
    - #323: Adds printout parameter`|+show if undefined`. Setting it makes filters show a result item even if the printout does not contain a value.
* #328: Brings useability fixes to the "filtered" foramat like the fix for the styling of Value filter for long labels as well as the fix for the slider grid when showing less than 4 step values (by Stephan Gambke)
* #331: Switches the "filtered" format to use Less instead of CSS (by Stephan Gambke)
* #334: Adds an On/Off switch for filters to the "filtered" format (by Stephan Gambke)

## SRF 2.5.2

Released on August 17, 2017.

* #266: Fixed bug #224: The and/or selectors can not be selected at the same time anymore in "filtered" format (by Stephan Gambke)
* #269: Fixed bug #263: Fix the `link` and `userparam` parameters on the "tree" format and provide tests for it (by Stephan Gambke)
* #276: Use type `parser-html` for JsonScript tests of the "tree" format (by Stephan Gambke)
* #284: Fixed rendering of the "calendar" format in Internet Explorer (by kwji)
* #285: Add `+hide` for all views of the "filtered" format (by Stephan Gambke)
* Provided translation updates (by translatewiki.net community)

## SRF 2.5.1

Released on July 11, 2017.

* #236: Fixed bug #234: Make the "oltree" format to actually use `<ol>`
* #237: Fixed bug #235: Fix the `template` parameter to the "tree", "oltree" and "ultree" formats
* Fixed bug #253: Remove obsolete `"div"` element `align="justify"` from the "tagcloud" and "gallery" formats
* Provided translation updates (by translatewiki.net community)

## SRF 2.5.0

Released on June 13, 2017.

* Dropped compatibility with PHP 5.3 and 5.4
* Dropped compatibility with MediaWiki 1.19 to 1.22
* Updated installation instructions in [INSTALL.md](INSTALL.md)
* Changed bootstrapping of SRF to make it work with SMW 3.0+ (by James Hong Kong)
* Re-organized file layout unit testing and added JSONScript integration testing facility from SMW (by Stephan Gambke)
* Improved math format to recognize output format "-" (by Sebastian Schmid (gesinn.it))
* Improved eventcalendar format: Added parameter 'clicktarget' to allow users to define a target URL that get's called when clicking on a calendar date. (by Felix Aba)
* Reworked tree format (by Stephan Gambke)
* Reworked filtered format which is no longer available by default (by Stephan Gambke)
* Fixed bug #199 in HTML utils JS script (by gesinn.it)
* Fixed bug #207: Added missing system messages for the process format and improved existing system messages for the graph format (by Karsten Hoffmeyer)
* Fixed bug #215: Added missing argument 4 for `GraphViz::graphvizParserHook()` (by Karsten Hoffmeyer)
* Fixed jplayer file path used by media format (by Stephan Gambke)
* Fixed gallery format to ensure compatibility with MW 1.23+ (by James Hong Kong)
* Provided translation updates (by translatewiki.net community)

## SRF 2.4.3

Released on May 7, 2017

* #199: Fixed uncaught TypeError on 'in' operator to search for 'length' for "eventcalendar" format (by gesinn.it)

## SRF 2.4.2

Released on February 25, 2017

* Fixed slidshow format from using a dependancy removed with MediaWiki 1.26+ (by Stephan Gambke)
* Provided translation updates (by translatewiki.net community)

## SRF 2.4.1

Released on December 20, 2016.

* Fixed excel format to throw an error if the required phpExcel library is missing (by Stephan Gambke)
* Provided minor internal code changes to the excel format (by Stephan Gambke)
* Fixed datatables format not reading property 'aTypes' of undefined TypeError (by James Hong Kong)
* Provided translation updates (by translatewiki.net community)

## SRF 2.4.0

Released on October 10, 2016.

### Enhancements

* Added link support to the media format (by James Hong Kong)
* Added displaytitle label support to filtered format (by Simon Heimler)
* Improved list and page widget CSS (by James Hong Kong)
* Updated jplayer to version 2.9.2 (by James Hong Kong)
* Improved compatibility with the latest versions of MediaWiki (by Florian Schmidt)
* Improved internationalization (by Karsten Hoffmeyer)
* Made installation via Composer more robust (by Cindy Cicalese)
* Removed the Ploticus format previously disabled due to security concerns (by Jeroen De Dauw)
* Provided translation updates (by translatewiki.net community)

### Bugfixes

* Fixed RuntimeError when selecting excel format in Special:Ask (by Stephan Gambke)
* Fixed bug causing occasional exceptions in the calendar format (by Mark A. Hershberger)
* Fixed bug in timeseries format that caused the value 0 to be excluded (by James Hong Kong)
* Fixed bug in the calendar parser functions (by James Montalvo)
* Fixed bug in the datatables format when having empty printouts (by Fr Jeremy Krieg)
* Fixed bug in filtered format that broke the format on browsers supporting the
  [Array.prototype.values()](https://developer.mozilla.org/en/docs/Web/JavaScript/Reference/Global_Objects/Array/values) method

## SRF 2.3.0

Released on September 24, 2015.

* Added table view to filtered format
* Fixed eventcalendar format to return a truncated version of strings in tooltips
* Internal code cleanup concerning the calendar format
* Internal code cleanup concerning the tagcould format
* Provided translation updates (by translatewiki.net community)

## SRF 2.2.0

Released on July 30, 2015.

* Fixed filtered format so that filters work for ol/ul type lists
* Fixed gallery format by adding a required dependency for the carousel option to the widget parameter
* Enhanced calendar format by adding the startmonth and startyear parameters
* Provided translation updates (by translatewiki.net community)

## SRF 2.1.2

Released on February 26, 2015.

* Fixed bug in the slideshow format API

## SRF 2.1.1

Released on February 4, 2015.

* Fixed various jQuery 1.9+ issues that appeared in connection with MW 1.24+ including `jquery.jqplot`, `jquery.fancybox`, and`jquery.jgrid`
* Added replacement pattern for `%3A` in `gallery` overlay format (65abda9)
* Fixed the usage of plain-text title attribute in `gallery` overlay format (f18f3ea8)
* Added support for apostrophes in title text `gallery` overlay format (8dec4106)
* #79 Fixed `event calender` class parameter usage
* #73 Fixed `icalendar` escaping issues (as per RFC)

## SRF 2.0.0

Released on August 6, 2014.

* #26 Fixed not showing up of graphvis legend when it should
* #35 Fixed error in the gallary format for a null object
* #37 Fixed error in the timeline format for named arguments
* #43 Fixed graphname parameter in the graphviz format
* [14daff1](https://github.com/SemanticMediaWiki/SemanticResultFormats/commit/14daff10350190634b96f644961beb15d0b29e09)
commit added support for date/time values to the [excel format](https://www.semantic-mediawiki.org/wiki/Help:Excel_format)
* #46 Added support for `format=graph` using Composer `mediawiki/graph-viz` package
* #47 Added parameters 'filename' (the download file name for the generated file) and 'templatefile' (a template file
      from the NS_FILE namespace used for formatting the generated file) to [excel format](https://www.semantic-mediawiki.org/wiki/Help:Excel_format)
* #51 Fixed null title issue in Gallery.php for MW 1.23+
* #52 Fixed `format=process` exception that was caused by missing message parameters
* #53 Updated jQuery blockUI plugin to v.2.66.0-2013.10.09
* Provided translation updates (by translatewiki.net community)

## SRF 1.9.1

Released on April 25, 2014.

* #13 Fixed PHP warning when running PHP >=5.1 in strict mode
* #16 Improved handling of empty values in the filtered format
* #19 Fixed duplicate headers bug in the excel format
* #22 The excel format is now enabled by default when PHPExcel is loaded
* #23 The PHPUnit bootstrap now works on Windows
* #24 Added support for the new MediaWiki i18n JSON system
* #25 Fixed resource path issue occurring on some installations
* #27 Fixed error in the tagcloud format occurring when referencing a non-existing page
* #31 Added template parameter to the timeline format

## SRF 1.9.0.1

Released on January 17, 2014.

* #7 Fix tagcloud rendering on special pages and when using templates

## SRF 1.9.0

Released on January 10, 2014.

### Compatibility changes

* Changed minimum MediaWiki version from 1.17 to 1.19.
* Changed minimum PHP version from 5.2.x. to 5.3.x.
* Changed Semantic MediaWiki compatibility from 1.8.x to 1.9.x.
* Full compatibility with MediaWiki 1.19, 1.20, 1.21, 1.22 and forward-compatibility with 1.23.
* Deleted SRF_Settings.php entry point, the main entry point is SemanticResultFormats.php
* [Installation](INSTALL.md) is now done via the [Composer](http://getcomposer.org/) dependency manager.

### New formats

* [media](https://www.semantic-mediawiki.org/wiki/Help:Media_format) (Added by James Hong Kong)
* [excel](https://www.semantic-mediawiki.org/wiki/Help:Excel_format) (Requires PHPExcel, disabled by default) (Added by Kim Eik)

### New features

* [EventCalendar](https://www.semantic-mediawiki.org/wiki/Help:Eventcalendar_format) SMWAPI/Ajax integration
* [tree format](https://www.semantic-mediawiki.org/wiki/Help:Tree_format): new parameters 'root' and 'start level'

### Other improvements and changes

* jquery.tagcanvas increase from 1.18 to 1.20
* jquery.responsiveslides increase from v1.32 to v1.53
* jquery.sparkline increase from 2.0 to 2.1
* d3 increase from d3.vs to d3.v3
* Introduce PHP SRF\ namespaces

### Bug fixes

* tree format: root elements not included

## SRF 1.8.0

Released on December 2, 2012.

### Compatibility changes

* Changed minimum MediaWiki version from 1.16 to 1.17.
* Changed minimum Semantic MediaWiki version from 1.7 to 1.8.
* Full compatibility with MediaWiki 1.19 and forward-compatibility with 1.20.
* Changed minimum Validator version from 0.4 to 1.0.
* jqplotbar and jqplotpie format are replaced by jqplotchart format
* SRF_Settings.php has been deprecated (will be removed in 1.9) as entry point, use SemanticResultFormats.php instead

### New formats

* slideshow (written by Stephan Gambke)
* listwidget (bug 37721, I54660c15) (James Hong Kong)
* sparkline format (I911862ce) (James Hong Kong)
* timeseries printer (Ibad00690) (James Hong Kong)
* d3chart format (I4baa7df8) (James Hong Kong)
* jqplotseries format (I3c8847aa) (James Hong Kong)
* jqplotchart format (I3c8847aa) (James Hong Kong)
* incoming format (Ie5be9196) (James Hong Kong)
* syndication feed (atom, rss) (bug 38636, Ia3cdc243) (James Hong Kong)
* dygraphs chart format (Ibac4b753) (James Hong Kong)
* event calendar (Iaff44b71) (James Hong Kong)
* earliest format (written by Jeroen De Dauw, Nischay Nahata)
* latest format (written by Jeroen De Dauw, Nischay Nahata)

### New features

* (Ice7ba7ea) Enable tableview plugin support for timeseries, jqplotseries, and dygraphs format
* (bug 38094) Tag cloud format added 'sphere widget' (James Hong Kong)
* (I6920ae49) Tag cloud format added 'wordcloud widget' (James Hong Kong)
* (bug 37695) Tag cloud format added template support (James Hong Kong)
* (bug 38184) Gallery format added 'slideshow widget' (James Hong Kong)
* (bug 38357) Gallery format added 'overlay' parameter enabling gallery slideshow/carousel image overlay  (James Hong Kong)
* (I7c49a644) Gallery format added redirects to enable images to be redirect to another target (James Hong Kong)
* (bug 38296, Ic9f5e186) Gallery format fixed Special:Ask gallery display error (James Hong Kong)
* (I338b6b19, I7a0e663b) Gallery format added support for pointing to the subject property in the gallery property parameters using "-"
* (I762cde6a) Value rank format added template support (James Hong Kong)

### Other improvements

* Added test file support (see SemanticResultFormats/tests/...)
* All formats have been moved (see SemanticResultFormats/formats/...)
* Added new folder (SemanticResultFormats/resources/...) where all external plug-ins will be successively been moved
* Introduce a new array-based syntax to define parameters (see Validator/IParameterDefinition class)

### Bug fixes

* (bug 38258, I10be92c9) Fix authors/editors in bibtex

## SRF 1.7.1

Released on March 8, 2012.

* Fixed issue with the graphlenegd parameter in the graph format (bug 33745).
* Added 'default' parameter to math formats (bug 34983).
* Added 'galleryformat' parameter with carousel option (bug 34411) (James Hong Kong)

New formats in this version are:

* tree, ultree, oltree (written by Stephan Gambke)
* JitGraph (still in alpha, disabled by default) (written by Alex Shapovalov) (bug 32877)
* filtered (still in alpha, disabled by default) (written by Stephan Gambke)

## SRF 1.7.0

Released on January 1, 2012.

* Compatibility with SMW 1.7 and later.
* Dropped support for MediaWiki 1.15.x and SMW < 1.7.
* Added warning message to jqplotpie and jqplotbar shown when there are no results instead of a non-working chart.
* Added value distribution support to jqplotpie and jqplotbar.
* Added min parameter to jqplotbar to set the minimun value for the Y-axis.
* Added pointlabel parameter to jqplotbar and chartlegend, legendlocation,
  datalabels and datalabeltype parameters to jqplotpie based on a patches by James Hong Kong.
* Made array and hash formats compatible with 'Array' extension 2.0 and 'HashTables' 1.0.
* Added summary parameter to the icalendar format.

New formats in this version are:

* valuerank (written by Daniel Schuba)
* D3Line, D3Bar and D3Treemap (written by James Hong Kong) (requires MW 1.17 or later)

## SRF 1.6.2

Released on September 18, 2011.

* Fixed error in math printer when there are no numerical results.
* Fixed vCard compatibility with SMW 1.6 and later.
* Fixed array compatibility with SMW 1.6 and later.
* Added median and product formats to the list of default enabled formats.

## SRF 1.6.1

Released on August 20, 2011.

* Fixed rendering bug in the tagcloud format occuring for inline queries.
* Fixed jqPlotBar and jqPlotPie rendering on Special:Ask and other special pages.
* Cleaned up the jqPlotBar format somewhat.
* Dropped compatibility with SMW < 1.6 for the tagcloud format.

## SRF 1.6

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

## SRF 1.5.3

Released on February 9, 2011.

Changes in this version:

* Support for images specified by properties in the gallery format.
* Fixes to the calendar and jqplot formats.
* Improvements to the timeline and eventline formats.

New formats in this version are:

* tagcloud (written by Jeroen De Dauw)

## SRF 1.5.2

Released on January 11, 2011.

Changes in this version:

* Handling for ResourceLoader in MediaWiki 1.17+ added for
  'timeline', 'eventline', 'jqplotbar' and 'jqplotpie' formats.
* Visualization improvements for 'process' format.

## SRF 1.5.1

Released on August 26, 2010.

New formats in this version are:

* jqplotbar (written by Sanyam Goyal and Yaron Koren)
* jqplotpie (written by Sanyam Goyal and Yaron Koren)

Other changes:

* Added support for 'semantic' extension type, added by SMW 1.5.2 and above.

## SRF 1.5.0

Released on June 22, 2010.

New formats in this version are:

* gallery (written by Rowan Rodrik van der Molen)

Changes in this version:

* the functions getName() and getParameters() were added to most formats, for use in Special:Ask
* a 'lang' parameter was added to the 'calendar' format
* improvements in 'exhibit' result format
** new facet styles (slider and search)

## SRF 1.4.5

Released on June 3, 2009.

New formats in this version are:

* outline (written by Yaron Koren)

Other changes:

* the 'ploticus' format was disabled, due to a security hole
* the 'calendar' format no longer requires disabling of caching
* imagemap links were fixed for the 'graph' format
* handling was added for the Admin Links extension

## SRF 1.4.4

Released on April 16, 2009.

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

## SRF 1.4.3

Released on March 2, 2009.

New formats in this version are:
* bibtex (written by Steren Giannini)

Also, handling of templates was added to the 'calendar' format by David
Loomer.

## SRF 1.4.2

Released on February 10, 2009.

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

## SRF 1.4.0

Released on November 26, 2008.

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

[ProWiki]: https://professional.wiki
