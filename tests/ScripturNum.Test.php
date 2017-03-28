<?php

//require_once "../src/ScripturNum.class.php";

use PHPUnit\Framework\TestCase;
use ScripturNum\ScripturNum;


class ScripturNumProtectedTest extends TestCase {

	public function invokeProtected($method, array $parameters = []) {
		$reflection = new ReflectionClass('ScripturNum\ScripturNum');
		$method = $reflection->getMethod($method);
		$method->setAccessible(true);
		return $method->invokeArgs(null, $parameters);
	}

	public function test_bkIndex2singleRef() {
		$chapter = 0;
		$verse = 0;
		$this->invokeProtected('_bkIndex2singleRef', [1, 132, &$chapter, &$verse]);
		$this->assertEquals([6, 10], [$chapter, $verse]);
	}

	public function test_bookName2BookNum_bookNameInvalid() {
		$this->expectException('\ScripturNum\ScripturNumException');
		$this->expectExceptionMessage('Book name is invalid.');

		$this->invokeProtected('_bookName2BookNum', ['Elizabeth']);
	}

	public function test_bookName2BookNum_bookNameValid() {
		$this->assertEquals(1, $this->invokeProtected('_bookName2BookNum', ['Genesis']));
	}

	public function test_int2refNums() {
		$book = 0;
		$chapterStart = 0;
		$verseStart = 0;
		$chapterEnd = 0;
		$verseEnd = 0;

		$this->invokeProtected('_int2refNums', [738197728, &$book, &$chapterStart, &$verseStart, &$chapterEnd, &$verseEnd]);
		$this->assertEquals([45, 1, 1, 8, 39], [$book, $chapterStart, $verseStart, $chapterEnd, $verseEnd]);
	}

	public function test_refNumString2refNums_10a() {
		$chapterStart = null;
		$verseStart = null;
		$chapterEnd = null;
		$verseEnd = null;

		$this->invokeProtected('_refNumString2refNums', ['', &$chapterStart, &$verseStart, &$chapterEnd, &$verseEnd]);
		$this->assertEquals([null, null, null, null], [$chapterStart, $verseStart, $chapterEnd, $verseEnd]);
	}

	public function test_refNumString2refNums_10b() {
		$chapterStart = null;
		$verseStart = null;
		$chapterEnd = null;
		$verseEnd = null;

		$this->invokeProtected('_refNumString2refNums', ['1', &$chapterStart, &$verseStart, &$chapterEnd, &$verseEnd]);
		$this->assertEquals([1, null, 1, null], [$chapterStart, $verseStart, $chapterEnd, $verseEnd]);
	}

	public function test_refNumString2refNums_11() {
		$chapterStart = null;
		$verseStart = null;
		$chapterEnd = null;
		$verseEnd = null;

		$this->invokeProtected('_refNumString2refNums', ['1-2', &$chapterStart, &$verseStart, &$chapterEnd, &$verseEnd]);
		$this->assertEquals([1, null, 2, null], [$chapterStart, $verseStart, $chapterEnd, $verseEnd]);
	}

	public function test_refNumString2refNums_12() {
		$chapterStart = null;
		$verseStart = null;
		$chapterEnd = null;
		$verseEnd = null;

		$this->invokeProtected('_refNumString2refNums', ['1-2:3', &$chapterStart, &$verseStart, &$chapterEnd, &$verseEnd]);
		$this->assertEquals([1, null, 2, 3], [$chapterStart, $verseStart, $chapterEnd, $verseEnd]);
	}

	public function test_refNumString2refNums_20() {
		$chapterStart = null;
		$verseStart = null;
		$chapterEnd = null;
		$verseEnd = null;

		$this->invokeProtected('_refNumString2refNums', ['1:2', &$chapterStart, &$verseStart, &$chapterEnd, &$verseEnd]);
		$this->assertEquals([1, 2, null, 2], [$chapterStart, $verseStart, $chapterEnd, $verseEnd]);
	}

