<?php

namespace ScripturNumTests;

use ScripturNum\ScripturNum;

/**
 * A ScripturNum that uses the made-up language of TestBareBible.
 */
class TestBareScripturNum extends ScripturNum
{
	protected static $bibleClass = TestBareBible::class;
}
