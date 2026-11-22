<?php

namespace ScripturNumTests;

use PHPUnit\Framework\TestCase;
use ScripturNum\ScripturNum;
use ScripturNum\ScripturNumArray;

/**
 * A number that begins the name of a numbered book (1 Kings, 2 Corinthians, ...) belongs to that book, not to the
 * reference before it.  See issue #23.
 *
 * Passages come back in a ScripturNumArray, which keeps them in the order of the Bible, so the expectations are
 * written in that order.
 */
class ScripturNumNumberedBookTests extends TestCase
{
	/**
	 * @return string[]
	 */
	protected static function strings(ScripturNumArray $a): array
	{
		$r = [];
		foreach ($a as $s) {
			$r[] = $s->getLongString();
		}
		return $r;
	}

	/**
	 * Reads the text as prose and as a list (without exceptions), and by the deprecated extractFromString(), and checks
	 * that all give the expected passages.
	 *
	 * @param string[] $expected
	 */
	protected function assertReadsAs(array $expected, string $text, string $class = ScripturNum::class)
	{
		$this->assertSame($expected, self::strings($class::parseString($text)), "parseString (prose) for: $text");

		$ex = [];
		$list = $class::parseString($text, ScripturNum::BARE_BOOKS_ALL, ScripturNum::UNREADABLE_REPORT, $ex);
		$this->assertSame($expected, self::strings($list), "parseString (list) for: $text");
		$this->assertSame([], $ex, "parseString (list) exceptions for: $text");

		$this->assertSame($expected, self::strings($class::extractFromString($text)), "extractFromString for: $text");
	}

	// ---- The examples from the issue ----

	public function test_issue23_afterAChapterAndVerse()
	{
		$this->assertReadsAs(['1 Kings 1', 'John 3:16'], 'John 3:16 and 1 Kings 1');
	}

	public function test_issue23_afterASemicolon()
	{
		$this->assertReadsAs(['1 Kings 1', 'John 3:16'], 'John 3:16; 1 Kings 1');
	}

	public function test_issue23_afterAComma()
	{
		$this->assertReadsAs(['1 Kings 1', 'John 3:16'], 'John 3:16, 1 Kings 1');
	}

	public function test_issue23_secondCorinthians()
	{
		$this->assertReadsAs(['Romans 8', '2 Corinthians 5'], 'Romans 8 and 2 Corinthians 5');
	}

	public function test_issue23_oneNumberedBookAfterAnother()
	{
		$this->assertReadsAs(['1 Kings 1', '2 Kings 3'], '1 Kings 1 and 2 Kings 3');
	}

	public function test_issue23_unaffected_unnumberedBookAfterAReference()
	{
		$this->assertReadsAs(['Genesis 1', 'John 3:16'], 'John 3:16 and Genesis 1');
	}

	// ---- Other ways to write it ----

	public function test_issue23_namesWithoutSpacesAndAbbreviations()
	{
		$this->assertReadsAs(['1 Kings 1', 'John 3:16'], 'John 3:16, 1Kings 1');
		$this->assertReadsAs(['1 Kings 1', 'John 3:16'], 'John 3:16, 1 Ki 1');
		$this->assertReadsAs(['1 Kings 1', 'John 3:16'], 'John 3:16, 1Ki1');
		$this->assertReadsAs(['Romans 8', '2 Corinthians 5'], 'Romans 8, 2Co 5');
	}

	public function test_issue23_aWholeNumberedBook()
	{
		$this->assertReadsAs(['Romans 8', '2 Corinthians'], 'Romans 8; 2 Corinthians');
		$this->assertReadsAs(['1 Kings', 'John 3:16'], 'John 3:16; 1 Kings');
	}

	public function test_issue23_withVersesAndRanges()
	{
		$this->assertReadsAs(['1 Kings 1:2-4', 'John 3:16-18'], 'John 3:16-18; 1 Kings 1:2-4');
		$this->assertReadsAs(['1 Kings 1:2-4', 'John 3:16-18'], 'John 3:16-18 and 1 Kings 1:2-4');
	}

	public function test_issue23_severalInARow()
	{
		// as prose (in list mode, "Read" and "today." would rightly be reported as unreadable)
		$this->assertSame(
			['1 Kings 1:1', 'John 3:16', '1 John 1:9'],
			self::strings(ScripturNum::parseString('Read John 3:16; 1 John 1:9 and 1 Kings 1:1 today.')));
		$this->assertReadsAs(
			['1 Kings 1:1', 'John 3:16', '1 John 1:9'],
			'John 3:16; 1 John 1:9 and 1 Kings 1:1');
		$this->assertReadsAs(
			['1 Corinthians 13', '2 Corinthians 5', '1 John 4'],
			'1 Corinthians 13, 2 Corinthians 5, 1 John 4');
	}

