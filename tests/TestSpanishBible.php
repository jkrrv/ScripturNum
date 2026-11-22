<?php

namespace ScripturNumTests;

use ScripturNum\Bible;

/**
 * A partial Spanish translation, to test a conjunction ("y") that is also inside the names of books ("Rey", "Reyes").
 *
 * Only the books needed for the tests have Spanish names.  The English names remain for the others.
 */
abstract class TestSpanishBible extends Bible
{
	// These are re-declared so that this class doesn't share storage with the English Bible.
	protected static $BOOK_NAMES = [];

	protected static $COMMON_TERMS = [];

	protected static $CONJUNCTIONS = [
		"chapter" => "capitulo",
		"and"     => "y",
		"through" => "hasta"
	];

	protected static function prepareBookNames()
	{
		parent::prepareBookNames();

		// Zero-indexed: 1 Kings is 10, 2 Kings 11, John 42, Romans 44.
		static::$BOOK_NAMES[10] = static::ordinals(1, ['Reyes', 'Rey']);
		static::$BOOK_NAMES[11] = static::ordinals(2, ['Reyes', 'Rey']);
		static::$BOOK_NAMES[42] = ['Juan', 'Jn'];
		static::$BOOK_NAMES[44] = ['Romanos', 'Ro'];

		static::$COMMON_TERMS = [];
	}
}
