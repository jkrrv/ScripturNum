<?php

namespace ScripturNumTests;

use PHPUnit\Framework\TestCase;
use ScripturNum\ScripturNum;
use ScripturNum\ScripturNumArray;
use ScripturNum\ScripturNumException;

/**
 * Exhaustive tests of ScripturNum::parseString(): every combination of its options, in each of several languages,
 * and the tests of its predecessors (extractFromString() and stringToInts()), re-expressed in terms of parseString().
 *
 * Passages come back in a ScripturNumArray, which keeps them in the order of the Bible.  The expectations here are
 * written in that order.
 */
class ScripturNumParseStringMatrixTests extends TestCase
{
	const NONE_IGNORE     = 'none/ignore';
	const NONE_REPORT     = 'none/report';
	const UNCOMMON_IGNORE = 'uncommon/ignore';
	const UNCOMMON_REPORT = 'uncommon/report';
	const ALL_IGNORE      = 'all/ignore';
	const ALL_REPORT      = 'all/report';

	/**
	 * The option values for each of the combinations.
	 */
	protected static $combos = [
		self::NONE_IGNORE     => [ScripturNum::BARE_BOOKS_NONE,     ScripturNum::UNREADABLE_IGNORE],
		self::NONE_REPORT     => [ScripturNum::BARE_BOOKS_NONE,     ScripturNum::UNREADABLE_REPORT],
		self::UNCOMMON_IGNORE => [ScripturNum::BARE_BOOKS_UNCOMMON, ScripturNum::UNREADABLE_IGNORE],
		self::UNCOMMON_REPORT => [ScripturNum::BARE_BOOKS_UNCOMMON, ScripturNum::UNREADABLE_REPORT],
		self::ALL_IGNORE      => [ScripturNum::BARE_BOOKS_ALL,      ScripturNum::UNREADABLE_IGNORE],
		self::ALL_REPORT      => [ScripturNum::BARE_BOOKS_ALL,      ScripturNum::UNREADABLE_REPORT],
	];

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
	 * Parses the input in one combination of options, collecting exceptions.
	 *
	 * @return array [passages as strings, inputs of the exceptions]
	 */
	protected static function parseCombo(string $class, string $input, string $combo): array
	{
		$ex = [];
		$r = $class::parseString($input, self::$combos[$combo][0], self::$combos[$combo][1], $ex);
		return [self::strings($r), self::inputs($ex)];
	}

	/**
	 * Checks every combination of options for every input, both when collecting exceptions and when having them
	 * thrown.
	 *
	 * @param string $class
	 * @param array  $cases  input => [combo => [expected passages, expected inputs of exceptions]]
	 */
	protected function checkMatrix(string $class, array $cases)
	{
		foreach ($cases as $input => $expectations) {
			$input = (string)$input;
			$this->assertSame(array_keys(self::$combos), array_keys($expectations), "Every combination is covered for: $input");

			foreach ($expectations as $combo => $expected) {
				$label = "$combo for: $input";

				// Collecting exceptions.
				$this->assertSame($expected, self::parseCombo($class, $input, $combo), "Collected, $label");

				// Not collecting exceptions: the first is thrown, or else the same passages are returned.
				$opts = self::$combos[$combo];
				try {
					$r = $class::parseString($input, $opts[0], $opts[1]);
					$this->assertSame([], $expected[1], "Nothing was thrown, $label");
					$this->assertSame($expected[0], self::strings($r), "Returned, $label");
				} catch (ScripturNumException $e) {
					$this->assertNotSame([], $expected[1], "Something was thrown, $label");
					$this->assertSame($expected[1][0], $e->getInput(), "Thrown, $label");
				}
			}
		}
	}

	/**
	 * Shorthand for a case in which the result is the same in all of the combinations.
	 */
	protected static function same(array $passages, array $inputs = []): array
	{
		$r = [];
		foreach (self::$combos as $combo => $_) {
			$r[$combo] = [$passages, $inputs];
		}
		return $r;
	}

	// ---- The matrix: English ----

