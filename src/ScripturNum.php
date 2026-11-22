<?php


namespace ScripturNum;

use TypeError;

/**
 * The ScripturNum class, which represents a single continuous passage.
 *
 * @property-read ?int $int
 * @property-read ?int $book
 * @property-read ?int $startCh
 * @property-read ?int $startV
 * @property-read ?int $endCh
 * @property-read ?int $endV
 */
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

	/**
	 * For parseString(): never match a book name without chapters or verses after it.
	 */
	const BARE_BOOKS_NONE = 'none';

	/**
	 * For parseString(): match a book name on its own, unless the name is a common word (see Bible::getCommonTerms()).
	 */
	const BARE_BOOKS_UNCOMMON = 'uncommon';

	/**
	 * For parseString(): match any book name on its own, even if it is a common word.
	 */
	const BARE_BOOKS_ALL = 'all';

	/**
	 * For parseString(): text that is not a reference is skipped silently.  Suitable for prose.
	 */
	const UNREADABLE_IGNORE = 'ignore';

	/**
	 * For parseString(): text that is not a reference is reported as an exception.  Suitable for text that is known to be
	 * a list of references.
	 */
	const UNREADABLE_REPORT = 'report';

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

	/**
	 * @param $what
	 *
	 * @since 2.0.0
	 *
	 * @return int
	 */
	public function __get($what)
	{
		switch ($what) {
			case "int":
				return $this->getInt();

			case "book":
				return $this->book;

			case "startCh":
				return $this->startCh;

			case "startV":
				return $this->startV;

			case "endCh":
				return $this->endCh;

			case "endV":
				return $this->endV;
		}
		throw new \Error("The requested property ($what) does not exist.");
	}

    protected function startInt(): int
    {
        return ($this->int & (self::BOOK_MASK | self::START_MASK)) >> 12;
    }

    protected function endInt(): int
    {
        return ($this->int & self::BOOK_MASK) >> 12 | ($this->int & self::END_MASK);
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
		return $this->toString('abbrev');
	}


	/**
	 * Get a human-readable name of the passage.  By default, these are meant for humans to read.
	 *
	 * @return string The name of the passage, as one might pronounce it.
	 * @throws ScripturNumException
	 */
	public function getLongString(): string
	{
		return $this->toString('long');
	}


	/**
	 * Get a string.  Publicly-accessible.
	 *
	 * @since 2.0.0
	 *
	 * @throws ScripturNumException
	 */
	public function toString($options = []): string
	{
		$s = $this->getStringWithSettings($options);
		if (isset($options['callback']) && is_callable($options['callback'])) {
			$s = call_user_func($options['callback'], $s, $this);
		}
		return $s;
	}

    /**
     * Parse and standardize string options.
     *
     * @param $options
     * @return array
     * @throws ScripturNumException
     *
     * @internal
     */
    public static function parseStringOptions($options): array
    {
        $settingKey = 'long';
        if (is_string($options)) {
            $settingKey = $options;
            $options = [];
        } else if (is_array($options) && isset($options['settings'])) {
            $settingKey = $options['settings'];
        }

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

        $options['space'] = $options['space'] ?? static::$stringSettings[$settingKey]['space'];
        $options['cvsep'] = $options['cvsep'] ?? static::$stringSettings[$settingKey]['cvsep'];
        $options['range'] = $options['range'] ?? static::$stringSettings[$settingKey]['range'];
        $options['names'] = isset($options['names']) ? intval($options['names']) : intval(static::$stringSettings[$settingKey]['names']);
        $options['plurl'] = isset($options['plurl']) ? !!$options['plurl'] : !!static::$stringSettings[$settingKey]['plurl'];

        return $options;
    }

	/**
	 * Returns a human-readable string with the settings defined in a given set of settings.
	 *
	 * @param string|array $options The setting set to use, or an array of options.
	 *
	 * @return string The human-intelligible string.
	 * @throws ScripturNumException  If a setting is invalid.
	 */
	protected function getStringWithSettings(&$options): string
	{
		$options = static::parseStringOptions($options);

		$s = $options['space'];
		$c = $options['cvsep'];
		$r = $options['range'];
		$n = $options['names'];
		$p = $options['plurl'];

		if (!isset($options['excludeBook']) || !$options['excludeBook']) {
			$b = static::getBookNames();

			if ($n > count($b[$this->book - 1])) {
				$n = count($b[$this->book - 1]) - 1;
			}

			$b = $b[$this->book - 1][$n];

			if ($this->startCh !== $this->endCh && $p) {
				$b = self::pluralizeBookName($b);
			}
		} else {
			$b = "";
			$s = "";
		}

		if ($this->isWholeBook()) {
			return $b;
		} elseif ($this->isWholeChapters()) {
			if ($this->startCh === $this->endCh) {
				return $b . $s . $this->startCh;
			}

			return $b . $s . $this->startCh . $r . $this->endCh;
		} else {
			$startC = $this->startCh;
			$endC = $this->endCh;
			if (isset($options['excludeCh']) && !!$options['excludeCh']) {
				$startC = "";
				$endC = "";
				$c = "";
			}

			if ($this->bookHasSingleChapter()) {
				if ($this->startV === $this->endV) {
					return $b . $s . $this->startV;
				}

				return $b . $s . $this->startV . $r . $this->endV;
			} elseif ($this->startCh === $this->endCh) {
				if ($this->startV === $this->endV) {
					return $b . $s . $startC . $c . $this->startV;
				}

				return $b . $s . $startC . $c . $this->startV . $r . $this->endV;
			}

			return $b . $s . $startC . $c . $this->startV . $r . $endC . $c . $this->endV;
		}
	}

    /**
     * Remove a passage from this passage, returning an array of ScripturNum objects that represent the difference.
     *
     * @see ScripturNumArray::remove()
     *
     * @param $passageToRemove
     * @return ScripturNumArray
     * @throws ScripturNumException
     */
    public function remove($passageToRemove): ScripturNumArray
    {
        // if no overlap at all, keep whole (condition 1)
        if (!$this->overlapsWith($passageToRemove)) {
            return new ScripturNumArray([$this]);
        }

        // if fully contained, do not keep (condition 2)
        if ($passageToRemove->contains($this)) {
            return new ScripturNumArray([]);
        }

        $return = new ScripturNumArray();

        // if sn starts before other starts, keep sn start to other start-1 (condition 3)
        if ($this->startInt() < $passageToRemove->startInt()) {
            $return[] = static::newFromInts(
                $this->book,
                $this->startCh,
                $this->startV,
                $passageToRemove->startCh,
                $passageToRemove->startV - 1
            );
        }

        // if sn ends after other ends, keep other end+1 to sn end (condition 4)
        if ($this->endInt() > $passageToRemove->endInt()) {
            $return[] = static::newFromInts(
                $this->book,
                $passageToRemove->endCh,
                $passageToRemove->endV + 1,
                $this->endCh,
                $this->endV
            );
        }

        return $return;
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
	 * @since 2.0.0
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
	 * @since 2.0.0
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
	 * @since 2.0.0
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
     * Get a ScripturNum for the whole book of the Bible that this passage is in, or this one if already a whole book.
     *
     * @return ScripturNum
     * @throws ScripturNumException
     */
    public function getWholeBook(): ScripturNum
    {
        if ($this->isWholeBook()) {
            return $this;
        }

        $v = Bible::getVerseCounts();

        $i = static::refNumsToInt($this->book, 1, null, count($v[$this->book - 1]), null);
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
	 * @since 2.0.0
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
	 * @since 2.0.0
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
	 * @since 2.0.0
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
	 * @since 2.0.0
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
	 * @since 2.0.0
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
		int $startCh, $startV = null,
        $endCh = null, $endV = null): ScripturNum
	{
		self::validateRefNums($book, $startCh, $startV, $endCh, $endV);
		$int = self::refNumsToInt($book, $startCh, $startV, $endCh, $endV);
		$c   = static::class;

		return new $c($int);
	}


    /**
     * Either throws the exception, or--if an array was provided to collect them--adds it to the array.  If the
     * exception doesn't already know what text it couldn't read, it is told.
     *
     * @since 2.3.0
     *
     * @param ScripturNumException $e
     * @param bool|array|null      $exceptions If an array, the exception is appended to it.  Otherwise, it is thrown.
     * @param string|null          $input      The text that couldn't be read.
     *
     * @return void
     * @throws ScripturNumException
     */
    protected static function report(ScripturNumException $e, &$exceptions, $input = null)
    {
        if ($e->getInput() === null && $input !== null) {
            $e->setInput($input);
        }
        if (is_array($exceptions)) {
            $exceptions[] = $e;
            return;
        }
        throw $e;
    }


	/**
	 * Takes a string that represents a single passage and returns it as an int.
	 *
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
	 * Takes a reference string and returns a plurality of ints for corresponding passages.  This should only be passed
	 * values that are known to be references, not just any text. Use self::extractFromString for that.
	 *
	 * @param string     $string A human-readable scripture reference that should be converted to one or more ints.
	 * Allows for commas and semicolons.
	 * @param bool|array $exceptions Pass an array to this parameter, and it will be populated with any exceptions that
	 * occur.  By passing an array, this will not throw the exception, and by not throwing the exception, execution
	 * continues and you may be apprised of multiple errors that may exist.
	 *
	 * @return int[] The ints.
	 * @throws ScripturNumException
	 * @see self::extractFromString();
	 *
	 * @since 2.0.0
	 *
	 */
	public static function stringToInts(string $string, &$exceptions = false): array
	{
		return static::parseSpan($string, $exceptions);
	}

	/**
	 * Standardizes the punctuation and whitespace of reference text, so that conjunctions, "through", and the like are
	 * expressed as the separators that the parser understands.
	 *
	 * @since 2.3.0
	 *
	 * @param string $string
	 *
	 * @return string
	 */
	protected static function normalize(string $string): string
	{
		$cj = static::getConjunctions();

		// standardize punctuation bits
		$string = str_replace([$cj['and'], '&', $cj['chapter'], $cj['through'], '&endash;', '–'],
		                      [',', ',', '', '-', '-', '-'],
		                      $string);

		// Remove duplicate spaces (Can't remove all spaces because spaces may occur in book names like Song of Songs
		return preg_replace("/\s\s+/", ' ', $string);
	}

	/**
	 * Parses a single span: one book's name, followed by any number of references within that book.
	 *
	 * @since 2.3.0
	 *
	 * @param string     $string     The span, e.g. "John 3:16, 18; 4:2"
	 * @param bool|array $exceptions Pass an array to have exceptions collected, rather than thrown.
	 *
	 * @return int[]
	 * @throws ScripturNumException
	 */
	protected static function parseSpan(string $string, &$exceptions = false): array
	{
		$original = trim($string);

		$string = static::normalize($string);

		// Look for right-most alpha char.  This should separate book name from numerical ref.
		preg_match('/.*([a-zA-Z])/', $string, $matches, PREG_OFFSET_CAPTURE);
		$spaceIndex = (int)$matches[1][1] + 1; // Space index may not actually be a space.
		$book       = trim(substr($string, 0, $spaceIndex));
		$ref        = substr($string, $spaceIndex);

		// Change book name to number
		try {
			$book = self::bookNameToBookNum($book);
		} catch (ScripturNumException $e) {
			static::report($e, $exceptions, $original);
			return [];
		}

		$ints = [];

		// Remove all spaces from reference.
		$ref = preg_replace("/\s*/", '', $ref);

		// A reference of nothing but separators (e.g. "John;") is just a bare book name.
		if (trim($ref, ',;') === '') {
			$ref = '';
		}

		$levelsOfCommas = (2 * (strpos($ref, ';') > -1) + (strpos($ref, ',') > -1));
		foreach (explode(";", $ref) as $sA) {
			if ($levelsOfCommas > 1) {
				unset($startCh, $startV, $endCh, $endV);
			}
			foreach (explode(",", $sA) as $s) {
				$s = trim($s);
				if ($s === "" && $ref !== "") // empty items are ignored, unless there's nothing but a book name.
					continue;
				try {
					// Parse numbers
					self::refNumStringToRefNums($s, $startCh, $startV, $endCh, $endV, true);

					// Assemble and return the int
					self::validateRefNums($book, $startCh, $startV, $endCh, $endV);
					$ints[] = self::refNumsToInt($book, $startCh, $startV, $endCh, $endV);

				} catch (ScripturNumException $e) {
					static::report($e, $exceptions, $original);
					continue;
				}
			}
		}

		return $ints;
	}

	/**
	 * Validate that reference numbers can be matched to verses that exist.
	 *
	 * @since 2.0.0  Previously, this functionality was handled within the refNumsToInt function.
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
		if ($startCh > count(Bible::getVerseCounts()[$book]) ||
            $endCh > count(Bible::getVerseCounts()[$book]
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
		if ($endCh === null) { // single chapter
			$endCh = $startCh;
		}
		if ($startCh !== null) $startCh--;
		if ($startV !== null) $startV--;
		if ($endCh !== null) $endCh--;
		if ($endV !== null) $endV--;
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
	 * @param bool   $useHints If true, will consider the values provided to the chapter and verse parameters in
	 *     parsing the string.  Default false.
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
     * Given a string with any kind of text content, this method will search for any human-readable scripture references
     * and try to parse them into discrete passages.  Returns a ScripturNumArray.
     *
     * @param string $string
     * @param bool $excludeAllBookOnlyRefs
     * @param null $exceptions  To have exceptions collected rather than thrown, pass an array to this parameter.
     *
     * @return ScripturNumArray
     * @throws ScripturNumException
     * @since 2.0.0
     *
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

        $combinedMatches = [];
		foreach ($regExSets as $re) {
            $b = implode("|", $re['bs']);
            $plusOrStar = $re['ps'];
            /** @noinspection RegExpUnnecessaryNonCapturingGroup -- They really are necessary. */
            $pattern = "/\b(?:$b)\.?(?:[-\s,;&]*1?\d{1,2}:?(?:1?\d{1,2})?)$plusOrStar\b/i";

            preg_match_all($pattern, $string, $matches, PREG_OFFSET_CAPTURE);

            $combinedMatches = array_merge($combinedMatches, $matches[0]);
        }

        // Sort matches by position in string
        usort($combinedMatches, function($a, $b) {
            return $a[1] - $b[1];
        });

        $lastStart = -1;
        $lastEnd = -1;
        foreach ($combinedMatches as $m) {
            if ($m[1] !== $lastStart && $m[1] < $lastEnd) {
                // Overlapping match.  Skip it.
                continue;
            }

            $ints = static::stringToInts($m[0], $exceptions);

            foreach($ints as $i) {
                $results[] = new static($i);
            }
            $lastStart = $m[1];
            $lastEnd = $m[1] + strlen($m[0]);
        }

		return $results;
	}

	/**
	 * Finds and parses the scripture references in a string, which may be prose or a list of references.
	 *
	 * - For prose, use the defaults.  Text that isn't a reference is skipped, and a book name on its own is found only
	 *   if it isn't a common word.
	 * - For text that is known to be a list of references (for instance, from an editing field), use
	 *   BARE_BOOKS_ALL and UNREADABLE_REPORT.  Every reference that can be read is returned, and every item that can't
	 *   is reported, with its text available from the exception's getInput().
	 *
	 * References that are found but are not valid (e.g. John 3:99) are always reported, whatever $unreadableText is.
	 *
	 * @since 2.3.0
	 *
	 * @param string     $string         The text to read.
	 * @param string     $bareBookNames  Whether a book name with nothing after it counts as a reference to the whole
	 *                                   book.  One of the BARE_BOOKS_* constants.
	 * @param string     $unreadableText What to do with text that isn't a reference.  One of the UNREADABLE_*
	 *                                   constants.
	 * @param bool|array $exceptions     To have exceptions collected rather than thrown, pass an array.  Otherwise, the
	 *                                   first exception is thrown.
	 *
	 * @return ScripturNumArray
	 * @throws ScripturNumException  If an exception occurs and $exceptions isn't an array.
	 * @throws \InvalidArgumentException  If $bareBookNames or $unreadableText isn't one of the constants.
	 */
	public static function parseString(string $string,
	                                   string $bareBookNames = self::BARE_BOOKS_UNCOMMON,
	                                   string $unreadableText = self::UNREADABLE_IGNORE,
	                                   &$exceptions = null): ScripturNumArray
	{
		if (!in_array($bareBookNames, [self::BARE_BOOKS_NONE, self::BARE_BOOKS_UNCOMMON, self::BARE_BOOKS_ALL], true)) {
			throw new \InvalidArgumentException("Invalid value for \$bareBookNames: $bareBookNames");
		}
		if (!in_array($unreadableText, [self::UNREADABLE_IGNORE, self::UNREADABLE_REPORT], true)) {
			throw new \InvalidArgumentException("Invalid value for \$unreadableText: $unreadableText");
		}

		$results = new ScripturNumArray();
		$masked = static::maskPunctuation($string);
		$reportGaps = $unreadableText === self::UNREADABLE_REPORT;

		$pos = 0;
		foreach (static::scan($masked, $bareBookNames) as $m) {
			$start = $m[1];
			$end = $start + strlen($m[0]);

			if ($reportGaps) {
				$gap = static::unreadableText($string, $masked, $pos, $start);
				if ($gap !== null) {
					static::report(new ScripturNumException("Text is not a scripture reference.", 0, null, $gap), $exceptions);
				}
			}

			foreach (static::parseSpan(substr($string, $start, $end - $start), $exceptions) as $i) {
				$results[] = new static($i);
			}
			$pos = max($pos, $end);
		}

		if ($reportGaps) {
			$gap = static::unreadableText($string, $masked, $pos, strlen($string));
			if ($gap !== null) {
				static::report(new ScripturNumException("Text is not a scripture reference.", 0, null, $gap), $exceptions);
			}
		}

		return $results;
	}

	/**
	 * Replaces conjunctions, dashes, and the like with the separator they stand for, so that they can be recognized when
	 * scanning.  The length in bytes is preserved, so that offsets in the result are also offsets in the original.
	 *
	 * @since 2.3.0
	 *
	 * @param string $string
	 *
	 * @return string
	 */
	protected static function maskPunctuation(string $string): string
	{
		$cj = static::getConjunctions();
		$replacements = [
			$cj['and']     => ',',
			$cj['through'] => '-',
			$cj['chapter'] => ' ',
			'&endash;'     => '-',
			'&ndash;'      => '-',
			"\u{2013}"     => '-', // en dash
		];

		foreach ($replacements as $word => $sep) {
			$word = (string)$word;
			if ($word === '') {
				continue;
			}
			$quoted = preg_quote($word, '/');
			$pattern = '/' . $quoted . '/iu';
			if (preg_match('/^[\p{L}\p{N}]/u', $word)) { // words (as opposed to punctuation) need to stand alone
				$pattern = '/(?<![\p{L}\p{N}])' . $quoted . '(?![\p{L}\p{N}])/iu';
			}
			$masked = preg_replace_callback($pattern, function ($m) use ($sep) {
				return str_pad($sep, strlen($m[0]), ' ');
			}, $string);
			if ($masked !== null) { // null if the string isn't valid UTF-8.
				$string = $masked;
			}
		}

		return $string;
	}

	/**
	 * Finds the spans of a string that look like references.
	 *
	 * @since 2.3.0
	 *
	 * @param string $masked        The string, as processed by maskPunctuation().
	 * @param string $bareBookNames One of the BARE_BOOKS_* constants.
	 *
	 * @return array[]  Each is [the matched text, its offset], in the order found.  Matches that overlap an earlier one
	 *                  are left out.
	 */
	protected static function scan(string $masked, string $bareBookNames): array
	{
		$allBookNames = array_merge(...static::getBookNames());

		switch ($bareBookNames) {
			case self::BARE_BOOKS_NONE:
				$sets = [[$allBookNames, '+']];
				break;

			case self::BARE_BOOKS_ALL:
				$sets = [[$allBookNames, '*']];
				break;

			default: // BARE_BOOKS_UNCOMMON
				$common = static::getCommonTerms();
				$sets = [
					[array_diff($allBookNames, $common), '*'],
					[$common, '+']
				];
		}

		$matches = [];
		foreach ($sets as $set) {
			if (count($set[0]) === 0) {
				continue;
			}
			$b = implode("|", array_map(function ($n) {
				return preg_quote($n, '/');
			}, $set[0]));
			$pattern = "/\\b(?:$b)\\.?(?:[-\\s,;&]*1?\\d{1,2}:?(?:1?\\d{1,2})?){$set[1]}\\b/i";
			preg_match_all($pattern, $masked, $found, PREG_OFFSET_CAPTURE);
			$matches = array_merge($matches, $found[0]);
		}

		usort($matches, function ($a, $b) {
			return $a[1] - $b[1];
		});

		$ret = [];
		$lastStart = -1;
		$lastEnd = -1;
		foreach ($matches as $m) {
			if ($m[1] !== $lastStart && $m[1] < $lastEnd) {
				continue; // Overlapping match.
			}
			$ret[] = $m;
			$lastStart = $m[1];
			$lastEnd = $m[1] + strlen($m[0]);
		}
		return $ret;
	}

	/**
	 * Gets the part of the original string that is neither a reference nor a mere separator, if there is any.
	 *
	 * @since 2.3.0
	 *
	 * @param string $original
	 * @param string $masked   The string, as processed by maskPunctuation().
	 * @param int    $from     Offset of the start of the part to check.
	 * @param int    $to       Offset of the end of the part to check.
	 *
	 * @return string|null  The original text, without surrounding separators, or null if there is nothing there.
	 */
	protected static function unreadableText(string $original, string $masked, int $from, int $to)
	{
		if ($to <= $from) {
			return null;
		}
		$gap = substr($masked, $from, $to - $from);
		if (!preg_match('/[^\s,;&.:-]/', $gap, $first, PREG_OFFSET_CAPTURE)) {
			return null;
		}
		$last = strlen(rtrim($gap, " \t\r\n,;&.:-"));
		return substr($original, $from + $first[0][1], $last - $first[0][1]);
	}

	/**
	 * Test whether a given passage is within a given larger passage.  Will also return true if they are the same.
	 *
	 * @since 2.0.0
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
	 * @since 2.0.0
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
	 * @since 2.0.0
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
	 * @since 2.0.0
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
	 * @since 2.0.0
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
	 * @since 2.0.0
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
	 * Combines two adjacent or overlapping passages into one int.
	 *
	 * @since 2.0.0
	 *
	 * @param int $otherPassage
	 *
	 * @return int
	 * @throws ScripturNumException
	 *@since 2.0.0
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
	 * Combines two adjacent or overlapping passages into one ScripturNum.
	 *
	 * @since 2.0.0
	 *
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
	 * Generate a query statement that can be used to search three numeric columns in generic SQL for a passage that is
	 * entirely contained within the given ScripturNum.  The three columns should represent book, start index, and end
	 * index respectively.
	 *
	 * @param string $bookColumnRef  The name of the column or value to use for the book index.
	 * @param string $startColumnRef The name of the column or value to use for the start index.
	 * @param string $endColumnRef   The name of the column or value to use for the end index.
	 *
	 * @return string
	 */
	public function toSqlExclusive3(string $bookColumnRef, string $startColumnRef, string $endColumnRef): string
	{
		$bookValue = $this->int >> 24;
		$startValue = ($this->int & self::START_MASK) >> 12;
		$endValue = ($this->int & self::END_MASK);

		if ($this->isSingleVerse()) {
			return "( $bookColumnRef = $bookValue AND $startColumnRef = $startValue AND $endColumnRef = $endValue )";
		}

		$r = [];
		$r[] = "$bookColumnRef = $bookValue";
		$r[] = "$startColumnRef >= $startValue";
		$r[] = "$endColumnRef <= $endValue";

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

	/**
	 * Generate a query statement that can be used to search three numeric columns in generic SQL for a passage that
	 * overlaps with the given ScripturNum.  The three columns should represent book, start index, and end index
	 * respectively.
	 *
	 * @param string $bookColumnRef  The name of the column or value to use for the book index.
	 * @param string $startColumnRef The name of the column or value to use for the start index.
	 * @param string $endColumnRef   The name of the column or value to use for the end index.
	 *
	 * @return string
	 */
	public function toSqlInclusive3(string $bookColumnRef, string $startColumnRef, string $endColumnRef): string
	{
		$bookValue = $this->int >> 24;
		$startValue = ($this->int & self::START_MASK) >> 12;
		$endValue = ($this->int & self::END_MASK);

		$r = [];
		$r[] = "$bookColumnRef = $bookValue";
		$r[] = "$startColumnRef <= $endValue";
		$r[] = "$endColumnRef >= $startValue";

		return "( " . implode(" AND ", $r) . " )";
	}


    /**
     * Get a ScripturNumArray representing the whole Bible.
     *
     * @return ScripturNumArray
     * @throws ScripturNumException -- would only be thrown if there's something peculiar with a custom Bible class.
     * @since 2.1.0
     */
    public static function getWholeBible(): ScripturNumArray
    {
        $sna = new ScripturNumArray();
        foreach (ScripturNum::getBookNames() as $bookNames) {
            $sna[] = new ScripturNum($bookNames[0]);
        }
        return $sna;
    }
}
