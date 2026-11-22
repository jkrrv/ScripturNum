<?php
/** @noinspection PhpParamsInspection */

namespace ScripturNumTests;

use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ScripturNum\ScripturNum;
use ScripturNum\ScripturNumException;

class ScripturNumProtectedTests extends TestCase {

	public function invokeProtected($method, array $parameters = []) {
		$reflection = new ReflectionClass(ScripturNum::class);
		$method = $reflection->getMethod($method);
		@$method->setAccessible(true); // Deprecated in PHP 8.1, but required for older versions.
		return $method->invokeArgs(null,$parameters);
	}

	public function test_bkIndexToSingleRef() {
		$chapter = 0;
		$verse = 0;
		$this->invokeProtected('bkIndexToSingleRef', [1, 132, &$chapter, &$verse]);
		$this->assertEquals(6, $chapter);
		$this->assertEquals(10, $verse);
	}

	public function test_bookNameToBookNum_bookNameInvalid() {
		$this->expectException(ScripturNumException::class);
		$this->expectExceptionMessage('Book name is invalid.');

		$this->invokeProtected('bookNameToBookNum', ['Elizabeth']);
	}

	public function test_bookNameToBookNum_bookNameValid() {
		$this->assertEquals(1, $this->invokeProtected('bookNameToBookNum', ['Genesis']));
	}

	public function test_intToRefNums() {
		$book = 0;
		$chapterStart = 0;
		$verseStart = 0;
		$chapterEnd = 0;
		$verseEnd = 0;

		$this->invokeProtected('intToRefNums', [738197728, &$book, &$chapterStart, &$verseStart, &$chapterEnd, &$verseEnd]);
		$this->assertEquals(45, $book);
		$this->assertEquals(1, $chapterStart);
		$this->assertEquals(1, $verseStart);
		$this->assertEquals(8, $chapterEnd);
		$this->assertEquals(39, $verseEnd);
	}

	public function test_refNumStringToRefNums_0() {
		$chapterStart = null;
		$verseStart = null;
		$chapterEnd = null;
		$verseEnd = null;

		$this->invokeProtected('refNumStringToRefNums', ['', &$chapterStart, &$verseStart, &$chapterEnd, &$verseEnd]);
		$this->assertEquals(null, $chapterStart);
		$this->assertEquals(null, $verseStart);
		$this->assertEquals(null, $chapterEnd);
		$this->assertEquals(null, $verseEnd);
	}

	public function test_refNumStringToRefNums_10() {
		$chapterStart = null;
		$verseStart = null;
		$chapterEnd = null;
		$verseEnd = null;

		$this->invokeProtected('refNumStringToRefNums', ['1', &$chapterStart, &$verseStart, &$chapterEnd, &$verseEnd]);
		$this->assertEquals(1, $chapterStart);
		$this->assertEquals(null, $verseStart);
		$this->assertEquals(1, $chapterEnd);
		$this->assertEquals(null, $verseEnd);
	}

	public function test_refNumStringToRefNums_11() {
		$chapterStart = null;
		$verseStart = null;
		$chapterEnd = null;
		$verseEnd = null;

		$this->invokeProtected('refNumStringToRefNums', ['1-2', &$chapterStart, &$verseStart, &$chapterEnd, &$verseEnd]);
		$this->assertEquals(1, $chapterStart);
		$this->assertEquals(null, $verseStart);
		$this->assertEquals(2, $chapterEnd);
		$this->assertEquals(null, $verseEnd);
	}

	public function test_refNumStringToRefNums_12() {
		$chapterStart = null;
		$verseStart = null;
		$chapterEnd = null;
		$verseEnd = null;

		$this->invokeProtected('refNumStringToRefNums', ['1-2:3', &$chapterStart, &$verseStart, &$chapterEnd, &$verseEnd]);
		$this->assertEquals(1, $chapterStart);
		$this->assertEquals(null, $verseStart);
		$this->assertEquals(2, $chapterEnd);
		$this->assertEquals(3, $verseEnd);
	}

