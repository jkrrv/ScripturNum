<?php

namespace ScripturNum;

use ArrayAccess;
use Countable;
use Iterator;

/**
 * A class that contains a number of ScripturNum objects, but which can do useful things like print a human-readable
 * string.
 *
 * @since 2.0.0
 */
class ScripturNumArray implements ArrayAccess, Iterator, Countable
{
	/**
	 * @var ScripturNum[]
	 */
	protected $container = [];
	protected $sortEnqueued = false;
	protected $hasMultipleBooks = false;
	protected $hasMultiplePassagesFromABook = false;
	protected $hasMultiplePassagesFromAChapter = false;


	public function __construct($initialValues = [])
	{
		foreach ($initialValues as $k => $i) {
			if (is_object($i) && get_class($i) === ScripturNum::class) {
				$this->offsetSet($k, $i);
				continue;
			}
			try {
				$s = new ScripturNum($i);
				$this->offsetSet($k, $s);
			} catch (ScripturNumException $e) {}
		}
	}

	/**
	 * @param ScripturNum $a
	 * @param ScripturNum $b
	 *
	 * @return int
	 */
	protected static function sortCompare(ScripturNum $a, ScripturNum $b): int
	{
		return $a->getInt() <=> $b->getInt();
	}

	/**
	 * Sorts the container.
	 *
	 * @return void
	 */
	protected function sort()
	{
		uasort($this->container, [static::class, 'sortCompare']);
	}

	/**
	 * @return void
	 */
	protected function combineAdjacents()
	{
		$prev = null;
		$prevK = null;
		$this->hasMultipleBooks = false;
		$this->hasMultiplePassagesFromABook = false;
		$this->hasMultiplePassagesFromAChapter = false;
		foreach ($this->container as $k => $curr) {
			if ($prev != null) {
				if ($prev->overlapsOrAdjacent($curr)) {
					/** @noinspection PhpUnhandledExceptionInspection -- Exception won't happen with overlapsOrAdjacent check */
					$this->container[$prevK] = $prev->combineWith($curr);
					unset($this->container[$k]);
					$prev = $this->container[$prevK];
					continue;
				}

				if ($curr->book === $prev->book) {
					$this->hasMultiplePassagesFromABook = true;
					if ($prev->endCh === $curr->startCh) {
						$this->hasMultiplePassagesFromAChapter = true;
					}
				} else {
					$this->hasMultipleBooks = true;
				}
			}
			$prev = $curr;
			$prevK = $k;
		}
	}

	protected function sortAndCombineIfNeeded()
	{
		if ($this->sortEnqueued) {
			$this->sort();
			$this->combineAdjacents();
			$this->sortEnqueued = false;
		}
	}

	/**
	 * Whether a offset exists
	 * @link https://php.net/manual/en/arrayaccess.offsetexists.php
	 *
	 * @param mixed $offset
	 * An offset to check for.
	 *
	 * @return bool true on success or false on failure.
	 *
	 * The return value will be casted to boolean if non-boolean was returned.
	 */
	public function offsetExists($offset): bool
	{
		return isset($this->container[$offset]);
	}

	/**
	 * Offset to retrieve
	 * @link https://php.net/manual/en/arrayaccess.offsetget.php
	 *
	 * @param mixed $offset
	 * The offset to retrieve.
	 *
	 * @return ScripturNum Value Can return all value types.
	 */
	public function offsetGet($offset): ScripturNum
	{
		$this->sortAndCombineIfNeeded();
		return $this->container[$offset];
	}

	/**
	 * Offset to set
	 * @link https://php.net/manual/en/arrayaccess.offsetset.php
	 *
	 * @param mixed   $offset
	 * The offset to assign the value to.
	 *
	 * @param ScripturNum $value
	 * The value to set.
	 *
	 * @return void
	 *
	 * @See Issue #11
	 */
	#[\ReturnTypeWillChange]
	public function offsetSet($offset, $value)
	{
		if (is_null($offset)) {
			$this->container[] = $value;
		} else {
			$this->container[$offset] = $value;
		}
		$this->sortEnqueued = true;
	}

	/**
	 * Offset to unset
	 * @link https://php.net/manual/en/arrayaccess.offsetunset.php
	 *
	 * @param mixed $offset
	 * The offset to unset.
	 *
	 * @return void
	 *
	 * @See Issue #11
	 */
	#[\ReturnTypeWillChange]
	public function offsetUnset($offset)
	{
		$this->sortEnqueued = true;
		unset($this->container[$offset]);
	}

	/**
	 * Return the current element
	 * @link https://php.net/manual/en/iterator.current.php
	 * @return ScripturNum Can return any type.
	 */
	public function current(): ScripturNum
	{
		$this->sortAndCombineIfNeeded();
		return current($this->container);
	}

	/**
	 * Move forward to next element
	 *
	 * @link https://php.net/manual/en/iterator.next.php
	 *
	 * @return void Any returned value is ignored.
	 *
	 * @See Issue #11
	 */
	#[\ReturnTypeWillChange]
	public function next()
	{
		next($this->container);
	}

	/**
	 * Return the key of the current element
	 *
	 * @link https://php.net/manual/en/iterator.key.php
	 *
	 * @return int|string|null TKey on success, or null on failure.
	 *
	 * @See Issue #11
	 */
	#[\ReturnTypeWillChange]
	public function key()
	{
		return key($this->container);
	}

	/**
	 * Checks if current position is valid
	 * @link https://php.net/manual/en/iterator.valid.php
	 * @return bool The return value will be casted to boolean and then evaluated.
	 * Returns true on success or false on failure.
	 */
	public function valid(): bool
	{
		$k = $this->key();
        if ($k === null) {
            $k = '';
        }
		return isset($this->container[$k]);
	}

	/**
	 * Rewind the Iterator to the first element
	 *
	 * @link https://php.net/manual/en/iterator.rewind.php
	 *
	 * @return void Any returned value is ignored.
	 *
	 * @See Issue #11
	 */
	#[\ReturnTypeWillChange]
	public function rewind()
	{
		reset($this->container);
	}

	/**
	 * Count elements of an object
	 *
	 * @link https://php.net/manual/en/countable.count.php
	 *
	 * @return int<0,max> The custom count as an integer.
	 */
	public function count(): int
	{
		$this->sortAndCombineIfNeeded();
		return count($this->container);
	}

	/**
	 * @return string
	 */
	public function __toString(): string
	{
		return $this->getString();
	}

	/**
	 * @param array $options
	 *
	 * @return string
	 */
	public function getString(array $options = []): string
	{
		$this->sortAndCombineIfNeeded();
		$ret = "";

		$prev = null;
		foreach ($this->container as $curr) {
			try {
				$options['excludeCh'] = false;
				$options['excludeBook'] = false;
				if ($prev !== null) {
					$c = ', ';
					if ($prev->book === $curr->book) { // same book
						$options['excludeBook'] = true;
						if ($prev->startCh !== $curr->endCh && $this->hasMultiplePassagesFromABook) { // diff chapter
							$c = '; ';
						}
						if ($this->hasMultiplePassagesFromAChapter && $prev->startCh === $curr->endCh) {
							$options['excludeCh'] = true;
						}
					} else if ($this->hasMultiplePassagesFromABook) { // Different books, when a book has multiple items
						$c = "; ";
					}
					$ret .= $c;
				}
				$ret .= $curr->toString($options);
				$prev = $curr;
			} catch (ScripturNumException $e) {
				continue;
			}
		}

		return $ret;
	}
}