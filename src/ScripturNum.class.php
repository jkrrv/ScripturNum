<?php


namespace ScripturNum;

require_once 'Bible.class.php';

class ScripturNum
{
	protected $int;
	protected $book;
	protected $startCh;
	protected $startV;
	protected $endCh;
	protected $endV;

	public function __construct($intOrString)
	{
		if (is_numeric($intOrString)) {
			$int = intval($intOrString);
		} else {
			$int = self::string2int($intOrString);
		}
		self::_int2refNums($int, $this->book, $this->startCh, $this->startV, $this->endCh, $this->endV);
		$this->int = $int;
	}


	public function toInt() {
		return $this->int;
	}


	public function __toString()
	{
		return $this->getLongString();
	}


	public function getLongString()
	{
		$b = Bible::getBookNames();
		if ($this->isWholeBook()) {
			return $b[$this->book - 1][0];
		} elseif ($this->isWholeChapters()) {
			if ($this->startCh === $this->endCh) {
				return $b[$this->book - 1][0] . " " . $this->startCh;
			}
			return $b[$this->book - 1][0] . " " . $this->startCh . "-" . $this->endCh;
		} else {
			if ($this->bookHasSingleChapter()) {
				if ($this->startV === $this->endV) {
					return $b[$this->book - 1][0] . " " . $this->startV;
				}
				return $b[$this->book - 1][0] . " " . $this->startV . "-" . $this->endV;
			} elseif ($this->startCh === $this->endCh) {
				if ($this->startV === $this->endV) {
					return $b[$this->book - 1][0] . " " . $this->startCh . ":" . $this->startV;
				}
				return $b[$this->book - 1][0] . " " . $this->startCh . ":" . $this->startV . "-" . $this->endV;
			}
			return $b[$this->book - 1][0] . " " . $this->startCh . ":" . $this->startV . "-" . $this->endCh . ":" . $this->endV;
		}
	}


	public function getAbbrev()
	{
		$separator = ':'; //TODO this should be a proper option
		$b = Bible::getBookNames();
		if ($this->isWholeBook()) {
			return $b[$this->book - 1][1];
		} elseif ($this->isWholeChapters()) {
			if ($this->startCh === $this->endCh) {
				return $b[$this->book - 1][1] . $this->startCh;
			}
			return $b[$this->book - 1][1] . $this->startCh . "-" . $this->endCh;
		} else {
			if ($this->bookHasSingleChapter()) {
				if ($this->startV === $this->endV) {
					return $b[$this->book - 1][1] . $this->startV;
				}
				return $b[$this->book - 1][1] . $this->startV . "-" . $this->endV;
			} elseif ($this->startCh === $this->endCh) {
				if ($this->startV === $this->endV) {
					return $b[$this->book - 1][1] . $this->startCh . $separator . $this->startV;
				}
				return $b[$this->book - 1][1] . $this->startCh . $separator . $this->startV . "-" . $this->endV;
			}
			return $b[$this->book - 1][1] . $this->startCh . $separator . $this->startV . "-" . $this->endCh . $separator . $this->endV;
		}
	}


	public function isWholeChapters()
	{
		$v = Bible::getVerseCounts();
		return ($this->startV === 1 && $this->endV === $v[$this->book - 1][$this->endCh - 1]);
	}


	public function isWholeBook()
	{
		$v = Bible::getVerseCounts();
		return ($this->startCh === 1
		        && $this->startV === 1
		        && $this->endCh === count($v[$this->book - 1])
		        && $this->endV === $v[$this->book - 1][$this->endCh - 1]);
	}


	public function bookHasSingleChapter()
	{
		return Bible::bookHasSingleChapter($this->book - 1);
	}


	protected static function _bookName2bookNum($bookName)
	{
		$books = Bible::getBookNames();
		foreach ($books as $book => $bookNames) {
			if (Bible::in_arrayi($bookName, $bookNames)) {
				return $book + 1;
			}
		}
		throw new ScripturNumException('Book name is invalid.');
	}


	public static function newFromParsed($bookStr, $startCh = null, $startV = 1, $endCh = null, $endV = null)
	{
		$book = self::_bookName2bookNum($bookStr);
		$int = self::_refNums2int($book, $startCh, $startV, $endCh, $endV);
		$c = get_called_class();
		return new $c($int);
	}