	public function test_refNumStringToRefNums_20() {
		$chapterStart = null;
		$verseStart = null;
		$chapterEnd = null;
		$verseEnd = null;

		$this->invokeProtected('refNumStringToRefNums', ['1:2', &$chapterStart, &$verseStart, &$chapterEnd, &$verseEnd]);
		$this->assertEquals(1, $chapterStart);
		$this->assertEquals(2, $verseStart);
		$this->assertEquals(1, $chapterEnd);
		$this->assertEquals(2, $verseEnd);
	}

	public function test_refNumStringToRefNums_21() {
		$chapterStart = null;
		$verseStart = null;
		$chapterEnd = null;
		$verseEnd = null;

		$this->invokeProtected('refNumStringToRefNums', ['1:2-3', &$chapterStart, &$verseStart, &$chapterEnd, &$verseEnd]);
		$this->assertEquals(1, $chapterStart);
		$this->assertEquals(2, $verseStart);
		$this->assertEquals(1, $chapterEnd);
		$this->assertEquals(3, $verseEnd);
	}

	public function test_refNumStringToRefNums_22() {
		$chapterStart = null;
		$verseStart = null;
		$chapterEnd = null;
		$verseEnd = null;

		$this->invokeProtected('refNumStringToRefNums', ['1:2-3:4', &$chapterStart, &$verseStart, &$chapterEnd, &$verseEnd]);
		$this->assertEquals(1, $chapterStart);
		$this->assertEquals(2, $verseStart);
		$this->assertEquals(3, $chapterEnd);
		$this->assertEquals(4, $verseEnd);
	}

	public function test_refNumStringToRefNums_30() {
		$chapterStart = null;
		$verseStart = null;
		$chapterEnd = null;
		$verseEnd = null;

		$this->expectException(ScripturNumException::class);
		$this->expectExceptionMessage('Badly formed numerical reference.');

		$this->invokeProtected('refNumStringToRefNums', ['1:2:3', &$chapterStart, &$verseStart, &$chapterEnd, &$verseEnd]);
	}

	public function test_refNumStringToRefNums_az() {
		$chapterStart = null;
		$verseStart = null;
		$chapterEnd = null;
		$verseEnd = null;

		$this->expectException(ScripturNumException::class);
		$this->expectExceptionMessage('Parse Ref only handles the numerical part of the reference.  Alphabetical characters are not permitted.');

		$this->invokeProtected('refNumStringToRefNums', ['1:2b', &$chapterStart, &$verseStart, &$chapterEnd, &$verseEnd]);
	}

	public function test_refNumsToInt_multipleChapters() {
		$this->assertEquals(738197728, $this->invokeProtected('refNumsToInt', [45, 1, 1, 8, 39]));
	}

	public function test_validateRefNums_SingleChapterIsWholeBook() {
		$book = 65;
		$startCh = 1;
		$startV = null;
		$endCh = 25;
		$endV = null;
		$this->invokeProtected('validateRefNums', [&$book, &$startCh, &$startV, &$endCh, &$endV]);
		$this->assertEquals(65, $book);
		$this->assertEquals(1, $startCh);
		$this->assertEquals(1, $startV);
		$this->assertEquals(1, $endCh);
		$this->assertEquals(25, $endV);
	}

	public function test_validateRefNums_ChapterOutOfRange() {
		$book = 63;
		$startCh = 1;
		$startV = 2;
		$endCh = 3;
		$endV = 4;
		$this->expectException(ScripturNumException::class);
		$this->expectExceptionMessage('A chapter was requested that does not exist within the requested book.');
		$this->invokeProtected('validateRefNums', [&$book, &$startCh, &$startV, &$endCh, &$endV]);
	}

	public function test_refNumsToInt_WholeBook() {
		$this->assertEquals(654312494, $this->invokeProtected('refNumsToInt', [40, null, null, null, null]));
	}

	public function test_refNumsToInt_SingleChapter() {
		$this->assertEquals(654311448, $this->invokeProtected('refNumsToInt', [40, 1, null, null, null]));
	}

	public function test_validateRefNums_StartVerseOutOfRange() {
		$book = 62;
		$startCh = 1;
		$startV = 30;
		$endCh = 2;
		$endV = 1;
		$this->expectException(ScripturNumException::class);
		$this->expectExceptionMessage('A verse was requested that does not exist within the requested chapter.');
		$this->invokeProtected('validateRefNums', [&$book, &$startCh, &$startV, &$endCh, &$endV]);

	}

