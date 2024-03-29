ScripturNum
===========

[![Unit Tests](https://github.com/jkrrv/ScripturNum/actions/workflows/Unit_Tests.yml/badge.svg)](https://github.com/jkrrv/ScripturNum/actions/workflows/Unit_Tests.yml)
[![Coverage Status](https://coveralls.io/repos/github/jkrrv/ScripturNum/badge.svg)](https://coveralls.io/github/jkrrv/ScripturNum)

This is a PHP Library intended for working with scripture references. It can parse text-based scripture references, and
can express passages of scripture in only 32 bits (31, actually), which allows a scripture range to be stored in a
single unsigned integer in most DBs.

It handles all the weird exceptions with how we talk about scripture, including:

- Books with only a single chapter won't list the chapter.  (e.g. 3 John 11)
- Psalm is singular and Psalms is plural. (e.g. Psalm 23 and Psalms 101-102)  Same for Song(s) of Solomon. This behavior
  can be changed with the 'plurl' option. (The misspelling is deliberate--all options are five letters.)

## Installation

Use Composer. Just run:

    composer require jkrrv/scripturnum
    composer install --no-dev

Or, download the source files from GitHub. The code comes ready-to-run; there is no particular 'build' procedure.

## Prerequisites

- PHP >7.0

A few other packages are needed if you want to run the tests, but those will easily install with Composer.

## Restrictions, Limitations and Cautions

There are a few (only a few) restrictions imposed within this library, most of which have to do with just how little can
really be crammed into 32 bits. Specifically:

1. Ranges can only be contained within one book. So, "John 2-3" is fine.  "2 John - 3 John" is not.
2. Behavior is undefined when a start reference comes after the end reference. In most cases, exceptions will be thrown
   to prevent you from doing this, because "Ephesians 2:10-1" doesn't really make sense. This reversal is discouraged,
   as it makes database searching dramatically more difficult, as database searching would probably be based on
   bitmasking.
3. The Canon and versification *cannot* change while values are stored in a database, except in some fairly narrow
   cases.
4. Beware of similarly-named books, which may not quite render what you intended when abbreviated. For instance, does '
   Ju' refer to Judges or Jude? We say Jude.  'Jg' refers to Judges.

This library has 100% test coverage, and issues are usually dealt with expediently. If you encounter a bug,
please [report it to the issue tracker](https://github.com/jkrrv/ScripturNum/issues).

## A Note on Canon

From a technical perspective, having a closed cannon is *vital* because verses and books are numbered sequentially.
Should insertions or omissions be made, numbering would be askew for the rest of that book.

The author of this library is protestant, and holds to a closed canon consisting of the 66
books [listed as western protestantism here](https://en.wikipedia.org/wiki/Biblical_canon#Western_Church). Changing the versification of scripture would be disastrous for any 
integer ScripturNums that are saved.

## Usage

For full API documentation, [see the wiki](https://github.com/jkrrv/ScripturNum/wiki).

The two most common ways of referring to a passage of scripture are, probably, human-readable and as stored in a
database. The constructor takes both of these forms.

For instance:

    $s = new ScripturNum('Romans 1-8');
    var_dump($s);
    echo $s;

Produces the result:

    object(ScripturNum\ScripturNum)
      protected 'int' => int 738197728
      protected 'book' => int 45
      protected 'startCh' => int 1
      protected 'startV' => int 1
      protected 'endCh' => int 8
      protected 'endV' => int 39
    Romans 1-8

Or, start with a number:

    $s = new ScripturNum(739119536);
    var_dump($s);
    echo $s;

Produces the output:

    object(ScripturNum\ScripturNum)
      protected 'int' => int 739119536
      protected 'book' => int 45
      protected 'startCh' => int 9
      protected 'startV' => int 1
      protected 'endCh' => int 16
      protected 'endV' => int 27
    Romans 9-16

### Starting with Other Numerical References

If you've already parsed a string input and have broken apart the numerical portions, you may need a different means.

(Matthew is the 40th book.)

    //   ScripturNum::newFromInts($book, $startCh, $startV = 1, $endCh = null, $endV = null)
    $n = ScripturNum::newFromInts(40, 8, null, 9);
    var_dump($n);
    echo $n;

Produces the output:

    object(ScripturNum\ScripturNum)[3]
      protected 'int' => int 655134992
      protected 'book' => int 40
      protected 'startCh' => int 8
      protected 'startV' => int 1
      protected 'endCh' => int 9
      protected 'endV' => int 38
    Matthew 8-9

### Getting Strings Out: Full Name

The object supports direct conversion to strings. Doing so will produce the same output as the `getLongString` function.
For example:

    echo new ScripturNum('1Jo1:9')

Produces the output:

    1 John 1:9

By default, full references use the numerical ordinal (2, as opposed to II or Second), and the *first* name given for
each book in the array of names in the Bible class.

### Getting Strings Out: An Abbreviation

Abbreviated references are available through the `getAbbrev` function. By default, these are intended for use in a URL.
Thus, they use a period in place of a colon to avoid the need for escaping.

	echo (new ScripturNum('John 3:16'))->getAbbrev();

Produces the output:

	Jn3.16

By default, abbreviations use the numerical ordinal (2, as opposed to II or Second), and the *second* name given for
each book in the array of names in the Bible class.

### Database Querying

If you save ScripturNum integers to a given column in a database, you can easily and (relatively) efficiently query them
using bitwise operators. For your convenience, two methods exist that generate conditions that can be added to a `WHERE`
clause: `toSqlExclusive`, and `toSqlInclusive`.  Exclusive will include only the results that are
entirely contained within the range.  Inclusive will, instead, include all results that overlap with the given range at all,
even if it isn't entirely contained within the range.

For example, for a column named "Scripture", we could use this:

    $s = new ScripturNum('Romans 8');
    $wheres = $s->toSqlInclusive('Scripture');

At this point, `$wheres` has a value of:

    ( (Scripture & 4278190080) = 738197504 AND (Scripture & 16773120) <= 917504 AND (Scripture & 4095) >= 186 )

So, we can insert this into a query:

    $queryString = "SELECT * FROM scriptureData WHERE $wheres";

`$queryString` now has the value:

    SELECT * FROM scriptureData WHERE ( (Scripture & 4278190080) = 738197504 AND (Scripture & 16773120) <= 917504 AND (Scripture & 4095) >= 186 )

This can easily be used in most flavors of SQL.  Two notes of caution:
- If you want to use prepared statements, beware that you won't be able to re-use prepared statements with different
    passages.
- Because you can't really prepare statements, beware of SQL injection.  In my application, I'm hard-coding the column
    name (or, at least attaching it to a const), which eliminates this risk.  But, that may not be the case for you.

## Sorting

In general, sorting by ScrupturNum integer value will result in the passages being ordered in the same sequential order
as in the Bible. Books take first priority, followed by start chapter, followed by start verse, followed by end
chapter, followed by end verse.

## Bitwise Operations

The ScripturNum integer is just sequential numbers, bit-shifted to their respective positions.  All individual values 
are zero-indexed.  Genesis is book 0.  Genesis 1:1 has the integer value of 0. 

- The Book index << 24
- Start reference << 12
- End reference << 0
