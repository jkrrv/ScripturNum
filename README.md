Scripturnum
===========

Like a nasturtium, but tastier. 

This is a PHP Library intended to allow the storage of references to scripture in only 32 bits (31, actually), which allows a scripture range to be stored in a single unsigned integer (num... get it?) in most DBs.  

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
 
 
## Usage

### Starting with the Numerical ScrupturNum

Starting with a number is the easy thing.  Take, for instance, 738197728, which represents Romans 1-8.  

	$s = new ScripturNum(738197728);

    var_dump($s);
   
    echo $s;
    
Produces the output:

	object(ScripturNum\ScripturNum)[1]
	  protected 'int' => int 738197728
	  protected 'book' => int 45
	  protected 'startCh' => int 1
	  protected 'startV' => int 1
	  protected 'endCh' => int 8
	  protected 'endV' => int 39
	  
	Romans 1-8
	
	
### Starting with a Reference

If you've already parsed a string input so that you can number-ify the information, you can get a ScripturNum as follows for Matthew 8-9. (Matthew is the 40th book.)

	//   ScripturNum::newFromInts($book, $startCh, $startV = 1, $endCh = null, $endV = null)
	$n = ScripturNum::newFromInts(40, 8, null, 9);
    
    var_dump($n);
    
    echo $n;

Produces the output:
 
	object(ScripturNum\ScripturNum)[2]
	  protected 'int' => int 655134992
	  protected 'book' => int 40
	  protected 'startCh' => int 8
	  protected 'startV' => int 1
	  protected 'endCh' => int 9
	  protected 'endV' => int 38
	Matthew 8-9


## Restrictions and Limitations
There are a few (only a few) restrictions imposed within this library, most of which have to do with just how little can really be crammed into 32 bits.  Specifically:

1.  Ranges can only be contained within one book.
2.  While the format theoretically allows for start and end to be switched, errors will be thrown to prevent it because "Ephesians 2:10-1" doesn't really make much sense.  Also, doing so would significantly complicate querying in databases where bitmasking could be used otherwise. 
3.  The Canon and versification (at least in terms of the number of verses in each chapter) *cannot* change while values are stored.
	

## A Note on Canon

The author of this library is protestant, and holds to a closed canon consisting of the 66 books [listed as western protestantism here](https://en.wikipedia.org/wiki/Biblical_canon).  Pull requests and bugs requesting anything outside this canon will be marked as [heresy](https://github.com/jkrrv/ScripturNum/labels/Heresy%21) and closed.

From a technical perspective, having a closed cannon is *vital* because verses and books are numbered sequentially.  Should insertions or omissions be made, numbering would be askew everywhere thenceforth.  Also, since the aim of this library is to allow concise storage of references in a database where, presumably, they would not be modified often, maintaining consistent results is always imperative. 