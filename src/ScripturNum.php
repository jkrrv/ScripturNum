<?php


namespace ScripturNum;

use TypeError;

class ScripturNum
{
	protected $int;
	protected $book;
	protected $startCh;
	protected $startV;
	protected $endCh;
	protected $endV;

	const BOOK_MASK = 4278190080;
	const START_MASK = 16773120;
	const END_MASK = 4095;

	protected static $bibleClass = Bible::class;

	/**
	 * ScripturNum constructor.
	 *
	 * @param int|string $intOrString ScripturNum int or a human-readable string.
	 *
	 * @throws ScripturNumException  Thrown if the provided int or string can't be understood.
	 */
	public function __construct($intOrString)
	{
		if (is_numeric($intOrString)) {
			$int = (int)$intOrString;
		} else {
			try {
				$int = self::stringToInt($intOrString);
			} catch (TypeError $e) {
				throw new ScripturNumException("Invalid value provided to ScripturNum constructor.");
			}
		}
		self::intToRefNums($int, $this->book, $this->startCh, $this->startV, $this->endCh, $this->endV);
		$this->int = $int;
	}


	protected static $stringSettings = [
		'abbrev' => [
			'space' => '',
			'cvsep' => '.',
			'range' => '-',
			'names' => 1,
			'plurl' => false
		],
		'long'   => [
			'space' => ' ',
			'cvsep' => ':',
			'range' => '-',
			'names' => 0,
			'plurl' => true
		],
	];


	/**
	 * Update string settings.
	 *
	 * @param       $key
	 * @param array $settings
	 *
	 * @return void
	 */
	public static function setStringSettings($key, array $settings)
	{
		if ( ! isset(static::$stringSettings[$key])) {
			static::$stringSettings[$key] = [];
		}

		foreach (reset(static::$stringSettings) as $k => $v) {
			if (isset($settings[$k])) {
				static::$stringSettings[$key][$k] = $settings[$k];
			}
		}
	}


	/**
	 * Get the ScripturNum integer.
	 *
	 * @return int The ScripturNum integer
	 */
	public function getInt(): int
	{
		return $this->int;
	}


	/**
	 * Generic toString.  Uses the long form.
	 *
	 * @return string
	 * @throws ScripturNumException
	 */
	public function __toString(): string
	{
		return $this->getLongString();
	}


	/**
	 * Get a human-readable abbreviation for the passage.  By default, these are meant for usage in short links.
	 *
	 * @return string An abbreviation
	 * @throws ScripturNumException
	 */
	public function getAbbrev(): string
	{
		return $this->getStringWithSettings('abbrev');
	}


	/**
	 * Get a human-readable name of the passage.  By default, these are meant for humans to read.
	 *
	 * @return string The name of the passage, as one might pronounce it.
	 * @throws ScripturNumException
	 */
	public function getLongString(): string
	{
		return $this->getStringWithSettings('long');
	}


