<?php

namespace ScripturNumTests;

use ScripturNum\ScripturNum;

/**
 * A ScripturNum that uses the partial Spanish translation of TestSpanishBible.
 */
class TestSpanishScripturNum extends ScripturNum
{
	protected static $bibleClass = TestSpanishBible::class;
}
