<?php

namespace ScripturNumTests;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use ScripturNum\ScripturNum;
use ScripturNum\ScripturNumArray;
use ScripturNum\ScripturNumException;

/**
 * Tests of ScripturNum::parseString().
 *
 * Passages come back in a ScripturNumArray, which keeps them in the order of the Bible.  The expectations here are
 * written in that order.
 */
class ScripturNumParseStringTests extends TestCase
{
	/**
	 * @param ScripturNumArray $a
	 *
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
	 * @param ScripturNumException[] $ex
	 *
	 * @return string[]
	 */
	protected static function inputs(array $ex): array
	{
		return array_map(function (ScripturNumException $e) {
			return $e->getInput();
		}, $ex);
	}

	/**
	 * Reads text that is known to be a list of references.
	 *
	 * @param string $class  The ScripturNum class to use, which determines the language.
	 */
	protected static function readList(string $string, &$ex, string $class = ScripturNum::class): ScripturNumArray
	{
		$ex = [];
		return $class::parseString($string, ScripturNum::BARE_BOOKS_ALL, ScripturNum::UNREADABLE_REPORT, $ex);
	}

	// ---- Lists of references (the examples from issue #16) ----

	// The tests directly of an issue are named test_issueNN_..., after the issue.

	public function test_issue16_list_cleanInputs()
	{
		$cases = [
			'John 3:16'                    => ['John 3:16'],
			'John 3:16;'                   => ['John 3:16'],
			';John 3:16'                   => ['John 3:16'],
			'John 3:16;;Romans 8'          => ['John 3:16', 'Romans 8'],
			'Gen 1; Ex 2; '                => ['Genesis 1', 'Exodus 2'],
			'John 3:16; Romans 8'          => ['John 3:16', 'Romans 8'],
			'John 3:16; 4:2'               => ['John 3:16', 'John 4:2'],
			'John 3:16, 18'                => ['John 3:16', 'John 3:18'],
			'John 3:16 and Romans 8'       => ['John 3:16', 'Romans 8'],
			"John 3:16\nRomans 8:1"        => ['John 3:16', 'Romans 8:1'],
			'John'                         => ['John'],
			'Mark'                         => ['Mark'],
			'Mark 3; John'                 => ['Mark 3', 'John'],
		];
		foreach ($cases as $input => $expected) {
			$r = self::readList($input, $ex);
			$this->assertSame($expected, self::strings($r), "Passages for: $input");
			$this->assertSame([], $ex, "Exceptions for: $input");
		}
	}

	public function test_issue16_list_empty()
	{
		foreach (['', '   ', ';', " \n ; \n"] as $input) {
			$r = self::readList($input, $ex);
			$this->assertCount(0, $r, "Passages for: '$input'");
			$this->assertSame([], $ex, "Exceptions for: '$input'");
		}
	}

	public function test_issue16_list_unreadableItemIsReported()
	{
		$r = self::readList('Romans 8; Hezekiah 1', $ex);
		$this->assertSame(['Romans 8'], self::strings($r));
		$this->assertCount(1, $ex);
		$this->assertInstanceOf(ScripturNumException::class, $ex[0]);
		$this->assertSame('Hezekiah 1', $ex[0]->getInput());
	}

	public function test_issue16_list_unreadableItemInTheMiddle()
	{
		$r = self::readList('Romans 8; Hezekiah 1; Acts 2:38', $ex);
		$this->assertSame(['Acts 2:38', 'Romans 8'], self::strings($r));
		$this->assertSame(['Hezekiah 1'], self::inputs($ex));
	}

	public function test_issue16_list_unreadableItemAtTheStart()
	{
		$r = self::readList('Hezekiah 1, Romans 8', $ex);
		$this->assertSame(['Romans 8'], self::strings($r));
		$this->assertSame(['Hezekiah 1'], self::inputs($ex));
	}

	public function test_issue16_list_severalUnreadableItems()
	{
		$r = self::readList('Hezekiah 1; Romans 8; Fred; Acts 2', $ex);
		$this->assertSame(['Acts 2', 'Romans 8'], self::strings($r));
		$this->assertSame(['Hezekiah 1', 'Fred'], self::inputs($ex));
	}

	public function test_issue16_list_invalidReference()
	{
		$r = self::readList('John 3:99', $ex);
		$this->assertCount(0, $r);
		$this->assertSame(['John 3:99'], self::inputs($ex));
	}

	public function test_issue16_list_invalidReferenceNextToValidOnes()
	{
		$r = self::readList('Romans 8; John 3:99; Acts 2:38', $ex);
		$this->assertSame(['Acts 2:38', 'Romans 8'], self::strings($r));
		$this->assertSame(['John 3:99'], self::inputs($ex));
	}

	public function test_issue16_list_firstExceptionIsThrownWithoutAnArray()
	{
		try {
			ScripturNum::parseString('Romans 8; Hezekiah 1', ScripturNum::BARE_BOOKS_ALL, ScripturNum::UNREADABLE_REPORT);
			$this->fail('Expected an exception.');
		} catch (ScripturNumException $e) {
			$this->assertSame('Hezekiah 1', $e->getInput());
		}
	}