	/**
	 * Returns a human-readable string with the settings defined in a given setting set.
	 *
	 * @param string $settingKey The setting set to use.  Default options are 'abbrev' and 'long'
	 *
	 * @return string The human-intelligible string.
	 * @throws ScripturNumException  If a setting is invalid.
	 */
	public function getStringWithSettings($settingKey): string
	{
		if ( ! isset(static::$stringSettings[$settingKey])) {
			throw new ScripturNumException('Invalid key for creating a string.');
		}

		if ( ! isset(static::$stringSettings[$settingKey]['space'])) {
			throw new ScripturNumException('Invalid space character.');
		}

		if ( ! isset(static::$stringSettings[$settingKey]['cvsep'])) {
			throw new ScripturNumException('Invalid chapter-verse separation character.');
		}

		if ( ! isset(static::$stringSettings[$settingKey]['range'])) {
			throw new ScripturNumException('Invalid range character.');
		}

		if ( ! isset(static::$stringSettings[$settingKey]['names']) ||
		     ! is_numeric(static::$stringSettings[$settingKey]['names'])) {
			throw new ScripturNumException('Invalid name offset.');
		}

		if ( ! isset(static::$stringSettings[$settingKey]['plurl'])) {
			throw new ScripturNumException('Plurality is not defined.');
		}


		$s = static::$stringSettings[$settingKey]['space'];
		$c = static::$stringSettings[$settingKey]['cvsep'];
		$r = static::$stringSettings[$settingKey]['range'];
		$n = (int)static::$stringSettings[$settingKey]['names'];
		$p = ! ! static::$stringSettings[$settingKey]['plurl'];

		$b = static::getBookNames();

		if ($n > count($b[$this->book - 1])) {
			$n = count($b[$this->book - 1]) - 1;
		}

		$b = $b[$this->book - 1][$n];

		if ($this->startCh !== $this->endCh && $p) {
			$b = self::pluralizeBookName($b);
		}

		if ($this->isWholeBook()) {
			return $b;
		} elseif ($this->isWholeChapters()) {
			if ($this->startCh === $this->endCh) {
				return $b . $s . $this->startCh;
			}

			return $b . $s . $this->startCh . $r . $this->endCh;
		} else {
			if ($this->bookHasSingleChapter()) {
				if ($this->startV === $this->endV) {
					return $b . $s . $this->startV;
				}

				return $b . $s . $this->startV . $r . $this->endV;
			} elseif ($this->startCh === $this->endCh) {
				if ($this->startV === $this->endV) {
					return $b . $s . $this->startCh . $c . $this->startV;
				}

				return $b . $s . $this->startCh . $c . $this->startV . $r . $this->endV;
			}

			return $b . $s . $this->startCh . $c . $this->startV . $r . $this->endCh . $c . $this->endV;
		}
	}


	/**
	 * Returns true if the passage is an entire chapter.
	 *
	 * @return bool
	 */
	public function isWholeChapters(): bool
	{
		$v = Bible::getVerseCounts();

		return ($this->startV === 1 && $this->endV === $v[$this->book - 1][$this->endCh - 1]);
	}

	/**
	 * Returns a ScripturNum for the current range, expanded to the whole chapter.
	 *
	 * @return static
	 * @throws ScripturNumException
	 */
	public function getWholeChapters(): ScripturNum
	{
		if ($this->isWholeChapters()) {
			return $this;
		}

		$i = static::refNumsToInt($this->book, $this->startCh, null, $this->endCh, null);
		return new static($i);
	}

	/**
	 * Returns a ScripturNum for the chapter after the current highest chapter.
	 *
	 * @return static
	 * @throws ScripturNumException
	 */
	public function getNextChapter(): ScripturNum
	{
		$v = Bible::getVerseCounts();
		if (! isset($v[$this->book]) && $this->endCh === count($v[$this->book - 1])) {
			throw new ScripturNumException("There are no more chapters in the Bible.");
		}
		if (! isset($v[$this->book - 1][$this->endCh])) {
			$i = static::refNumsToInt($this->book + 1, 1, null, 1, null);
		} else {
			$ch = $this->endCh + 1;
			$i = static::refNumsToInt($this->book, $ch, null, $ch, null);
		}
		return new static($i);
	}

	/**
	 * Returns a ScripturNum for the chapter prior to the current lowest chapter.
	 *
	 * @return static
	 * @throws ScripturNumException
	 */
	public function getPrevChapter(): ScripturNum
	{
		if ($this->startCh === 1 && $this->book === 1) {
			throw new ScripturNumException("There are no more chapters in the Bible.");
		}
		$v = Bible::getVerseCounts();
		if ($this->startCh === 1) {
			$ch = count($v[$this->book - 2]);
			$i = static::refNumsToInt($this->book - 1, $ch, null, $ch, null);
		} else {
			$ch = $this->startCh - 1;
			$i = static::refNumsToInt($this->book, $ch, null, $ch, null);
		}
		return new static($i);
	}

	/**
	 * Returns true if the passage is a whole book.
	 *
	 * @return bool
	 */
	public function isWholeBook(): bool
	{
		$v = Bible::getVerseCounts();

		return ($this->startCh === 1
		        && $this->startV === 1
		        && $this->endCh === count($v[$this->book - 1])
		        && $this->endV === $v[$this->book - 1][$this->endCh - 1]);
	}