	public function test_validateRefNums_EndVerseOutOfRange() {
		$book = 62;
		$startCh = 1;
		$startV = 1;
		$endCh = 2;
		$endV = 40;
		$this->expectException(ScripturNumException::class);
		$this->expectExceptionMessage('A verse was requested that does not exist within the requested chapter.');
		$this->invokeProtected('validateRefNums', [&$book, &$startCh, &$startV, &$endCh, &$endV]);
	}

	public function test_refNumsToInt_ChaptersNotOne() {
		$this->assertEquals(654413871, $this->invokeProtected('refNumsToInt', [40, 2, null, null, null]));
	}

	// ---- The pieces that parseString() is made of ----

	/**
	 * Like invokeProtected(), but for a ScripturNum of a particular language.
	 */
	protected function invokeProtectedOn(string $class, string $method, array $parameters = []) {
		$reflection = new ReflectionClass($class);
		$method = $reflection->getMethod($method);
		@$method->setAccessible(true); // Deprecated in PHP 8.1, but required for older versions.
		return $method->invokeArgs(null, $parameters);
	}

	public function test_normalize_english() {
		$cases = [
			'John 3 and 4'        => 'John 3 , 4',
			'JOHN 3 AND 4'        => 'JOHN 3 , 4',
			'John  3   through  4' => 'John 3 - 4',
			'John 3 THROUGH 4'    => 'John 3 - 4',
			'Genesis chapter 1'   => 'Genesis 1',
			'Mark & Luke'         => 'Mark , Luke',
			'Romans 8-9'          => 'Romans 8-9',
			"Romans 8\u{2013}9"   => 'Romans 8-9',
			'Romans 8&ndash;9'    => 'Romans 8-9',
			'Romans 8&endash;9'   => 'Romans 8-9',
			// words that merely contain a conjunction are left alone
			'Sandy and Candle'    => 'Sandy , Candle',
			'Chapters 1'          => 'Chapters 1',
			'Throughout 1'        => 'Throughout 1',
			''                    => '',
		];
		foreach ($cases as $input => $expected) {
			$this->assertSame($expected, $this->invokeProtected('normalize', [(string)$input]), "For: $input");
		}
	}

	public function test_normalize_otherLanguages() {
		$this->assertSame('Ioannes 3 , 4 - 5 6', $this->invokeProtectedOn(TestScripturNum::class, 'normalize', ['Ioannes 3 et 4 usque 5 caput 6']));
		$this->assertSame('Ioannes 3 and 4', $this->invokeProtectedOn(TestScripturNum::class, 'normalize', ['Ioannes 3 and 4']));

		// There is no word for "chapter" in the second language, so it's not removed.  "and" and "through" are symbols.
		$this->assertSame('John 3 , 4 - 5 chapter 6', $this->invokeProtectedOn(TestBareScripturNum::class, 'normalize', ['John 3 + 4 ~ 5 chapter 6']));
	}

	public function test_normalize_notUtf8() {
		// The conjunctions can't be found in bytes that aren't valid UTF-8, but nothing else is harmed.
		$this->assertSame("\xFF and 3", $this->invokeProtected('normalize', ["\xFF and 3"]));
		$this->assertSame("\xFF , 3", $this->invokeProtected('normalize', ["\xFF & 3"]));
	}

	public function test_punctuationPatterns_countsOnlyWhatTheLanguageHas() {
		$this->assertCount(7, $this->invokeProtected('punctuationPatterns'));
		$this->assertCount(7, $this->invokeProtectedOn(TestScripturNum::class, 'punctuationPatterns'));
		$this->assertCount(6, $this->invokeProtectedOn(TestBareScripturNum::class, 'punctuationPatterns')); // No word for chapter.
	}

	public function test_maskPunctuation_preservesLength() {
		$inputs = [
			'John 3 and 4', 'JOHN 3 AND 4', "Romans 8\u{2013}9", 'Romans 8&ndash;9', 'Romans 8&endash;9',
			'Genesis chapter 1', 'Sandy and Candle', 'a through b', "\u{00E9} and \u{00E9}", '', "\xFF and \xFE",
		];
		foreach ([ScripturNum::class, TestScripturNum::class, TestBareScripturNum::class] as $class) {
			foreach ($inputs as $input) {
				$masked = $this->invokeProtectedOn($class, 'maskPunctuation', [$input]);
				$this->assertSame(strlen($input), strlen($masked), "Length of $class mask of: $input");
			}
		}
	}