	public function test_matrix_english()
	{
		$this->checkMatrix(ScripturNum::class, [

			// A plain reference is read in every combination.
			'John 3:16' => self::same(['John 3:16']),

			// An uncommon book name on its own.
			'Genesis' => [
				self::NONE_IGNORE     => [[], []],
				self::NONE_REPORT     => [[], ['Genesis']],
				self::UNCOMMON_IGNORE => [['Genesis'], []],
				self::UNCOMMON_REPORT => [['Genesis'], []],
				self::ALL_IGNORE      => [['Genesis'], []],
				self::ALL_REPORT      => [['Genesis'], []],
			],

			// A common book name on its own.
			'Mark' => [
				self::NONE_IGNORE     => [[], []],
				self::NONE_REPORT     => [[], ['Mark']],
				self::UNCOMMON_IGNORE => [[], []],
				self::UNCOMMON_REPORT => [[], ['Mark']],
				self::ALL_IGNORE      => [['Mark'], []],
				self::ALL_REPORT      => [['Mark'], []],
			],

			// A book name that doesn't exist.
			'Hezekiah 1' => [
				self::NONE_IGNORE     => [[], []],
				self::NONE_REPORT     => [[], ['Hezekiah 1']],
				self::UNCOMMON_IGNORE => [[], []],
				self::UNCOMMON_REPORT => [[], ['Hezekiah 1']],
				self::ALL_IGNORE      => [[], []],
				self::ALL_REPORT      => [[], ['Hezekiah 1']],
			],

			// A reference that is found, but isn't valid, is reported even when unreadable text is ignored.
			'John 3:99' => self::same([], ['John 3:99']),

			// Several items, some of which are bare book names, and some of which aren't books.
			'Genesis; Mark 3; Hezekiah' => [
				self::NONE_IGNORE     => [['Mark 3'], []],
				self::NONE_REPORT     => [['Mark 3'], ['Genesis', 'Hezekiah']],
				self::UNCOMMON_IGNORE => [['Genesis', 'Mark 3'], []],
				self::UNCOMMON_REPORT => [['Genesis', 'Mark 3'], ['Hezekiah']],
				self::ALL_IGNORE      => [['Genesis', 'Mark 3'], []],
				self::ALL_REPORT      => [['Genesis', 'Mark 3'], ['Hezekiah']],
			],

			// Prose around book names.
			'sermons on Mark and Genesis' => [
				self::NONE_IGNORE     => [[], []],
				self::NONE_REPORT     => [[], ['sermons on Mark', 'Genesis']],
				self::UNCOMMON_IGNORE => [['Genesis'], []],
				self::UNCOMMON_REPORT => [['Genesis'], ['sermons on Mark']],
				self::ALL_IGNORE      => [['Genesis', 'Mark'], []],
				self::ALL_REPORT      => [['Genesis', 'Mark'], ['sermons on']],
			],

			// Unreadable text and an invalid reference between valid references.
			'Romans 8; Fred; John 3:99' => [
				self::NONE_IGNORE     => [['Romans 8'], ['John 3:99']],
				self::NONE_REPORT     => [['Romans 8'], ['Fred', 'John 3:99']],
				self::UNCOMMON_IGNORE => [['Romans 8'], ['John 3:99']],
				self::UNCOMMON_REPORT => [['Romans 8'], ['Fred', 'John 3:99']],
				self::ALL_IGNORE      => [['Romans 8'], ['John 3:99']],
				self::ALL_REPORT      => [['Romans 8'], ['Fred', 'John 3:99']],
			],

			// A bare common name after a reference.
			'Genesis 1; Mark' => [
				self::NONE_IGNORE     => [['Genesis 1'], []],
				self::NONE_REPORT     => [['Genesis 1'], ['Mark']],
				self::UNCOMMON_IGNORE => [['Genesis 1'], []],
				self::UNCOMMON_REPORT => [['Genesis 1'], ['Mark']],
				self::ALL_IGNORE      => [['Genesis 1', 'Mark'], []],
				self::ALL_REPORT      => [['Genesis 1', 'Mark'], []],
			],

			// A common name that is ordinary prose.
			'mark my words' => [
				self::NONE_IGNORE     => [[], []],
				self::NONE_REPORT     => [[], ['mark my words']],
				self::UNCOMMON_IGNORE => [[], []],
				self::UNCOMMON_REPORT => [[], ['mark my words']],
				self::ALL_IGNORE      => [['Mark'], []],
				self::ALL_REPORT      => [['Mark'], ['my words']],
			],

			// Nothing at all.
			'' => self::same([]),
			'   ' => self::same([]),
			';' => self::same([]),
			" ,; \n ;, " => self::same([]),
		]);
	}

	// ---- The matrix: a made-up language, with different words ----

