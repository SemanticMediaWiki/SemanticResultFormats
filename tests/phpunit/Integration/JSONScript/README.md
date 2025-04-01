## How to update List of tests

The `readmeContentsBuilder.php` script can be used to update the list of available test cases, including their descriptions.

To use this script, navigate to the following directory in your project:
- `extensions/SemanticResultFormats/tests/phpunit/Integration/JSONScript/`

Once there, run the script using PHP with the following command:
- `php ReadmeContentsBuilder.php`

The script will automatically fetch the test cases, update the `README.md` file, and ensure that the list of tests is up to date with any changes made in the test case files.
It will also generate the descriptions for each test based on the contents of the corresponding JSON files.

<!-- Begin of generated contents by readmeContentsBuilder.php -->

## List of tests

- Files: 33 (includes 131 tests)
- Last update: 2025-04-01

### B
* [bibtex-01.json](https://github.com/SemanticMediaWiki/SemanticResultFormats/tree/master/tests/phpunit/Integration/JSONScript/TestCases/bibtex-01.json) Test `format=bibtex`

### C
* [carousel-01.json](https://github.com/SemanticMediaWiki/SemanticResultFormats/tree/master/tests/phpunit/Integration/JSONScript/TestCases/carousel-01.json) Test `format=carousel` html output (no JS validation)

### D
* [datatables-01.json](https://github.com/SemanticMediaWiki/SemanticResultFormats/tree/master/tests/phpunit/Integration/JSONScript/TestCases/datatables-01.json) Test `format=datatables` html output (no JS validation)

### E
* [earliest-01.json](https://github.com/SemanticMediaWiki/SemanticResultFormats/tree/master/tests/phpunit/Integration/JSONScript/TestCases/earliest-01.json) Test for `format=earliest`
* [eventcalendar-01.json](https://github.com/SemanticMediaWiki/SemanticResultFormats/tree/master/tests/phpunit/Integration/JSONScript/TestCases/eventcalendar-01.json) Test `format=eventcalendar` html output (no JS validation)

### F
* [filtered-01.json](https://github.com/SemanticMediaWiki/SemanticResultFormats/tree/master/tests/phpunit/Integration/JSONScript/TestCases/filtered-01.json) Filtered format: ...
* [filtered-02.json](https://github.com/SemanticMediaWiki/SemanticResultFormats/tree/master/tests/phpunit/Integration/JSONScript/TestCases/filtered-02.json) Filtered format: ...
* [filtered-03.json](https://github.com/SemanticMediaWiki/SemanticResultFormats/tree/master/tests/phpunit/Integration/JSONScript/TestCases/filtered-03.json) Filtered format: List view tests

### G
* [gallery-01.json](https://github.com/SemanticMediaWiki/SemanticResultFormats/tree/master/tests/phpunit/Integration/JSONScript/TestCases/gallery-01.json) Test `format=gallery` with file upload
* [gallery-02.json](https://github.com/SemanticMediaWiki/SemanticResultFormats/tree/master/tests/phpunit/Integration/JSONScript/TestCases/gallery-02.json) Test `format=gallery` with file upload and captiontemplate
* [gallery-03.json](https://github.com/SemanticMediaWiki/SemanticResultFormats/tree/master/tests/phpunit/Integration/JSONScript/TestCases/gallery-03.json) Test `format=gallery` with different different parameters like widget, redirects, overlay
* [gantt-01.json](https://github.com/SemanticMediaWiki/SemanticResultFormats/tree/master/tests/phpunit/Integration/JSONScript/TestCases/gantt-01.json) Test the gantt format

### I
* [icalendar-01.json](https://github.com/SemanticMediaWiki/SemanticResultFormats/tree/master/tests/phpunit/Integration/JSONScript/TestCases/icalendar-01.json) Test `format=icalendar`
* [icalendar-02.json](https://github.com/SemanticMediaWiki/SemanticResultFormats/tree/master/tests/phpunit/Integration/JSONScript/TestCases/icalendar-02.json) Test `format=icalendar` on iCalendar specific labels using `Special:Ask`
* [icalendar-03.json](https://github.com/SemanticMediaWiki/SemanticResultFormats/tree/master/tests/phpunit/Integration/JSONScript/TestCases/icalendar-03.json) Test `format=icalendar` with timezone using `Special:Ask`

### L
* [latest-01.json](https://github.com/SemanticMediaWiki/SemanticResultFormats/tree/master/tests/phpunit/Integration/JSONScript/TestCases/latest-01.json) Test for `format=latest`
* [listwidget-01.json](https://github.com/SemanticMediaWiki/SemanticResultFormats/tree/master/tests/phpunit/Integration/JSONScript/TestCases/listwidget-01.json) Listwidget format: widgets - unordered (#449 - `wgContLang=fr`, `wgLang=en`)

### M
* [math-01.json](https://github.com/SemanticMediaWiki/SemanticResultFormats/tree/master/tests/phpunit/Integration/JSONScript/TestCases/math-01.json) Test for math/sum result format in pt-br lang
* [media-01.json](https://github.com/SemanticMediaWiki/SemanticResultFormats/tree/master/tests/phpunit/Integration/JSONScript/TestCases/media-01.json) Test `format=media` with file upload

### O
* [outline-01.json](https://github.com/SemanticMediaWiki/SemanticResultFormats/tree/master/tests/phpunit/Integration/JSONScript/TestCases/outline-01.json) Test `format=outline` template output
* [outline-02.json](https://github.com/SemanticMediaWiki/SemanticResultFormats/tree/master/tests/phpunit/Integration/JSONScript/TestCases/outline-02.json) Test `format=outline` list output

### T
* [tagcloud-01.json](https://github.com/SemanticMediaWiki/SemanticResultFormats/tree/master/tests/phpunit/Integration/JSONScript/TestCases/tagcloud-01.json) Test `format=tagcloud` html output
* [tagcloud-02.json](https://github.com/SemanticMediaWiki/SemanticResultFormats/tree/master/tests/phpunit/Integration/JSONScript/TestCases/tagcloud-02.json) Test `format=tagcloud` html output, namespaced
* [timeline-01.json](https://github.com/SemanticMediaWiki/SemanticResultFormats/tree/master/tests/phpunit/Integration/JSONScript/TestCases/timeline-01.json) Test for timeline result format
* [tree-01.json](https://github.com/SemanticMediaWiki/SemanticResultFormats/tree/master/tests/phpunit/Integration/JSONScript/TestCases/tree-01.json) Tree format: Multi-page results
* [tree-02.json](https://github.com/SemanticMediaWiki/SemanticResultFormats/tree/master/tests/phpunit/Integration/JSONScript/TestCases/tree-02.json) Tree format: Simple one-page result
* [tree-03.json](https://github.com/SemanticMediaWiki/SemanticResultFormats/tree/master/tests/phpunit/Integration/JSONScript/TestCases/tree-03.json) Tree format: Raising error: Missing 'parent' parameter
* [tree-04.json](https://github.com/SemanticMediaWiki/SemanticResultFormats/tree/master/tests/phpunit/Integration/JSONScript/TestCases/tree-04.json) Tree format: Empty resultset does not produce tree
* [tree-05.json](https://github.com/SemanticMediaWiki/SemanticResultFormats/tree/master/tests/phpunit/Integration/JSONScript/TestCases/tree-05.json) Tree format: Loop detection
* [tree-06.json](https://github.com/SemanticMediaWiki/SemanticResultFormats/tree/master/tests/phpunit/Integration/JSONScript/TestCases/tree-06.json) Tree format: Insert elements with multiple parents multiple times
* [tree-07.json](https://github.com/SemanticMediaWiki/SemanticResultFormats/tree/master/tests/phpunit/Integration/JSONScript/TestCases/tree-07.json) Tree format: Simple one-page result

### V
* [valuerank-01.json](https://github.com/SemanticMediaWiki/SemanticResultFormats/tree/master/tests/phpunit/Integration/JSONScript/TestCases/valuerank-01.json) Test `format=valuerank` with all related parameters
* [vcard-01.json](https://github.com/SemanticMediaWiki/SemanticResultFormats/tree/master/tests/phpunit/Integration/JSONScript/TestCases/vcard-01.json) Test `format=vcard`

<!-- End of generated contents by readmeContentsBuilder.php -->

## Writing a test case

Have a look at the [bootstrap.json](https://github.com/SemanticMediaWiki/SemanticResultFormats/tree/master/tests/phpunit/Integration/JSONScript/bootstrap.json) example test case and the [introduction video](https://youtu.be/7fDKjPFaTaY). For further assistance, please read the following [document](https://github.com/SemanticMediaWiki/SemanticMediaWiki/tree/master/tests/phpunit/Integration/JSONScript#designing-an-integration-test).

## Running a test

The `composer integration` command can be used the quickly execute the integration tests and in
combination with the `--filter` option allows to select a single specific case.

<pre>
$ composer integration -- --filter vcard
Using PHP 8.1.31

Semantic Result Formats: 5.0.0-alpha

Semantic MediaWiki: 5.0.1-alpha (25f24c1, SMWSQLStore, mysql)
MediaWiki:          1.39.11 (MediaWiki vendor autoloader)
Site language:      en

Execution time:     2025-04-01 06:40
Debug logs:         Disabled
Xdebug:             Disabled (or not installed)

PHPUnit 8.5.41 by Sebastian Bergmann and contributors.

Runtime:        PHP 8.1.31
Configuration:  /var/www/html/w/extensions/SemanticResultFormats/phpunit.xml.dist

.

Time: 18.38 seconds, Memory: 38.00MB
</pre>
