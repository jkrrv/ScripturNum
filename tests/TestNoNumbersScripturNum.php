<?php

namespace ScripturNumTests;

use ScripturNum\ScripturNum;

/**
 * A ScripturNum that uses the made-up language of TestNoNumbersBible.
 */
class TestNoNumbersScripturNum extends ScripturNum
{
	protected static $bibleClass = TestNoNumbersBible::class;
}