	public function test_matrix_otherLanguage()
	{
		$this->checkMatrix(TestScripturNum::class, [

			'Ioannes 3:16' => self::same(['Ioannes 3:16']),

			// Ioannes is not a common word in this language.
			'Ioannes' => [
				self::NONE_IGNORE     => [[], []],
				self::NONE_REPORT     => [[], ['Ioannes']],
				self::UNCOMMON_IGNORE => [['Ioannes'], []],
				self::UNCOMMON_REPORT => [['Ioannes'], []],
				self::ALL_IGNORE      => [['Ioannes'], []],
				self::ALL_REPORT      => [['Ioannes'], []],
			],

			// Marcus is.
			'Marcus' => [
				self::NONE_IGNORE     => [[], []],
				self::NONE_REPORT     => [[], ['Marcus']],
				self::UNCOMMON_IGNORE => [[], []],
				self::UNCOMMON_REPORT => [[], ['Marcus']],
				self::ALL_IGNORE      => [['Marcus'], []],
				self::ALL_REPORT      => [['Marcus'], []],
			],

			'Ioannes 3:99' => self::same([], ['Ioannes 3:99']),

			'Ioannes; Marcus 3; Hezekiah' => [
				self::NONE_IGNORE     => [['Marcus 3'], []],
				self::NONE_REPORT     => [['Marcus 3'], ['Ioannes', 'Hezekiah']],
				self::UNCOMMON_IGNORE => [['Marcus 3', 'Ioannes'], []],
				self::UNCOMMON_REPORT => [['Marcus 3', 'Ioannes'], ['Hezekiah']],
				self::ALL_IGNORE      => [['Marcus 3', 'Ioannes'], []],
				self::ALL_REPORT      => [['Marcus 3', 'Ioannes'], ['Hezekiah']],
			],

			// "et" is this language's "and".
			'sermo Marcus et Ioannes' => [
				self::NONE_IGNORE     => [[], []],
				self::NONE_REPORT     => [[], ['sermo Marcus', 'Ioannes']],
				self::UNCOMMON_IGNORE => [['Ioannes'], []],
				self::UNCOMMON_REPORT => [['Ioannes'], ['sermo Marcus']],
				self::ALL_IGNORE      => [['Marcus', 'Ioannes'], []],
				self::ALL_REPORT      => [['Marcus', 'Ioannes'], ['sermo']],
			],

			'Romani 8; Fred; Ioannes 3:99' => [
				self::NONE_IGNORE     => [['Romani 8'], ['Ioannes 3:99']],
				self::NONE_REPORT     => [['Romani 8'], ['Fred', 'Ioannes 3:99']],
				self::UNCOMMON_IGNORE => [['Romani 8'], ['Ioannes 3:99']],
				self::UNCOMMON_REPORT => [['Romani 8'], ['Fred', 'Ioannes 3:99']],
				self::ALL_IGNORE      => [['Romani 8'], ['Ioannes 3:99']],
				self::ALL_REPORT      => [['Romani 8'], ['Fred', 'Ioannes 3:99']],
			],

			// "and" is not a conjunction in this language, so it's just unreadable text.
			'Ioannes 3:16 and Romani 8' => [
				self::NONE_IGNORE     => [['Ioannes 3:16', 'Romani 8'], []],
				self::NONE_REPORT     => [['Ioannes 3:16', 'Romani 8'], ['and']],
				self::UNCOMMON_IGNORE => [['Ioannes 3:16', 'Romani 8'], []],
				self::UNCOMMON_REPORT => [['Ioannes 3:16', 'Romani 8'], ['and']],
				self::ALL_IGNORE      => [['Ioannes 3:16', 'Romani 8'], []],
				self::ALL_REPORT      => [['Ioannes 3:16', 'Romani 8'], ['and']],
			],

			// "caput" and "usque" are this language's "chapter" and "through".
			'Ioannes caput 3 usque 4' => self::same(['Ioannes 3-4']),

			// The English names are not recognized where they have been replaced.
			'John 3' => [
				self::NONE_IGNORE     => [[], []],
				self::NONE_REPORT     => [[], ['John 3']],
				self::UNCOMMON_IGNORE => [[], []],
				self::UNCOMMON_REPORT => [[], ['John 3']],
				self::ALL_IGNORE      => [[], []],
				self::ALL_REPORT      => [[], ['John 3']],
			],
		]);
	}

	// ---- The matrix: a language with no common terms, no word for chapter, and symbols for conjunctions ----