	public function test_maskPunctuation_values() {
		$cases = [
			'John 3 and 4'       => 'John 3 ,   4',
			'JOHN 3 AND 4'       => 'JOHN 3 ,   4',
			"Romans 8\u{2013}9"  => "Romans 8-  9",
			'Romans 8&ndash;9'   => 'Romans 8-      9',
			'Genesis chapter 1'  => 'Genesis         1',
			'Sandy and Candle'   => 'Sandy ,   Candle',
			'a through b'        => 'a -       b',
			'Sandy Chapters'     => 'Sandy Chapters',
		];
		foreach ($cases as $input => $expected) {
			$this->assertSame($expected, $this->invokeProtected('maskPunctuation', [(string)$input]), "For: $input");
		}
	}

	public function test_maskPunctuation_otherLanguages() {
		$this->assertSame('Ioannes 3 ,  4 -     5', $this->invokeProtectedOn(TestScripturNum::class, 'maskPunctuation', ['Ioannes 3 et 4 usque 5']));
		$this->assertSame('John 3 , 4 - 5 chapter 6', $this->invokeProtectedOn(TestBareScripturNum::class, 'maskPunctuation', ['John 3 + 4 ~ 5 chapter 6']));
	}

	public function test_maskPunctuation_unicodeSpaces() {
		// A no-break space (two bytes), an em space (three), and an ideographic space (three) become that many spaces.
		$this->assertSame('John  3', $this->invokeProtected('maskPunctuation', ["John\u{00A0}3"]));
		$this->assertSame('John   3', $this->invokeProtected('maskPunctuation', ["John\u{2003}3"]));
		$this->assertSame('John   3', $this->invokeProtected('maskPunctuation', ["John\u{3000}3"]));
		$this->assertSame('John 3', $this->invokeProtected('maskPunctuation', ['John 3']));
	}

	public function test_normalize_unicodeSpaces() {
		$this->assertSame('John 3', $this->invokeProtected('normalize', ["John\u{00A0}3"]));
		$this->assertSame('John 3 , 4', $this->invokeProtected('normalize', ["John\u{00A0}3\u{00A0}and\u{00A0}4"]));
	}

	public function test_maskPunctuation_notUtf8() {
		$this->assertSame("a \xFF and b", $this->invokeProtected('maskPunctuation', ["a \xFF and b"]));
	}

	public function test_scan_findsSpansAndOffsets() {
		$this->assertSame([['John 3:16', 0], ['Genesis', 14]], $this->invokeProtected('scan', ['John 3:16 and Genesis', ScripturNum::BARE_BOOKS_UNCOMMON]));
		$this->assertSame([['John 3:16', 0]], $this->invokeProtected('scan', ['John 3:16 and Genesis', ScripturNum::BARE_BOOKS_NONE]));
		$this->assertSame([], $this->invokeProtected('scan', ['', ScripturNum::BARE_BOOKS_ALL]));
		$this->assertSame([], $this->invokeProtected('scan', ['nothing to see here', ScripturNum::BARE_BOOKS_NONE]));
	}

	public function test_scan_commonNamesNeedNumbersUnlessAllNamesAreWanted() {
		$this->assertSame([['Mark 3', 5]], $this->invokeProtected('scan', ['mark Mark 3', ScripturNum::BARE_BOOKS_UNCOMMON]));
		$this->assertSame([['Mark 3', 5]], $this->invokeProtected('scan', ['mark Mark 3', ScripturNum::BARE_BOOKS_NONE]));
		$this->assertSame([['mark', 0], ['Mark 3', 5]], $this->invokeProtected('scan', ['mark Mark 3', ScripturNum::BARE_BOOKS_ALL]));
	}

