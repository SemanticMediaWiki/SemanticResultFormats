# Semantic Result Formats
[![CI](https://github.com/SemanticMediaWiki/SemanticResultFormats/actions/workflows/ci.yml/badge.svg)](https://github.com/SemanticMediaWiki/SemanticResultFormats/actions/workflows/ci.yml)
[![Latest Stable Version](https://poser.pugx.org/mediawiki/semantic-result-formats/version.png)](https://packagist.org/packages/mediawiki/semantic-result-formats)
[![Packagist download count](https://poser.pugx.org/mediawiki/semantic-result-formats/d/total.png)](https://packagist.org/packages/mediawiki/semantic-result-formats)

Semantic Result Formats (SRF) is a MediaWiki extension that provides extra visualizations (result formats) for [Semantic MediaWiki][smw] queries. For more information, see the [Semantic Result Formats documentation][srf] or consult the
[release notes](RELEASE-NOTES.md).

## Requirements

- PHP 7.3.19 or later
- MediaWiki 1.35 or later
- Semantic MediaWiki 3.0 or later

## Installation

The recommended way to install this extension is by using [Composer][composer]. See the detailed
[installation guide](docs/INSTALL.md) which also contains information about compatibility and
configuration.

## Contribution and support

Semantic Result Formats is maintained by @JeroenDeDauw.

[![Chatroom](https://www.semantic-mediawiki.org/w/thumb.php?f=Comment-alt-solid.svg&width=35)](https://www.semantic-mediawiki.org/wiki/Semantic_MediaWiki_chatroom)
[![Twitter](https://www.semantic-mediawiki.org/w/thumb.php?f=Twitter-square.svg&width=35)](https://twitter.com/#!/semanticmw)
[![Facebook](https://www.semantic-mediawiki.org/w/thumb.php?f=Facebook-square.svg&width=35)](https://www.facebook.com/pages/Semantic-MediaWiki/160459700707245)
[![LinkedIn](https://www.semantic-mediawiki.org/w/thumb.php?f=LinkedIn-square.svg&width=35)]([https://twitter.com/#!/semanticmw](https://www.linkedin.com/groups/2482811/))
[![YouTube](https://www.semantic-mediawiki.org/w/thumb.php?f=Youtube-square.svg&width=35)](https://www.youtube.com/c/semanticmediawiki)
[![Mailing lists](https://www.semantic-mediawiki.org/w/thumb.php?f=Envelope-square.svg&width=35)](https://www.semantic-mediawiki.org/wiki/Semantic_MediaWiki_mailing_lists)

Primary support channels:

* [User mailing list](https://sourceforge.net/projects/semediawiki/lists/semediawiki-user) - for user questions
* [SMW chat room](https://www.semantic-mediawiki.org/wiki/Semantic_MediaWiki_chatroom) - for questions and developer discussions
* [Issue tracker](https://github.com/SemanticMediaWiki/SemanticMediaWiki/issues) - for bug reports

If you want to contribute work to the project please look at the [contribution guildline](/CONTRIBUTING.md).
A list of people who have made contributions in the past can be found [here][contributors].

* [File an issue](https://github.com/SemanticMediaWiki/SemanticResultFormats/issues)
* [Submit a pull request](https://github.com/SemanticMediaWiki/SemanticResultFormats/pulls)
* Ask a question on [the mailing list](https://www.semantic-mediawiki.org/wiki/Mailing_list)

## Tests

This extension provides unit and integration tests and are normally run by a [continues integration platform][travis]
but can also be executed locally using the shortcut command `composer phpunit` from the extension base directory.


## License

Generally published under [GNU General Public License 2.0 or later][licence] together with
third-party plugins and their license.

[smw]: https://github.com/SemanticMediaWiki/SemanticMediaWiki
[travis]: https://travis-ci.org/SemanticMediaWiki/SemanticResultFormats
[srf]: https://www.semantic-mediawiki.org/wiki/Extension:Semantic_Result_Formats
[composer]: https://getcomposer.org/
[contributors]: https://github.com/SemanticMediaWiki/SemanticResultFormats/graphs/contributors
[licence]: https://www.gnu.org/copyleft/gpl.html
