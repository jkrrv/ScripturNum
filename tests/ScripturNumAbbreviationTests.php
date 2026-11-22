<?php

namespace ScripturNumTests;

use PHPUnit\Framework\TestCase;
use ScripturNum\Bible;
use ScripturNum\ScripturNum;
use ScripturNum\ScripturNumArray;

ini_set('memory_limit', '-1');

/**
 * What getAbbrev() writes, such as "Jn3.16", must be read back by parseString() as the passage it came from.  See issue
 * #24.  In it, a "." is equivalent to a ":" between a chapter and a verse, and in a book of one chapter, a number that
 * is alone is a verse ("Ob1" is Obadiah 1:1, as "Jude 5" is Jude 5), so that what is written for those can be read.
 *
 * Passages come back in a ScripturNumArray, which keeps them in the order of the Bible, so the expectations are
 * written in that order.
 */
class ScripturNumAbbreviationTests extends TestCase
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
	 * Reads the text as prose, as a list (checking that there is nothing in it that couldn't be read), and by the
	 * deprecated extractFromString(), and checks that all give the expected passages.
	 *
	 * @param string[] $expected
	 */
	protected function assertReadsAs(array $expected, string $text, string $class = ScripturNum::class)
	{
		$this->assertSame($expected, self::strings($class::parseString($text, ScripturNum::BARE_BOOKS_ALL)), "parseString (prose) for: $text");

		$ex = [];
		$list = $class::parseString($text, ScripturNum::BARE_BOOKS_ALL, ScripturNum::UNREADABLE_REPORT, $ex);
		$this->assertSame($expected, self::strings($list), "parseString (list) for: $text");
		$this->assertSame([], $ex, "parseString (list) exceptions for: $text");

		$this->assertSame($expected, self::strings($class::extractFromString($text)), "extractFromString for: $text");
	}

	/**
	 * Writes the passage as getAbbrev() does, reads it back as a list, and returns what went wrong, if anything.
	 *
	 * @return string|null
	 */
	protected static function roundTrip(string $class, ScripturNum $p)
	{
		$abbrev = $p->getAbbrev();
		$ex = [];
		$r = $class::parseString($abbrev, ScripturNum::BARE_BOOKS_ALL, ScripturNum::UNREADABLE_REPORT, $ex);
		if (count($ex) > 0) {
			return "$abbrev: " . count($ex) . " exceptions, the first for '" . $ex[0]->getInput() . "' (from " . $p->getLongString() . ")";
		}
		if (count($r) !== 1 || $r[0]->getInt() !== $p->getInt()) {
			return "$abbrev: read as '" . $r . "' (from " . $p->getLongString() . ")";
		}
		return null;
	}

	// ---- The examples from the issue ----

	public function test_issue24_dottedVerse()
	{
		$this->assertReadsAs(['John 3:16'], 'Jn3.16');
	}

	public function test_issue24_dottedVerseRange()
	{
		$this->assertReadsAs(['John 3:16-21'], 'Jn3.16-21');
	}

	public function test_issue24_dottedAcrossChapters()
	{
		$this->assertReadsAs(['Genesis 1:1-2:3'], 'Ge1.1-2.3');
	}

	public function test_issue24_dottedWithASpace()
	{
		$this->assertReadsAs(['John 3:16'], 'John 3.16');
	}

	public function test_issue24_chaptersAreUnchanged()
	{
		$this->assertReadsAs(['John 3'], 'Jn3');
		$this->assertReadsAs(['John 3-4'], 'Jn3-4');
		$this->assertReadsAs(['John 3'], 'John 3');
	}

	public function test_issue24_agreesWithStringToInts()
	{
		foreach (['Jn3.16', 'Jn3.16-21', 'Ge1.1-2.3', 'John 3.16', 'Jn3', 'Ps101.1-102.3', '1Jn1.9'] as $text) {
			$ints = ScripturNum::stringToInts($text);
			$parsed = [];
			foreach (ScripturNum::parseString($text, ScripturNum::BARE_BOOKS_ALL) as $s) {
				$parsed[] = $s->getInt();
			}
			$this->assertSame($ints, $parsed, "For: $text");
		}
	}

	// ---- A "." and a ":" are the same ----

	public function test_issue24_dotAndColonAreEquivalent()
	{
		$this->assertReadsAs(['Psalm 23:1-3'], 'Ps23.1-3');
		$this->assertReadsAs(['Psalm 23:1-3'], 'Ps23:1-3');
		$this->assertReadsAs(['Psalms 101:1-102:3'], 'Ps101.1-102.3');
		$this->assertReadsAs(['Psalms 101:1-102:3'], 'Ps101:1-102:3');
	}

	public function test_issue24_dotAndColonMixed()
	{
		$this->assertReadsAs(['Genesis 1:1-2:3'], 'Ge1.1-2:3');
		$this->assertReadsAs(['Genesis 1:1-2:3'], 'Ge1:1-2.3');
	}

	public function test_issue24_inLists()
	{
		$this->assertReadsAs(['John 3:16', '1 John 1:9'], 'Jn3.16; 1Jn1.9');
		$this->assertReadsAs(['Psalm 23:1-3', 'Romans 8:28'], 'Romans 8.28 and Ps 23.1-3');
		$this->assertReadsAs(['John 3:16', 'John 3:18', 'John 4:2'], 'Jn3.16, 18; 4.2');
		$this->assertReadsAs(['1 Kings 1:2', 'John 3:16'], 'John 3.16 and 1 Kings 1.2');
	}

	public function test_issue24_inProse()
	{
		$this->assertSame(
			['Psalm 23:1-3', 'Romans 8:28'],
			self::strings(ScripturNum::parseString('Read Romans 8.28 and Ps 23.1-3 today.')));
		$this->assertSame(
			['Genesis 1:1-2:3'],
			self::strings(ScripturNum::parseString('Read Genesis 1.1-2.3 today.')));
	}

	public function test_issue24_aPeriodThatEndsASentenceIsNotASeparator()
	{
		$this->assertSame(['John 3'], self::strings(ScripturNum::parseString('See John 3. Then 16 people left.')));
		$this->assertSame(['Psalm 23'], self::strings(ScripturNum::parseString('Psalm 23.')));
		$this->assertSame(['Psalm 23'], self::strings(ScripturNum::parseString("Psalm 23.\n16 people left.")));
	}

	public function test_issue24_numbersThatAreNotAfterABookAreNotReferences()
	{
		$this->assertCount(0, ScripturNum::parseString('Mark scored 3.5 stars on 5.5 days'));
		$this->assertCount(0, ScripturNum::parseString('Version 2.3 is out'));
	}

	public function test_issue24_inAnotherLanguage()
	{
		$this->assertReadsAs(['Juan 3:16'], 'Jn3.16', TestSpanishScripturNum::class);
		$this->assertReadsAs(['Juan 3:16-18'], 'Juan 3.16-18', TestSpanishScripturNum::class);
		$this->assertReadsAs(['1 Reyes 1:2-4', 'Juan 3:16'], 'Jn3.16 y 1Rey1.2-4', TestSpanishScripturNum::class);
	}

	// ---- In a book of one chapter, a number that is alone is a verse ----

	public function test_issue24_oneChapterBooks_aLoneNumberIsAVerse()
	{
		$this->assertReadsAs(['Obadiah 1'], 'Ob1');
		$this->assertReadsAs(['Philemon 1'], 'Pm1');
		$this->assertReadsAs(['2 John 1'], '2Jo1');
		$this->assertReadsAs(['3 John 1'], '3Jo1');
		$this->assertReadsAs(['Jude 1'], 'Ju1');
		$this->assertReadsAs(['Jude 1'], 'Jude 1');
	}

	public function test_issue24_oneChapterBooks_verseOneIsNotTheWholeBook()
	{
		foreach (['Obadiah 1', 'Philemon 1', '2 John 1', '3 John 1', 'Jude 1'] as $text) {
			$sn = new ScripturNum($text);
			$this->assertFalse($sn->isWholeBook(), $text);
			$this->assertTrue($sn->isSingleVerse(), $text);
			$this->assertSame(1, $sn->startV, $text);
			$this->assertSame(1, $sn->endV, $text);
		}
	}

	public function test_issue24_oneChapterBooks_wholeBookIsStillTheBookName()
	{
		// (Some of these are common words, so only parseString() with every name allowed finds them on their own.)
		foreach (['Obadiah', 'Philemon', '2 John', '3 John', 'Jude'] as $text) {
			$this->assertTrue((new ScripturNum($text))->isWholeBook(), $text);

			$ex = [];
			$r = ScripturNum::parseString($text, ScripturNum::BARE_BOOKS_ALL, ScripturNum::UNREADABLE_REPORT, $ex);
			$this->assertSame([$text], self::strings($r), $text);
			$this->assertSame([], $ex, $text);
			$this->assertTrue($r[0]->isWholeBook(), $text);
		}
	}

	public function test_issue24_oneChapterBooks_otherNumbersAreUnchanged()
	{
		$this->assertReadsAs(['Jude 5'], 'Jude 5');
		$this->assertReadsAs(['Jude 25'], 'Ju25');
		$this->assertReadsAs(['3 John 11'], '3Jo11');
		$this->assertReadsAs(['Jude 13-14'], 'Jude 13-14');
	}

	public function test_issue24_oneChapterBooks_rangesAndListsStartingWithOne()
	{
		$this->assertReadsAs(['Jude 1-5'], 'Jude 1-5');
		$this->assertReadsAs(['Jude 1-5'], 'Ju1-5');
		$this->assertReadsAs(['Jude 1', 'Jude 5'], 'Jude 1, 5');
		$this->assertReadsAs(['Jude 1', 'Jude 5-7'], 'Ju1, 5-7');
	}

	public function test_issue24_oneChapterBooks_withAChapterIsUnchanged()
	{
		$this->assertReadsAs(['Jude 1'], 'Jude 1:1');
		$this->assertReadsAs(['Jude 1'], 'Ju1.1');
		$this->assertReadsAs(['Jude 3-5'], 'Jude 1:3-5');
		$this->assertReadsAs(['Jude 3-5'], 'Ju1.3-5');
		$this->assertReadsAs(['Jude 1-5'], 'Jude 1-1:5'); // from the start of the chapter
	}

	public function test_issue24_oneChapterBooks_inEveryWayThatAStringIsRead()
	{
		// the constructor, stringToInts() and parseString() agree.
		foreach (['Obadiah 1', 'Jude 1', '3 John 1', 'Jude 1-5'] as $text) {
			$expected = (new ScripturNum($text))->getInt();
			$this->assertSame($expected, ScripturNum::stringToInt($text), "stringToInt: $text");
			$this->assertSame([$expected], ScripturNum::stringToInts($text), "stringToInts: $text");
			$this->assertSame($expected, ScripturNum::parseString($text, ScripturNum::BARE_BOOKS_ALL)[0]->getInt(), "parseString: $text");
		}
	}

	public function test_issue24_oneChapterBooks_otherBooksAreUnchanged()
	{
		// A book of more than one chapter still takes a lone number to be a chapter.
		$this->assertReadsAs(['John 1'], 'John 1');
		$this->assertReadsAs(['Psalm 1'], 'Ps1');
		$this->assertReadsAs(['John 1-2'], 'John 1-2');
		$this->assertFalse((new ScripturNum('John 1'))->isSingleVerse());
	}

	// ---- Everything that getAbbrev() writes can be read ----

	public function test_issue24_roundTrip_wholeBooks()
	{
		$problems = [];
		foreach (Bible::getVerseCounts() as $b => $chapters) {
			$whole = ScripturNum::newFromInts($b + 1, 1, 1, 1, 1)->getWholeBook();
			if ($p = self::roundTrip(ScripturNum::class, $whole)) {
				$problems[] = $p;
			}
		}
		$this->assertSame([], $problems);
	}

	public function test_issue24_roundTrip_wholeChapters()
	{
		$problems = [];
		foreach (Bible::getVerseCounts() as $b => $chapters) {
			foreach ($chapters as $c => $verseCount) {
				if (count($chapters) === 1) {
					continue; // A whole chapter of a book of one chapter is the whole book.
				}
				if ($p = self::roundTrip(ScripturNum::class, ScripturNum::newFromInts($b + 1, $c + 1, null, $c + 1))) {
					$problems[] = $p;
				}
			}
		}
		$this->assertSame([], $problems);
	}

	public function test_issue24_roundTrip_verses()
	{
		// The first, the middle and the last verse of every chapter of every book.
		$problems = [];
		$count = 0;
		foreach (Bible::getVerseCounts() as $b => $chapters) {
			foreach ($chapters as $c => $verseCount) {
				foreach (array_unique([1, (int)ceil($verseCount / 2), $verseCount]) as $v) {
					$count++;
					if ($p = self::roundTrip(ScripturNum::class, ScripturNum::newFromInts($b + 1, $c + 1, $v, $c + 1, $v))) {
						$problems[] = $p;
					}
				}
			}
		}
		$this->assertGreaterThan(3000, $count);
		$this->assertSame([], $problems);
	}

	public function test_issue24_roundTrip_everyVerseOfSomeBooks()
	{
		// A book of one chapter, one of a few chapters, and the longest chapters of the Bible.
		$problems = [];
		foreach (['Jude', 'Philemon', 'Ruth', 'Psalm 119'] as $name) {
			$sn = new ScripturNum($name);
			$chapters = Bible::getVerseCounts()[$sn->book - 1];
			foreach ($chapters as $c => $verseCount) {
				if ($name === 'Psalm 119' && $c !== 118) {
					continue;
				}
				for ($v = 1; $v <= $verseCount; $v++) {
					if ($p = self::roundTrip(ScripturNum::class, ScripturNum::newFromInts($sn->book, $c + 1, $v, $c + 1, $v))) {
						$problems[] = $p;
					}
				}
			}
		}
		$this->assertSame([], $problems);
	}

	public function test_issue24_roundTrip_ranges()
	{
		$problems = [];
		$count = 0;
		foreach (Bible::getVerseCounts() as $b => $chapters) {
			$book = $b + 1;
			$last = count($chapters);
			$cases = [
				// the verses of the first chapter
				ScripturNum::newFromInts($book, 1, 1, 1, min($chapters[0], 3)),
			];
			if ($last >= 2) {
				$cases[] = ScripturNum::newFromInts($book, 1, null, 2);                                       // chapters
				$cases[] = ScripturNum::newFromInts($book, 1, 1, 2, 1);                                       // across chapters
				$cases[] = ScripturNum::newFromInts($book, $last - 1, 1, $last, $chapters[$last - 1]);        // last two chapters
				$cases[] = ScripturNum::newFromInts($book, $last, 1, $last, min($chapters[$last - 1], 3));     // verses of the last
			}
			foreach ($cases as $sn) {
				$count++;
				if ($p = self::roundTrip(ScripturNum::class, $sn)) {
					$problems[] = $p;
				}
			}
		}
		$this->assertGreaterThan(300, $count);
		$this->assertSame([], $problems);
	}

	public function test_issue24_roundTrip_inAnotherLanguage()
	{
		$problems = [];
		foreach (['Juan 3:16', 'Juan 3:16-21', 'Juan 3', 'Juan', '1 Reyes 1:2-4', '2 Reyes 3:1-4:2', 'Romanos 8:28'] as $text) {
			if ($p = self::roundTrip(TestSpanishScripturNum::class, new TestSpanishScripturNum($text))) {
				$problems[] = $p;
			}
		}
		$this->assertSame([], $problems);
	}

	public function test_issue24_roundTrip_listsOfAbbreviations()
	{
		$a = new ScripturNumArray(['John 3:16', 'Genesis 1:1-2:3', 'Jude 1', '1 Kings 1:2']);
		$written = $a->toString('abbrev');
		$ex = [];
		$read = ScripturNum::parseString($written, ScripturNum::BARE_BOOKS_ALL, ScripturNum::UNREADABLE_REPORT, $ex);
		$this->assertSame([], $ex, $written);
		$this->assertSame(self::strings($a), self::strings($read), $written);
	}
}