	public function test_matrix_otherLanguage_noCommonTerms()
	{
		$this->checkMatrix(TestBareScripturNum::class, [

			'John 3:16 + Romans 8' => self::same(['John 3:16', 'Romans 8']),

			'John 3 ~ 4' => self::same(['John 3-4']),

			'John 3 and 4' => [
				// "and" is not a conjunction in this language.
				self::NONE_IGNORE     => [['John 3'], []],
				self::NONE_REPORT     => [['John 3'], ['and 4']],
				self::UNCOMMON_IGNORE => [['John 3'], []],
				self::UNCOMMON_REPORT => [['John 3'], ['and 4']],
				self::ALL_IGNORE      => [['John 3'], []],
				self::ALL_REPORT      => [['John 3'], ['and 4']],
			],

			// With no common terms, every name may stand alone unless no bare names are wanted.
			'Mark' => [
				self::NONE_IGNORE     => [[], []],
				self::NONE_REPORT     => [[], ['Mark']],
				self::UNCOMMON_IGNORE => [['Mark'], []],
				self::UNCOMMON_REPORT => [['Mark'], []],
				self::ALL_IGNORE      => [['Mark'], []],
				self::ALL_REPORT      => [['Mark'], []],
			],

			'mark my words' => [
				self::NONE_IGNORE     => [[], []],
				self::NONE_REPORT     => [[], ['mark my words']],
				self::UNCOMMON_IGNORE => [['Mark'], []],
				self::UNCOMMON_REPORT => [['Mark'], ['my words']],
				self::ALL_IGNORE      => [['Mark'], []],
				self::ALL_REPORT      => [['Mark'], ['my words']],
			],

			'Hezekiah 1' => [
				self::NONE_IGNORE     => [[], []],
				self::NONE_REPORT     => [[], ['Hezekiah 1']],
				self::UNCOMMON_IGNORE => [[], []],
				self::UNCOMMON_REPORT => [[], ['Hezekiah 1']],
				self::ALL_IGNORE      => [[], []],
				self::ALL_REPORT      => [[], ['Hezekiah 1']],
			],
		]);
	}

	// ---- The tests of extractFromString(), re-expressed in terms of parseString() ----

	/**
	 * Checks that prose is read the same way whether bare book names are never, or only uncommonly, matched (and
	 * since this is prose with no exceptions to collect, whatever is done with unreadable text).  Matching every book
	 * name is noisier, so for that the expected passages need only be among those found.
	 */
	protected function assertProse(array $expected, string $prose)
	{
		foreach (self::$combos as $combo => $opts) {
			if ($opts[0] === ScripturNum::BARE_BOOKS_ALL) {
				continue;
			}
			$ex = [];
			$r = ScripturNum::parseString($prose, $opts[0], ScripturNum::UNREADABLE_IGNORE, $ex);
			$this->assertSame($expected, self::strings($r), "$combo for: $prose");
			$this->assertSame([], $ex, "Exceptions in $combo for: $prose");
		}
		$r = ScripturNum::parseString($prose, ScripturNum::BARE_BOOKS_ALL, ScripturNum::UNREADABLE_IGNORE);
		foreach ($expected as $e) {
			$this->assertContains($e, self::strings($r), "all/ignore for: $prose");
		}
	}

	public function test_prose_chapter()
	{
		$this->assertProse(['Leviticus 10'], "let's turn in our bibles to Leviticus chapter 10 and verse 3.");
	}

	public function test_prose_amp_chapters()
	{
		$this->assertProse(['Romans 8-9', 'Romans 16'], "See Romans 8-9 & 16 for more information");
	}

	public function test_prose_amp_verses()
	{
		$this->assertProse(['Romans 8:9', 'Romans 8:16'], "See Romans 8:9 & 16");
	}

	public function test_prose_amp_dual()
	{
		$this->assertProse(['Romans 8:1-9:10', 'Romans 15:1-16:9'], "See Romans 8:1-9:10 & 15:1-16:9");
	}

	public function test_prose_words_chaptersOnRight()
	{
		$this->assertProse(['Romans 8', 'Romans 15-16'], "See Romans 8 and 15 through 16");
	}

	public function test_prose_words_versesOnRight()
	{
		$this->assertProse(['Romans 8:1', 'Romans 8:15-16'], "See Romans 8:1 and 15 through 16");
	}

	public function test_prose_arrayCombining()
	{
		$this->assertProse(['Genesis 1-3'], "Genesis 1, 2, and 3 are essential for understanding the gospel.");
	}

	public function test_prose_checkJohns()
	{
		$r = ScripturNum::parseString("1 Jn 1:9 and Jn 1:10 are thematically related.");
		$this->assertCount(2, $r);
		$this->assertFalse($r[0]->overlapsWith($r[1]));
		$this->assertSame(['John 1:10', '1 John 1:9'], self::strings($r));
	}

	public function test_prose_commonNames()
	{
		$text = "Consider Matthew Henry's commentaries.";
		$this->assertSame([], self::strings(ScripturNum::parseString($text, ScripturNum::BARE_BOOKS_NONE)));
		$this->assertSame([], self::strings(ScripturNum::parseString($text, ScripturNum::BARE_BOOKS_UNCOMMON)));
		$this->assertSame([], self::strings(ScripturNum::parseString($text)));
		$this->assertContains('Matthew', self::strings(ScripturNum::parseString($text, ScripturNum::BARE_BOOKS_ALL)));
	}

	public function test_prose_bookName()
	{
		$text = "We're going to start a series on first John this fall.";
		$this->assertSame(['1 John'], self::strings(ScripturNum::parseString($text)));
		$this->assertSame(['1 John'], self::strings(ScripturNum::parseString($text, ScripturNum::BARE_BOOKS_UNCOMMON)));
		$this->assertContains('1 John', self::strings(ScripturNum::parseString($text, ScripturNum::BARE_BOOKS_ALL)));
	}

