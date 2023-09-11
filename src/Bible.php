<?php

namespace ScripturNum;

/**
 * A class that defines the composition of a Bible.  This includes the number of verses in each chapter, and the names
 * of the books of the Bible.  To translate, extend this class.
 */
abstract class Bible
{
	/**
	 * An array containing book names that should be recognized.  Keyed by sequential integers.
	 *
	 * @var string[][]  The array of book names, grouped by book.
	 */
	protected static $BOOK_NAMES = [];

	/**
	 * An array containing book names that are common words in English. Must match the terms used in $BOOK_NAMES in
	 * capitalization and spelling.
	 *
	 * @var string[]  The array of book names, grouped by book.
	 */
	protected static $COMMON_TERMS = [];

	protected static $CONJUNCTIONS = [
		"chapter" => "chapter",
		"and"     => "and",
		"through" => "through"
	];

	/**
	 * An array containing the default ordinal strings.
	 *
	 * To translate, overwrite these values with translated values.
	 *
	 * @var string[][]  The ordinal terms, grouped by number.
	 */
	protected static $ORDINALS = [
		['1', 'I', 'First'],
		['2', 'II', 'Second'],
		['3', 'III', 'Third']
	];

	/**
	 * An array containing the length of each chapter of each book.  Changing anything here could be REALLY bad; don't
	 * do it.
	 *
	 * @var int[][]  The lengths of each chapter, grouped by book.
	 */
	private static $VERSES = [
		[31, 25, 24, 26, 32, 22, 24, 22, 29, 32, 32, 20, 18, 24, 21, 16, 27, 33, 38, 18, 34, 24, 20, 67, 34, 35, 46, 22, 35, 43, 55, 32, 20, 31, 29, 43, 36, 30, 23, 23, 57, 38, 34, 34, 28, 34, 31, 22, 33, 26],
		[22, 25, 22, 31, 23, 30, 25, 32, 35, 29, 10, 51, 22, 31, 27, 36, 16, 27, 25, 26, 36, 31, 33, 18, 40, 37, 21, 43, 46, 38, 18, 35, 23, 35, 35, 38, 29, 31, 43, 38],
		[17, 16, 17, 35, 19, 30, 38, 36, 24, 20, 47, 8, 59, 57, 33, 34, 16, 30, 37, 27, 24, 33, 44, 23, 55, 46, 34],
		[54, 34, 51, 49, 31, 27, 89, 26, 23, 36, 35, 16, 33, 45, 41, 50, 13, 32, 22, 29, 35, 41, 30, 25, 18, 65, 23, 31, 40, 16, 54, 42, 56, 29, 34, 13],
		[46, 37, 29, 49, 33, 25, 26, 20, 29, 22, 32, 32, 18, 29, 23, 22, 20, 22, 21, 20, 23, 30, 25, 22, 19, 19, 26, 68, 29, 20, 30, 52, 29, 12],
		[18, 24, 17, 24, 15, 27, 26, 35, 27, 43, 23, 24, 33, 15, 63, 10, 18, 28, 51, 9, 45, 34, 16, 33],
		[36, 23, 31, 24, 31, 40, 25, 35, 57, 18, 40, 15, 25, 20, 20, 31, 13, 31, 30, 48, 25],
		[22, 23, 18, 22],
		[28, 36, 21, 22, 12, 21, 17, 22, 27, 27, 15, 25, 23, 52, 35, 23, 58, 30, 24, 42, 15, 23, 29, 22, 44, 25, 12, 25, 11, 31, 13],
		[27, 32, 39, 12, 25, 23, 29, 18, 13, 19, 27, 31, 39, 33, 37, 23, 29, 33, 43, 26, 22, 51, 39, 25],
		[53, 46, 28, 34, 18, 38, 51, 66, 28, 29, 43, 33, 34, 31, 34, 34, 24, 46, 21, 43, 29, 53],
		[18, 25, 27, 44, 27, 33, 20, 29, 37, 36, 21, 21, 25, 29, 38, 20, 41, 37, 37, 21, 26, 20, 37, 20, 30],
		[54, 55, 24, 43, 26, 81, 40, 40, 44, 14, 47, 40, 14, 17, 29, 43, 27, 17, 19, 8, 30, 19, 32, 31, 31, 32, 34, 21, 30],
		[17, 18, 17, 22, 14, 42, 22, 18, 31, 19, 23, 16, 22, 15, 19, 14, 19, 34, 11, 37, 20, 12, 21, 27, 28, 23, 9, 27, 36, 27, 21, 33, 25, 33, 27, 23],
		[11, 70, 13, 24, 17, 22, 28, 36, 15, 44],
		[11, 20, 32, 23, 19, 19, 73, 18, 38, 39, 36, 47, 31],
		[22, 23, 15, 17, 14, 14, 10, 17, 32, 3],
		[22, 13, 26, 21, 27, 30, 21, 22, 35, 22, 20, 25, 28, 22, 35, 22, 16, 21, 29, 29, 34, 30, 17, 25, 6, 14, 23, 28, 25, 31, 40, 22, 33, 37, 16, 33, 24, 41, 30, 24, 34, 17],
		[6, 12, 8, 8, 12, 10, 17, 9, 20, 18, 7, 8, 6, 7, 5, 11, 15, 50, 14, 9, 13, 31, 6, 10, 22, 12, 14, 9, 11, 12, 24, 11, 22, 22, 28, 12, 40, 22, 13, 17, 13, 11, 5, 26, 17, 11, 9, 14, 20, 23, 19, 9, 6, 7, 23, 13, 11, 11, 17, 12, 8, 12, 11, 10, 13, 20, 7, 35, 36, 5, 24, 20, 28, 23, 10, 12, 20, 72, 13, 19, 16, 8, 18, 12, 13, 17, 7, 18, 52, 17, 16, 15, 5, 23, 11, 13, 12, 9, 9, 5, 8, 28, 22, 35, 45, 48, 43, 13, 31, 7, 10, 10, 9, 8, 18, 19, 2, 29, 176, 7, 8, 9, 4, 8, 5, 6, 5, 6, 8, 8, 3, 18, 3, 3, 21, 26, 9, 8, 24, 13, 10, 7, 12, 15, 21, 10, 20, 14, 9, 6],
		[33, 22, 35, 27, 23, 35, 27, 36, 18, 32, 31, 28, 25, 35, 33, 33, 28, 24, 29, 30, 31, 29, 35, 34, 28, 28, 27, 28, 27, 33, 31],
		[18, 26, 22, 16, 20, 12, 29, 17, 18, 20, 10, 14],
		[17, 17, 11, 16, 16, 13, 13, 14],
		[31, 22, 26, 6, 30, 13, 25, 22, 21, 34, 16, 6, 22, 32, 9, 14, 14, 7, 25, 6, 17, 25, 18, 23, 12, 21, 13, 29, 24, 33, 9, 20, 24, 17, 10, 22, 38, 22, 8, 31, 29, 25, 28, 28, 25, 13, 15, 22, 26, 11, 23, 15, 12, 17, 13, 12, 21, 14, 21, 22, 11, 12, 19, 12, 25, 24],
		[19, 37, 25, 31, 31, 30, 34, 22, 26, 25, 23, 17, 27, 22, 21, 21, 27, 23, 15, 18, 14, 30, 40, 10, 38, 24, 22, 17, 32, 24, 40, 44, 26, 22, 19, 32, 21, 28, 18, 16, 18, 22, 13, 30, 5, 28, 7, 47, 39, 46, 64, 34],
		[22, 22, 66, 22, 22],
		[28, 10, 27, 17, 17, 14, 27, 18, 11, 22, 25, 28, 23, 23, 8, 63, 24, 32, 14, 49, 32, 31, 49, 27, 17, 21, 36, 26, 21, 26, 18, 32, 33, 31, 15, 38, 28, 23, 29, 49, 26, 20, 27, 31, 25, 24, 23, 35],
		[21, 49, 30, 37, 31, 28, 28, 27, 27, 21, 45, 13],
		[11, 23, 5, 19, 15, 11, 16, 14, 17, 15, 12, 14, 16, 9],
		[20, 32, 21],
		[15, 16, 15, 13, 27, 14, 17, 14, 15],
		[21],
		[17, 10, 10, 11],
		[16, 13, 12, 13, 15, 16, 20],
		[15, 13, 19],
		[17, 20, 19],
		[18, 15, 20],
		[15, 23],
		[21, 13, 10, 14, 11, 15, 14, 23, 17, 12, 17, 14, 9, 21],
		[14, 17, 18, 6],
		[25, 23, 17, 25, 48, 34, 29, 34, 38, 42, 30, 50, 58, 36, 39, 28, 27, 35, 30, 34, 46, 46, 39, 51, 46, 75, 66, 20],
		[45, 28, 35, 41, 43, 56, 37, 38, 50, 52, 33, 44, 37, 72, 47, 20],
		[80, 52, 38, 44, 39, 49, 50, 56, 62, 42, 54, 59, 35, 35, 32, 31, 37, 43, 48, 47, 38, 71, 56, 53],
		[51, 25, 36, 54, 47, 71, 53, 59, 41, 42, 57, 50, 38, 31, 27, 33, 26, 40, 42, 31, 25],
		[26, 47, 26, 37, 42, 15, 60, 40, 43, 48, 30, 25, 52, 28, 41, 40, 34, 28, 41, 38, 40, 30, 35, 27, 27, 32, 44, 31],
		[32, 29, 31, 25, 21, 23, 25, 39, 33, 21, 36, 21, 14, 23, 33, 27],
		[31, 16, 23, 21, 13, 20, 40, 13, 27, 33, 34, 31, 13, 40, 58, 24],
		[24, 17, 18, 18, 21, 18, 16, 24, 15, 18, 33, 21, 14],
		[24, 21, 29, 31, 26, 18],
		[23, 22, 21, 32, 33, 24],
		[30, 30, 21, 23],
		[29, 23, 25, 18],
		[10, 20, 13, 18, 28],
		[12, 17, 18],
		[20, 15, 16, 16, 25, 21],
		[18, 26, 17, 22],
		[16, 15, 15],
		[25],
		[14, 18, 19, 16, 14, 20, 28, 13, 28, 39, 40, 29, 25],
		[27, 26, 18, 17, 20],
		[25, 25, 22, 19, 14],
		[21, 22, 18],
		[10, 29, 24, 21, 21],
		[13],
		[15],
		[25],
		[20, 29, 22, 11, 14, 17, 17, 13, 21, 11, 19, 17, 18, 20, 8, 21, 18, 24, 21, 15, 27, 20]
	];

