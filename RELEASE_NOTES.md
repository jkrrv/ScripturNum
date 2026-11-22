# Release Notes

All notable changes to ScripturNum, newest first.  The version numbers are meant to follow
[semantic versioning](https://semver.org/): a new major version may break compatibility, a minor version adds to the
API (and may deprecate things), and a patch version fixes bugs.

For what each method does, see the [documentation](https://github.com/jkrrv/ScripturNum/wiki).

---

## [2.3.1] (2026-11-22)

### Fixed

- **`parseString()` reads the short form that `getAbbrev()` writes.**  A `.` can stand where a `:` does, between a
  chapter and a verse, so `Jn3.16`, `Jn3.16-21`, `Ge1.1-2.3` and `John 3.16` are now read as John 3:16, John 3:16-21,
  Genesis 1:1-2:3 and John 3:16.  Before, only the chapter was read, and the rest was reported as unreadable.  Anything
  that `getAbbrev()` writes can be read back by `parseString()`, which `stringToInts()` (deprecated) could already do.
  The deprecated `extractFromString()` reads it too.  A period that ends a sentence is not a separator: a `.` counts
  only if a digit follows it right away.  (#24)

### Changed: how references are read

- **In a book of only one chapter** (Obadiah, Philemon, 2 John, 3 John and Jude), **a number that is alone is a
  verse.**  `Ob1` is Obadiah 1:1, and `Jude 1` is verse 1 of Jude, as `Jude 5` is verse 5.  Before, `Jude 1` was read
  as chapter 1, which is the whole book, so that the first verse could not be written and read back.  This applies
  wherever a string is read: the constructor, `stringToInts()`, and `parseString()`.  A book is still written, and
  read, as the whole book when it is only its name (`Jude`).  `Jude 1-5` and `Jude 1:1` are unchanged.  (One effect:
  `Mark 1, 2 John 1` is Mark 1 and 2 John 1; in 2.3.0 it was Mark 1 and all of 2 John.)  Integers that have already
  been stored are not affected.  (#24)

---

## [2.3.0] (2026-11-22)

This release replaces the two quite different string-reading methods, `stringToInts()` and `extractFromString()`,
with one method, `parseString()`, and fixes a number of problems in how references were read.  The old methods still
work, and are deprecated.

### Added

- **`ScripturNum::parseString()`**, which finds and reads the scripture references in a string, whether that string is
  prose with references in it, or text that is known to be a list of references (such as what a person types into an
  editing field).  Its options say which:
  - `$bareBookNames` says whether a book name with nothing after it counts as a reference to the whole book:
    `BARE_BOOKS_NONE` (never), `BARE_BOOKS_UNCOMMON` (unless the name is a common word; the default, and what
    `extractFromString()` always did), or `BARE_BOOKS_ALL` (always, even for `Mark`, `Job` or `Ruth`).
  - `$unreadableText` says what to do with text that isn't a reference: `UNREADABLE_IGNORE` (skip it; the default),
    or `UNREADABLE_REPORT` (report each item that can't be read, as an exception).
  - `$exceptions` works as it does in the older methods: pass an array to collect exceptions, and otherwise the first
    one is thrown.

  For a list of references, use `ScripturNum::parseString($text, ScripturNum::BARE_BOOKS_ALL,
  ScripturNum::UNREADABLE_REPORT, $exceptions)`.  Every reference that can be read is returned, and every item that
  can't is reported separately.  (#16, #19)
- **`ScripturNumException::getInput()`** (and `setInput()`, and an optional fourth constructor argument) gives the
  text that couldn't be read, so that a person can be told what wasn't understood.  Exceptions from `parseString()`
  and `stringToInts()` have it.  For a reference that is found but isn't valid (such as `John 3:99`), the input is
  the whole span that it was in.  (#16)
- **`ScripturNum::getWholeBook()`**, the counterpart of `getWholeChapters()`: it returns a passage for the whole book
  that a passage is in, or the same object if it is already a whole book.  (#18)

### Deprecated

These still work, and will be removed in 3.0.0.  Their documentation gives the equivalent call.

- **`ScripturNum::extractFromString($s, $excludeBookOnly, $exceptions)`**: use `parseString($s, BARE_BOOKS_UNCOMMON,
  UNREADABLE_IGNORE, $exceptions)`, or with `BARE_BOOKS_NONE` in place of `BARE_BOOKS_UNCOMMON` if
  `$excludeBookOnly` was true.
- **`ScripturNum::stringToInts($s, $exceptions)`**: use `parseString($s, BARE_BOOKS_ALL, UNREADABLE_REPORT,
  $exceptions)`.  The replacement returns a `ScripturNumArray` (call `getInt()` on each passage for the ints), puts
  the passages in Bible order and combines those that overlap or are adjacent, and also reads more than one book.

### Changed: how references are read

Some references are now read differently.  Integers that have already been stored are not affected.

- **Conjunctions are replaced only as whole words, and are not case-sensitive.**  A language's word for "and",
  "chapter" or "through" used to be replaced wherever it appeared, including inside other words.  In Spanish, where
  "and" is `y`, that made `1Rey1` and `1 Reyes 1` unreadable.  Now `Rey` and `Reyes` are left alone, `Juan 3:16 y 18`
  is read as John 3:16 and 3:18, and `John 3:16 AND 18` works as `and` does.  A letter or a digit next to the word
  means that it is part of a longer word, so `y` is not a conjunction in `3y4`.  (#22)
- **`&ndash;` is recognized as a dash.**  (The code listed `&endash;`, which isn't an HTML entity, and neither worked,
  because the `&` was replaced before the entity was seen.  Both are now recognized.)  An ampersand by itself is still
  a separator.
- **Other kinds of space** (such as a non-breaking space) are treated as spaces.
- **A number that begins the name of a numbered book belongs to that book.**  In `John 3:16 and 1 Kings 1`, the `1`
  used to be read as another verse of John, giving John 3:1 and 3:16 (and leaving "Kings 1" unread).  Now it is
  1 Kings 1 and John 3:16.  Which names count comes from the language's list of book names.  A number followed by
  something that isn't a book's name (`John 3:16 and 1 Kingsley`) still belongs to the reference before it.  Note that
  `Mark 1, 2 John 1` is read as Mark 1 and 2 John, while `Mark 1, 2, John 1` is Mark 1-2 and John 1.  (#23)
- **A trailing separator no longer adds a passage.**  `John 3:16;` used to be read as John 3:16 *and* the whole book
  of John, and `Exodus 2;` as Exodus 2 and all of Exodus.  A reference that is only separators after a book name
  (`John;`) is still the whole book.  (#16)
- **`Am` and `Pm` are on the list of common terms**, so that `5 pm` and `Am I late?` are no longer read as Philemon
  and Amos.  `Am 3` and `Pm 4` are still read as references.  If you provide your own list of common terms (as you
  should, if you provide your own book names), you may want to add the same.

### Fixed

- **"Songs of Solomon" is no longer written** when the whole book of Song of Solomon is.  It is a single book, with no
  plural, and is now written as "Song of Solomon" (which also applies to ranges of its chapters).  "Songs of Solomon"
  is still understood when reading.  Only "Psalm" is pluralized.  (#17)
- **Subclasses of `ScripturNum` are kept in a `ScripturNumArray`.**  The array's constructor kept an object only if its
  class was exactly `ScripturNum`, so a subclass came back as a plain `ScripturNum`, including in the callback of
  `toString()`.  Passages combined by the array, passages returned by `remove()`, and the passages from
  `getWholeBible()` are also now of the class they came from (or were called on), and written with that class's
  book names.  Strings and integers put into an array still become plain `ScripturNum` objects.  (#20)

### Development

- Tests run on PHP 7.0 through 8.5 and now include tests for every option of `parseString()` in several languages
  (made-up ones, since a language is a `Bible` subclass with its own names, conjunctions and common terms).  Tests
  of a particular issue are named `test_issueNN_...`.
- `phpunit.xml` no longer uses `executionOrder="depends"`, which PHPUnit 14 rejects.
- `composer.json` sets `config.audit.block-insecure` to false.  The documentation generator, which is only used to
  build the documentation and is not part of the library, depends on old Twig versions with known advisories, and
  current Composer refuses to install them otherwise.
- The workflows use `actions/checkout@v7`.  The documentation workflow no longer uses a deprecated third-party action
  to push.

---

## [2.2.0] (2026-10-08)

### Added

- **`toSqlExclusive3()` and `toSqlInclusive3()`**, which make the same conditions as `toSqlExclusive()` and
  `toSqlInclusive()` for a passage whose book, start and end are stored in three separate columns, which can be better
  indexed than bitwise operations on one column.

### Fixed

- **Revelation 22 has 21 verses, not 20.**  `Revelation 22:21` could not be written.  Integers stored for a passage
  that includes the last verse of Revelation (the whole book, or chapter 22) were made with the shorter count, so they
  are different from those made now.

### Development

- Documentation for the wiki is generated automatically, and the process was made more robust.  The Coveralls step no
  longer fails a build when Coveralls is down.

---

## [2.1.0] (2025-11-04)

### Added

- **Removing passages:** `ScripturNum::remove()` subtracts one passage from another and returns what is left, as a
  `ScripturNumArray`.  `ScripturNumArray::remove()` and `ScripturNumArray::removeAll()` subtract a passage, or all of
  another array's passages, from every passage in an array.
- **`ScripturNum::getWholeBible()`** returns every book of the Bible, as a `ScripturNumArray`.
- **`ScripturNumArray::toString()`** takes the same options as `ScripturNum::toString()`, and throws an exception if
  it can't write the passages.  `getString()` remains, as an alias that returns an empty string instead.
- **`ScripturNum::parseStringOptions()`** is public.

### Changed

- Improvements to the efficiency of the array's string methods.

---

## [2.0.5] (2025-10-27)

### Fixed

- `extractFromString()` skips a possible reference that turns out not to be valid, instead of throwing an exception,
  including when it is turning a reference into a passage.

---

## [2.0.4] (2025-10-27)

### Fixed

- `extractFromString()` no longer throws an exception for the candidates it finds that are not valid when no array is
  provided to collect exceptions, such as `John 2:18-27` within `1 John 2:18-27`.  (#15)

---

## [2.0.3] (2025-10-27)

### Fixed

- A deprecation notice in newer versions of PHP, from looking up an array with a `null` key when iterating a
  `ScripturNumArray`.
- Problems building the project.

---

## [2.0.2] (2025-10-27)

### Fixed

- `extractFromString()` returned a book and its numbered counterpart for the same text, such as `John 4:7-9` as well as
  `1 John 4:7-9` for "1 John 4:7-9".  Matches that begin inside an earlier match are left out.  (#14)
- A deprecation notice in PHP 8.4 for parameters that were nullable without being declared so (in `newFromInts()`).

### Development

- PHP 8.5 was added to the versions that are tested.  A build status badge was added to the README.

---

## [2.0.1] (2023-11-05)

### Fixed

- Deprecation notices in PHP 8.1 and later, including for the methods of `ScripturNumArray` that implement
  PHP's array interfaces, which have a `#[\ReturnTypeWillChange]` attribute until their return types can be declared
  (#11), and for decrementing a `null`.
- Chapter and verse numbers that are `null` are handled explicitly, because decrementing `null` has no effect in PHP
  and is going to change.

### Development

- Tests now fail on notices and deprecations in PHP 8.0 and later.  A test confirms that `1 Jn` and `Jn` aren't
  confused.  The README has a section on sorting.

---

## [2.0.0] (2023-09-19)

A new version of the API.  **It is not compatible with 1.x.**  PHP 7.0 or later is required (1.x required PHP 5.5).

### Added

- **`ScripturNumArray`**, a collection of passages.  It sorts them, combines those that overlap or are adjacent, can be
  iterated and counted, and can write them as a single string: `getString()` (and, later, `toString()`).  (#10)
- **Reading text:** `ScripturNum::stringToInts()` reads a reference with several parts (separated by commas and
  semicolons), and `ScripturNum::extractFromString()` finds references in any text.
- **Links:** the string options include a `callback`, which is given each reference and its passage, so that the
  string can be made into a link or otherwise formatted.  (#10)
- **Comparing and combining passages:** `isWithin()`, `overlapsWith()`, `overlapsOrAdjacent()`, `contains()` and
  `combineWith()`, each with a version that takes an integer.
- **Moving around:** `getWholeChapters()`, `getNextChapter()`, `getPrevChapter()` and `isSingleVerse()`.
- **Database queries:** `toSqlExclusive()` and `toSqlInclusive()` make conditions for a `WHERE` clause that find passages
  stored as integers that are entirely within a given passage, or that overlap with it at all.
- **Properties:** `book`, `startCh`, `startV`, `endCh` and `endV` can be read.
- **Languages:** the `Bible` class has `getCommonTerms()`, `getConjunctions()` and `pluralizeBookName()`, so that a
  subclass can supply the words that are used in another language.  It can check its own book names for duplicates
  (`validateBookNames()`).
- Psalm and Song of Solomon are singular or plural according to the passage, and this can be switched off with the
  `plurl` option.  (Song of Solomon was changed in 2.3.0.)
- Documentation is generated from the source and published to the wiki.  The tests run in GitHub Actions, with code
  coverage.

### Changed

- **Renamed methods:** `string2int()` is `stringToInt()`, `int2concats()` is `intToConcats()`, and
  `getStringWithSettings()` was replaced by `toString()`, which takes a set of settings or an array of options.
- Methods declare their parameter and return types.

---

## [1.0.2] (2018-05-26)

- Documentation of what the constructor throws, so that IDEs can follow it.

---

## [1.0.1] (2018-05-26)

### Changed

- Classes are autoloaded by PSR-4, and not PSR-0.
- Improvements to the documentation in the code.

---

## [1.0.0] (2018-02-24)

The first release.  A `ScripturNum` is a passage of scripture in a single integer, which can be made from an integer,
from a human-readable string, or from numbers for the book, chapters and verses.

### Added

- Writing a passage: `getLongString()`, `getAbbrev()` and settings for how the strings are made, which can be changed
  with `setStringSettings()`.  Books of only one chapter are written without the chapter (`3 John 11`).
- `getInt()` returns the integer.  (#5)
- Exceptions that can be caught as `ScripturNumException`.
- The `Bible` class, which holds the verse counts and the names of the books.  It can be extended, and a subclass of
  `ScripturNum` gets passages of its own class from `newFromInts()`.

### Fixed

- A single verse in a book of one chapter (`3Jo11`) was read as the verse and the rest of the chapter.  (#1)
- Numbers beyond the end of a book no longer cause PHP notices.  (#2)
- A range that ends before it starts (`Ps 1:3-2`) is rejected.  (#4)
- Jude was not quite long enough.

---

[2.3.1]: https://github.com/jkrrv/ScripturNum/compare/v2.3.0...v2.3.1
[2.3.0]: https://github.com/jkrrv/ScripturNum/compare/v2.2.0...v2.3.0
[2.2.0]: https://github.com/jkrrv/ScripturNum/compare/v2.1.0...v2.2.0
[2.1.0]: https://github.com/jkrrv/ScripturNum/compare/v2.0.5...v2.1.0
[2.0.5]: https://github.com/jkrrv/ScripturNum/compare/v2.0.4...v2.0.5
[2.0.4]: https://github.com/jkrrv/ScripturNum/compare/v2.0.3...v2.0.4
[2.0.3]: https://github.com/jkrrv/ScripturNum/compare/v2.0.2...v2.0.3
[2.0.2]: https://github.com/jkrrv/ScripturNum/compare/v2.0.1...v2.0.2
[2.0.1]: https://github.com/jkrrv/ScripturNum/compare/v2.0.0...v2.0.1
[2.0.0]: https://github.com/jkrrv/ScripturNum/compare/v1.0.2...v2.0.0
[1.0.2]: https://github.com/jkrrv/ScripturNum/compare/v1.0.1...v1.0.2
[1.0.1]: https://github.com/jkrrv/ScripturNum/compare/v1.0.0...v1.0.1
[1.0.0]: https://github.com/jkrrv/ScripturNum/releases/tag/v1.0.0