	public function test_refNumString2refNums_21() {
		$chapterStart = null;
		$verseStart = null;
		$chapterEnd = null;
		$verseEnd = null;

		$this->invokeProtected('_refNumString2refNums', ['1:2-3', &$chapterStart, &$verseStart, &$chapterEnd, &$verseEnd]);
		$this->assertEquals([1, 2, null, 3], [$chapterStart, $verseStart, $chapterEnd, $verseEnd]);
	}

	public function test_refNumString2refNums_22() {
		$chapterStart = null;
		$verseStart = null;
		$chapterEnd = null;
		$verseEnd = null;

		$this->invokeProtected('_refNumString2refNums', ['1:2-3:4', &$chapterStart, &$verseStart, &$chapterEnd, &$verseEnd]);
		$this->assertEquals([1, 2, 3, 4], [$chapterStart, $verseStart, $chapterEnd, $verseEnd]);
	}

	public function test_refNumString2refNums_30() {
		$chapterStart = null;
		$verseStart = null;
		$chapterEnd = null;
		$verseEnd = null;

		$this->expectException('\ScripturNum\ScripturNumException');
		$this->expectExceptionMessage('Badly formed numerical reference.');

		$this->invokeProtected('_refNumString2refNums', ['1:2:3', &$chapterStart, &$verseStart, &$chapterEnd, &$verseEnd]);
	}

	public function test_refNumString2refNums_az() {
		$chapterStart = null;
		$verseStart = null;
		$chapterEnd = null;
		$verseEnd = null;

		$this->expectException('\ScripturNum\ScripturNumException');
		$this->expectExceptionMessage('Parse Ref only handles the numerical part of the reference.  Alphabetical characters are not permitted.');

		$this->invokeProtected('_refNumString2refNums', ['1:2b', &$chapterStart, &$verseStart, &$chapterEnd, &$verseEnd]);
	}

	public function test__refNums2int_multipleChapters() {
		$this->assertEquals(738197728, $this->invokeProtected('_refNums2int', [45, 1, 1, 8, 39]));
	}

	public function test__refNums2int_SingleChapterIsWholeBook() {
		$this->assertEquals(1073741848, $this->invokeProtected('_refNums2int', [65, 1, null, 25, null]));
	}

	public function test__refNums2int_ChapterOutOfRange() {
		$this->expectException('\ScripturNum\ScripturNumException');
		$this->expectExceptionMessage('A chapter was requested that does not exist within the requested book.');

		$this->invokeProtected('_refNums2int', [63, 1, 2, 3, 4]);
	}

	public function test__refNums2int_WholeBook() {
		$this->assertEquals(654312494, $this->invokeProtected('_refNums2int', [40, null, null, null, null]));
	}

	public function test__refNums2int_SingleChapter() {
		$this->assertEquals(654311448, $this->invokeProtected('_refNums2int', [40, 1, null, null, null]));
	}

	public function test__refNums2int_StartVerseOutOfRange() {
		$this->expectException('\ScripturNum\ScripturNumException');
		$this->expectExceptionMessage('A verse was requested that does not exist within the requested chapter.');

		$this->invokeProtected('_refNums2int', [62, 1, 30, 2, 1]);
	}

	public function test__refNums2int_EndVerseOutOfRange() {
		$this->expectException('\ScripturNum\ScripturNumException');
		$this->expectExceptionMessage('A verse was requested that does not exist within the requested chapter.');

		$this->invokeProtected('_refNums2int', [62, 1, 1, 2, 40]);
	}

	public function test__refNums2int_ChaptersNotOne() {
		$this->assertEquals(654413871, $this->invokeProtected('_refNums2int', [40, 2, null, null, null]));
	}




}

class ScripturNumPublicTest extends TestCase
{
	public function test_constructFromNumber() {
		$n = new ScripturNum(738197728);
		$this->assertEquals('Romans 1-8', (string)$n);
	}

	public function test_constructFromString() {
		$n = new ScripturNum('1 Corinthians 13');
		$this->assertEquals('1 Corinthians 13', (string)$n);
	}

