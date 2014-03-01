# Semantic Result Formats
[![Build Status](https://secure.travis-ci.org/SemanticMediaWiki/SemanticResultFormats.png?branch=master)](http://travis-ci.org/SemanticMediaWiki/SemanticResultFormats)
[![Scrutinizer Quality Score](https://scrutinizer-ci.com/g/SemanticMediaWiki/SemanticResultFormats/badges/quality-score.png?s=a2f091e91cb9c8aa297e028f2f30d99153446796)](https://scrutinizer-ci.com/g/SemanticMediaWiki/SemanticResultFormats/)
[![Latest Stable Version](https://poser.pugx.org/mediawiki/semantic-result-formats/version.png)](https://packagist.org/packages/mediawiki/semantic-result-formats)
[![Packagist download count](https://poser.pugx.org/mediawiki/semantic-result-formats/d/total.png)](https://packagist.org/packages/mediawiki/semantic-result-formats)

Semantic Result Formats is an extension to MediaWiki that bundles a number of result formats for Semantic MediaWiki's inline queries. The individual formats can be added to the installation independently. For more information, visit the [SRF homepage][srf] or consult the [release notes](RELEASE-NOTES.md).

## Installation

The recommended way to install this extension is by using [Composer][composer]. Just add the following to the MediaWiki `composer.json` file and run the ``php composer.phar install/update`` command.

```json
{
	"require": {
		"mediawiki/semantic-result-formats": "~1.9.*"
	}
}
```
Information about compatability and deatils about the installation can be found [here](INSTALL.md).


## Contact

If you have remarks, questions, or suggestions, please send them to
semediawiki-users@lists.sourceforge.net. You can subscribe to this
list [here](http://sourceforge.net/mailarchive/forum.php?forum_name=semediawiki-user).

Bugs should be filed at the [MediaWiki Bugzilla](http://bugzilla.wikimedia.org/).
Please select "SemanticResultFormats" as the "component".

If you want to contribute work to the project please subscribe to the
developers mailing list, semediawiki-devel@lists.sourceforge.net.

## Developers

Development is coordinated by James Hong Kong and Jeroen De Dauw.

Some parts of Semantic Result Formats development have been funded by
[Institut AIFB](http://www.aifb.kit.edu/web/Hauptseite) of the
Karlsruhe Institute of Technology in Germany.

Specific development tasks have also been supported by the European Union
under the project Active.

Development of the jqPlot-based formats was funded via the Google Summer
of Code.

## Contributors

* S Page has contributed code.
* The Graph format was contributed by Frank Dengler, based on AT&T's Dot
Graphic utility and the MediaWiki Graph extension. The Process format
had great contributions by Hans-Jörg Happel.
* The Eventline and Timeline code is based on the Timeline code by the
MIT Simile project.
* The Exhibit code was contributed by Fabian Howahl, based on code by the
MIT Simile project.
* The Calendar, Outline and mathematical formats were contributed by Yaron Koren.
* The Ploticus format was contributed by Joel Natividad.
* The BibTeX format was contributed by Steren Giannini.
* The Gallery format was contributed by Rowan van der Molen.
* The jqPlot-based formats were contributed by Sanyam Goyal and Yaron Koren,
using the jQuery and jqPlot Javascript libraries.
* The valuerank format was added by DaSch.
* Many other people have contributed to the project by providing helpful
comments and suggestions. More comments are always welcome!

[srf]: https://www.semantic-mediawiki.org/wiki/Semantic_Result_Formats
[composer]: https://getcomposer.org/
