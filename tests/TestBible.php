<?php

namespace ScripturNumTests;

use ScripturNum\Bible;

/**
 * A made-up "language" for testing, with its own words for some books and its own conjunctions, to show that
 * book names and conjunctions come from the configured Bible, and not from English.
 *
 * The English names remain for the books that aren't renamed.
 */
abstract class TestBible extends Bible
{
	// These are re-declared so that this class doesn't share storage with the English Bible.
	protected static $BOOK_NAMES = [];

	protected static $COMMON_TERMS = [];

	protected static $CONJUNCTIONS = [
		"chapter" => "caput",
		"and"     => "et",
		"through" => "usque"
	];

	protected static function prepareBookNames()
	{
		parent::prepareBookNames();

		// Zero-indexed: Matthew is 39, Mark 40, John 42, Acts 43, Romans 44.
		static::$BOOK_NAMES[39] = ['Matthaeus', 'Mat'];
		static::$BOOK_NAMES[40] = ['Marcus', 'Marc'];
		static::$BOOK_NAMES[42] = ['Ioannes', 'Ioa'];
		static::$BOOK_NAMES[43] = ['Actus', 'Act'];
		static::$BOOK_NAMES[44] = ['Romani', 'Rom'];

		// Names in this language that are also ordinary words.  (The others, such as Ioannes, are not.)
		static::$COMMON_TERMS = ['Marcus', 'Actus'];
	}
}
