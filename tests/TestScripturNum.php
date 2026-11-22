<?php

namespace ScripturNumTests;

use ScripturNum\ScripturNum;

/**
 * A ScripturNum that uses the made-up language of TestBible.
 */
class TestScripturNum extends ScripturNum
{
	protected static $bibleClass = TestBible::class;
}