	/**
	 * Returns true if the passage is just a single verse.
	 *
	 * @return bool
	 */
	public function isSingleVerse(): bool
	{
		return ($this->startCh === $this->endCh
		        && $this->startV === $this->endV);
	}


	/**
	 * Returns true if the book only has one chapter (e.g. Jude)
	 *
	 * @return bool
	 */
	public function bookHasSingleChapter(): bool
	{
		return Bible::bookHasSingleChapter($this->book - 1);
	}


	/**
	 * @param $bookName
	 *
	 * @return int
	 * @throws ScripturNumException
	 */
	protected static function bookNameToBookNum($bookName): int
	{
		$books = static::getBookNames();
		foreach ($books as $book => $bookNames) {
			if (Bible::in_arrayi($bookName, $bookNames)) {
				return $book + 1;
			}
		}
		throw new ScripturNumException('Book name is invalid.');
	}

	/**
	 * @return string[][]
	 *
	 * @see Bible::getBookNames()
	 */
	protected static function getBookNames(): array
	{
		return call_user_func([static::$bibleClass, 'getBookNames']);
	}

	/**
	 * @return string[]
	 *
	 * @see Bible::getCommonTerms()
	 */
	protected static function getCommonTerms(): array
	{
		return call_user_func([static::$bibleClass, 'getCommonTerms']);
	}

	/**
	 * @return string[]
	 *
	 * @see Bible::getConjunctions()
	 */
	protected static function getConjunctions(): array
	{
		return call_user_func([static::$bibleClass, 'getConjunctions']);
	}

	/**
	 * @param string $bookNameSingular
	 *
	 * @return string
	 *
	 * @see Bible::pluralizeBookName()
	 */
	protected static function pluralizeBookName(string $bookNameSingular): string
	{
		return call_user_func([static::$bibleClass, 'pluralizeBookName'], $bookNameSingular);
	}

	/**
	 * @param string $bookStr
	 * @param ?int   $startCh
	 * @param ?int   $startV
	 * @param ?int   $endCh
	 * @param ?int   $endV
	 *
	 * @return ScripturNum
	 * @throws ScripturNumException
	 */
	public static function newFromParsed(string $bookStr,
		$startCh = null, $startV = 1,
		$endCh = null, $endV = null): ScripturNum
	{
		$book = self::bookNameToBookNum($bookStr);
		self::validateRefNums($book, $startCh, $startV, $endCh, $endV);
		$int  = self::refNumsToInt($book, $startCh, $startV, $endCh, $endV);
		$c    = static::class;

		return new $c($int);
	}


	/**
	 * @param int      $book The book of the Bible the range is within. 1-rel.
	 * @param int      $startCh The chapter of the start of the range. 1-rel.
	 * @param int|null $startV The verse of the start of the range.  1-rel. Defaults to 1.
	 * @param int|null $endCh The end chapter of the range.  If null or not provided, assumed to be the same as the
	 *     start chapter.
	 * @param int|null $endV The end verse of the range.  If null or not provided, assumed to be the end of the
	 *     chapter.
	 *
	 * @return ScripturNum The ScripturNum object that represents this range of scripture.
	 *
	 * @throws ScripturNumException A chapter was requested that does not exist within the requested book.
	 * @throws ScripturNumException A verse was requested that does not exist within the requested range.
	 */
	public static function newFromInts(int $book,
		int $startCh, int $startV = null,
		int $endCh = null, int $endV = null): ScripturNum
	{
		self::validateRefNums($book, $startCh, $startV, $endCh, $endV);
		$int = self::refNumsToInt($book, $startCh, $startV, $endCh, $endV);
		$c   = static::class;

		return new $c($int);
	}


