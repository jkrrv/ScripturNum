<?php

namespace ScripturNumTests;

use PHPUnit\Framework\TestCase;
use ScripturNum\ScripturNum;
use ScripturNum\ScripturNumArray;

/**
 * A conjunction is a conjunction only when it stands alone as a word.  A short one (such as the Spanish "y") must not
 * be found inside other words, like the "Rey" and "Reyes" of 1 and 2 Kings.  See issue #22.
 *
 * Each of these is tested through parseString(), and through the methods it replaces.
 */
class ScripturNumConjunctionTests extends TestCase
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
	 * Reads the text in all of the ways that it can be read, and checks that the results are the same.
	 *
	 * @param string[] $expected
	 */
	protected function assertReadsAs(array $expected, string $text, string $class = TestSpanishScripturNum::class)
	{
		// as a list, with everything that can't be read reported
		$ex = [];
		$list = $class::parseString($text, ScripturNum::BARE_BOOKS_ALL, ScripturNum::UNREADABLE_REPORT, $ex);
		$this->assertSame($expected, self::strings($list), "parseString (list) for: $text");
		$this->assertSame([], $ex, "parseString (list) exceptions for: $text");

		// as prose
		$this->assertSame($expected, self::strings($class::parseString($text)), "parseString (prose) for: $text");

		// by extractFromString
		$this->assertSame($expected, self::strings($class::extractFromString($text)), "extractFromString for: $text");

		// by stringToInts, which reads only a single book
		$books = [];
		foreach ($list as $s) {
			$books[$s->book] = true;
		}
		if (count($books) > 1) {
			return;
		}
		$ex = [];
		$ints = $class::stringToInts($text, $ex);
		$this->assertSame([], $ex, "stringToInts exceptions for: $text");
		$fromInts = [];
		foreach ((new ScripturNumArray($ints)) as $s) {
			$fromInts[] = $s->getInt();
		}
		$fromObjects = [];
		foreach ($list as $s) {
			$fromObjects[] = $s->getInt();
		}
		$this->assertSame($fromObjects, $fromInts, "stringToInts for: $text");
	}

	// ---- The conjunction is inside a book's name ----

	public function test_issue22_conjunctionInsideABookName_noSpace()
	{
		$this->assertReadsAs(['1 Reyes 1'], '1Rey1');
		$this->assertReadsAs(['2 Reyes 3'], '2Rey3');
	}

	public function test_issue22_conjunctionInsideABookName_spaces()
	{
		$this->assertReadsAs(['1 Reyes 1'], '1 Reyes 1');
		$this->assertReadsAs(['1 Reyes 1'], '1 Rey 1');
		$this->assertReadsAs(['2 Reyes 3'], '2 Reyes 3');
	}

	public function test_issue22_conjunctionInsideABookName_withVerses()
	{
		$this->assertReadsAs(['1 Reyes 1:2-4'], '1 Reyes 1:2-4');
		$this->assertReadsAs(['1 Reyes 1:2-4'], '1Rey1:2-4');
	}

	public function test_issue22_conjunctionInsideABookName_aWholeBook()
	{
		$this->assertReadsAs(['1 Reyes'], '1 Reyes');
		$this->assertReadsAs(['2 Reyes'], '2Reyes');
	}

	// ---- The conjunction stands alone, with whatever may be around it ----

	public function test_issue22_conjunctionBetweenVerses()
	{
		foreach ([
			'Juan 3:16 y 18',
			'Juan 3:16,y 18',
			'Juan 3:16, y 18',
			"Juan 3:16 y\n18",
			"Juan 3:16\ny 18",
			"Juan 3:16\ty\t18",
			"Juan 3:16\u{00A0}y\u{00A0}18",
			'Juan 3:16 Y 18',
		] as $text) {
			$this->assertReadsAs(['Juan 3:16', 'Juan 3:18'], $text);
		}
	}

	public function test_issue22_conjunctionBetweenBooks()
	{
		foreach ([
			'Juan 3:16 y Romanos 8',
			'Juan 3:16 Y Romanos 8',
			'Juan 3:16,y Romanos 8',
			"Juan 3:16 y\nRomanos 8",
		] as $text) {
			$this->assertReadsAs(['Juan 3:16', 'Romanos 8'], $text);
		}
	}

	public function test_issue22_conjunctionBetweenBooks_oneOfWhichHasItInItsName()
	{
		$this->assertReadsAs(['1 Reyes 1', 'Juan 3:16'], '1 Reyes 1 y Juan 3:16');
		$this->assertReadsAs(['1 Reyes 1', 'Juan 3:16'], '1Rey1 y Juan 3:16');
		$this->assertReadsAs(['1 Reyes 1', '2 Reyes 3'], '1Rey1 y 2Rey3');
	}

	public function test_issue22_conjunctionBetweenChapters()
	{
		$this->assertReadsAs(['1 Reyes 1', '1 Reyes 3'], '1 Reyes 1 y 3');
		$this->assertReadsAs(['1 Reyes 1', '1 Reyes 3'], '1Rey1 y 3');
		$this->assertReadsAs(['1 Reyes 1', '1 Reyes 3'], '1Rey1,y 3');
	}

	public function test_issue22_aConjunctionWithDigitsNextToIt_isPartOfAWord()
	{
		// "y" next to digits isn't standing alone, so it can't be read as a conjunction.
		$ex = [];
		$r = TestSpanishScripturNum::parseString('Juan 3y4', ScripturNum::BARE_BOOKS_ALL, ScripturNum::UNREADABLE_REPORT, $ex);
		$this->assertSame(['Juan'], self::strings($r));
		$this->assertSame(['3y4'], array_map(function ($e) {
			return $e->getInput();
		}, $ex));
	}

	public function test_issue22_aConjunctionWithADigitOnOnlyOneSide_isPartOfAWord()
	{
		$input = function ($e) {
			return $e->getInput();
		};

		// A digit before it
		$ex = [];
		$r = TestSpanishScripturNum::parseString('Juan 3y 4', ScripturNum::BARE_BOOKS_ALL, ScripturNum::UNREADABLE_REPORT, $ex);
		$this->assertSame(['Juan'], self::strings($r));
		$this->assertSame(['3y 4'], array_map($input, $ex));

		// A digit after it
		$ex = [];
		$r = TestSpanishScripturNum::parseString('Juan 3 y4', ScripturNum::BARE_BOOKS_ALL, ScripturNum::UNREADABLE_REPORT, $ex);
		$this->assertSame(['Juan 3'], self::strings($r));
		$this->assertSame(['y4'], array_map($input, $ex));
	}

	// ---- The words for "chapter" and "through" are the same ----

	public function test_issue22_otherWordsMustAlsoStandAlone()
	{
		// "hasta" is "through", and "capitulo" is "chapter".
		$this->assertReadsAs(['Juan 3-4'], 'Juan 3 hasta 4');
		$this->assertReadsAs(['Juan 3'], 'Juan capitulo 3');
		$this->assertReadsAs(['Juan 3-4'], 'Juan capitulo 3 hasta 4');
		$this->assertReadsAs(['Juan 3-4'], 'Juan 3 HASTA 4');
	}

	// ---- English is unchanged ----

	public function test_issue22_english()
	{
		$this->assertReadsAs(['John 3:16', 'John 3:18'], 'John 3:16 and 18', ScripturNum::class);
		$this->assertReadsAs(['John 3:16', 'John 3:18'], 'John 3:16,and 18', ScripturNum::class);
		$this->assertReadsAs(['John 3:16', 'Romans 8'], 'John 3:16 and Romans 8', ScripturNum::class);
		$this->assertReadsAs(['John 3:16', 'John 3:18'], 'John 3:16 AND 18', ScripturNum::class);
		$this->assertReadsAs(['John 3-4'], 'John 3 through 4', ScripturNum::class);
	}

	public function test_issue22_englishWordsContainingAConjunction()
	{
		// None of these books has "and" in its name, but they show that "and" within a word isn't a conjunction.
		$ex = [];
		$r = ScripturNum::parseString('Sandy 1; Candle and Bandit', ScripturNum::BARE_BOOKS_ALL, ScripturNum::UNREADABLE_REPORT, $ex);
		$this->assertCount(0, $r);
		$this->assertSame(['Sandy 1', 'Candle', 'Bandit'], array_map(function ($e) {
			return $e->getInput();
		}, $ex));
	}

	// ---- The conjunction is as it is in the language's translation ----

	public function test_issue22_theLanguagesOwnConjunctionIsTheOnlyOneUsed()
	{
		// In Spanish, "and" isn't a conjunction.
		$ex = [];
		$r = TestSpanishScripturNum::parseString('Juan 3:16 and 18', ScripturNum::BARE_BOOKS_ALL, ScripturNum::UNREADABLE_REPORT, $ex);
		$this->assertSame(['Juan 3:16'], self::strings($r));
		$this->assertCount(1, $ex);
		$this->assertSame('and 18', $ex[0]->getInput());
	}

	public function test_issue22_spanishNamesAreNotEnglishNames()
	{
		$ex = [];
		$r = TestSpanishScripturNum::parseString('Reyes 1', ScripturNum::BARE_BOOKS_ALL, ScripturNum::UNREADABLE_REPORT, $ex);
		$this->assertCount(0, $r); // 1 and 2 Reyes have to be numbered.
		$this->assertSame(['Reyes 1'], array_map(function ($e) {
			return $e->getInput();
		}, $ex));
	}
}
