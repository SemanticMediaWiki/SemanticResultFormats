
<!-- Begin of generated contents by readmeContentsBuilder.php -->

## TestCases

Contains 13 files with a total of 47 tests:

### F
* [filtered-01.json](https://github.com/SemanticMediaWiki/SemanticResultFormats/tree/master/tests/phpunit/Integration/JSONScript/TestCases/filtered-01.json) Filtered format: ...
* [filtered-02.json](https://github.com/SemanticMediaWiki/SemanticResultFormats/tree/master/tests/phpunit/Integration/JSONScript/TestCases/filtered-02.json) Filtered format: ...

### I
* [icalendar-01.json](https://github.com/SemanticMediaWiki/SemanticResultFormats/tree/master/tests/phpunit/Integration/JSONScript/TestCases/icalendar-01.json) Test `format=icalendar`
* [icalendar-02.json](https://github.com/SemanticMediaWiki/SemanticResultFormats/tree/master/tests/phpunit/Integration/JSONScript/TestCases/icalendar-02.json) Test `format=icalendar` on iCalendar specific labels using `Special:Ask`

### M
* [math-01.json](https://github.com/SemanticMediaWiki/SemanticResultFormats/tree/master/tests/phpunit/Integration/JSONScript/TestCases/math-01.json) Test for math/sum result format in pt-br lang

### T
* [timeline-01.json](https://github.com/SemanticMediaWiki/SemanticResultFormats/tree/master/tests/phpunit/Integration/JSONScript/TestCases/timeline-01.json) Test for timeline result format
* [tree-01.json](https://github.com/SemanticMediaWiki/SemanticResultFormats/tree/master/tests/phpunit/Integration/JSONScript/TestCases/tree-01.json) Tree format: Multi-page results
* [tree-02.json](https://github.com/SemanticMediaWiki/SemanticResultFormats/tree/master/tests/phpunit/Integration/JSONScript/TestCases/tree-02.json) Tree format: Simple one-page result
* [tree-03.json](https://github.com/SemanticMediaWiki/SemanticResultFormats/tree/master/tests/phpunit/Integration/JSONScript/TestCases/tree-03.json) Tree format: Raising error: Missing 'parent' parameter
* [tree-04.json](https://github.com/SemanticMediaWiki/SemanticResultFormats/tree/master/tests/phpunit/Integration/JSONScript/TestCases/tree-04.json) Tree format: Empty resultset does not produce tree
* [tree-05.json](https://github.com/SemanticMediaWiki/SemanticResultFormats/tree/master/tests/phpunit/Integration/JSONScript/TestCases/tree-05.json) Tree format: Loop detection
* [tree-06.json](https://github.com/SemanticMediaWiki/SemanticResultFormats/tree/master/tests/phpunit/Integration/JSONScript/TestCases/tree-06.json) Tree format: Insert elements with multiple parents multiple times
* [tree-07.json](https://github.com/SemanticMediaWiki/SemanticResultFormats/tree/master/tests/phpunit/Integration/JSONScript/TestCases/tree-07.json) Tree format: Simple one-page result

-- Last updated on 2017-10-06 by `readmeContentsBuilder.php`

<!-- End of generated contents by readmeContentsBuilder.php -->

## Writing a test case

Have a look at the [bootstrap.json](https://github.com/SemanticMediaWiki/SemanticResultFormats/tree/master/tests/phpunit/Integration/JSONScript/bootstrap.json) example test case and the [introduction video](https://youtu.be/7fDKjPFaTaY). For further assistance, please read the following [document](https://github.com/SemanticMediaWiki/SemanticMediaWiki/tree/master/tests/phpunit/Integration/JSONScript#designing-an-integration-test).

## Running a test

The `composer integration` command can be used the quickly execute the integration tests and in
combination with the `--filter` option allows to select a single specific case.

<pre>
$ composer integration -- --filter vcard
Using PHP 7.1.1

Semantic Result Formats: 3.0.0-alpha

Semantic MediaWiki: 3.0.0-alpha (c352b6a, SMWSQLStore3, mysql)
MediaWiki:          1.31.0-alpha (44c06df, MediaWiki vendor autoloader)
Site language:      en

Execution time:     2017-01-01 12:00
Debug logs:         Disabled
Xdebug:             Disabled (or not installed)

PHPUnit 4.8.35 by Sebastian Bergmann and contributors.

Runtime:        PHP 7.1.1
Configuration:  /var/www/html/w/extensions/SemanticResultFormats/phpunit.xml.dist

.

Time: 18.38 seconds, Memory: 38.00MB
</pre>
