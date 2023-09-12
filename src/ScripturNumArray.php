<?php


namespace ScripturNum;

use ArrayAccess;
use Countable;
use Iterator;

class ScripturNumArray implements ArrayAccess, Iterator, Countable
{
	protected $container = [];
	protected $position = 0;
	
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
	 *
	 * @return ScripturNum Value Can return all value types.
	 */
	public function offsetGet($offset): ScripturNum
	{
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
	 */
	public function offsetSet($offset, $value)
	{
		if (is_null($offset)) {
			$this->container[] = $value;
			end($this->container);
			$this->keys[] = key($this->container);
		} else {
			$this->container[$offset] = $value;
			if ( ! in_array($offset, $this->keys)) {
				$this->keys[] = $offset;
			}
		}
	}

	/**
	 * Offset to unset
	 * @link https://php.net/manual/en/arrayaccess.offsetunset.php
	 *
	 * @param mixed $offset
	 * The offset to unset.
	 *
	 * @return void
	 */
	public function offsetUnset($offset)
	{
		unset($this->container[$offset]);
	}

	/**
	 * Return the current element
	 * @link https://php.net/manual/en/iterator.current.php
	 * @return ScripturNum Can return any type.
	 */
	public function current(): ScripturNum
	{
		return $this->container[$this->keys[$this->position]];
	}

	/**
	 * Move forward to next element
	 * @link https://php.net/manual/en/iterator.next.php
	 * @return void Any returned value is ignored.
	 */
	public function next()
	{
		$this->position++;
	}

	/**
	 * Return the key of the current element
	 * @link https://php.net/manual/en/iterator.key.php
	 * @return mixed TKey on success, or null on failure.
	 */
	public function key()
	{
		return $this->keys[$this->position] ?? null;
	}

	/**
	 * Checks if current position is valid
	 * @link https://php.net/manual/en/iterator.valid.php
	 * @return bool The return value will be casted to boolean and then evaluated.
	 * Returns true on success or false on failure.
	 */
	public function valid()
	{
		return isset($this->keys[$this->position]);
	}

	/**
	 * Rewind the Iterator to the first element
	 * @link https://php.net/manual/en/iterator.rewind.php
	 * @return void Any returned value is ignored.
	 */
	public function rewind()
	{
		$this->position = 0;
	}

	/**
	 * Count elements of an object
	 * @link https://php.net/manual/en/countable.count.php
	 * @return int<0,max> The custom count as an integer.
	 *
	 */
	public function count(): int
	{
		return count($this->container);
	}

	/**
	 * @return string
	 */
	public function __toString(): string
	{
		// TODO: Implement __toString() method.
	}
}