	/**
	 * @param string $string A human-readable scripture reference that should be converted to an int.
	 *
	 * @return int The int.
	 *
	 * @throws ScripturNumException
	 */
	public static function stringToInt(string $string): int
	{
		// Standardize dashes
		$string = str_replace(['&ndash;', '–'], '-', $string);

		// Remove duplicate spaces (Can't remove all spaces because spaces may occur in book names like Song of Songs
		$string = preg_replace("/\s\s+/", ' ', $string);

		// Remove spaces among the numerical parts.
		$string = preg_replace("/(\d+)\s*([-.:])\s*(\d+)/i", '$1$2$3', $string);

		// Look for right-most space or alpha char.  This should separate book name from numerical ref.
		preg_match('/.*([a-zA-Z\s])/', $string, $matches, PREG_OFFSET_CAPTURE);
		$spaceIndex = (int)$matches[1][1] + 1;
		$book       = trim(substr($string, 0, $spaceIndex));
		$ref        = substr($string, $spaceIndex);

		// Parse numbers
		self::refNumStringToRefNums($ref, $startCh, $startV, $endCh, $endV);

		// Change book name to number
		$book = self::bookNameToBookNum($book);

		// Assemble and return the int
		self::validateRefNums($book, $startCh, $startV, $endCh, $endV);
		return self::refNumsToInt($book, $startCh, $startV, $endCh, $endV);
	}

	/**
	 * @param string $string A human-readable scripture reference that should be converted to one or more ints.
	 * Allows for commas and semicolons.
	 *
	 * @return int[] The ints.
	 * @throws ScripturNumException
	 */
	public static function stringToInts(string $string, &$exceptions = false): array
	{
		$cj = static::getConjunctions();

		// standardize punctuation bits
		$string = str_replace([$cj['and'], '&', $cj['chapter'], $cj['through'], '&endash;', '–'],
		                      [',', ',', '', '-', '-', '-'],
		                      $string);

		// Remove duplicate spaces (Can't remove all spaces because spaces may occur in book names like Song of Songs
		$string = preg_replace("/\s\s+/", ' ', $string);

		// Look for right-most alpha char.  This should separate book name from numerical ref.
		preg_match('/.*([a-zA-Z])/', $string, $matches, PREG_OFFSET_CAPTURE);
		$spaceIndex = (int)$matches[1][1] + 1; // Space index may not may not actually be a space.
		$book       = trim(substr($string, 0, $spaceIndex));
		$ref        = substr($string, $spaceIndex);

		// Change book name to number
		try {
			$book = self::bookNameToBookNum($book);
		} catch (ScripturNumException $e) {
			if (is_array($exceptions)) {
				$exceptions[] = $e;
			} else {
				throw $e;
			}
			return [];
		}

		$ints = [];

		// Remove all spaces from reference.
		$ref = preg_replace("/\s*/", '', $ref);

		foreach (explode(";", $ref) as $sA) {
			foreach (explode(",", $sA) as $s) {
				$s = trim($s);
				if ($s === "" && isset($endCh))
					continue;
				try {
					// Parse numbers
					self::refNumStringToRefNums($s, $startCh, $startV, $endCh, $endV, true);

					// Assemble and return the int
					self::validateRefNums($book, $startCh, $startV, $endCh, $endV);
					$ints[] = self::refNumsToInt($book, $startCh, $startV, $endCh, $endV);

				} catch (ScripturNumException $e) {
					if (is_array($exceptions)) {
						$exceptions[] = $e;
					} else {
						throw $e;
					}
					continue;
				}
			}
		}

		return $ints;
	}

	/**
	 * Validate that reference numbers can be matched to verses that exist.
	 *
	 * @param int  $book
	 * @param ?int $startCh
	 * @param ?int $startV
	 * @param ?int $endCh
	 * @param ?int $endV
	 *
	 * @return void
	 * @throws ScripturNumException
	 */
	protected static function validateRefNums(int $book, &$startCh, &$startV, &$endCh, &$endV)
	{
		$book--;
		if ($startCh > count(Bible::getVerseCounts()[$book]) || $endCh > count(
				Bible::getVerseCounts()[$book]
			)) { // invalid request OR request for a single-chapter book.
			if (Bible::bookHasSingleChapter($book) && $startV === null && $endV === null) { // single-chapter book.
				$startV  = $startCh;
				$endV    = $endCh;
				$startCh = 1;
				$endCh   = 1;
			} else {
				throw new ScripturNumException("A chapter was requested that does not exist within the requested book.");
			}
		}
		if ($startCh === null && $endCh === null) { // whole book
			$startCh = 1;
			$endCh   = count(Bible::getVerseCounts()[$book]);
		}
		if (($startV - 1) > Bible::getVerseCounts()[$book][$startCh - 1] || ($endV - 1) > Bible::getVerseCounts()[$book][$endCh - 1]) {
			throw new ScripturNumException("A verse was requested that does not exist within the requested chapter.");
		}
	}