	public function test_prose_bookNameExcluded()
	{
		$text = "We're going to start a series on first John this fall.";
		$this->assertSame([], self::strings(ScripturNum::parseString($text, ScripturNum::BARE_BOOKS_NONE)));
	}

	// ---- The tests of stringToInts(), re-expressed in terms of parseString() ----

	public function test_list_outOfBounds_thrown()
	{
		foreach (self::$combos as $combo => $opts) {
			try {
				ScripturNum::parseString('Genesis 110, 120', $opts[0], $opts[1]);
				$this->fail("Expected an exception in $combo.");
			} catch (ScripturNumException $e) {
				$this->assertSame('A chapter was requested that does not exist within the requested book.', $e->getMessage(), $combo);
			}
		}
	}

	public function test_list_outOfBounds_collected()
	{
		foreach (self::$combos as $combo => $opts) {
			$ex = [];
			$r = ScripturNum::parseString('Genesis 110, 120', $opts[0], $opts[1], $ex);
			$this->assertCount(0, $r, $combo);
			$this->assertCount(2, $ex, $combo);
			$this->assertSame(ScripturNumException::class, get_class($ex[0]), $combo);
			$this->assertSame(ScripturNumException::class, get_class($ex[1]), $combo);
		}
	}

	public function test_list_invalidBook_thrownWhenReported()
	{
		try {
			ScripturNum::parseString('Asdf 2', ScripturNum::BARE_BOOKS_ALL, ScripturNum::UNREADABLE_REPORT);
			$this->fail('Expected an exception.');
		} catch (ScripturNumException $e) {
			$this->assertSame('Text is not a scripture reference.', $e->getMessage());
			$this->assertSame('Asdf 2', $e->getInput());
		}
	}

	public function test_list_invalidBook_collectedWhenReported()
	{
		$ex = [];
		$r = ScripturNum::parseString('Asdf 2', ScripturNum::BARE_BOOKS_ALL, ScripturNum::UNREADABLE_REPORT, $ex);
		$this->assertCount(0, $r);
		$this->assertCount(1, $ex);
		$this->assertSame(ScripturNumException::class, get_class($ex[0]));
	}

	public function test_list_invalidBook_ignored()
	{
		$ex = [];
		$r = ScripturNum::parseString('Asdf 2', ScripturNum::BARE_BOOKS_ALL, ScripturNum::UNREADABLE_IGNORE, $ex);
		$this->assertCount(0, $r);
		$this->assertCount(0, $ex);
		$this->assertCount(0, ScripturNum::parseString('Asdf 2'));
	}

	public function test_list_separators()
	{
		$cases = [
			// a trailing separator never stands for the whole book
			'John 3:16;'            => ['John 3:16'],
			'Exodus 2;'             => ['Exodus 2'],
			'John 3:16;;'           => ['John 3:16'],
			'John 3:16; '           => ['John 3:16'],
			'John 3:16, '           => ['John 3:16'],
			'John 3:16;,; '         => ['John 3:16'],
			'John 3:16;;4:2'        => ['John 3:16', 'John 4:2'],
			// every level of separators
			'John 3:16'             => ['John 3:16'],
			'John 3:16, 18'         => ['John 3:16', 'John 3:18'],
			'John 3:16; 4:2'        => ['John 3:16', 'John 4:2'],
			'John 3:16, 18; 4:2, 5' => ['John 3:16', 'John 3:18', 'John 4:2', 'John 4:5'],
			'John 3, 5'             => ['John 3', 'John 5'],
			'John 3:1-4, 6; 5'      => ['John 3:1-4', 'John 3:6', 'John 5'],
		];
		foreach (self::$combos as $combo => $opts) {
			foreach ($cases as $input => $expected) {
				$ex = [];
				$r = ScripturNum::parseString((string)$input, $opts[0], $opts[1], $ex);
				$this->assertSame($expected, self::strings($r), "$combo for: $input");
				$this->assertSame([], $ex, "Exceptions in $combo for: $input");
			}
		}
	}

	public function test_list_bareBookWithSeparators()
	{
		foreach (['John', 'John;', 'John ;', 'John,', 'John;;', ' John '] as $input) {
			$ex = [];
			$r = ScripturNum::parseString($input, ScripturNum::BARE_BOOKS_ALL, ScripturNum::UNREADABLE_REPORT, $ex);
			$this->assertSame(['John'], self::strings($r), "For: '$input'");
			$this->assertSame([], $ex, "Exceptions for: '$input'");
		}
	}