	public function test_scan_withNoCommonTerms() {
		// With nothing in the list of common terms, there is nothing for that set of the search to find.
		$this->assertSame([['Mark 3', 0], ['mark', 7]], $this->invokeProtectedOn(TestBareScripturNum::class, 'scan', ['Mark 3 mark', ScripturNum::BARE_BOOKS_UNCOMMON]));
		$this->assertSame([['Mark 3', 0]], $this->invokeProtectedOn(TestBareScripturNum::class, 'scan', ['Mark 3 mark', ScripturNum::BARE_BOOKS_NONE]));
	}

	public function test_scan_overlappingMatchesAreLeftOut() {
		// "John 3" is a match of its own (John is a common word, so it needs the number), and "First John 3" (an
		// uncommon name for 1 John) is another, which begins earlier and includes it.
		$this->assertSame([['First John 3', 0]], $this->invokeProtected('scan', ['First John 3', ScripturNum::BARE_BOOKS_UNCOMMON]));
	}

	public function test_scan_inOrderFound() {
		$found = $this->invokeProtected('scan', ['Romans 8, Genesis 1, John 3', ScripturNum::BARE_BOOKS_NONE]);
		$this->assertSame([0, 10, 21], array_column($found, 1));
	}

	public function test_unreadableItems() {
		$cases = [
			// [text, from, to] => items
			['abc; def',                     0, 0, []],
			['abc; def',                     5, 2, []],
			['abc; def',                     0, 4, ['abc']],
			['abc; def',                     0, 8, ['abc', 'def']],
			['abc; def',                     5, 8, ['def']],
			['  ;  , &  ',                   0, 9, []],
			['  ; , .Fred.: ; Barney -',     0, 24, ['Fred', 'Barney']],
			["one\ntwo\r\nthree",             0, 14, ['one', 'two', 'three']],
			["one\rtwo\r",                   0, 8, ['one', 'two']],
			["one\r\n\r\ntwo \t\r\n",        0, 14, ['one', 'two']],
			["\f one \v;\f two \f",          0, 15, ['one', 'two']],
			['one two three',                0, 13, ['one two three']],
			['one & two, three; four',       0, 22, ['one', 'two', 'three', 'four']],
		];
		foreach ($cases as $c) {
			$this->assertSame($c[3], $this->invokeProtected('unreadableItems', [$c[0], $c[0], $c[1], $c[2]]), "For: {$c[0]} ({$c[1]}-{$c[2]})");
		}
	}

	public function test_unreadableItems_originalTextIsReturnedNotMaskedText() {
		$original = 'Fred and Barney';
		$masked   = $this->invokeProtected('maskPunctuation', [$original]);
		$this->assertSame(['Fred', 'Barney'], $this->invokeProtected('unreadableItems', [$original, $masked, 0, strlen($original)]));

		$original = 'Fred through Barney';
		$masked   = $this->invokeProtected('maskPunctuation', [$original]);
		$this->assertSame(['Fred through Barney'], $this->invokeProtected('unreadableItems', [$original, $masked, 0, strlen($original)]));
	}

	public function test_report_collectsWhenGivenAnArray() {
		$ex = ['first'];
		$e = new ScripturNumException('Oops');
		$this->invokeProtected('report', [$e, &$ex, 'the input']);
		$this->assertCount(2, $ex);
		$this->assertSame('first', $ex[0]);
		$this->assertSame($e, $ex[1]);
		$this->assertSame('the input', $e->getInput());
	}

	public function test_report_throwsWhenNotGivenAnArray() {
		foreach ([null, false] as $notAnArray) {
			$e = new ScripturNumException('Oops');
			try {
				$this->invokeProtected('report', [$e, &$notAnArray, 'the input']);
				$this->fail('Expected an exception.');
			} catch (ScripturNumException $thrown) {
				$this->assertSame($e, $thrown);
				$this->assertSame('the input', $thrown->getInput());
			}
		}
	}

	public function test_report_doesNotOverwriteKnownInput() {
		$ex = [];
		$e = new ScripturNumException('Oops', 0, null, 'already known');
		$this->invokeProtected('report', [$e, &$ex, 'something else']);
		$this->assertSame('already known', $e->getInput());
	}

	public function test_report_leavesInputUnknownIfNoneIsGiven() {
		$ex = [];
		$e = new ScripturNumException('Oops');
		$this->invokeProtected('report', [$e, &$ex]);
		$this->assertCount(1, $ex);
		$this->assertNull($e->getInput());
	}