	/**
	 * @return int[][] An array containing the length of each chapter.
	 */
	public final static function getVerseCounts(): array
	{
		return self::$VERSES;
	}

	/**
	 * Prepare the Book Names array. To translate, override this method.  Also, when translating, be sure to use this
	 * override to set new values for $CONJUNCTIONS.
	 *
	 * @return void
	 *
	 * @since 2.0.0
	 */
	protected static function prepareBookNames()
	{
		static::$COMMON_TERMS = [
			'Numbers',
			'Joshua',
			'Josh',
			'Ruth',
			'Rut',
			'Ezra',
			'Esther',
			'Est',
			'Job',
			'Pro',
			'SoS',
			'Isaiah',
			'Isa',
			'Jeremiah',
			'Jer',
			'Lament',
			'Ezekiel',
			'Daniel',
			'Dan',
			'Hosea',
			'Joel',
			'Joe',
			'Amos',
			'Jonah',
			'Micah',
			'Mic',
			'Nah',
			'Zeph',
			'Hag',
			'Zechariah',
			'Zech',
			'Zec',
			'Mal',
			'Matthew',
			'Matt',
			'Mark',
			'Luke',
			'John',
			'Acts',
			'Romans',
			'Rom',
			'Gal',
			'Phil',
			'Co',
			'Col',
			'Colo',
			'Titus',
			'Tit',
			'James',
			'Jas',
			'Jude',
			'Revelation',
			'Rev',
			'Re'
		];

		static::$BOOK_NAMES = [
			['Genesis', 'Ge', 'Gen'],
			['Exodus', 'Ex', 'Exo'],
			['Leviticus', 'Le', 'Lev'],
			['Numbers', 'Nu', 'Num'],
			['Deuteronomy', 'Dt', 'Deut', 'Deu', 'De'],
			['Joshua', 'Js', 'Jos', 'Josh'],
			['Judges', 'Jg', 'Jdg', 'Jdgs', 'Judg'],
			['Ruth', 'Ru', 'Rut'],
			static::ordinals(1, ['Samuel', 'Sa', 'Sam']),
			static::ordinals(2, ['Samuel', 'Sa', 'Sam']),
			static::ordinals(1, ['Kings', 'Ki', 'King', 'Kin', 'Kngs']),
			static::ordinals(2, ['Kings', 'Ki', 'King', 'Kin', 'Kngs']),
			static::ordinals(1, ['Chronicles', 'Ch', 'Chr', 'Chron']),
			static::ordinals(2, ['Chronicles', 'Ch', 'Chr', 'Chron']),
			['Ezra', 'Ez', 'Ezr'],
			['Nehemiah', 'Ne', 'Neh'],
			['Esther', 'Es', 'Est', 'Esth'],
			['Job', 'Jb'],
			['Psalm', 'Ps', 'Psa', 'Pss', 'Psalms'],
			['Proverbs', 'Pr', 'Prov', 'Pro'],
			['Ecclesiastes', 'Ec', 'Ecc'],
			['Song of Solomon', 'SoS','Song of Songs', 'Songs of Solomon'],
			['Isaiah', 'Isa'],
			['Jeremiah', 'Je', 'Jer'],
			['Lamentations', 'La', 'Lam', 'Lament'],
			['Ezekiel', 'Ek', 'Ezek', 'Eze'],
			['Daniel', 'Da', 'Dan', 'Dl', 'Dnl'],
			['Hosea', 'Ho', 'Hos'],
			['Joel', 'Jl', 'Joe'],
			['Amos', 'Am', 'Amo'],
			['Obadiah', 'Ob', 'Oba', 'Obd', 'Odbh'],
			['Jonah', 'Jh', 'Jon', 'Jnh'],
			['Micah', 'Mi', 'Mic'],
			['Nahum', 'Na', 'Nah'],
			['Habakkuk', 'Hb', 'Hab', 'Hk', 'Habk'],
			['Zephaniah', 'Zp', 'Zep', 'Zeph', 'Ze'],
			['Haggai', 'Ha', 'Hag', 'Hagg'],
			['Zechariah', 'Zc', 'Zech', 'Zec'],
			['Malachi', 'Ml', 'Mal', 'Mlc'],
			['Matthew', 'Mt', 'Matt', 'Mat'],
			['Mark', 'Mk', 'Mrk'],
			['Luke', 'Lk', 'Luk', 'Lu'],
			['John', 'Jn', 'Joh', 'Jo'],
			['Acts', 'Ac', 'Act'],
			['Romans', 'Ro', 'Rom', 'Rmn', 'Rmns'],
			static::ordinals(1, ['Corinthians', 'Co', 'Cor']),
			static::ordinals(2, ['Corinthians', 'Co', 'Cor']),
			['Galatians', 'Ga', 'Gal', 'Gltns'],
			['Ephesians', 'Ep', 'Eph', 'Ephn'],
			['Philippians', 'Phi', 'Phil'],
			['Colossians', 'Co', 'Col', 'Colo', 'Cln', 'Clns'],
			static::ordinals(1, ['Thessalonians', 'Th', 'Thess', 'Thes']),
			static::ordinals(2, ['Thessalonians', 'Th', 'Thess', 'Thes']),
			static::ordinals(1, ['Timothy', 'Ti', 'Tim']),
			static::ordinals(2, ['Timothy', 'Ti', 'Tim']),
			['Titus', 'Ti', 'Tit', 'Tt', 'Ts'],
			['Philemon', 'Pm', 'Phile', 'Philm'],
			['Hebrews', 'He', 'Heb', 'Hw'],
			['James', 'Jm', 'Jam', 'Jas', 'Ja'],
			static::ordinals(1, ['Peter', 'Pe', 'Pet', 'P']),
			static::ordinals(2, ['Peter', 'Pe', 'Pet', 'P']),
			static::ordinals(1, ['John', 'Jo', 'Jn', 'J']),
			static::ordinals(2, ['John', 'Jo', 'Jn', 'J']),
			static::ordinals(3, ['John', 'Jo', 'Jn', 'J']),
			['Jude', 'Ju'],
			['Revelation', 'Re', 'Rev', 'Rvltn']
		];
	}