	/**
	 * @param int $book The book of the Bible the range is within. 1-rel.
	 * @param int $startCh The chapter of the start of the range. 1-rel.
	 * @param int $startV The verse of the start of the range.  1-rel. Defaults to 1.
	 * @param int|null $endCh The end chapter of the range.  If null or not provided, assumed to be the same as the start chapter.
	 * @param int|null $endV The end verse of the range.  If null or not provided, assumed to be the end of the chapter.
	 * @return ScripturNum The ScripturNum object that represents this range of scripture.
	 *
	 * @throws ScripturNumException A chapter was requested that does not exist within the requested book.
	 * @throws ScripturNumException A verse was requested that does not exist within the requested range.
	 */
	public static function newFromInts($book, $startCh, $startV = null, $endCh = null, $endV = null)
	{
		$int = self::_refNums2int($book, $startCh, $startV, $endCh, $endV);
		$c = get_called_class();
		return new $c($int);
	}


	public static function newFromString($string)
	{
		$c = get_called_class();
		return new $c($string);
	}


	public static function string2int($string)
	{
		// Standardize dashes
		$string = str_replace(['&ndash;', '–'], '-', $string);

		// Remove duplicate spaces
		$string = preg_replace("/\s\s+/i", ' ', $string);

		// Remove spaces among the numerical parts.
		$string = preg_replace("/(\d+)\s*([-.:])\s+(\d+)/i", '$1$2$3', $string);
		$string = preg_replace("/(\d+)\s+([-.:])\s*(\d+)/i", '$1$2$3', $string);

		// Look for right-most space or alpha char.  This should separate book name from numerical ref.
		preg_match_all('/[a-zA-Z\s]/', $string, $asdf, PREG_OFFSET_CAPTURE);
		$spaceIndex = array_pop($asdf[0])[1] + 1;
		$book = trim(substr($string, 0, $spaceIndex));
		$ref = substr($string, $spaceIndex);

		// Parse numbers
		self::_refNumString2refNums($ref, $startCh, $startV, $endCh, $endV);

		// Change book name to number
		$book = self::_bookName2bookNum($book);

		// Assemble and return the int
		return self::_refNums2int($book, $startCh, $startV, $endCh, $endV);
	}


	protected static function _refNums2int($book, $startCh, $startV, $endCh, $endV)
	{
		$book--;
		$int = ($book) << 24;
		if ($startCh > count(Bible::getVerseCounts()[$book]) || $endCh > count(Bible::getVerseCounts()[$book])) { // invalid request OR request for a single-chapter book.
			if (Bible::bookHasSingleChapter($book) && $startV === null && $endV === null) { // single-chapter book.
				$startV = $startCh;
				$endV = $endCh;
				$startCh = 1;
				$endCh = 1;
			} else {
				throw new ScripturNumException("A chapter was requested that does not exist within the requested book.");
			}
		}
		if ($startCh === null && $endCh === null) { // whole book
			$startCh = 1;
			$endCh = count(Bible::getVerseCounts()[$book]);
		}
		if ($endCh == null) { // single chapter
			$endCh = $startCh;
		}
		$startCh--;
		$startV--;
		$endCh--;
		$endV--;
		if ($startV > Bible::getVerseCounts()[$book][$startCh] || $endV > Bible::getVerseCounts()[$book][$endCh]) {
			throw new ScripturNumException("A verse was requested that does not exist within the requested chapter.");
		}
		if ($endV === null) {
			$endV = Bible::getVerseCounts()[$book][$endCh] - 1;
		}

		$ch = 0;
		while ($ch < ($startCh)) {
			$startV += Bible::getVerseCounts()[$book][$ch];
			$ch++;
		}
		$ch = 0;
		while ($ch < ($endCh)) {
			$endV += Bible::getVerseCounts()[$book][$ch];
			$ch++;
		}

		$int += ($startV << 12);
		$int += ($endV);

		return $int;
	}