	/**
	 * Take reference indexes and convert them to the ScripturNum int.  Assumes numbers are already validated by either
	 * safely existing or being validated against self::validateRefNums()
	 *
	 * @param int $book
	 * @param ?int $startCh
	 * @param ?int $startV
	 * @param ?int $endCh
	 * @param ?int $endV
	 *
	 * @return int
	 */
	protected static function refNumsToInt(int $book, $startCh, $startV, $endCh, $endV): int
	{
		$v = Bible::getVerseCounts();
		$book--;
		$int = ($book) << 24;
		if ($startCh === null && $endCh === null) { // whole book
			$startCh = 1;
			$endCh   = count($v[$book]);
		}
		if ($endCh == null) { // single chapter
			$endCh = $startCh;
		}
		$startCh--;
		$startV--;
		$endCh--;
		$endV--;
		if ($endV === null) {
			$endV = $v[$book][$endCh] - 1;
		}

		$ch = 0;
		while ($ch < ($startCh)) {
			$startV += $v[$book][$ch];
			$ch++;
		}
		$ch = 0;
		while ($ch < ($endCh)) {
			$endV += $v[$book][$ch];
			$ch++;
		}

		$int += ($startV << 12);
		$int += ($endV);

		return $int;
	}

	/**
	 * This function reads through a single ref string (e.g. 3:5-6:9) one character at a time to parse it into a known
	 * reference.
	 *
	 * @param string $string The string to parse.
	 * @param        $chapterStart
	 * @param        $verseStart
	 * @param        $chapterEnd
	 * @param        $verseEnd
	 * @param bool   $useHints
	 *
	 * @throws ScripturNumException
	 */
	protected static function refNumStringToRefNums(string $string, &$chapterStart = null, &$verseStart = null, &$chapterEnd = null, &$verseEnd = null, bool $useHints = false)
	{
		if (preg_match('/[a-zA-Z]/', $string)) {
			throw new ScripturNumException("Parse Ref only handles the numerical part of the reference.  Alphabetical characters are not permitted.");
		}

		$startNums     = [];
		$endNums       = [];
		$currentNumber = '';
		$beforeHyphen  = true;
		$useHints      = $useHints && !!$chapterEnd;

		// adding the extra character allows the last digit to actually get parsed.
		foreach (str_split($string . ' ') as $char) {
			if (is_numeric($char)) {
				// still finding the full number
				$currentNumber .= $char;
			} else {
				// End of number.  Int-ify and assign to appropriate half.
				$currentNumber = (int)$currentNumber;
				if ($currentNumber === 0) {
					continue;
				}
				if ($beforeHyphen) {
					$startNums[] = $currentNumber;
				} else {
					$endNums[] = $currentNumber;
				}
				$currentNumber = ''; // reset for next number.

				if ($char == '-') {
					$beforeHyphen = false;
				}
			}
		}

		$numInx = count($startNums) * 10 + count($endNums) + ($useHints ? 100 : 0);
		switch ($numInx) {
			case 0: // whole book
				$chapterStart = null;
				$chapterEnd = null;
				$verseStart = null;
				$verseEnd = null;
				break;
			case 10: // one full chapter
				$chapterStart = $startNums[0];
				$chapterEnd   = $chapterStart;
				break;
			case 110: // One verse, same chapter as previous (Probably?)
				if (!$verseStart && !$verseEnd) { // Previous indicator was chapters only.  This should be, too.
					$chapterStart = $startNums[0];
					$chapterEnd = $chapterStart;
				} else { // Previous indicator had verses; this should be verses, too.
					$chapterStart = $chapterEnd;
					$verseStart   = $startNums[0];
					$verseEnd     = $verseStart;
				}
				break;
			case 11: // multiple full chapters
				$chapterStart = $startNums[0];
				$chapterEnd   = $endNums[0];
				break;
			case 111: // multiple verses from previous chapter (probably?)
				if (!$verseStart && !$verseEnd) { // Previous indicator was chapters only.  This should be, too.
					$chapterStart = $startNums[0];
					$chapterEnd = $endNums[0];
					$verseStart = null;
					$verseEnd = null;
				} else { // Previous indicator had verses; this should be verses, too.
					$chapterStart = $chapterEnd;
					$verseStart   = $startNums[0];
					$verseEnd     = $endNums[0];
				}
				break;
			case 12: // full chapter to part of chapter
			case 112:
				$chapterStart = $startNums[0];
				$verseStart   = null;
				$chapterEnd   = $endNums[0];
				$verseEnd     = $endNums[1];
				break;
			case 20: // one verse.  This is the weird case.
			case 120:
				$chapterStart = $startNums[0];
				$verseStart   = $startNums[1];
				$chapterEnd   = $chapterStart;
				$verseEnd     = $verseStart;
				break;
			case 21: // multiple verses from one chapter.
			case 121:
				$chapterStart = $startNums[0];
				$chapterEnd   = $chapterStart;
				$verseStart   = $startNums[1];
				$verseEnd     = $endNums[0];
				break;
			case 22: // multiple verses from across chapters
			case 122:
				$chapterStart = $startNums[0];
				$verseStart   = $startNums[1];
				$chapterEnd   = $endNums[0];
				$verseEnd     = $endNums[1];
				break;
			default:
				throw new ScripturNumException("Badly formed numerical reference.");
		}
	}

