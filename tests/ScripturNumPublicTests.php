<?php /** @noinspection PhpParamsInspection */

namespace ScripturNumTests;

use Error;
use PHPUnit\Framework\TestCase;
use ScripturNum\Bible;
use ScripturNum\ScripturNum;
use ScripturNum\ScripturNumException;

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

	public function test_multiplePsalmsArePlural(){
		$n = new ScripturNum('Ps101.1-102.3');
		$this->assertEquals('Psalms 101:1-102:3', $n->getLongString());
	}

	public function test_singlePsalmIsSingular(){
		$n = new ScripturNum('Ps101.1-3');
		$this->assertEquals('Psalm 101:1-3', $n->getLongString());
	}

	public function test_intToCats() {
		$concatStart = 0;
		$concatEnd = 0;
		ScripturNum::intToConcats(301993985, $concatStart, $concatEnd);
		$this->assertEquals(19001002, $concatStart);
		$this->assertEquals(19001002, $concatEnd);
	}



	public function test_issue01() {
		$n = new ScripturNum('3Jo11');
		$this->assertEquals('3Jo11', $n->getAbbrev());
	}

	public function test_issue02_01(){
		$this->expectException(ScripturNumException::class);
		$this->expectExceptionMessage('There are not that many books in the Bible.');

		new ScripturNum(4496293888);
	}

	public function test_issue02_02() {
		$this->expectException(ScripturNumException::class);
		$this->expectExceptionMessage('There are not that many verses in this book.');

		new ScripturNum(739860913);
	}

	public function test_settingOption_doesNotExist() { // when a string is supposed to be created with bad settings
		$this->expectException(ScripturNumException::class);
		$this->expectExceptionMessage('Invalid key for creating a string.');

		$n = new ScripturNum('Ro 1-8');

		$n->toString(['settings' => 'settings that do not exist']);
	}

	public function test_issue03_01() { // when a string is supposed to be created with bad settings
		$this->expectException(ScripturNumException::class);
		$this->expectExceptionMessage('Invalid key for creating a string.');

		$n = new ScripturNum('Ro 1-8');

		$n->toString('settings that do not exist');
	}

	public function test_issue03_02() { // when a default setting is overridden.
		$n = new ScripturNum('Ro 1-8');
		ScripturNum::setStringSettings('long', ['space' => ' and Greeks ']);

		$this->assertEquals("Romans and Greeks 1-8", $n->getLongString());

		ScripturNum::setStringSettings('long', ['space' => ' ']); // reset to prevent other tests failing
	}

	public function test_issue03_03() { // New settings, incomplete
		$this->expectException(ScripturNumException::class);
		$this->expectExceptionMessage('Invalid space character.');

		$n = new ScripturNum('Ro 1-8');
		ScripturNum::setStringSettings('testSettings_03_03', []);
		$n->toString('testSettings_03_03');
	}

	public function test_issue04() {
		$this->expectException(ScripturNumException::class);
		$this->expectExceptionMessage('Unintelligible Reference');

		new ScripturNum('Ps1:3-2');
	}

	public function test_issue05() {
		$n = new ScripturNum(301993985);
		$this->assertEquals(301993985, $n->getInt());
	}

	public function test_issue08_01() { // invalid separation character.
		$this->expectException(ScripturNumException::class);
		$this->expectExceptionMessage('Invalid chapter-verse separation character.');

		$n = new ScripturNum('Ro 1-8');
		ScripturNum::setStringSettings('testSettings_08_01', ['space' => ' ']);
		$n->toString('testSettings_08_01');
	}

	public function test_issue08_02() { // invalid range character.
		$this->expectException(ScripturNumException::class);
		$this->expectExceptionMessage('Invalid range character.');

		$n = new ScripturNum('Ro 1-8');
		ScripturNum::setStringSettings('testSettings_08_02', ['space' => ' ', 'cvsep' => ' ']);
		$n->toString('testSettings_08_02');
	}

	public function test_issue08_03() { // invalid separation character.
		$this->expectException(ScripturNumException::class);
		$this->expectExceptionMessage('Invalid name offset.');

		$n = new ScripturNum('Ro 1-8');
		ScripturNum::setStringSettings('testSettings_08_03', ['space' => ' ', 'cvsep' => ' ', 'range' => ' ']);
		$n->toString('testSettings_08_03');
	}

	public function test_issue08_04() { // invalid separation character.
		$this->expectException(ScripturNumException::class);
		$this->expectExceptionMessage('Plurality is not defined.');

		$n = new ScripturNum('Ro 1-8');
		ScripturNum::setStringSettings('testSettings_08_04', ['space' => ' ', 'cvsep' => ':', 'range' => '-', 'names' => 10]);
		$n->toString('testSettings_08_04');
	}

	public function test_issue08_05() { // invalid plurality.
		$this->expectException(ScripturNumException::class);
		$this->expectExceptionMessage('Plurality is not defined.');

		$n = new ScripturNum('Ro 1-8');
		ScripturNum::setStringSettings('testSettings_08_05', ['space' => ' ', 'cvsep' => ':', 'range' => '-', 'names' => 10]);
		$n->toString('testSettings_08_05');
	}

	public function test_issue08_06() { // invalid separation character.
		$n = new ScripturNum('Ro 1-8');
		ScripturNum::setStringSettings('testSettings_08_06', ['space' => ' ', 'cvsep' => ':', 'range' => '-', 'names' => 10, 'plurl' => true]);
		$this->assertEquals('Rmns 1-8', $n->toString('testSettings_08_06'));
	}

	public function test_bits() {
		$n = new ScripturNum((5 << 24) + (4 << 12) + 221);
		$i = $n->getInt();
		$this->assertEquals('Joshua 1:5-10:9', $n->getLongString());
		$this->assertEquals((5 << 24), $i & ScripturNum::BOOK_MASK);
		$this->assertEquals((4 << 12), $i & ScripturNum::START_MASK);
		$this->assertEquals(221, $i & ScripturNum::END_MASK);
	}

	public function test_containsInt_true() {
		$bigger = new ScripturNum("Exodus 20");
		$smaller = new ScripturNum("Exodus 20:14");
		$test = $bigger->containsInt($smaller->getInt());
		$this->assertTrue($test);
	}

	public function test_containsInt_wrongBook() {
		$bigger = new ScripturNum("Exodus 20");
		$smaller = new ScripturNum("Genesis 20:14");
		$test = $bigger->containsInt($smaller->getInt());
		$this->assertFalse($test);
	}

	public function test_containsInt_differentPassage() {
		$bigger = new ScripturNum("Exodus 20");
		$smaller = new ScripturNum("Exodus 2:14-20");
		$test = $bigger->containsInt($smaller->getInt());
		$this->assertFalse($test);
	}

	public function test_containsInt_overlapLow() {
		$bigger = new ScripturNum("Exodus 20");
		$smaller = new ScripturNum("Exodus 19-20");
		$test = $bigger->containsInt($smaller->getInt());
		$this->assertFalse($test);
	}

	public function test_containsInt_overlapBoth() {
		$bigger = new ScripturNum("Exodus 20");
		$smaller = new ScripturNum("Exodus 19-21");
		$test = $bigger->containsInt($smaller->getInt());
		$this->assertFalse($test);
	}

	public function test_containsInt_overlapHigh() {
		$bigger = new ScripturNum("Exodus 20");
		$smaller = new ScripturNum("Exodus 20-21");
		$test = $bigger->containsInt($smaller->getInt());
		$this->assertFalse($test);
	}

	public function test_isWithinInt_true() {
		$bigger = new ScripturNum("Exodus 20");
		$smaller = new ScripturNum("Exodus 20:14");
		$test = $smaller->isWithinInt($bigger->getInt());
		$this->assertTrue($test);
	}

	public function test_isWithinInt_wrongBook() {
		$bigger = new ScripturNum("Exodus 20");
		$smaller = new ScripturNum("Genesis 20:14");
		$test = $smaller->isWithinInt($bigger->getInt());
		$this->assertFalse($test);
	}

	public function test_isWithinInt_differentPassageLow() {
		$bigger = new ScripturNum("Exodus 20");
		$smaller = new ScripturNum("Exodus 2:14-2:20");
		$test = $smaller->isWithinInt($bigger->getInt());
		$this->assertFalse($test);
	}

	public function test_isWithinInt_differentPassageHigh() {
		$bigger = new ScripturNum("Exodus 20");
		$smaller = new ScripturNum("Exodus 21");
		$test = $smaller->isWithinInt($bigger->getInt());
		$this->assertFalse($test);
	}

	public function test_isWithinInt_overlapLow() {
		$bigger = new ScripturNum("Exodus 19-20");
		$smaller = new ScripturNum("Exodus 20");
		$test = $smaller->isWithinInt($bigger->getInt());
		$this->assertTrue($test);
	}

	public function test_isWithinInt_overlapBoth() {
		$bigger = new ScripturNum("Exodus 19-21");
		$smaller = new ScripturNum("Exodus 20");
		$test = $smaller->isWithinInt($bigger->getInt());
		$this->assertTrue($test);
	}

	public function test_isWithinInt_overlapHigh() {
		$bigger = new ScripturNum("Exodus 20-21");
		$smaller = new ScripturNum("Exodus 20");
		$test = $smaller->isWithinInt($bigger->getInt());
		$this->assertTrue($test);
	}

	public function test_overlapsWithInt_true() {
		$bigger = new ScripturNum("Exodus 20");
		$smaller = new ScripturNum("Exodus 20:14");
		$test = $bigger->overlapsWithInt($smaller->getInt());
		$this->assertTrue($test);
	}

	public function test_overlapsWithInt_wrongBook() {
		$bigger = new ScripturNum("Exodus 20");
		$smaller = new ScripturNum("Genesis 20:14");
		$test = $bigger->overlapsWithInt($smaller->getInt());
		$this->assertFalse($test);
	}

	public function test_overlapsWithInt_differentPassageLow() {
		$bigger = new ScripturNum("Exodus 20");
		$smaller = new ScripturNum("Exodus 2:14-20");
		$test = $bigger->overlapsWithInt($smaller->getInt());
		$this->assertFalse($test);
	}

	public function test_overlapsWithInt_differentPassageHigh() {
		$bigger = new ScripturNum("Exodus 20");
		$smaller = new ScripturNum("Exodus 21");
		$test = $bigger->overlapsWithInt($smaller->getInt());
		$this->assertFalse($test);
	}

	public function test_overlapsWithInt_overlapLow() {
		$bigger = new ScripturNum("Exodus 20");
		$smaller = new ScripturNum("Exodus 19-20");
		$test = $bigger->overlapsWithInt($smaller->getInt());
		$this->assertTrue($test);
	}

	public function test_overlapsWithInt_overlapBoth() {
		$bigger = new ScripturNum("Exodus 20");
		$smaller = new ScripturNum("Exodus 19-21");
		$test = $bigger->overlapsWithInt($smaller->getInt());
		$this->assertTrue($test);
	}

	public function test_overlapsWithInt_overlapHigh() {
		$bigger = new ScripturNum("Exodus 20");
		$smaller = new ScripturNum("Exodus 20-21");
		$test = $bigger->overlapsWithInt($smaller->getInt());
		$this->assertTrue($test);
	}

	public function test_overlapsOrAdjacentInt_wrongBook() {
		$bigger = new ScripturNum("Exodus 20");
		$smaller = new ScripturNum("Genesis 20:14");
		$test = $bigger->overlapsOrAdjacentInt($smaller->getInt());
		$this->assertFalse($test);
	}

	public function test_overlapsOrAdjacentInt_differentPassageLow() {
		$bigger = new ScripturNum("Exodus 20");
		$smaller = new ScripturNum("Exodus 2:14-20");
		$test = $bigger->overlapsOrAdjacentInt($smaller->getInt());
		$this->assertFalse($test);
	}

	public function test_overlapsOrAdjacentInt_adjacentLow() {
		$bigger = new ScripturNum("Exodus 20");
		$smaller = new ScripturNum("Exodus 19");
		$test = $bigger->overlapsOrAdjacentInt($smaller->getInt());
		$this->assertTrue($test);
	}

	public function test_overlapsOrAdjacentInt_differentPassageHigh() {
		$bigger = new ScripturNum("Exodus 20");
		$smaller = new ScripturNum("Exodus 22");
		$test = $bigger->overlapsOrAdjacentInt($smaller->getInt());
		$this->assertFalse($test);
	}

	public function test_overlapsOrAdjacentInt_adjacentHigh() {
		$bigger = new ScripturNum("Exodus 20");
		$smaller = new ScripturNum("Exodus 21");
		$test = $bigger->overlapsOrAdjacentInt($smaller->getInt());
		$this->assertTrue($test);
	}

	public function test_overlapsOrAdjacentInt_oneHigh() {
		$bigger = new ScripturNum("Psalm 1:5");
		$smaller = new ScripturNum("Psalm 1:7");
		$test = $bigger->overlapsOrAdjacentInt($smaller->getInt());
		$this->assertFalse($test);
	}

	public function test_overlapsOrAdjacentInt_oneLow() {
		$bigger = new ScripturNum("Psalm 1:5");
		$smaller = new ScripturNum("Psalm 1:3");
		$test = $bigger->overlapsOrAdjacentInt($smaller->getInt());
		$this->assertFalse($test);
	}

	public function test_overlapsOrAdjacentInt_overlapLow() {
		$bigger = new ScripturNum("Exodus 20");
		$smaller = new ScripturNum("Exodus 19-20");
		$test = $bigger->overlapsOrAdjacentInt($smaller->getInt());
		$this->assertTrue($test);
	}

	public function test_overlapsOrAdjacentInt_overlapBoth() {
		$bigger = new ScripturNum("Exodus 20");
		$smaller = new ScripturNum("Exodus 19-21");
		$test = $bigger->overlapsOrAdjacentInt($smaller->getInt());
		$this->assertTrue($test);
	}

	public function test_overlapsOrAdjacentInt_overlapHigh() {
		$bigger = new ScripturNum("Exodus 20");
		$smaller = new ScripturNum("Exodus 20-21");
		$test = $bigger->overlapsOrAdjacentInt($smaller->getInt());
		$this->assertTrue($test);
	}

	public function test_combineWithInt_adjacentLow() {
		$bigger = new ScripturNum("Matthew 5-6");
		$smaller = new ScripturNum("Matthew 3-4");
		$test = $bigger->combineWithInt($smaller->getInt());
		$sn = new ScripturNum($test);
		$this->assertEquals("Matthew 3-6", $sn->getLongString());
	}

	public function test_combineWithInt_notAdjacent() {
		$this->expectExceptionMessage("Cannot combine passages that aren't overlapping or adjacent.");
		$this->expectException(ScripturNumException::class);
		$bigger = new ScripturNum("Luke 2");
		$smaller = new ScripturNum("Luke 4");
		$bigger->combineWithInt($smaller->getInt());
	}

	public function test_combineWithInt_overlapHigh() {
		$bigger = new ScripturNum("Exodus 19-20");
		$smaller = new ScripturNum("Exodus 20-21");
		$test = $bigger->combineWithInt($smaller->getInt());
		$sn = new ScripturNum($test);
		$this->assertEquals("Exodus 19-21", $sn->getLongString());
	}



	public function test_contains_true() {
		$bigger = new ScripturNum("Exodus 20");
		$smaller = new ScripturNum("Exodus 20:14");
		$test = $bigger->contains($smaller);
		$this->assertTrue($test);
	}

	public function test_contains_wrongBook() {
		$bigger = new ScripturNum("Exodus 20");
		$smaller = new ScripturNum("Genesis 20:14");
		$test = $bigger->contains($smaller);
		$this->assertFalse($test);
	}

	public function test_contains_differentPassage() {
		$bigger = new ScripturNum("Exodus 20");
		$smaller = new ScripturNum("Exodus 2:14-20");
		$test = $bigger->contains($smaller);
		$this->assertFalse($test);
	}

	public function test_contains_overlapLow() {
		$bigger = new ScripturNum("Exodus 20");
		$smaller = new ScripturNum("Exodus 19-20");
		$test = $bigger->contains($smaller);
		$this->assertFalse($test);
	}

	public function test_contains_overlapBoth() {
		$bigger = new ScripturNum("Exodus 20");
		$smaller = new ScripturNum("Exodus 19-21");
		$test = $bigger->contains($smaller);
		$this->assertFalse($test);
	}

	public function test_contains_overlapHigh() {
		$bigger = new ScripturNum("Exodus 20");
		$smaller = new ScripturNum("Exodus 20-21");
		$test = $bigger->contains($smaller);
		$this->assertFalse($test);
	}

	public function test_isWithin_true() {
		$bigger = new ScripturNum("Exodus 20");
		$smaller = new ScripturNum("Exodus 20:14");
		$test = $smaller->isWithin($bigger);
		$this->assertTrue($test);
	}

	public function test_isWithin_wrongBook() {
		$bigger = new ScripturNum("Exodus 20");
		$smaller = new ScripturNum("Genesis 20:14");
		$test = $smaller->isWithin($bigger);
		$this->assertFalse($test);
	}

	public function test_isWithin_differentPassageLow() {
		$bigger = new ScripturNum("Exodus 20");
		$smaller = new ScripturNum("Exodus 2:14-2:20");
		$test = $smaller->isWithin($bigger);
		$this->assertFalse($test);
	}

	public function test_isWithin_differentPassageHigh() {
		$bigger = new ScripturNum("Exodus 20");
		$smaller = new ScripturNum("Exodus 21");
		$test = $smaller->isWithin($bigger);
		$this->assertFalse($test);
	}

	public function test_isWithin_overlapLow() {
		$bigger = new ScripturNum("Exodus 19-20");
		$smaller = new ScripturNum("Exodus 20");
		$test = $smaller->isWithin($bigger);
		$this->assertTrue($test);
	}

	public function test_isWithin_overlapBoth() {
		$bigger = new ScripturNum("Exodus 19-21");
		$smaller = new ScripturNum("Exodus 20");
		$test = $smaller->isWithin($bigger);
		$this->assertTrue($test);
	}

	public function test_isWithin_overlapHigh() {
		$bigger = new ScripturNum("Exodus 20-21");
		$smaller = new ScripturNum("Exodus 20");
		$test = $smaller->isWithin($bigger);
		$this->assertTrue($test);
	}

	public function test_overlapsWith_true() {
		$bigger = new ScripturNum("Exodus 20");
		$smaller = new ScripturNum("Exodus 20:14");
		$test = $bigger->overlapsWith($smaller);
		$this->assertTrue($test);
	}

	public function test_overlapsWith_wrongBook() {
		$bigger = new ScripturNum("Exodus 20");
		$smaller = new ScripturNum("Genesis 20:14");
		$test = $bigger->overlapsWith($smaller);
		$this->assertFalse($test);
	}

	public function test_overlapsWith_differentPassageLow() {
		$bigger = new ScripturNum("Exodus 20");
		$smaller = new ScripturNum("Exodus 2:14-20");
		$test = $bigger->overlapsWith($smaller);
		$this->assertFalse($test);
	}

	public function test_overlapsWith_differentPassageHigh() {
		$bigger = new ScripturNum("Exodus 20");
		$smaller = new ScripturNum("Exodus 21");
		$test = $bigger->overlapsWith($smaller);
		$this->assertFalse($test);
	}

	public function test_overlapsWith_overlapLow() {
		$bigger = new ScripturNum("Exodus 20");
		$smaller = new ScripturNum("Exodus 19-20");
		$test = $bigger->overlapsWith($smaller);
		$this->assertTrue($test);
	}

	public function test_overlapsWith_overlapBoth() {
		$bigger = new ScripturNum("Exodus 20");
		$smaller = new ScripturNum("Exodus 19-21");
		$test = $bigger->overlapsWith($smaller);
		$this->assertTrue($test);
	}

	public function test_overlapsWith_overlapHigh() {
		$bigger = new ScripturNum("Exodus 20");
		$smaller = new ScripturNum("Exodus 20-21");
		$test = $bigger->overlapsWith($smaller);
		$this->assertTrue($test);
	}

	public function test_overlapsOrAdjacent_wrongBook() {
		$bigger = new ScripturNum("Exodus 20");
		$smaller = new ScripturNum("Genesis 20:14");
		$test = $bigger->overlapsOrAdjacent($smaller);
		$this->assertFalse($test);
	}

	public function test_overlapsOrAdjacent_differentPassageLow() {
		$bigger = new ScripturNum("Exodus 20");
		$smaller = new ScripturNum("Exodus 2:14-20");
		$test = $bigger->overlapsOrAdjacent($smaller);
		$this->assertFalse($test);
	}

	public function test_overlapsOrAdjacent_adjacentLow() {
		$bigger = new ScripturNum("Exodus 20");
		$smaller = new ScripturNum("Exodus 19");
		$test = $bigger->overlapsOrAdjacent($smaller);
		$this->assertTrue($test);
	}

	public function test_overlapsOrAdjacent_differentPassageHigh() {
		$bigger = new ScripturNum("Exodus 20");
		$smaller = new ScripturNum("Exodus 22");
		$test = $bigger->overlapsOrAdjacent($smaller);
		$this->assertFalse($test);
	}

	public function test_overlapsOrAdjacent_adjacentHigh() {
		$bigger = new ScripturNum("Exodus 20");
		$smaller = new ScripturNum("Exodus 21");
		$test = $bigger->overlapsOrAdjacent($smaller);
		$this->assertTrue($test);
	}

	public function test_overlapsOrAdjacent_oneHigh() {
		$bigger = new ScripturNum("Psalm 1:5");
		$smaller = new ScripturNum("Psalm 1:7");
		$test = $bigger->overlapsOrAdjacent($smaller);
		$this->assertFalse($test);
	}

	public function test_overlapsOrAdjacent_oneLow() {
		$bigger = new ScripturNum("Psalm 1:5");
		$smaller = new ScripturNum("Psalm 1:3");
		$test = $bigger->overlapsOrAdjacent($smaller);
		$this->assertFalse($test);
	}

	public function test_overlapsOrAdjacent_overlapLow() {
		$bigger = new ScripturNum("Exodus 20");
		$smaller = new ScripturNum("Exodus 19-20");
		$test = $bigger->overlapsOrAdjacent($smaller);
		$this->assertTrue($test);
	}

	public function test_overlapsOrAdjacent_overlapBoth() {
		$bigger = new ScripturNum("Exodus 20");
		$smaller = new ScripturNum("Exodus 19-21");
		$test = $bigger->overlapsOrAdjacent($smaller);
		$this->assertTrue($test);
	}

	public function test_overlapsOrAdjacent_overlapHigh() {
		$bigger = new ScripturNum("Exodus 20");
		$smaller = new ScripturNum("Exodus 20-21");
		$test = $bigger->overlapsOrAdjacent($smaller);
		$this->assertTrue($test);
	}

	public function test_combineWith_adjacentLow() {
		$bigger = new ScripturNum("Matthew 5-6");
		$smaller = new ScripturNum("Matthew 3-4");
		$test = $bigger->combineWith($smaller);
		$this->assertEquals("Matthew 3-6", $test->getLongString());
	}

	public function test_combineWith_notAdjacent() {
		$this->expectExceptionMessage("Cannot combine passages that aren't overlapping or adjacent.");
		$this->expectException(ScripturNumException::class);
		$bigger = new ScripturNum("Luke 2");
		$smaller = new ScripturNum("Luke 4");
		$bigger->combineWith($smaller);
	}

	public function test_combineWith_overlapHigh() {
		$bigger = new ScripturNum("Exodus 19-20");
		$smaller = new ScripturNum("Exodus 20-21");
		$test = $bigger->combineWith($smaller);
		$this->assertEquals("Exodus 19-21", $test->getLongString());
	}




	public function test_InclusiveSql() {
		$n = new ScripturNum((5 << 24) + (4 << 12) + 221);
		$this->assertEquals('( (theColumn & 4278190080) = 83886080 AND (theColumn & 16773120) <= 905216 AND (theColumn & 4095) >= 4 )', $n->toSqlInclusive("theColumn"));
	}

	public function test_ExclusiveSql() {
		$n = new ScripturNum((5 << 24) + (4 << 12) + 221);
		$this->assertEquals('( (theColumn & 4278190080) = 83886080 AND (theColumn & 16773120) >= 16384 AND (theColumn & 4095) <= 221 )', $n->toSqlExclusive("theColumn"));
	}

	public function test_ExclusiveSql_Single() {
		$n = new ScripturNum((8 << 24) + (255 << 12) + 255);
		$this->assertEquals('theColumn = 135262463', $n->toSqlExclusive("theColumn"));
	}

	public function test_validateBookNames_exValid() {
		$res = Bible::validateBookNamesEx();
		$this->assertTrue($res);
	}

	public function test_validateBookNames_boolValid() {
		$res = Bible::validateBookNames();
		$this->assertTrue($res);
	}

	public function test_validateBookNames_arrValid() {
		$res = Bible::validateBookNames(true);
		$this->assertTrue(is_array($res));
		$this->assertCount(0, $res);
	}

	public function test_validateBookNames_exInvalid() {
		$this->expectException(ScripturNumException::class);
		$this->expectExceptionMessage('Duplicate book names exist: Fake');
		ErrantBible::validateBookNamesEx();
	}

	public function test_validateBookNames_boolInvalid() {
		$res = ErrantBible::validateBookNames();
		$this->assertFalse($res);
	}

	public function test_validateBookNames_arrInvalid() {
		$res = ErrantBible::validateBookNames(true);
		$this->assertTrue(is_array($res));
		$this->assertCount(1, $res);
	}

	public function test_wholeChapterSingle() {
		$a = new ScripturNum("Exodus 20:10-12");
		$b = $a->getWholeChapters();
		$this->assertEquals("Exodus 20", $b->getLongString());
	}

	public function test_wholeChapterSingleFromSingle() {
		$a = new ScripturNum("3 John 2-5");
		$b = $a->getWholeChapters();
		$this->assertEquals("3 John", $b->getLongString());
	}

	public function test_wholeChapterSingleAlready() {
		$a = new ScripturNum("Romans 1");
		$b = $a->getWholeChapters();
		$this->assertEquals($a, $b);
		$this->assertEquals($a->getLongString(), $a->getLongString());
	}

	public function test_nextChapterSingle() {
		$a = new ScripturNum("Exodus 20:10-12");
		$b = $a->getNextChapter();
		$this->assertEquals("Exodus 21", $b->getLongString());
	}

	public function test_nextChapterMultiple() {
		$a = new ScripturNum("Exodus 2-3");
		$b = $a->getNextChapter();
		$this->assertEquals("Exodus 4", $b->getLongString());
	}

	public function test_nextChapterSingleFromSingle() {
		$a = new ScripturNum("3 John 2-5");
		$b = $a->getNextChapter();
		$this->assertEquals("Jude", $b->getLongString());
	}

	public function test_nextChapterAcrossBooks() {
		$a = new ScripturNum("Acts 28");
		$b = $a->getNextChapter();
		$this->assertEquals("Romans 1", $b->getLongString());
	}

	public function test_nextChapterAtEnd() {
		$a = new ScripturNum("Rev 22");
		$this->expectException(ScripturNumException::class);
		$this->expectExceptionMessage('There are no more chapters in the Bible.');
		$a->getNextChapter();
	}


	public function test_prevChapterSingle() {
		$a = new ScripturNum("Exodus 20:10-12");
		$b = $a->getPrevChapter();
		$this->assertEquals("Exodus 19", $b->getLongString());
	}

	public function test_prevChapterMultiple() {
		$a = new ScripturNum("Exodus 2-3");
		$b = $a->getPrevChapter();
		$this->assertEquals("Exodus 1", $b->getLongString());
	}

	public function test_prevChapterSingleFromSingle() {
		$a = new ScripturNum("3 John 2-5");
		$b = $a->getPrevChapter();
		$this->assertEquals("2 John", $b->getLongString());
	}

	public function test_prevChapterAcrossBooks() {
		$a = new ScripturNum("Romans 1");
		$b = $a->getPrevChapter();
		$this->assertEquals("Acts 28", $b->getLongString());
	}

	public function test_prevChapterAtEnd() {
		$a = new ScripturNum("Gen 1");
		$this->expectException(ScripturNumException::class);
		$this->expectExceptionMessage('There are no more chapters in the Bible.');
		$a->getPrevChapter();
	}

	public function test_extractFromString_chapter() {
		$r = ScripturNum::extractFromString("let's turn in our bibles to Leviticus chapter 10 and verse 3.");
		$this->assertCount(1, $r);
		$this->assertEquals("Leviticus 10",$r[0]->getLongString());
	}

	public function test_extractFromString_amp_chapters() {
		$r = ScripturNum::extractFromString("See Romans 8-9 & 16 for more information");
		$this->assertCount(2, $r);
		$this->assertEquals("Romans 8-9",$r[0]->getLongString());
		$this->assertEquals("Romans 16",$r[1]->getLongString());
	}

	public function test_extractFromString_amp_verses() {
		$r = ScripturNum::extractFromString("See Romans 8:9 & 16");
		$this->assertCount(2, $r);
		$this->assertEquals("Romans 8:9",$r[0]->getLongString());
		$this->assertEquals("Romans 8:16",$r[1]->getLongString());
	}

	public function test_extractFromString_amp_dual() {
		$r = ScripturNum::extractFromString("See Romans 8:1-9:10 & 15:1-16:9");
		$this->assertCount(2, $r);
		$this->assertEquals("Romans 8:1-9:10",$r[0]->getLongString());
		$this->assertEquals("Romans 15:1-16:9",$r[1]->getLongString());
	}

	public function test_extractFromString_words_chaptersOnRight() {
		$r = ScripturNum::extractFromString("See Romans 8 and 15 through 16");
		$this->assertCount(2, $r);
		$this->assertEquals("Romans 8",$r[0]->getLongString());
		$this->assertEquals("Romans 15-16",$r[1]->getLongString());
	}

	public function test_extractFromString_words_versesOnRight() {
		$r = ScripturNum::extractFromString("See Romans 8:1 and 15 through 16");
		$this->assertCount(2, $r);
		$this->assertEquals("Romans 8:1",$r[0]->getLongString());
		$this->assertEquals("Romans 8:15-16",$r[1]->getLongString());
	}

	public function test_extractFromString_commonNames() {
		$r = ScripturNum::extractFromString("Consider Matthew Henry's commentaries.");
		$this->assertCount(0, $r);
	}

	public function test_extractFromString_bookName() {
		$r = ScripturNum::extractFromString("We're going to start a series on first John this fall.");
		$this->assertCount(1, $r);
		$this->assertEquals("1 John",$r[0]->getLongString());
	}

	public function test_extractFromString_bookNameExcluded() {
		$r = ScripturNum::extractFromString("We're going to start a series on first John this fall.", true);
		$this->assertCount(0, $r);
	}

	public function test_extractFromString_ArrayCombining() {
		$r = ScripturNum::extractFromString("Genesis 1, 2, and 3 are essential for understanding the gospel.");
		$this->assertCount(1, $r);
	}

	public function test_extractFromString_CheckJohns() {
		$r = ScripturNum::extractFromString("1 Jn 1:9 and Jn 1:9 are thematically related.");
		$test1 = $r[0]->overlapsWith($r[1]);
		$this->assertFalse($test1);
		$this->assertEquals("1 John 1:9", $r[0]->getLongString());
		$this->assertEquals("John 1:9", $r[1]->getLongString());
		$this->assertCount(2, $r);
	}

	public function test_stringToInts_Ex_OutOfBounds_throw() {
		$this->expectException(ScripturNumException::class);
		$this->expectExceptionMessage('A chapter was requested that does not exist within the requested book.');
		ScripturNum::stringToInts('Genesis 110, 120');
	}

	public function test_stringToInts_Ex_OutOfBounds_return() {
		$ex = [];
		ScripturNum::stringToInts('Genesis 110, 120', $ex);
		$this->assertCount(2, $ex);
		$this->assertEquals(ScripturNumException::class, get_class($ex[0]));
		$this->assertEquals(ScripturNumException::class, get_class($ex[1]));
	}

	public function test_stringToInts_Ex_invalidBook_throw() {
		$this->expectException(ScripturNumException::class);
		$this->expectExceptionMessage('Book name is invalid.');
		ScripturNum::stringToInts('Asdf 2');
	}

	public function test_stringToInts_Ex_invalidBook_return() {
		$ex = [];
		ScripturNum::stringToInts('Asdf 2', $ex);
		$this->assertCount(1, $ex);
		$this->assertEquals(ScripturNumException::class, get_class($ex[0]));
	}

	public function test_getter() {
		$s = new ScripturNum(135252); // Genesis 2:3-4:5
		$this->assertEquals(135252,$s->getInt());
		$this->assertEquals(135252,$s->int);
		$this->assertEquals(1,$s->book);
		$this->assertEquals(2,$s->startCh);
		$this->assertEquals(3,$s->startV);
		$this->assertEquals(4,$s->endCh);
		$this->assertEquals(5,$s->endV);
		$this->expectException(Error::class);
		$s->doesNotExist;
	}
}