	public function test_issue16_list_inputKeepsOriginalText()
	{
		// The words in the unreadable text shouldn't be altered by the way that conjunctions are handled.
		self::readList('Romans 8; Candle 1', $ex);
		$this->assertSame(['Candle 1'], self::inputs($ex));
	}

	public function test_issue16_list_aConjunctionSeparatesUnreadableItems()
	{
		self::readList('Romans 8; Candle and Hezekiah 1', $ex);
		$this->assertSame(['Candle', 'Hezekiah 1'], self::inputs($ex));
	}

	public function test_issue16_list_lineBreaksSeparateItems()
	{
		foreach (["\n", "\r\n", "\r"] as $break) {
			self::readList("Fred{$break}Hezekiah 1{$break}Romans 8{$break}Barney{$break}", $ex);
			$this->assertSame(['Fred', 'Hezekiah 1', 'Barney'], self::inputs($ex), 'Line break: ' . json_encode($break));
		}
	}

	public function test_issue16_list_unreadableItemsAreReportedSeparately()
	{
		self::readList("Fred; Hezekiah 1, Sandy
Romans 8 & Barney", $ex);
		$this->assertSame(['Fred', 'Hezekiah 1', 'Sandy', 'Barney'], self::inputs($ex));
	}

	public function test_issue16_list_bareBookNamesNone_reportsBareBooks()
	{
		$ex = [];
		$r = ScripturNum::parseString('Romans 8; John', ScripturNum::BARE_BOOKS_NONE, ScripturNum::UNREADABLE_REPORT, $ex);
		$this->assertSame(['Romans 8'], self::strings($r));
		$this->assertSame(['John'], self::inputs($ex));
	}

	// ---- The same, with different words ----

	public function test_issue16_list_otherLanguage_cleanInputs()
	{
		$cases = [
			'Ioannes 3:16'                => ['Ioannes 3:16'],
			'Ioannes 3:16;'               => ['Ioannes 3:16'],
			'Ioannes 3:16; Romani 8'      => ['Ioannes 3:16', 'Romani 8'],
			'Ioannes 3:16 et Romani 8'    => ['Ioannes 3:16', 'Romani 8'],
			'Ioannes 3:16; 4:2'           => ['Ioannes 3:16', 'Ioannes 4:2'],
			'Ioannes 3:16 et 18'          => ['Ioannes 3:16', 'Ioannes 3:18'],
			'Marcus 3 usque 4'            => ['Marcus 3-4'],
			'Ioannes caput 3'             => ['Ioannes 3'],
			'Ioannes'                     => ['Ioannes'],
			'Marcus'                      => ['Marcus'],
			'Marcus 3; Ioannes'           => ['Marcus 3', 'Ioannes'],
			"Ioannes 3:16\nRomani 8:1"    => ['Ioannes 3:16', 'Romani 8:1'],
		];
		foreach ($cases as $input => $expected) {
			$r = self::readList($input, $ex, TestScripturNum::class);
			$this->assertSame($expected, self::strings($r), "Passages for: $input");
			$this->assertSame([], $ex, "Exceptions for: $input");
		}
	}

	public function test_issue16_list_otherLanguage_unreadableItemIsReported()
	{
		$r = self::readList('Romani 8; Hezekiah 1; Actus 2:38', $ex, TestScripturNum::class);
		$this->assertSame(['Actus 2:38', 'Romani 8'], self::strings($r));
		$this->assertSame(['Hezekiah 1'], self::inputs($ex));
	}

	public function test_issue16_list_otherLanguage_englishNamesAndConjunctionsAreNotUsedWhereTheyWereReplaced()
	{
		// "John" was replaced by "Ioannes", and "and" by "et".
		$r = self::readList('John 3; Romani 8', $ex, TestScripturNum::class);
		$this->assertSame(['Romani 8'], self::strings($r));
		$this->assertSame(['John 3'], self::inputs($ex));

		$r = self::readList('Ioannes 3:16 and Romani 8', $ex, TestScripturNum::class);
		$this->assertSame(['Ioannes 3:16', 'Romani 8'], self::strings($r));
		$this->assertSame(['and'], self::inputs($ex));
	}

	public function test_issue16_list_otherLanguage_resultsAreOfTheClassUsed()
	{
		$r = self::readList('Ioannes 3:16', $ex, TestScripturNum::class);
		$this->assertInstanceOf(TestScripturNum::class, $r[0]);
	}

	public function test_issue20_list_combinedPassagesAreWrittenInTheLanguageTheyAreIn()
	{
		// Passages that are adjacent are combined, and the combined passage is still in the same language.
		$r = self::readList('Ioannes 3:1-5, 6-10', $ex, TestScripturNum::class);
		$this->assertSame(['Ioannes 3:1-10'], self::strings($r));
		$this->assertInstanceOf(TestScripturNum::class, $r[0]);
	}

	// ---- Prose (the defaults, and the options for bare book names) ----

	public function test_prose_ignoresTextThatIsNotAReference()
	{
		$r = ScripturNum::parseString('Please read John 3:16 and then Hezekiah 1 before Sunday, Fred.', ScripturNum::BARE_BOOKS_UNCOMMON, ScripturNum::UNREADABLE_IGNORE, $ex);
		$this->assertSame(['John 3:16'], self::strings($r));
		$this->assertNull($ex);
	}

	public function test_prose_defaultsDoNotThrowOnUnreadableText()
	{
		$r = ScripturNum::parseString('Romans 8; Hezekiah 1');
		$this->assertSame(['Romans 8'], self::strings($r));
	}

	public function test_issue19_prose_bareUncommonNameIsFoundByDefault()
	{
		$r = ScripturNum::parseString("We're going to start a series on first John this fall.");
		$this->assertSame(['1 John'], self::strings($r));
	}

	public function test_issue19_prose_commonWordIsNotFoundByDefault()
	{
		$this->assertCount(0, ScripturNum::parseString('mark my words'));
		$this->assertCount(0, ScripturNum::parseString('Joe Smith'));
		$this->assertCount(0, ScripturNum::parseString('We meet at 5 pm.'));
		$this->assertCount(0, ScripturNum::parseString('Am I late?'));
	}

	public function test_issue19_prose_commonWordAbbreviationsAreStillFoundWithANumber()
	{
		$this->assertSame(['Amos 3'], self::strings(ScripturNum::parseString('Read Am 3 today.')));
		$this->assertSame(['Philemon 4'], self::strings(ScripturNum::parseString('Read Pm 4 today.')));
	}

	public function test_issue19_prose_bareBookNamesNone()
	{
		$r = ScripturNum::parseString("We're going to start a series on first John this fall.", ScripturNum::BARE_BOOKS_NONE);
		$this->assertCount(0, $r);

		$r = ScripturNum::parseString('sermons on John 3:16', ScripturNum::BARE_BOOKS_NONE);
		$this->assertSame(['John 3:16'], self::strings($r));
	}

	public function test_issue19_prose_bareBookNamesAll()
	{
		$r = ScripturNum::parseString('mark my words', ScripturNum::BARE_BOOKS_ALL);
		$this->assertSame(['Mark'], self::strings($r));

		$r = ScripturNum::parseString('sermons on Mark', ScripturNum::BARE_BOOKS_ALL);
		$this->assertSame(['Mark'], self::strings($r));
	}

	public function test_issue19_prose_aNameInALongerMatchIsNotAlsoFoundOnItsOwn()
	{
		foreach ([ScripturNum::BARE_BOOKS_NONE, ScripturNum::BARE_BOOKS_UNCOMMON, ScripturNum::BARE_BOOKS_ALL] as $mode) {
			$r = ScripturNum::parseString('sermons on John 3:16', $mode);
			$this->assertSame(['John 3:16'], self::strings($r), "Mode: $mode");
		}
	}

	public function test_issue19_prose_otherLanguage()
	{
		$c = TestScripturNum::class;

		// Ioannes isn't a common word in the test language, but Marcus is.
		$this->assertSame(['Ioannes'], self::strings($c::parseString('sermo Ioannes')));
		$this->assertCount(0, $c::parseString('marcus my words'));
		$this->assertSame(['Marcus'], self::strings($c::parseString('marcus my words', ScripturNum::BARE_BOOKS_ALL)));
		$this->assertCount(0, $c::parseString('sermo Ioannes', ScripturNum::BARE_BOOKS_NONE));
		$this->assertSame(['Marcus 3-4'], self::strings($c::parseString('legimus Marcus 3 usque 4 hodie')));
	}

	// ---- Behaviors that are common to both ----

	public function test_maskingPreservesOffsetsOfMultibyteText()
	{
		// An en dash is three bytes, and shouldn't throw off where the unreadable text is found.
		$r = self::readList("Romans 8\u{2013}9; Hezekiah 1", $ex);
		$this->assertSame(['Romans 8-9'], self::strings($r));
		$this->assertSame(['Hezekiah 1'], self::inputs($ex));
	}

	public function test_invalidBareBookNamesOption()
	{
		$this->expectException(InvalidArgumentException::class);
		ScripturNum::parseString('John 3:16', 'sometimes');
	}

	public function test_invalidUnreadableTextOption()
	{
		$this->expectException(InvalidArgumentException::class);
		ScripturNum::parseString('John 3:16', ScripturNum::BARE_BOOKS_ALL, 'shrug');
	}

	public function test_defaultsMatchExtractFromString()
	{
		$texts = [
			"We're going to start a series on first John this fall.",
			'Genesis 1, 2, and 3 are essential for understanding the gospel.',
			'mark my words: Romans 8 and John 3:16-4:2.',
			'Nothing to see here.',
			'Psalm 23; Hebrews 11:1-3, 6',
		];
		foreach ($texts as $t) {
			$this->assertSame(
				(string)ScripturNum::extractFromString($t),
				(string)ScripturNum::parseString($t),
				"Text: $t");
			$this->assertSame(
				(string)ScripturNum::extractFromString($t, true),
				(string)ScripturNum::parseString($t, ScripturNum::BARE_BOOKS_NONE),
				"Text (excluding bare books): $t");
		}
	}
}
