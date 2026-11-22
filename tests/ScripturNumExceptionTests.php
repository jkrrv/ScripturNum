<?php

namespace ScripturNumTests;

use PHPUnit\Framework\TestCase;
use ScripturNum\ScripturNumException;

class ScripturNumExceptionTests extends TestCase
{
	public function test_getInput_defaultsToNull()
	{
		$e = new ScripturNumException("Something went wrong.");
		$this->assertNull($e->getInput());
	}

	public function test_getInput_fromConstructor()
	{
		$e = new ScripturNumException("Book name is invalid.", 0, null, "Hezekiah 1");
		$this->assertEquals("Hezekiah 1", $e->getInput());
		$this->assertEquals("Book name is invalid.", $e->getMessage());
	}

	public function test_setInput()
	{
		$e = new ScripturNumException("Book name is invalid.");
		$this->assertSame($e, $e->setInput("Hezekiah 1"));
		$this->assertEquals("Hezekiah 1", $e->getInput());
	}

	public function test_setInput_null_clears()
	{
		$e = new ScripturNumException("Book name is invalid.", 0, null, "Hezekiah 1");
		$e->setInput(null);
		$this->assertNull($e->getInput());
	}

	public function test_constructor_keepsCodeAndPrevious()
	{
		$prev = new \Exception("prev");
		$e = new ScripturNumException("msg", 7, $prev, "John 3:99");
		$this->assertEquals(7, $e->getCode());
		$this->assertSame($prev, $e->getPrevious());
	}
}
