Scripturnum
===========

This is a PHP Library intended to allow the storage of references to scripture in only 32 bits (31, actually), which allows a scripture range to be stored in a single unsigned integer in most DBs.  

## License

Don't break laws.  Otherwise, use this however you want at your own risk.  The contributors take no responsibility for your behavior. 

If you expand or improve this API, you *must* at least consider submitting a pull request.  

## Installation

Use Composer.  

	composer require jkrrv/scripturnum
	composer install

Or, download the source files from GitHub.  The code comes ready-to-run; building is not required. 

## Prerequisites

 - PHP >5.5
 - some idea of how to write PHP.

## A Note on Canon

The author of this library is protestant, and holds to a closed canon consisting of the 66 books [listed as western protestantism here](https://en.wikipedia.org/wiki/Biblical_canon).  Pull requests and bugs requesting anything outside this canon will be marked as [heresy](https://github.com/jkrrv/ScripturNum/labels/Heresy%21) and closed.

From a technical perspective, having a closed cannon is *vital* because verses and books are numbered sequentially.  Should insertions or omissions be made, numbering would be askew everywhere thenceforth.  Also, since the aim of this library is to allow concise storage of references in a database where, presumably, they would not be modified often, maintaining consistent results is always imperative. 