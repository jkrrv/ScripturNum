<?php

namespace ScripturNumTests;

use PHPUnit\Framework\TestCase;
use ScripturNum\ScripturNum;

class ScripturNumPublicTests extends TestCase
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
		$n = new ScripturNum('1 Corinthians 13');
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

    public function test_multiplePsalmsArePlural() {
        $n = new ScripturNum('Ps101.1-102.3');
        $this->assertEquals('Psalms 101:1-102:3', $n->getLongString());
    }

    public function test_singlePsalmIsSingular() {
        $n = new ScripturNum('Ps101.1-3');
        $this->assertEquals('Psalm 101:1-3', $n->getLongString());
    }

	public function test_int2cats() {
		$concatStart = 0;
		$concatEnd = 0;
		ScripturNum::int2concats(301993985, $concatStart, $concatEnd);
		$this->assertEquals(19001002, $concatStart);
		$this->assertEquals(19001002, $concatEnd);
	}



	public function test_issue01() {
		$n = new ScripturNum('3Jo11');
		$this->assertEquals('3Jo11', $n->getAbbrev());
	}

    public function test_issue02_01() {
        $this->expectException('\ScripturNum\ScripturNumException');
        $this->expectExceptionMessage('There are not that many books in the Bible.');

        new ScripturNum(4496293888);
    }

	public function test_issue02_02() {
		$this->expectException('\ScripturNum\ScripturNumException');
		$this->expectExceptionMessage('There are not that many verses in this book.');

		new ScripturNum(739860913);
	}

	public function test_issue03_01() { // when a string is supposed to be created with bad settings
		$this->expectException('\ScripturNum\ScripturNumException');
		$this->expectExceptionMessage('Invalid key for creating a string.');

		$n = new ScripturNum('Ro 1-8');

		$n->getStringWithSettings('settings that do not exist');
	}

	public function test_issue03_02() { // when a default setting is overridden.
		$n = new ScripturNum('Ro 1-8');
		ScripturNum::setStringSettings('long', ['space' => ' and Greeks ']);

		$this->assertEquals("Romans and Greeks 1-8", $n->getLongString());

        ScripturNum::setStringSettings('long', ['space' => ' ']); // reset to prevent other tests failing
	}

	public function test_issue03_03() { // New settings, incomplete
		$this->expectException('\ScripturNum\ScripturNumException');
		$this->expectExceptionMessage('Invalid space character.');

		$n = new ScripturNum('Ro 1-8');
		ScripturNum::setStringSettings('testSettings_03_03', []);
		$n->getStringWithSettings('testSettings_03_03');
	}

	public function test_issue04() {
		$this->expectException('\ScripturNum\ScripturNumException');
		$this->expectExceptionMessage('Unintelligible Reference');

		new ScripturNum('Ps1:3-2');
	}

	public function test_issue05() {
		$n = new ScripturNum(301993985);
		$this->assertEquals(301993985, $n->getInt());
	}

    public function test_issue08_01() { // invalid separation character.
        $this->expectException('\ScripturNum\ScripturNumException');
        $this->expectExceptionMessage('Invalid chapter-verse separation character.');

        $n = new ScripturNum('Ro 1-8');
        ScripturNum::setStringSettings('testSettings_08_01', ['space' => ' ']);
        $n->getStringWithSettings('testSettings_08_01');
    }

    public function test_issue08_02() { // invalid range character.
        $this->expectException('\ScripturNum\ScripturNumException');
        $this->expectExceptionMessage('Invalid range character.');

        $n = new ScripturNum('Ro 1-8');
        ScripturNum::setStringSettings('testSettings_08_02', ['space' => ' ', 'cvsep' => ' ']);
        $n->getStringWithSettings('testSettings_08_02');
    }

    public function test_issue08_03() { // invalid separation character.
        $this->expectException('\ScripturNum\ScripturNumException');
        $this->expectExceptionMessage('Invalid name offset.');

        $n = new ScripturNum('Ro 1-8');
        ScripturNum::setStringSettings('testSettings_08_03', ['space' => ' ', 'cvsep' => ' ', 'range' => ' ']);
        $n->getStringWithSettings('testSettings_08_03');
    }

    public function test_issue08_04() { // invalid separation character.
        $this->expectException('\ScripturNum\ScripturNumException');
        $this->expectExceptionMessage('Plurality is not defined.');

        $n = new ScripturNum('Ro 1-8');
        ScripturNum::setStringSettings('testSettings_08_04', ['space' => ' ', 'cvsep' => ':', 'range' => '-', 'names' => 10]);
        $n->getStringWithSettings('testSettings_08_04');
    }

    public function test_issue08_05() { // invalid plurality.
        $this->expectException('\ScripturNum\ScripturNumException');
        $this->expectExceptionMessage('Plurality is not defined.');

        $n = new ScripturNum('Ro 1-8');
        ScripturNum::setStringSettings('testSettings_08_05', ['space' => ' ', 'cvsep' => ':', 'range' => '-', 'names' => 10]);
        $n->getStringWithSettings('testSettings_08_05');
    }

    public function test_issue08_06() { // invalid separation character.
        $n = new ScripturNum('Ro 1-8');
        ScripturNum::setStringSettings('testSettings_08_06', ['space' => ' ', 'cvsep' => ':', 'range' => '-', 'names' => 10, 'plurl' => true]);
        $this->assertEquals('Rmns 1-8', $n->getStringWithSettings('testSettings_08_06'));
    }
}
