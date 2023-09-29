<?php /** @noinspection PhpParamsInspection */

namespace ScripturNumTests;

use PHPUnit\Framework\TestCase;
use ScripturNum\ScripturNum;

/**
 * This test suite should mirror the README to validate that all examples in the README are true.
 */
class ReadmeTests extends TestCase
{
	public function test_parseExample() {
		$s = new ScripturNum('Romans 1-8');
		var_dump($s);
		$this->assertEquals("Romans 1-8", $s->__toString());
		// make sure the right int is in there
		$this->expectOutputRegex("/[\w\W]+738197728[\w\W]+/");
	}

	public function test_parseNumExample() {
		$s = new ScripturNum(739119536);
		echo $s;

		$this->expectOutputString("Romans 9-16");
	}

	public function test_otherNumericalExample() {
		$n = ScripturNum::newFromInts(40, 8, null, 9);
		echo $n;

		$this->expectOutputString("Matthew 8-9");
	}

	public function test_stringOutExample() {
		echo new ScripturNum('1Jo1:9');

		$this->expectOutputString("1 John 1:9");
	}

	public function test_stringOutAbbrevExample() {
		echo (new ScripturNum('John 3:16'))->getAbbrev();

		$this->expectOutputString("Jn3.16");
	}

	public function test_inclusiveSqlExample() {
		$s = new ScripturNum('Romans 8');
        $wheres = $s->toSqlInclusive('Scripture');

		$queryString = "SELECT * FROM scriptureData WHERE $wheres";

		$this->assertEquals("SELECT * FROM scriptureData WHERE ( (Scripture & 4278190080) = 738197504 AND (Scripture & 16773120) <= 917504 AND (Scripture & 4095) >= 186 )", $queryString);
	}

	public function test_zeroIndex() {
		echo (new ScripturNum(0))->getLongString();

		$this->expectOutputString("Genesis 1:1");
	}
}