	public function test_issue23_inEveryBareBookMode()
	{
		foreach ([ScripturNum::BARE_BOOKS_NONE, ScripturNum::BARE_BOOKS_UNCOMMON, ScripturNum::BARE_BOOKS_ALL] as $mode) {
			$this->assertSame(['1 Kings 1', 'John 3:16'], self::strings(ScripturNum::parseString('John 3:16 and 1 Kings 1', $mode)), $mode);
		}
	}

	// ---- Numbers that are not the beginning of a book's name still belong to the reference before ----

	public function test_issue23_unchanged_listsOfVerses()
	{
		$this->assertReadsAs(['John 3:16', 'John 3:18'], 'John 3:16, 18');
		$this->assertReadsAs(['John 3:16', 'John 3:18', 'John 3:20'], 'John 3:16, 18, 20');
		$this->assertReadsAs(['Romans 8', 'Romans 15-16'], 'Romans 8 and 15 through 16');
		$this->assertReadsAs(['Romans 8:1', 'Romans 8:15-16'], 'Romans 8:1 and 15 through 16');
	}

	public function test_issue23_unchanged_chaptersBeforeAnUnnumberedBook()
	{
		// The 2 is followed by a comma, and then the name of a book that has no number, so it is a chapter of Mark.
		$this->assertReadsAs(['Mark 1-2', 'John 1'], 'Mark 1, 2, John 1');
		$this->assertReadsAs(['Mark 1-2', 'John 1'], 'Mark 1, 2 and John 1');
		$this->assertReadsAs(['Mark 1-2', 'John 1'], 'Mark 1; 2; John 1');
	}

	public function test_issue23_chaptersBeforeANumberedBook_theNumberIsTheBooks()
	{
		// Without the comma, the 2 begins the name of "2 John", so it isn't a chapter of Mark.  (And, as 2 John has only one
		// chapter, the 1 after it is a verse.  See issue #24.)
		$this->assertReadsAs(['Mark 1', '2 John 1'], 'Mark 1, 2 John 1');
	}

	public function test_issue23_unchanged_aNumberThatBeginsAnotherWord()
	{
		// "1 Kingsley" isn't "1 Kings", so the 1 is still a verse.
		$this->assertSame(['John 3:1', 'John 3:16'], self::strings(ScripturNum::parseString('John 3:16 and 1 Kingsley')));
	}

	public function test_issue23_unchanged_aNumberAfterTheBookOfAList()
	{
		$this->assertReadsAs(['John 1', 'John 3'], 'John 1, 3');
	}

	// ---- In another language ----

	public function test_issue23_otherLanguage()
	{
		$c = TestSpanishScripturNum::class;
		$this->assertReadsAs(['1 Reyes 1', 'Juan 3:16'], 'Juan 3:16 y 1 Reyes 1', $c);
		$this->assertReadsAs(['1 Reyes 1', 'Juan 3:16'], 'Juan 3:16 y 1Rey1', $c);
		$this->assertReadsAs(['1 Reyes 1', '2 Reyes 3'], '1 Reyes 1 y 2 Reyes 3', $c);
		$this->assertReadsAs(['1 Reyes 1', '2 Reyes 3'], '1Rey1 y 2Rey3', $c);
		$this->assertReadsAs(['1 Reyes 1', '2 Reyes 3'], "1 Reyes 1 y\n2 Reyes 3", $c);
		$this->assertReadsAs(['Juan 3:16', 'Juan 3:18'], 'Juan 3:16 y 18', $c);
	}

	public function test_issue23_otherLanguage_namesOfThatLanguageOnly()
	{
		// "1 Kings" is not a name in this language, so the 1 is still a verse.
		$this->assertSame(['Juan 3:1', 'Juan 3:16'], self::strings(TestSpanishScripturNum::parseString('Juan 3:16 y 1 Kings 1')));
	}

	public function test_issue23_aLanguageWithNoNumberedBooks()
	{
		// With no names that begin with a digit, there is nothing for a number to be part of.
		$this->assertSame(['John 3:1', 'John 3:16'], self::strings(TestNoNumbersScripturNum::parseString('John 3:16, 1')));
	}
}
