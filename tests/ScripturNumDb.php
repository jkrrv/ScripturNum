<?php

namespace ScripturNumTests;

use SQLite3;

class ScripturNumDb extends SQLite3 {
	function __construct() {
		parent::__construct('test.db');
	}

	function __destruct() {
		$this->close();
		unlink('test.db');
	}
}