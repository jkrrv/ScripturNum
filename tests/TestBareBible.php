<?php

namespace ScripturNumTests;

use ScripturNum\Bible;

/**
 * Another made-up "language" for testing the edges: one that has no common terms (so every book name may stand alone),
 * and has no word for "chapter", and symbols for "and" and "through" in place of words.
 */
abstract class TestBareBible extends Bible
{
	// These are re-declared so that this class doesn't share storage with the English Bible.
	protected static $BOOK_NAMES = [];

	protected static $COMMON_TERMS = [];

	protected static $CONJUNCTIONS = [
		"chapter" => "",
		"and"     => "+",
		"through" => "~"
	];

	protected static function prepareBookNames()
	{
		parent::prepareBookNames();

		static::$COMMON_TERMS = [];
	}
}
