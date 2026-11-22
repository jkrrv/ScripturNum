<?php

namespace ScripturNumTests;

use ScripturNum\Bible;

/**
 * A made-up "language" in which no book name begins with a digit (the numbered books are written with words, as in
 * "First John"), to test that numbers are only treated as the beginning of a book's name if there are such names.
 */
abstract class TestNoNumbersBible extends Bible
{
	// These are re-declared so that this class doesn't share storage with the English Bible.
	protected static $BOOK_NAMES = [];

	protected static $COMMON_TERMS = [];

	protected static function prepareBookNames()
	{
		parent::prepareBookNames();

		foreach (static::$BOOK_NAMES as $i => $names) {
			static::$BOOK_NAMES[$i] = array_values(array_filter($names, function ($n) {
				return !preg_match('/^\d/', $n);
			}));
		}
	}
}