	/**
	 * Converts a ScripturNum int into reference numbers.
	 *
	 * @param int $int The ScripturNum integer
	 * @param int $book The book number
	 * @param int $chapterStart The first Chapter
	 * @param int $verseStart The first Verse
	 * @param int $chapterEnd The last Chapter
	 * @param int $verseEnd The last Verse
	 *
	 * @throws ScripturNumException If the reference is unintelligible.
	 */
	protected static function intToRefNums(int $int, &$book, &$chapterStart, &$verseStart, &$chapterEnd, &$verseEnd)
	{
		$book = $int >> 24;
		$int  -= ($book << 24);

		$refAIndex = $int >> 12;
		$int       -= ($refAIndex << 12);

		$refBIndex = &$int;

		if ($refBIndex < $refAIndex) {
			throw new ScripturNumException('Unintelligible Reference');
		}

		self::bkIndexToSingleRef($book, $refAIndex, $chapterStart, $verseStart);
		self::bkIndexToSingleRef($book, $refBIndex, $chapterEnd, $verseEnd);

		$book++;
	}


	/**
	 * Convert a ScrupturNum int into a concatenated number.  (Concatenated numbers are often used for text libraries.)
	 *
	 * @param int        $int The int representing the full passage
	 * @param string|int $concatStart The concatenated "number" possibly larger than an int representing the start of
	 *     the passage.
	 * @param string|int $concatEnd The concatenated "number" possibly larger than an int representing the end of the
	 *     passage.
	 *
	 * @throws ScripturNumException If the reference is unintelligible.
	 */
	public static function intToConcats(int $int, &$concatStart, &$concatEnd)
	{
		$p = [0, 0, 0, 0, 0];
		self::intToRefNums($int, $p[0], $p[1], $p[2], $p[3], $p[4]);
		$concatStart = $p[2] + ($p[1] * 1000) + ($p[0] * 1000000);
		$concatEnd   = $p[4] + ($p[3] * 1000) + ($p[0] * 1000000);
	}

	/**
	 * Parse a book index number into a chapter and verse.
	 *
	 * @param int $book Book number
	 * @param int $index Verse Index Number
	 * @param int $chapter Chapter
	 * @param int $verse Verse
	 *
	 * @throws ScripturNumException
	 */
	protected static function bkIndexToSingleRef($book, $index, &$chapter, &$verse)
	{
		$index++;
		$v       = Bible::getVerseCounts();
		$chapter = 0;
		if ( ! isset($v[$book])) {
			throw new ScripturNumException("There are not that many books in the Bible.");
		}
		while ($index > $v[$book][$chapter]) {
			$index -= $v[$book][$chapter];
			if ( ! isset($v[$book][++$chapter])) {
				throw new ScripturNumException("There are not that many verses in this book.");
			}
		}
		$chapter++;
		$verse = $index;
	}

