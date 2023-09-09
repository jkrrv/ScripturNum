<?php

namespace ScripturNumTests;

use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ScripturNum\ScripturNum;

class ScripturNumProtectedTests extends TestCase {

	public function invokeProtected($method, array $parameters = []) {
		$reflection = new ReflectionClass(ScripturNum::class);
		$method = $reflection->getMethod($method);
		$method->setAccessible(true);
		return $method->invokeArgs(null, $parameters);
	}

	public function test_bkIndexToSingleRef() {
		$chapter = 0;
		$verse = 0;
		$this->invokeProtected('bkIndexToSingleRef', [1, 132, &$chapter, &$verse]);
		$this->assertEquals([6, 10], [$chapter, $verse]);
	}

	public function test_bookNameToBookNum_bookNameInvalid() {
		$this->expectException('\ScripturNum\ScripturNumException');
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
		$this->assertEquals([45, 1, 1, 8, 39], [$book, $chapterStart, $verseStart, $chapterEnd, $verseEnd]);
	}

	public function test_refNumStringToRefNums_10a() {
		$chapterStart = null;
		$verseStart = null;
		$chapterEnd = null;
		$verseEnd = null;

		$this->invokeProtected('refNumStringToRefNums', ['', &$chapterStart, &$verseStart, &$chapterEnd, &$verseEnd]);
		$this->assertEquals([null, null, null, null], [$chapterStart, $verseStart, $chapterEnd, $verseEnd]);
	}

	public function test_refNumStringToRefNums_10b() {
		$chapterStart = null;
		$verseStart = null;
		$chapterEnd = null;
		$verseEnd = null;

		$this->invokeProtected('refNumStringToRefNums', ['1', &$chapterStart, &$verseStart, &$chapterEnd, &$verseEnd]);
		$this->assertEquals([1, null, 1, null], [$chapterStart, $verseStart, $chapterEnd, $verseEnd]);
	}

	public function test_refNumStringToRefNums_11() {
		$chapterStart = null;
		$verseStart = null;
		$chapterEnd = null;
		$verseEnd = null;

		$this->invokeProtected('refNumStringToRefNums', ['1-2', &$chapterStart, &$verseStart, &$chapterEnd, &$verseEnd]);
		$this->assertEquals([1, null, 2, null], [$chapterStart, $verseStart, $chapterEnd, $verseEnd]);
	}

	public function test_refNumStringToRefNums_12() {
		$chapterStart = null;
		$verseStart = null;
		$chapterEnd = null;
		$verseEnd = null;

		$this->invokeProtected('refNumStringToRefNums', ['1-2:3', &$chapterStart, &$verseStart, &$chapterEnd, &$verseEnd]);
		$this->assertEquals([1, null, 2, 3], [$chapterStart, $verseStart, $chapterEnd, $verseEnd]);
	}

	public function test_refNumStringToRefNums_20() {
		$chapterStart = null;
		$verseStart = null;
		$chapterEnd = null;
		$verseEnd = null;

		$this->invokeProtected('refNumStringToRefNums', ['1:2', &$chapterStart, &$verseStart, &$chapterEnd, &$verseEnd]);
		$this->assertEquals([1, 2, null, 2], [$chapterStart, $verseStart, $chapterEnd, $verseEnd]);
	}

	public function test_refNumStringToRefNums_21() {
		$chapterStart = null;
		$verseStart = null;
		$chapterEnd = null;
		$verseEnd = null;

		$this->invokeProtected('refNumStringToRefNums', ['1:2-3', &$chapterStart, &$verseStart, &$chapterEnd, &$verseEnd]);
		$this->assertEquals([1, 2, null, 3], [$chapterStart, $verseStart, $chapterEnd, $verseEnd]);
	}

	public function test_refNumStringToRefNums_22() {
		$chapterStart = null;
		$verseStart = null;
		$chapterEnd = null;
		$verseEnd = null;

		$this->invokeProtected('refNumStringToRefNums', ['1:2-3:4', &$chapterStart, &$verseStart, &$chapterEnd, &$verseEnd]);
		$this->assertEquals([1, 2, 3, 4], [$chapterStart, $verseStart, $chapterEnd, $verseEnd]);
	}

	public function test_refNumStringToRefNums_30() {
		$chapterStart = null;
		$verseStart = null;
		$chapterEnd = null;
		$verseEnd = null;

		$this->expectException('\ScripturNum\ScripturNumException');
		$this->expectExceptionMessage('Badly formed numerical reference.');

		$this->invokeProtected('refNumStringToRefNums', ['1:2:3', &$chapterStart, &$verseStart, &$chapterEnd, &$verseEnd]);
	}

	public function test_refNumStringToRefNums_az() {
		$chapterStart = null;
		$verseStart = null;
		$chapterEnd = null;
		$verseEnd = null;

		$this->expectException('\ScripturNum\ScripturNumException');
		$this->expectExceptionMessage('Parse Ref only handles the numerical part of the reference.  Alphabetical characters are not permitted.');

		$this->invokeProtected('refNumStringToRefNums', ['1:2b', &$chapterStart, &$verseStart, &$chapterEnd, &$verseEnd]);
	}

	public function test_refNumsToInt_multipleChapters() {
		$this->assertEquals(738197728, $this->invokeProtected('refNumsToInt', [45, 1, 1, 8, 39]));
	}

	public function test_refNumsToInt_SingleChapterIsWholeBook() {
		$this->assertEquals(1073741848, $this->invokeProtected('refNumsToInt', [65, 1, null, 25, null]));
	}

	public function test_refNumsToInt_ChapterOutOfRange() {
		$this->expectException('\ScripturNum\ScripturNumException');
		$this->expectExceptionMessage('A chapter was requested that does not exist within the requested book.');

		$this->invokeProtected('validateRefNums', [63, 1, 2, 3, 4]);
	}

	public function test_refNumsToInt_WholeBook() {
		$this->assertEquals(654312494, $this->invokeProtected('refNumsToInt', [40, null, null, null, null]));
	}

	public function test_refNumsToInt_SingleChapter() {
		$this->assertEquals(654311448, $this->invokeProtected('refNumsToInt', [40, 1, null, null, null]));
	}

	public function test_refNumsToInt_StartVerseOutOfRange() {
		$this->expectException('\ScripturNum\ScripturNumException');
		$this->expectExceptionMessage('A verse was requested that does not exist within the requested chapter.');

		$this->invokeProtected('validateRefNums', [62, 1, 30, 2, 1]);
	}

	public function test_refNumsToInt_EndVerseOutOfRange() {
		$this->expectException('\ScripturNum\ScripturNumException');
		$this->expectExceptionMessage('A verse was requested that does not exist within the requested chapter.');

		$this->invokeProtected('validateRefNums', [62, 1, 1, 2, 40]);
	}

	public function test_refNumsToInt_ChaptersNotOne() {
		$this->assertEquals(654413871, $this->invokeProtected('refNumsToInt', [40, 2, null, null, null]));
	}

}