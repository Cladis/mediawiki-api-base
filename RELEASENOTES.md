These are the release notes for the [mediawiki-api-base](README.md).

## Version 0.2 (under development)

### Compatibility changes

* Changed minimum MediaWiki version to 1.24
 * Session objects now use action=query&meta=tokens to get tokens.
 * The default token type is now 'csrf', 'edit' is no longer recognised here.

### Deprecations

* MediawikiApi getAction and postAction methods have been deprecated in favour of getRequest and postRequest

### New features

* If warnings are present in API results E_USER_WARNING errors are triggered
* The Request interface and SimpleRequest class have been added
* MediawikiApi now has a getRequest and postRequest method
* Unsuccessful logins now throw a UsageException with extra details

## Version 0.1.2 (25 May 2014)

* Fix issue where API tokens were not returned

## Version 0.1 (12 May 2014)

* Initial release after split from mediawiki-api lib