	/**
	 * Create all combinations of strings for a given ordinal book (ordinal books are the numbered books, like 1 and 2
	 * Kings).
	 *
	 * @param int             $number The number to use with the ordinal.  e.g. "1" in "1 John".
	 * @param string|string[] $strings The words to place with the ordinal.  e.g. "John", "Jn", etc for "1 John".
	 *
	 * @return string[]  The full name (for formal contexts) must be in position 0, and the shortest address (for urls)
	 * must be in position 1.
	 *
	 * @since 2.0.0
	 */
	protected static function ordinals(int $number, $strings): array
	{
		$number--;
		if (!is_array($strings)) {
			$strings = explode(",", $strings);
		}

		$ords = self::$ORDINALS[$number] ?? [];
		$r = [];

		foreach ($ords as $ok => $o) {
			foreach ($strings as $s) {
				$r[] = $o . ' ' . $s; // 1 John, 1 Jo, First Jo
				if ($ok == 0)
					$r[] = $o . $s; // 1John, 1Jo
			}
		}

		// switch positions 1 & 3 so that the full address is at position 0 and the shortest address is at position 1.
		$short = $r[3];
		$r[3] = $r[1];
		$r[1] = $short;
		return $r;
	}

	/**
	 * Get the Bible book names, as prepared.
	 *
	 * @return string[][] The array of book names, grouped by book.
	 */
	public static function getBookNames(): array
	{
		if (static::$BOOK_NAMES === [])
			static::prepareBookNames();

		return static::$BOOK_NAMES;
	}