	public function test_list_invalidReference_inputIsWholeSpan()
	{
		foreach (self::$combos as $combo => $opts) {
			$ex = [];
			$r = ScripturNum::parseString('John 3:16; 3:99', $opts[0], $opts[1], $ex);
			$this->assertSame(['John 3:16'], self::strings($r), $combo);
			$this->assertSame(['John 3:16; 3:99'], self::inputs($ex), $combo);
		}
	}

	public function test_list_noExceptionsWhenValid()
	{
		$ex = [];
		ScripturNum::parseString('John 3:16', ScripturNum::BARE_BOOKS_ALL, ScripturNum::UNREADABLE_REPORT, $ex);
		$this->assertCount(0, $ex);
	}

	// ---- How exceptions are delivered ----

	public function test_exceptions_arePassedInAnArrayThatAlreadyHasItems()
	{
		$ex = ['existing'];
		ScripturNum::parseString('Hezekiah 1', ScripturNum::BARE_BOOKS_ALL, ScripturNum::UNREADABLE_REPORT, $ex);
		$this->assertCount(2, $ex);
		$this->assertSame('existing', $ex[0]);
		$this->assertSame('Hezekiah 1', $ex[1]->getInput());
	}

	public function test_exceptions_areThrownIfNotAnArray()
	{
		foreach ([null, false] as $notAnArray) {
			try {
				ScripturNum::parseString('Hezekiah 1', ScripturNum::BARE_BOOKS_ALL, ScripturNum::UNREADABLE_REPORT, $notAnArray);
				$this->fail('Expected an exception.');
			} catch (ScripturNumException $e) {
				$this->assertSame('Hezekiah 1', $e->getInput());
			}
		}
	}

	public function test_exceptions_firstOneThrownIsTheFirstInTheText()
	{
		foreach (['Fred; John 3:99', 'John 3:99; Fred'] as $input) {
			$ex = [];
			ScripturNum::parseString($input, ScripturNum::BARE_BOOKS_ALL, ScripturNum::UNREADABLE_REPORT, $ex);
			try {
				ScripturNum::parseString($input, ScripturNum::BARE_BOOKS_ALL, ScripturNum::UNREADABLE_REPORT);
				$this->fail('Expected an exception.');
			} catch (ScripturNumException $e) {
				$this->assertSame($ex[0]->getInput(), $e->getInput(), $input);
			}
		}
	}

	public function test_exceptions_notCollectedWhenNothingIsWrong()
	{
		$ex = [];
		ScripturNum::parseString('Romans 8', ScripturNum::BARE_BOOKS_NONE, ScripturNum::UNREADABLE_IGNORE, $ex);
		ScripturNum::parseString('Hello there', ScripturNum::BARE_BOOKS_ALL, ScripturNum::UNREADABLE_IGNORE, $ex);
		$this->assertSame([], $ex);
	}

	// ---- The options ----

	public function test_options_everyValidValueIsAccepted()
	{
		foreach ([ScripturNum::BARE_BOOKS_NONE, ScripturNum::BARE_BOOKS_UNCOMMON, ScripturNum::BARE_BOOKS_ALL] as $b) {
			foreach ([ScripturNum::UNREADABLE_IGNORE, ScripturNum::UNREADABLE_REPORT] as $u) {
				$this->assertCount(1, ScripturNum::parseString('John 3:16', $b, $u));
			}
		}
	}

	public function test_options_invalidValuesAreRejected()
	{
		$bad = ['', 'ALL', 'All', 'sometimes', ' all', 'false'];
		foreach ($bad as $value) {
			try {
				ScripturNum::parseString('John 3:16', $value);
				$this->fail("Expected an exception for bareBookNames: '$value'");
			} catch (\InvalidArgumentException $e) {
				$this->assertStringContainsString('bareBookNames', $e->getMessage());
			}
			try {
				ScripturNum::parseString('John 3:16', ScripturNum::BARE_BOOKS_ALL, $value);
				$this->fail("Expected an exception for unreadableText: '$value'");
			} catch (\InvalidArgumentException $e) {
				$this->assertStringContainsString('unreadableText', $e->getMessage());
			}
		}
	}

	public function test_options_constantsAreDistinct()
	{
		$all = [
			ScripturNum::BARE_BOOKS_NONE, ScripturNum::BARE_BOOKS_UNCOMMON, ScripturNum::BARE_BOOKS_ALL,
			ScripturNum::UNREADABLE_IGNORE, ScripturNum::UNREADABLE_REPORT,
		];
		$this->assertSame($all, array_values(array_unique($all)));
	}