	/**
	 * @param string $string
	 * @param bool   $excludeAllBookOnlyRefs
	 * @param null   $exceptions
	 *
	 * @return ScripturNumArray
	 * @throws ScripturNumException
	 */
	public static function extractFromString(string $string, bool $excludeAllBookOnlyRefs = false, &$exceptions = null): ScripturNumArray
	{
		$results = new ScripturNumArray();

		$allBookNames = self::getBookNames();
		$allBookNames = array_merge(...$allBookNames);

		$cj = static::getConjunctions();

		$string = str_replace([$cj['and'], $cj['through'], $cj['chapter']], [',', '-', ''], $string);

		if ($excludeAllBookOnlyRefs) {
			$regExSets = [
				[
					'bs' => $allBookNames,
					'ps' => '+'
				]
			];
		} else {
			$b2 = self::getCommonTerms();
			$b1 = array_diff($allBookNames, $b2);
			$regExSets = [
				[
					'bs' => $b1,
					'ps' => '*'
				],
				[
					'bs' => $b2,
					'ps' => '+'
				]
			];
			unset($b1, $b2);
		}
		unset($allBookNames);

		foreach ($regExSets as $re) {
			$b = implode("|", $re['bs']);
			$plusOrStar = $re['ps'];
			/** @noinspection RegExpUnnecessaryNonCapturingGroup -- They really are necessary. */
			$pattern = "/\b(?:$b)\.?(?:[-\s,;&]*1?\d{1,2}:?(?:1?\d{1,2})?)$plusOrStar\b/i";

			preg_match_all($pattern, $string, $matches);

			foreach ($matches[0] as $m) {
				$ints = static::stringToInts($m, $exceptions);
				foreach($ints as $i) {
					$results[] = new static($i);
				}
			}
		}

		return $results;
	}

	/**
	 * Test whether a given passage is within a given larger passage.  Will also return true if they are the same.
	 *
	 * @param int $largerPassage
	 *
	 * @return bool
	 */
	public function isWithinInt(int $largerPassage): bool
	{
		if (($this->int & self::BOOK_MASK) != ($largerPassage & self::BOOK_MASK))
			return false;

		if (($this->int & self::START_MASK) < ($largerPassage & self::START_MASK))
			return false;

		if (($this->int & self::END_MASK) > ($largerPassage & self::END_MASK))
			return false;

		return true;
	}

	/**
	 * Test whether a given passage is within a given larger passage.  Will also return true if they are the same.
	 *
	 * @param ScripturNum $largerPassage
	 *
	 * @return bool
	 */
	public function isWithin(ScripturNum $largerPassage): bool
	{
		return $this->isWithinInt($largerPassage->getInt());
	}

	/**
	 * Test whether a given passage has any commonality with another passage.
	 *
	 * @param int $otherPassage
	 *
	 * @return bool
	 */
	public function overlapsWithInt(int $otherPassage): bool
	{
		if (($this->int & self::BOOK_MASK) != ($otherPassage & self::BOOK_MASK))
			return false;

		if (($this->int & self::START_MASK) > (($otherPassage & self::END_MASK) << 12))
			return false;

		if (($this->int & self::END_MASK) < (($otherPassage & self::START_MASK) >> 12))
			return false;

		return true;
	}

	/**
	 * Test whether a given passage has any commonality with another passage.
	 *
	 * @param ScripturNum $otherPassage
	 *
	 * @return bool
	 */
	public function overlapsWith(ScripturNum $otherPassage): bool
	{
		return $this->overlapsWithInt($otherPassage->getInt());
	}

	/**
	 * Test whether a given passage has any commonality with another passage, or is adjacent to it.
	 *
	 * @param int $otherPassage
	 *
	 * @return bool
	 */
	public function overlapsOrAdjacentInt(int $otherPassage): bool
	{
		if (($this->int & self::BOOK_MASK) != ($otherPassage & self::BOOK_MASK))
			return false;

		if (($this->int & self::START_MASK) - (1 << 12) > (($otherPassage & self::END_MASK) << 12))
			return false;

		if ((($this->int & self::END_MASK) + 1) < (($otherPassage & self::START_MASK) >> 12))
			return false;

		return true;
	}

