<?php /** @noinspection PhpParamsInspection */

namespace ScripturNumTests;

use PHPUnit\Framework\TestCase;
use ScripturNum\ScripturNum;
use ScripturNum\ScripturNumArray;
use ScripturNum\ScripturNumException;
use function PHPUnit\Framework\assertEquals;

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
		$this->expectOutputString("Psalm 1:2Romans 1-8");
		foreach ($a as $s) {
			echo $s;
		}
	}

	public function test_iteration_assoc()
	{
		$a = new ScripturNumArray(['a' => 738197728, 'b' => 301993985]);
		$this->expectOutputString("bPsalm 1:2aRomans 1-8");
		foreach ($a as $k => $s) {
			echo $k . $s;
		}
	}

    public function test_getString_deprecated()
    {
        $a = new ScripturNumArray(['a' => 738197728, 'b' => 301993985]);
        $this->expectOutputString("Psalm 1:2, Romans 1-8");
        echo $a->getString();
    }

    public function test_getString_exception()
    {
        $n = new ScripturNumArray(['Ro 1-8']);

        $this->assertEquals('', $n->getString(['settings' => 'settings that do not exist']));
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

	public function test_stringFunctionsAreEqual()
	{
		$a = new ScripturNumArray([738197728]);
		$this->assertEquals($a->toString(), (string)$a);
	}

	public function test_string_settingsInvalid()
	{
        $this->expectException(ScripturNumException::class);
        $this->expectExceptionMessage('Invalid key for creating a string.');

        $n = new ScripturNumArray(['Ro 1-8']);

        $n->toString(['settings' => 'settings that do not exist']);
	}

	public function test_ToStringParseable_1()
	{
		$a = new ScripturNumArray(['Genesis 3', 'Romans 8:10', 'Romans 8:11', 'Romans 8:1', 'Romans 3:28', 'Romans 16:19', 'Col 1:1-10']);
		$b = ScripturNum::extractFromString($a->toString());
		$this->assertEquals($a->toString(), $b->toString());
	}

	public function test_ToStringParseable_2()
	{
		$a = new ScripturNumArray(['Genesis 3', 'Romans 10', 'Romans 11', 'Romans 8:1', 'Romans 3:28', 'Romans 16:19', 'Col 1:1-10']);
		$b = ScripturNum::extractFromString($a->toString());
		$this->assertEquals($a->toString(), $b->toString());
	}

	public function test_linkHandler()
	{
		$a = new ScripturNumArray(['Romans 10','Romans 16:19']);
		$f = function (string $s, ScripturNum $sn) {
			return "<a href=\"https://kurtz.es/scripture/" . strtolower($sn->toString('abbrev')) . "\">$s</a>";
		};
		$this->assertEquals('<a href="https://kurtz.es/scripture/ro10">Romans 10</a>; <a href="https://kurtz.es/scripture/ro16.19">16:19</a>', $a->toString(['callback' => $f]));
	}

    public function test_arrayAbbrev()
    {
        $a = new ScripturNumArray(['Genesis 1:1-5', 'Exodus 3:1-10', 'John 3:16']);
        $this->assertEquals('Ge1.1-5, Ex3.1-10, Jn3.16', $a->toString('abbrev'));
    }

    public function test_removeItem()
    {
        $a = new ScripturNumArray(['Genesis 1:1-5', 'Exodus 3:1-10', 'John 3:16']);
        $b = new ScripturNum('Genesis 1:1-5');
        $c = $a->remove($b);
        $this->assertCount(2, $c);
        $this->assertEquals('Exodus 3:1-10', (string)$c[0]);
        $this->assertEquals('John 3:16', (string)$c[1]);
    }

    public function test_removeItem_notFound()
    {
        $a = new ScripturNumArray(['Genesis 1:1-5', 'Exodus 3:1-10', 'John 3:16']);
        $b = new ScripturNum('Romans 1:1-5');
        $c = $a->remove($b);
        $this->assertCount(3, $c);
    }

    public function test_removeAll()
    {
        $a = new ScripturNumArray(['Genesis 1:1-5', 'Exodus 3:1-10', 'John 3', 'Genesis 1:1-5']);
        $b = new ScripturNumArray(['Genesis 1:1-5', 'John 3:16']);
        $c = $a->removeAll($b);
        $this->assertCount(3, $c);
        $this->assertEquals('Exodus 3:1-10', (string)$c[0]);
        $this->assertEquals('John 3:1-15', (string)$c[1]);
    }
}