	public function test_newFromInts() {
		$n = ScripturNum::newFromInts(40, 8, null, 9);
		$this->assertEquals('Matthew 8-9', (string)$n);
	}

	public function test_newFromParsed() {
		$n = ScripturNum::newFromParsed('Matthew', 8, null, 9);
		$this->assertEquals('Matthew 8-9', (string)$n);
	}

	public function test_newFromString() {
		$n = ScripturNum::newFromString('1 Corinthians 13');
		$this->assertEquals('1 Corinthians 13', (string)$n);
	}

	public function test_wholeBook() {
		$n = new ScripturNum('Jude');
		$this->assertEquals('Jude', (string)$n);
	}

	public function test_wholeBookAbbrev() {
		$n = new ScripturNum('Ju');
		$this->assertEquals('Ju', $n->getAbbrev());
	}

	public function test_versesFromSingleChapterBook() {
		$n = new ScripturNum('Jude 13-14');
		$this->assertEquals('Jude 13-14', (string)$n);
	}

	public function test_versesFromSingleChapterBookAbbrev() {
		$n = new ScripturNum('Ju13-14');
		$this->assertEquals('Ju13-14', $n->getAbbrev());
	}

	public function test_verseFromSingleChapterBook() {
		$n = new ScripturNum('Jude 13');
		$this->assertEquals('Jude 13', (string)$n);
	}

	public function test_verseFromSingleChapterBookAbbrev() {
		$n = new ScripturNum('Ju13');
		$this->assertEquals('Ju13', $n->getAbbrev());
	}

	public function test_versesFromOneChapter() {
		$n = new ScripturNum('Romans 8:28-29');
		$this->assertEquals('Romans 8:28-29', (string)$n);
	}

	public function test_versesFromOneChapterAbbrev() {
		$n = new ScripturNum('Ro8.28-29');
		$this->assertEquals('Ro8.28-29', $n->getAbbrev());
	}

	public function test_verseFromOneChapter() {
		$n = new ScripturNum('Romans 8:28');
		$this->assertEquals('Romans 8:28', (string)$n);
	}

	public function test_verseFromOneChapterAbbrev() {
		$n = new ScripturNum('Ro8.28');
		$this->assertEquals('Ro8.28', $n->getAbbrev());
	}

	public function test_versesFromMultipleChapters() {
		$n = new ScripturNum('Romans 7:21-8:17');
		$this->assertEquals('Romans 7:21-8:17', (string)$n);
	}

	public function test_versesFromMultipleChaptersAbbrev() {
		$n = new ScripturNum('Ro7.21-8.17');
		$this->assertEquals('Ro7.21-8.17', $n->getAbbrev());
	}

	public function test_wholeChapters() {
		$n = new ScripturNum('Romans 6-8');
		$this->assertEquals('Romans 6-8', (string)$n);
	}

	public function test_wholeChaptersAbbrev() {
		$n = new ScripturNum('Ro6-8');
		$this->assertEquals('Ro6-8', $n->getAbbrev());
	}

	public function test_wholeChapter() {
		$n = new ScripturNum('Romans 8');
		$this->assertEquals('Romans 8', (string)$n);
	}

	public function test_wholeChapterAbbrev() {
		$n = new ScripturNum('Ro8');
		$this->assertEquals('Ro8', $n->getAbbrev());
	}

	public function test_wholeChapterToPartOfChapter() {
		$n = new ScripturNum('Romans 8-9:5');
		$this->assertEquals('Romans 8:1-9:5', (string)$n);
	}

	public function test_wholeChapterToPartOfChapterAbbrev() {
		$n = new ScripturNum('Ro8.1-9.5');
		$this->assertEquals('Ro8.1-9.5', $n->getAbbrev());
	}



	public function test_issue1() {
		$n = new ScripturNum('3Jo11');
		$this->assertEquals('3Jo11', $n->getAbbrev());
	}

}