	/**
	 * Test whether a given passage has any commonality with another passage, or is adjacent to it.
	 *
	 * @param ScripturNum $otherPassage
	 *
	 * @return bool
	 */
	public function overlapsOrAdjacent(ScripturNum $otherPassage): bool
	{
		return $this->overlapsOrAdjacentInt($otherPassage->getInt());
	}

	/**
	 * @param int $otherPassage
	 *
	 * @return int
	 * @throws ScripturNumException
	 */
	public function combineWithInt(int $otherPassage): int
	{
		if (!$this->overlapsOrAdjacentInt($otherPassage)) {
			throw new ScripturNumException("Cannot combine passages that aren't overlapping or adjacent.");
		}

		$newInt = $this->int & self::BOOK_MASK;
		$newInt += min($this->int & self::START_MASK, $otherPassage & self::START_MASK);
		$newInt += max($this->int & self::END_MASK, $otherPassage & self::END_MASK);

		return $newInt;
	}

	/**
	 * @param ScripturNum $otherPassage
	 *
	 * @return ScripturNum
	 * @throws ScripturNumException
	 */
	public function combineWith(ScripturNum $otherPassage): ScripturNum
	{
		$int = $this->combineWithInt($otherPassage->getInt());
		return new ScripturNum($int);
	}

	/**
	 * Test whether a given passage contains a given smaller passage.  Will also return true if they are the same.
	 *
	 * @param int $smallerPassage
	 *
	 * @return bool
	 */
	public function containsInt(int $smallerPassage): bool
	{
		if (($this->int & self::BOOK_MASK) != ($smallerPassage & self::BOOK_MASK))
			return false;

		if (($this->int & self::START_MASK) > ($smallerPassage & self::START_MASK))
			return false;

		if (($this->int & self::END_MASK) < ($smallerPassage & self::END_MASK))
			return false;

		return true;
	}

	/**
	 * Test whether a given passage contains a given smaller passage.  Will also return true if they are the same.
	 *
	 * @param ScripturNum $smallerPassage
	 *
	 * @return bool
	 */
	public function contains(ScripturNum $smallerPassage): bool
	{
		return $this->containsInt($smallerPassage->getInt());
	}

	/**
	 * Generate a query statement that can be used to search an int column in generic SQL for a passage that is
	 * entirely contained within the given ScripturNum.
	 *
	 * @param string $columnRef  The name of the column or value to use in the query.
	 *
	 * @return string
	 */
	public function toSqlExclusive(string $columnRef): string
	{
		if ($this->isSingleVerse()) {
			$i = $this->getInt();
			return "$columnRef = $i";
		}

		$r = [];

		$mask = self::BOOK_MASK;
		$value = $this->int & self::BOOK_MASK;
		$r[] = "($columnRef & $mask) = $value";

		$mask = self::START_MASK;
		$value = ($this->int & self::START_MASK);
		$r[] = "($columnRef & $mask) >= $value";

		$mask = self::END_MASK;
		$value = ($this->int & self::END_MASK);
		$r[] = "($columnRef & $mask) <= $value";

		return "( " . implode(" AND ", $r) . " )";
	}

	/**
	 * Generate a query statement that can be used to search an int column in generic SQL for a passage that overlaps
	 * with the given ScripturNum.
	 *
	 * @param string $columnRef  The name of the column or value to use in the query.
	 *
	 * @return string
	 */
	public function toSqlInclusive(string $columnRef): string
	{
		$r = [];

		$mask = self::BOOK_MASK;
		$value = $this->int & self::BOOK_MASK;
		$r[] = "($columnRef & $mask) = $value";

		$mask = self::START_MASK;
		$value = ($this->int & self::END_MASK) << 12;
		$r[] = "($columnRef & $mask) <= $value";

		$mask = self::END_MASK;
		$value = ($this->int & self::START_MASK) >> 12;
		$r[] = "($columnRef & $mask) >= $value";

		return "( " . implode(" AND ", $r) . " )";
	}
}
