# Semantic Result Formats

[![Build Status](https://secure.travis-ci.org/SemanticMediaWiki/SemanticResultFormats.svg?branch=master)](http://travis-ci.org/SemanticMediaWiki/SemanticResultFormats)
[![Scrutinizer Quality Score](https://scrutinizer-ci.com/g/SemanticMediaWiki/SemanticResultFormats/badges/quality-score.png?s=a2f091e91cb9c8aa297e028f2f30d99153446796)](https://scrutinizer-ci.com/g/SemanticMediaWiki/SemanticResultFormats/)
[![Latest Stable Version](https://poser.pugx.org/mediawiki/semantic-result-formats/version.png)](https://packagist.org/packages/mediawiki/semantic-result-formats)
[![Packagist download count](https://poser.pugx.org/mediawiki/semantic-result-formats/d/total.png)](https://packagist.org/packages/mediawiki/semantic-result-formats)
[![Dependency Status](https://www.versioneye.com/php/mediawiki:semantic-result-formats/badge.png)](https://www.versioneye.com/php/mediawiki:semantic-result-formats)

Semantic Result Formats (a.k.a. SRF) is an extension to MediaWiki that bundles a number of result
formats for [Semantic MediaWiki's][smw] inline queries. The individual formats can be added to the
installation independently. For more information, visit the [SRF homepage][srf] or consult the
[release notes](RELEASE-NOTES.md).


## Requirements

- PHP 5.5 or later
- MediaWiki 1.23 or later (1.27 or later recommended)
- Semantic MediaWiki 2.0 or later (2.5 or later recommended)
- MySQL 5 or later or SQLite 3 or later


## Installation

The recommended way to install this extension is by using [Composer][composer].
Just add the following to the MediaWiki `composer.local.json` file and run the
`php composer.phar install/update "mediawiki/semantic-result-formats"` command.

```json
{
	"require": {
		"mediawiki/semantic-result-formats": "~2.5"
	}
}
```
Information about compatibility, details about the installation, and its configuration can be found [here](INSTALL.md).


## Contribution and support

Development is coordinated by James Hong Kong and Jeroen De Dauw.

If you have remarks, questions, or suggestions, please ask them on semediawiki-users@lists.sourceforge.net.
You can subscribe to this list [here](https://lists.sourceforge.net/lists/listinfo/semediawiki-user).

If you want to contribute work to the project please subscribe to the
developers mailing list and have a look at the [contribution guildline](/CONTRIBUTING.md).
A list of people who have made contributions in the past can be found [here][contributors].

* [File an issue](https://github.com/SemanticMediaWiki/SemanticResultFormats/issues)
* [Submit a pull request](https://github.com/SemanticMediaWiki/SemanticResultFormats/pulls)
* Ask a question on [the mailing list](https://www.semantic-mediawiki.org/wiki/Mailing_list)
* Ask a question on the #semantic-mediawiki IRC channel on Freenode.


## License

Generally published under [GNU General Public License 2.0 or later][licence] together with
third-party plugins and their license.

[smw]: https://github.com/SemanticMediaWiki/SemanticMediaWiki
[srf]: https://www.semantic-mediawiki.org/wiki/Extension:Semantic_Result_Formats
[composer]: https://getcomposer.org/
[contributors]: https://github.com/SemanticMediaWiki/SemanticResultFormats/graphs/contributors
[licence]: https://www.gnu.org/copyleft/gpl.html