	public function test_options_invalidBareBookNamesIsCheckedBeforeInvalidUnreadableText()
	{
		try {
			ScripturNum::parseString('John 3:16', 'x', 'y');
			$this->fail('Expected an exception.');
		} catch (\InvalidArgumentException $e) {
			$this->assertStringContainsString('bareBookNames', $e->getMessage());
		}
	}

	// ---- The deprecated methods give the same results, as their docblocks say ----

	public function test_extractFromString_isParseStringWithTheEquivalentOptions()
	{
		$texts = [
			'John 3:16', 'Genesis', 'Mark', 'Hezekiah 1', 'John 3:99', 'Genesis; Mark 3; Hezekiah',
			'sermons on Mark and Genesis', 'Romans 8; Fred; John 3:99', 'Genesis 1; Mark', 'mark my words', '',
			"We're going to start a series on first John this fall.",
		];
		foreach ($texts as $text) {
			foreach ([false, true] as $exclude) {
				$bare = $exclude ? ScripturNum::BARE_BOOKS_NONE : ScripturNum::BARE_BOOKS_UNCOMMON;

				$exOld = [];
				$exNew = [];
				$old = ScripturNum::extractFromString($text, $exclude, $exOld);
				$new = ScripturNum::parseString($text, $bare, ScripturNum::UNREADABLE_IGNORE, $exNew);

				$this->assertSame(self::strings($new), self::strings($old), "Passages, exclude=$exclude, for: $text");
				$this->assertSame(self::inputs($exNew), self::inputs($exOld), "Exceptions, exclude=$exclude, for: $text");
			}
		}
	}

	public function test_extractFromString_isParseStringInAnotherLanguage()
	{
		foreach (['sermo Marcus et Ioannes', 'Ioannes 3:16 et Romani 8', 'Marcus 3'] as $text) {
			$this->assertSame(
				self::strings(TestScripturNum::parseString($text)),
				self::strings(TestScripturNum::extractFromString($text)),
				$text);
			$this->assertInstanceOf(TestScripturNum::class, TestScripturNum::extractFromString($text)[0]);
		}
	}

	public function test_stringToInts_isParseStringWithTheEquivalentOptions()
	{
		// Inputs for which stringToInts() keeps passages in the order written, and has none to combine.
		$texts = ['John 3:16', 'John 3:16, 18', 'John 3:16; 4:2', 'Genesis 110, 120', 'John 3:16; 3:99', 'John 3, 5', 'John'];
		foreach ($texts as $text) {
			$exOld = [];
			$exNew = [];
			$old = ScripturNum::stringToInts($text, $exOld);
			$new = ScripturNum::parseString($text, ScripturNum::BARE_BOOKS_ALL, ScripturNum::UNREADABLE_REPORT, $exNew);

			$ints = [];
			foreach ($new as $n) {
				$ints[] = $n->getInt();
			}
			$this->assertSame($old, $ints, "Ints for: $text");
			$this->assertSame(self::inputs($exOld), self::inputs($exNew), "Exceptions for: $text");
		}
	}

	public function test_stringToInts_differsFromParseStringInTheWaysTheDocblockSays()
	{
		// Adjacent passages are combined by parseString, and not by stringToInts.
		$this->assertCount(2, ScripturNum::stringToInts('John 3:16, 17'));
		$this->assertCount(1, ScripturNum::parseString('John 3:16, 17', ScripturNum::BARE_BOOKS_ALL, ScripturNum::UNREADABLE_REPORT));

		// More than one book is read by parseString, and not by stringToInts.
		$ex = [];
		ScripturNum::stringToInts('John 3:16; Romans 8', $ex);
		$this->assertNotEmpty($ex);
		$ex = [];
		$r = ScripturNum::parseString('John 3:16; Romans 8', ScripturNum::BARE_BOOKS_ALL, ScripturNum::UNREADABLE_REPORT, $ex);
		$this->assertCount(2, $r);
		$this->assertEmpty($ex);
	}

	// ---- Other behaviors ----

	public function test_returnsTheClassItWasCalledOn()
	{
		$cases = [
			ScripturNum::class         => 'John 3:16',
			TestScripturNum::class     => 'Ioannes 3:16',
			TestBareScripturNum::class => 'John 3:16',
		];
		foreach ($cases as $class => $input) {
			$r = $class::parseString($input);
			$this->assertInstanceOf(ScripturNumArray::class, $r);
			$this->assertCount(1, $r);
			$this->assertSame($class, get_class($r[0]));
		}
	}

	public function test_textThatIsNotUtf8()
	{
		// The conjunctions can't be recognized in bytes that aren't valid UTF-8, but references still can be, and what
		// can't be read is still reported with its original bytes.
		$ex = [];
		$r = ScripturNum::parseString("Romans 8; \xFF\xFE; Acts 2:38", ScripturNum::BARE_BOOKS_ALL, ScripturNum::UNREADABLE_REPORT, $ex);
		$this->assertSame(['Acts 2:38', 'Romans 8'], self::strings($r));
		$this->assertSame(["\xFF\xFE"], self::inputs($ex));
	}