	/**
	 * This function reads through the ref string one character at a time to interpret what's going on.
	 *
	 * @param String $string The string to parse.
	 * @param $chapterStart
	 * @param $verseStart
	 * @param $chapterEnd
	 * @param $verseEnd
	 *
	 * @throws ScripturNumException
	 */
	protected static function _refNumString2refNums($string, &$chapterStart, &$verseStart, &$chapterEnd, &$verseEnd)
	{
		if (preg_match('/[a-zA-Z]/', $string)) {
			throw new ScripturNumException("Parse Ref only handles the numerical part of the reference.  Alphabetical characters are not permitted.");
		}

		$startNums = [];
		$endNums = [];
		$currentNumber = '';
		$beforeHyphen = true;

		foreach (str_split($string . ' ') as $char) { // adding the extra character allows the last digit to actually get parsed.
			if (is_numeric($char)) {
				// still finding the full number
				$currentNumber .= $char;

			} else {
				// End of number.  Int-ify and assign to appropriate half.
				$currentNumber = intval($currentNumber);
				if ($beforeHyphen) {
					$startNums[] = $currentNumber;
				} else {
					$endNums[] = $currentNumber;
				}
				$currentNumber = ''; // reset for next number.

				if ($char == '-')
					$beforeHyphen = false;
			}
		}

		switch (count($startNums) * 10 + count($endNums)) {
			case 10: // one full chapter, or one full book
				if ($startNums[0] === 0) { // whole book
					$chapterStart = null;
				} else {
					$chapterStart = $startNums[0];
					$chapterEnd = $chapterStart;
				}
				break;
			case 11: // multiple full chapters
				$chapterStart = $startNums[0];
				$chapterEnd = $endNums[0];
				break;
			case 12: // full chapter to part of chapter
				$chapterStart = $startNums[0];
				$chapterEnd = $endNums[0];
				$verseEnd = $endNums[1];
				break;
			case 20: // one verse.  This is the weird case.
				$chapterStart = $startNums[0];
				$verseStart = $startNums[1];
				$verseEnd = $verseStart;
				break;
			case 21: // multiple verses from one chapter.
				$chapterStart = $startNums[0];
				$verseStart = $startNums[1];
				$verseEnd = $endNums[0];
				break;
			case 22: // multiple verses from across chapters
				$chapterStart = $startNums[0];
				$verseStart = $startNums[1];
				$chapterEnd = $endNums[0];
				$verseEnd = $endNums[1];
				break;
			default:
				throw new ScripturNumException("Badly formed numerical reference.");
		}
	}


	protected static function _int2refNums($int, &$book, &$chapterStart, &$verseStart, &$chapterEnd, &$verseEnd)
	{
		$book = $int >> 24;
		$int -= ($book << 24);

		$refAIndex = $int >> 12;
		$int -= ($refAIndex << 12);

		$refBIndex = &$int;

		if ($refBIndex < $refAIndex)
			throw new ScripturNumException('Unintelligible Reference');

		self::_bkIndex2singleRef($book, $refAIndex, $chapterStart, $verseStart);
		self::_bkIndex2singleRef($book, $refBIndex, $chapterEnd, $verseEnd);

		$book++;
	}


	/**
	 * @param int $int The int representing teh full passage
	 * @param string|int $concatStart The concatenated "number" possibly larger than an int representing the start of the passage.
	 * @param string|int $concatEnd The concatenated "number" possibly larger than an int representing the end of the passage.
	 */
	public static function int2concats($int, &$concatStart, &$concatEnd)
	{
		$p = [0, 0, 0, 0, 0];
		self::_int2refNums($int, $p[0], $p[1], $p[2], $p[3], $p[4]);
		$concatStart = $p[2] + ($p[1] * 1000) + ($p[0] * 1000000);
		$concatEnd = $p[4] + ($p[3] * 1000) + ($p[0] * 1000000);
	}


	protected static function _bkIndex2singleRef($book, $index, &$chapter, &$verse)
	{
		$index++;
		$v = Bible::getVerseCounts();
		$chapter = 0;
		if (!isset($v[$book]))
			throw new ScripturNumException("There are not that many books in the Bible.");
		while ($index > $v[$book][$chapter]) {
			$index -= $v[$book][$chapter];
			if (!isset($v[$book][++$chapter]))
				throw new ScripturNumException("There are not that many verses in this book.");
		}
		$chapter++;
		$verse = $index;
	}
}


class ScripturNumException extends \Exception
{

}

