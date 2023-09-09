<?php

namespace ScripturNumTests;

use ScripturNum\Bible;

abstract class ErrantBible extends Bible
{
	/**
	 * @return string[][]
	 *
	 * We aren't overriding prepareBookNames because BOOK_NAMES are shared across all tests.  It is assumed that book
	 * names won't change within a PHP runtime session.
	 */
	public static function getBookNames(): array
	{
		$books = parent::getBookNames();
		$books[0][] = 'Fake';
		$books[10][] = 'Fake';
		$books[63] = self::ordinals(1, "Hesitations,Hes");
		return $books;
	}
}