	/**
	 * Get the common terms, as prepared
	 *
	 * @return string[] The array of common names in the vernacular language.
	 */
	public static function getCommonTerms(): array
	{
		return static::$COMMON_TERMS;
	}

	/**
	 * Get the conjunctions, as prepared
	 *
	 * @return string[] The array of some specific conjunctions so they're translatable.
	 */
	public static function getConjunctions(): array
	{
		return static::$CONJUNCTIONS;
	}

	/**
	 * Pluralizes the books where singular and plural make a difference.  In English, Psalm(s) and Song(s) of Solomon.
	 *
	 * For translation, override this method.
	 *
	 * @param string $bookNameSingular  The singular book name
	 *
	 * @return string The plural book name
	 */
	public static function pluralizeBookName(string $bookNameSingular): string
	{
		return str_replace(['Psalm', 'Song of Solomon'], ['Psalms', 'Songs of Solomon'], $bookNameSingular);
	}

	/**
	 * Returns true if the given needle is in the haystack, case-insensitive.
	 *
	 * @param $needle
	 * @param $haystack
	 *
	 * @return bool
	 */
	public final static function in_arrayi($needle, $haystack): bool
	{
		return in_array(strtolower($needle), array_map('strtolower', $haystack));
	}

	/**
	 * Returns true if the book at a particular position has only a single chapter.
	 *
	 * @param $bookIndex
	 *
	 * @return bool
	 */
	public final static function bookHasSingleChapter($bookIndex): bool
	{
		return count(self::getVerseCounts()[$bookIndex]) === 1;
	}

