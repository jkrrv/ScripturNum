<?php

namespace ScripturNum;


class ScripturNumException extends \Exception {

	/** @var ?string */
	protected $input = null;

	/**
	 * @param string      $message
	 * @param int         $code
	 * @param ?\Throwable $previous
	 * @param ?string     $input    The text that could not be read, if known.
	 */
	public function __construct($message = "", $code = 0, $previous = null, $input = null)
	{
		parent::__construct($message, $code, $previous);
		$this->input = $input;
	}

	/**
	 * The text that could not be read, if it is known.  For instance, "Hezekiah 1" for an unrecognized book.
	 *
	 * @since 2.3.0
	 *
	 * @return string|null
	 */
	public function getInput()
	{
		return $this->input;
	}

	/**
	 * Attach the text that could not be read.
	 *
	 * @since 2.3.0
	 *
	 * @param string|null $input
	 *
	 * @return $this
	 */
	public function setInput($input)
	{
		$this->input = $input;
		return $this;
	}
}
