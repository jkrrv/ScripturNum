<?php /** @noinspection PhpParamsInspection */

namespace ScripturNumTests;

use PHPUnit\Framework\TestCase;
use ScripturNum\ScripturNum;
use ScripturNum\ScripturNumArray;

class ScripturNumArrayTests extends TestCase
{
	public function test_construct_fromInts()
	{
		$a = new ScripturNumArray([738197728, 301993985]);
		$this->assertCount(2, $a);
	}

	public function test_construct_fromObjs()
	{
		$a = new ScripturNumArray([new ScripturNum(738197728), new ScripturNum(301993985)]);
		$this->assertCount(2, $a);
	}

	public function test_construct_fromStrings()
	{
		$a = new ScripturNumArray(["Romans 1-8", "Psalm 1:2"]);
		$this->assertCount(2, $a);
	}

	public function test_construct_fromMix()
	{
		$a = new ScripturNumArray([new ScripturNum(301993985), "Romans 1-8", 738197726]);
		$this->assertCount(2, $a);
	}

	public function test_iteration()
	{
		$a = new ScripturNumArray([738197728, 301993985]);
		$this->expectOutputString("Romans 1-8Psalm 1:2");
		foreach ($a as $s) {
			echo $s;
		}
	}

	public function test_iteration_assoc()
	{
		$a = new ScripturNumArray(['a' => 738197728, 'b' => 301993985]);
		$this->expectOutputString("aRomans 1-8bPsalm 1:2");
		foreach ($a as $k => $s) {
			echo $k . $s;
		}
	}

	public function test_offsetExists()
	{
		$a = new ScripturNumArray(['a' => 738197728, 'b' => 301993985]);
		$this->assertTrue(isset($a['a']));
		$this->assertFalse(isset($a[0]));
	}

	public function test_offsetUnset_assoc()
	{
		$a = new ScripturNumArray(['a' => 738197728, 'b' => 301993985]);
		$this->assertCount(2, $a);
		unset($a['a']);
		$this->assertCount(1, $a);
	}

	public function test_constructorWithInvalidObject()
	{
		$a = new ScripturNumArray([new ScripturNumDb()]);
		$this->assertCount(0, $a);
	}
}