	public function test_parseSpan_returnsInts() {
		$ex = false;
		$ints = $this->invokeProtected('parseSpan', ['John 3:16, 4:2', &$ex]);
		$this->assertSame([(new ScripturNum('John 3:16'))->getInt(), (new ScripturNum('John 4:2'))->getInt()], $ints);
	}

	public function test_parseSpan_normalizesItsInput() {
		$ex = false;
		$ints = $this->invokeProtected('parseSpan', ['JOHN chapter 3 THROUGH 4', &$ex]);
		$this->assertSame([(new ScripturNum('John 3-4'))->getInt()], $ints);
	}

	public function test_parseSpan_reportsTheOriginalText() {
		$ex = [];
		$this->invokeProtected('parseSpan', ['  Hezekiah and 1  ', &$ex]);
		$this->assertCount(1, $ex);
		$this->assertSame('Hezekiah and 1', $ex[0]->getInput());
	}

	// ---- Issue #24: in a book of one chapter, a number that is alone is a verse ----

	/**
	 * Runs the numbers through singleChapterBookNumbersAreVerses(), and returns them as [startCh, startV, endCh, endV].
	 */
	protected function singleChapterNumbers(string $bookName, $startCh, $startV, $endCh, $endV): array {
		$book = (new ScripturNum($bookName))->book;
		$this->invokeProtected('singleChapterBookNumbersAreVerses', [$book, &$startCh, &$startV, &$endCh, &$endV]);
		return [$startCh, $startV, $endCh, $endV];
	}

	public function test_issue24_singleChapterBookNumbersAreVerses_aLoneNumber() {
		$this->assertSame([1, 1, 1, 1], $this->singleChapterNumbers('Jude', 1, null, 1, null));
		$this->assertSame([1, 5, 1, 5], $this->singleChapterNumbers('Jude', 5, null, 5, null));
	}

	public function test_issue24_singleChapterBookNumbersAreVerses_noEndChapter() {
		$this->assertSame([1, 3, 1, 3], $this->singleChapterNumbers('Jude', 3, null, null, null));
	}

	public function test_issue24_singleChapterBookNumbersAreVerses_aRange() {
		$this->assertSame([1, 1, 1, 5], $this->singleChapterNumbers('Jude', 1, null, 5, null));
	}

	public function test_issue24_singleChapterBookNumbersAreVerses_everyBookOfOneChapter() {
		foreach (['Obadiah', 'Philemon', '2 John', '3 John', 'Jude'] as $name) {
			$this->assertSame([1, 1, 1, 1], $this->singleChapterNumbers($name, 1, null, 1, null), $name);
		}
	}

	public function test_issue24_singleChapterBookNumbersAreVerses_wholeBookIsUnchanged() {
		$this->assertSame([null, null, null, null], $this->singleChapterNumbers('Jude', null, null, null, null));
	}

	public function test_issue24_singleChapterBookNumbersAreVerses_aStartVerseIsUnchanged() {
		// Not something a string can give (a start verse comes with an end verse), but the numbers are checked separately.
		$this->assertSame([1, 3, 1, null], $this->singleChapterNumbers('Jude', 1, 3, 1, null));
	}

	public function test_issue24_singleChapterBookNumbersAreVerses_anEndVerseIsUnchanged() {
		// "Jude 1-1:5", from a chapter to part of a chapter.
		$this->assertSame([1, null, 1, 5], $this->singleChapterNumbers('Jude', 1, null, 1, 5));
	}

	public function test_issue24_singleChapterBookNumbersAreVerses_versesAreUnchanged() {
		$this->assertSame([1, 3, 1, 5], $this->singleChapterNumbers('Jude', 1, 3, 1, 5));
	}

	public function test_issue24_singleChapterBookNumbersAreVerses_otherBooksAreUnchanged() {
		$this->assertSame([1, null, 1, null], $this->singleChapterNumbers('John', 1, null, 1, null));
		$this->assertSame([3, null, 4, null], $this->singleChapterNumbers('John', 3, null, 4, null));
		$this->assertSame([1, null, 1, null], $this->singleChapterNumbers('Psalm', 1, null, 1, null));
	}
}