	public function test_multibyteText()
	{
		// Multibyte characters before, between and after the references shouldn't throw off where anything is found.
		$ex = [];
		$r = ScripturNum::parseString("\u{00E9}\u{00E9} Romans 8\u{2013}9 \u{2014} \u{00FC}ber Acts 2:38 \u{00E9}", ScripturNum::BARE_BOOKS_ALL, ScripturNum::UNREADABLE_REPORT, $ex);
		$this->assertSame(['Acts 2:38', 'Romans 8-9'], self::strings($r));
		$this->assertSame(["\u{00E9}\u{00E9}", "\u{2014} \u{00FC}ber", "\u{00E9}"], self::inputs($ex));
	}

	public function test_conjunctionsMustStandAlone()
	{
		// "and" within a word is not a conjunction, and "chapter" within a word is not a word for chapter.
		$ex = [];
		ScripturNum::parseString('Romans 8; Candle 1; Sandy; Chapters', ScripturNum::BARE_BOOKS_ALL, ScripturNum::UNREADABLE_REPORT, $ex);
		$this->assertSame(['Candle 1', 'Sandy', 'Chapters'], self::inputs($ex));
	}

	public function test_conjunctionsAreNotCaseSensitive()
	{
		$ex = [];
		$r = ScripturNum::parseString('John 3:16 AND Romans 8 And Acts 2', ScripturNum::BARE_BOOKS_ALL, ScripturNum::UNREADABLE_REPORT, $ex);
		$this->assertSame(['John 3:16', 'Acts 2', 'Romans 8'], self::strings($r));
		$this->assertSame([], $ex);
	}

	public function test_bookNamesAreNotCaseSensitive()
	{
		$r = ScripturNum::parseString('JOHN 3:16, rOmAnS 8');
		$this->assertSame(['John 3:16', 'Romans 8'], self::strings($r));
	}

	public function test_bookNamesWithPeriods()
	{
		$ex = [];
		$r = ScripturNum::parseString('Gen. 1:1; Rom. 8:28', ScripturNum::BARE_BOOKS_ALL, ScripturNum::UNREADABLE_REPORT, $ex);
		$this->assertSame(['Genesis 1:1', 'Romans 8:28'], self::strings($r));
		$this->assertSame([], $ex);
	}

	public function test_dashesAndTheirCousins()
	{
		foreach (['Romans 8-9', "Romans 8\u{2013}9", 'Romans 8&ndash;9', 'Romans 8&endash;9', 'Romans 8 through 9', 'Romans 8 THROUGH 9'] as $input) {
			$ex = [];
			$r = ScripturNum::parseString($input, ScripturNum::BARE_BOOKS_ALL, ScripturNum::UNREADABLE_REPORT, $ex);
			$this->assertSame(['Romans 8-9'], self::strings($r), "For: $input");
			$this->assertSame([], $ex, "Exceptions for: $input");
		}
	}

	public function test_aBookNameInsideAnotherWordIsNotFound()
	{
		$r = ScripturNum::parseString('The Realm of Texas', ScripturNum::BARE_BOOKS_ALL);
		$this->assertCount(0, $r);
	}

	public function test_aNameThatIsPartOfALongerMatchIsNotAlsoReturned()
	{
		foreach ([ScripturNum::BARE_BOOKS_UNCOMMON, ScripturNum::BARE_BOOKS_ALL] as $mode) {
			$r = ScripturNum::parseString('1 John 2:3', $mode);
			$this->assertSame(['1 John 2:3'], self::strings($r), $mode);
		}
	}

	public function test_numberedBooks()
	{
		foreach (['1 John 3:16', '1John 3:16', 'First John 3:16', 'I John 3:16', '1 Jn 3:16'] as $input) {
			$this->assertSame(['1 John 3:16'], self::strings(ScripturNum::parseString($input)), $input);
		}
	}

	public function test_aLongTextWithManyReferences()
	{
		$text = str_repeat('Turn to Genesis 1:1 and Exodus 2 or maybe Leviticus 3:4-5; ', 50);
		$r = ScripturNum::parseString($text);
		$this->assertSame(['Genesis 1:1', 'Exodus 2', 'Leviticus 3:4-5'], self::strings($r));
	}

	public function test_bookNamesOfSeveralWords()
	{
		$this->assertSame(['Song of Solomon 2:1'], self::strings(ScripturNum::parseString('Song of Solomon 2:1')));
		$this->assertSame(['Song of Solomon 2:1'], self::strings(ScripturNum::parseString('Song of Songs 2:1')));
	}
}
