ScripturNum
===========

Like a nasturtium, but tastier. 

This is a PHP Library intended for working with scripture references.  It can parse text-based scripture references, and can express passages of scripture in only 32 bits (31, actually), which allows a scripture range to be stored in a single unsigned integer in most DBs.  

## License

Don't break laws.  Otherwise, use this however you want at your own risk.  The contributors take no responsibility for your behavior or what may occur when you use this code. 

If you expand or improve this API, you *must* at least *consider* submitting a pull request. 

## Installation

Use Composer. Just run: 

	composer require jkrrv/scripturnum
	composer install

Or, download the source files from GitHub.  The code comes ready-to-run; there is no particular 'build' procedure. 

## Prerequisites

 - PHP >5.5 (this will be bumped to 5.6 in some future version)
 - some idea of how to write PHP, so you can actually use this library.
 
 
## Usage

### Starting with a ScrupturNum

Starting with a number, as you might when pulling data from a database, is meant to be the easiest and most efficient call.  

Take, for instance, 738197728, which represents Romans 1-8.  

	$s = new ScripturNum(738197728);
    var_dump($s);
    echo $s;
    
Produces the output:

	object(ScripturNum\ScripturNum)
	  protected 'int' => int 738197728
	  protected 'book' => int 45
	  protected 'startCh' => int 1
	  protected 'startV' => int 1
	  protected 'endCh' => int 8
	  protected 'endV' => int 39
	Romans 1-8
	
	
### Starting with a Reference String

Most of us don't use numbers in our heads to refer to passages of scripture.  Use the `newFromString` method to parse a string.  It's (probably) not perfect in interpreting all feasible strings, but it seems to get most cases just fine.  This parser can easily understand any of the strings that may be output by the class. 

	//   ScripturNum::newFromString($string)
	$n = ScripturNum::newFromString("1 Corinthians 13");
    var_dump($n);
    echo $n;

Produces the output:

	object(ScripturNum\ScripturNum)
      protected 'int' => int 756212026
      protected 'book' => int 46
      protected 'startCh' => int 13
      protected 'startV' => int 1
      protected 'endCh' => int 13
      protected 'endV' => int 13
    1 Corinthians 13
	
	
### Starting with Completely Numerical References

If you've already parsed a string input and have broken apart the numerical portions, you may want to get a ScripturNum as follows.  The example below is for Matthew 8-9. (Matthew is the 40th book.)

This would probably be most useful if you have any interest in using a language other than English, as only English book names are currently understood. 

	//   ScripturNum::newFromInts($book, $startCh, $startV = 1, $endCh = null, $endV = null)
	$n = ScripturNum::newFromInts(40, 8, null, 9);
    var_dump($n);
    echo $n;

Produces the output:
 
	object(ScripturNum\ScripturNum)
	  protected 'int' => int 655134992
	  protected 'book' => int 40
	  protected 'startCh' => int 8
	  protected 'startV' => int 1
	  protected 'endCh' => int 9
	  protected 'endV' => int 38
	Matthew 8-9
	
### Starting with Book Name and otherwise Numerical References

Perhaps you don't want to take the time to figure out the index of a book.  Ok.  Provide the book name instead to `newFromParsed`.

	//   ScripturNum::newFromParsed($bookStr, $startCh = null, $startV = 1, $endCh = null, $endV = null)
	$n = ScripturNum::newFromParsed('Matthew', 8, null, 9);
    var_dump($n);
    echo $n;

Produces the output (same as the previous example):
 
	object(ScripturNum\ScripturNum)
	  protected 'int' => int 655134992
	  protected 'book' => int 40
	  protected 'startCh' => int 8
	  protected 'startV' => int 1
	  protected 'endCh' => int 9
	  protected 'endV' => int 38
	Matthew 8-9
	
### Getting the Full Reference
The Full Reference of the passage can be returned by either casting a ScripturNum object to a string, or by using the `getLongString` function.  The result is identical.  For example:

	echo ScripturNum::newFromString('3Jo11')->getLongString();
	
Produces the output:

	3 John 11
	
Note that the identical result can also be attained with:

	echo ScripturNum::newFromString('3Jo11')
	
Full references always use the numerical ordinal (2, as opposed to II or Second), and use the *first* name given for each book in the array of names in the Bible class.
	
### Getting an Abbreviated Reference

Abbreviated references are also available through the `getAbbrev` function.  These are intended to be readily passable as part of a URL, without having to escape or encode any of the characters.  Thus, they use a period in place of a colon. 

	echo ScripturNum::newFromString('1 Corinthians 15:50-58')->getAbbrev();
	
Produces the output:

	1Co15.50-58

Abbreviations always use the numerical ordinal (2, as opposed to II or Second), and use the *second* name given for each book in the array of names in the Bible class.


## Restrictions, Limitations and Cautions
There are a few (only a few) restrictions imposed within this library, most of which have to do with just how little can really be crammed into 32 bits.  Specifically:

1.  Ranges can only be contained within one book.  So, "John 2-3" is fine.  "2 John - 3 John" is not. 
2.  Behavior is undefined when a start reference comes after the end reference.  In most cases, exceptions will be thrown to prevent you from doing this, because "Ephesians 2:10-1" doesn't really make sense.  This reversal is discouraged, as it makes database searching dramatically more difficult, as database searching would probably be based on bitmasking. 
3.  The Canon and versification *cannot* change while values are stored in a database, except in some fairly narrow cases.
4.  Beware of similarly-named books, which may not quite render what you intended when abbreviated.  For instance, does 'Ju' refer to Judges or Jude?  We say Jude.  'Jg' refers to Judges.  Generally, we will adopt any interpretations used by [esvbible.org](http://esvbible.org), and these should be considered subject to change.
5. 	Like you, this package was designed to be perfect.  However, brokenness is real, and sanctification is ongoing.  Check [the Issues page](https://github.com/jkrrv/ScripturNum/issues) for the things we're currently struggling to kill, and let us know if you find other faults or failures. 
	

## A Note on Canon

The author of this library is protestant, and holds to a closed canon consisting of the 66 books [listed as western protestantism here](https://en.wikipedia.org/wiki/Biblical_canon).  Pull requests and bugs requesting anything outside this canon will be marked as [heresy](https://github.com/jkrrv/ScripturNum/labels/Heresy%21) and closed.

From a technical perspective, having a closed cannon is *vital* because verses and books are numbered sequentially.  Should insertions or omissions be made, numbering would be askew everywhere thenceforth.  Also, since the aim of this library is to allow concise storage of references in a database where, presumably, they would not be modified often, maintaining consistent results is always imperative. 