	/**
	 * Confirms that there are no duplicate names in the arrays of book names.  This is important because some
	 * abbreviations can be interpreted multiple different ways.
	 *
	 * The return value can be either an array of duplicates (or empty array if none), or a bool that is true if the
	 * list is valid.
	 *
	 * @see self::validateBookNamesEx()
	 *
	 * @param bool $returnDupes Default False. Set true to return an array of duplicate names.
	 *
	 * @return bool|array
	 *
	 * @since 2.0.0
	 */
	public static function validateBookNames(bool $returnDupes = false)
	{
		$allNames = [];
		$dupes = [];
		foreach (static::getBookNames() as $bk){
			foreach ($bk as $name) {
				if (static::in_arrayi($name, $allNames)) {
					if (!$returnDupes) {
						return false;
					}
					if (!static::in_arrayi($name, $dupes)) {
						$dupes[] = $name;
					}
				} else {
					$allNames[] = $name;
				}
			}
		}
		if (!$returnDupes) {
			return true;
		}
		return $dupes;
	}

	/**
	 * Confirms that there are no duplicate names in the arrays of book names.  This is important because some
	 * abbreviations can be interpreted multiple different ways.
	 *
	 * This function will return true if the book names are valid, and will throw an exception if not.
	 *
	 * @see self::validateBookNames()
	 *
	 * @return true
	 * @throws ScripturNumException
	 *
	 * @since 2.0.0
	 */
	public static function validateBookNamesEx(): bool
	{
		$r = static::validateBookNames(true);
		if (count($r) > 0) {
			$names = implode(', ', $r);
			throw new ScripturNumException("Duplicate book names exist: $names");
		}
		return true;
	}
}


