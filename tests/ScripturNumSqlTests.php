<?php /** @noinspection PhpParamsInspection */

namespace ScripturNumTests;

use PHPUnit\Framework\TestCase;
use ScripturNum\ScripturNum;

class ScripturNumSqlTests extends TestCase
{
	public function test_InclusiveSql_within() {
		$a = new ScripturNum("Romans 12:16");
		$b = new ScripturNum("Romans 12");

		$db = new ScripturNumDb();
		$where = $b->toSqlInclusive("ref");

		$aInt = $a->getInt();
		$query = "SELECT COUNT(*) FROM (SELECT $aInt as ref) as t WHERE $where;";

		$r = $db->querySingle($query);
		$this->assertTrue(!!$r);
	}

	public function test_InclusiveSql_sameSingle() {
		$a = new ScripturNum("Romans 12:16");
		$b = new ScripturNum("Romans 12:16");

		$db = new ScripturNumDb();
		$where = $b->toSqlInclusive("ref");

		$aInt = $a->getInt();
		$query = "SELECT COUNT(*) FROM (SELECT $aInt as ref) as t WHERE $where;";

		$r = $db->querySingle($query);
		$this->assertTrue(!!$r);
	}

	public function test_InclusiveSql_differentBook() {
		$a = new ScripturNum("Galatians 3");
		$b = new ScripturNum("Genesis 2");

		$db = new ScripturNumDb();
		$where = $b->toSqlInclusive("ref");

		$aInt = $a->getInt();
		$query = "SELECT COUNT(*) FROM (SELECT $aInt as ref) as t WHERE $where;";

		$r = $db->querySingle($query);
		$this->assertFalse(!!$r);
	}

	public function test_InclusiveSql_outLow() {
		$a = new ScripturNum("Romans 12");
		$b = new ScripturNum("Romans 2");

		$db = new ScripturNumDb();
		$where = $b->toSqlInclusive("ref");

		$aInt = $a->getInt();
		$query = "SELECT COUNT(*) FROM (SELECT $aInt as ref) as t WHERE $where;";

		$r = $db->querySingle($query);
		$this->assertFalse(!!$r);
	}


	public function test_InclusiveSql_overlapLow() {
		$a = new ScripturNum("Romans 12-13");
		$b = new ScripturNum("Romans 10-12");

		$db = new ScripturNumDb();
		$where = $b->toSqlInclusive("ref");

		$aInt = $a->getInt();
		$query = "SELECT COUNT(*) FROM (SELECT $aInt as ref) as t WHERE $where;";

		$r = $db->querySingle($query);
		$this->assertTrue(!!$r);
	}

	public function test_InclusiveSql_outHigh() {
		$a = new ScripturNum("Romans 8");
		$b = new ScripturNum("Romans 16:19-20");

		$db = new ScripturNumDb();
		$where = $b->toSqlInclusive("ref");

		$aInt = $a->getInt();
		$query = "SELECT COUNT(*) FROM (SELECT $aInt as ref) as t WHERE $where;";

		$r = $db->querySingle($query);
		$this->assertFalse(!!$r);
	}

	public function test_InclusiveSql_overlapHigh() {
		$a = new ScripturNum("Romans 12-13");
		$b = new ScripturNum("Romans 13-14");

		$db = new ScripturNumDb();
		$where = $b->toSqlInclusive("ref");

		$aInt = $a->getInt();
		$query = "SELECT COUNT(*) FROM (SELECT $aInt as ref) as t WHERE $where;";

		$r = $db->querySingle($query);
		$this->assertTrue(!!$r);
	}


	public function test_ExclusiveSql_within() {
		$a = new ScripturNum("Romans 12:16");
		$b = new ScripturNum("Romans 12");

		$db = new ScripturNumDb();
		$where = $b->toSqlExclusive("ref");

		$aInt = $a->getInt();
		$query = "SELECT COUNT(*) FROM (SELECT $aInt as ref) as t WHERE $where;";

		$r = $db->querySingle($query);
		$this->assertTrue(!!$r);
	}

	public function test_ExclusiveSql_sameSingle() {
		$a = new ScripturNum("Romans 12:16");
		$b = new ScripturNum("Romans 12:16");

		$db = new ScripturNumDb();
		$where = $b->toSqlExclusive("ref");

		$aInt = $a->getInt();
		$query = "SELECT COUNT(*) FROM (SELECT $aInt as ref) as t WHERE $where;";

		$r = $db->querySingle($query);
		$this->assertTrue(!!$r);
	}

	public function test_ExclusiveSql_differentBook() {
		$a = new ScripturNum("Galatians 3");
		$b = new ScripturNum("Genesis 2");

		$db = new ScripturNumDb();
		$where = $b->toSqlExclusive("ref");

		$aInt = $a->getInt();
		$query = "SELECT COUNT(*) FROM (SELECT $aInt as ref) as t WHERE $where;";

		$r = $db->querySingle($query);
		$this->assertFalse(!!$r);
	}

	public function test_ExclusiveSql_outLow() {
		$a = new ScripturNum("Romans 12");
		$b = new ScripturNum("Romans 2");

		$db = new ScripturNumDb();
		$where = $b->toSqlExclusive("ref");

		$aInt = $a->getInt();
		$query = "SELECT COUNT(*) FROM (SELECT $aInt as ref) as t WHERE $where;";

		$r = $db->querySingle($query);
		$this->assertFalse(!!$r);
	}


	public function test_ExclusiveSql_overlapLow() {
		$a = new ScripturNum("Romans 12-13");
		$b = new ScripturNum("Romans 10-12");

		$db = new ScripturNumDb();
		$where = $b->toSqlExclusive("ref");

		$aInt = $a->getInt();
		$query = "SELECT COUNT(*) FROM (SELECT $aInt as ref) as t WHERE $where;";

		$r = $db->querySingle($query);
		$this->assertFalse(!!$r);
	}

	public function test_ExclusiveSql_outHigh() {
		$a = new ScripturNum("Romans 8");
		$b = new ScripturNum("Romans 16:19-20");

		$db = new ScripturNumDb();
		$where = $b->toSqlExclusive("ref");

		$aInt = $a->getInt();
		$query = "SELECT COUNT(*) FROM (SELECT $aInt as ref) as t WHERE $where;";

		$r = $db->querySingle($query);
		$this->assertFalse(!!$r);
	}

	public function test_ExclusiveSql_overlapHigh() {
		$a = new ScripturNum("Romans 12-13");
		$b = new ScripturNum("Romans 13-14");

		$db = new ScripturNumDb();
		$where = $b->toSqlExclusive("ref");

		$aInt = $a->getInt();
		$query = "SELECT COUNT(*) FROM (SELECT $aInt as ref) as t WHERE $where;";

		$r = $db->querySingle($query);
		$this->assertFalse(!!$r);
	}

}