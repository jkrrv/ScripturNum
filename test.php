<?php



require_once 'src/ScripturNum.class.php';

use ScripturNum\ScripturNum;


$s = new ScripturNum(738197728);

var_dump($s);

echo $s;



// Matthew 8-9
$n = ScripturNum::newFromInts(40, 8, null, 9);

var_dump($n);

echo $n;


// 1 Corinthians 15:50-58
$n = ScripturNum::newFromInts(46, 15, 50, null, 58);

var_dump($n);

echo $n->getAbbrev();


// Revelation.  The whole thing.
$n = ScripturNum::newFromInts(66, 1, 1, 22);

var_dump($n);

echo $n;


// Titus.  The whole thing, the easy(er) way.
$n = ScripturNum::newFromParsed('Tit');

var_dump($n);

echo $n;


// From String.

$n = ScripturNum::newFromString("1 Corinthians 13"); // 10
var_dump($n);
echo $n;

$n = ScripturNum::newFromString("1 Corinthians 1-3"); // 11
var_dump($n);
echo $n;

$n = ScripturNum::newFromString("1 Corinthians 1-2:11"); // 12
var_dump($n);
echo $n;

$n = ScripturNum::newFromString("1 Corinthians 1:1"); // 20
var_dump($n);
echo $n;

$n = ScripturNum::newFromString("1 Corinthians 1:1-10"); // 21
var_dump($n);
echo $n;

$n = ScripturNum::newFromString("1Co1.1-2:10"); // 22
var_dump($n);
echo $n;

$n = ScripturNum::newFromString("3Jo13-15"); // 11
var_dump($n);
echo $n;
echo "<br />";
echo $n->getAbbrev();

$n = ScripturNum::newFromString("Jude"); // 10
var_dump($n);
echo $n;
echo "<br />";
echo $n->getAbbrev();

$n = ScripturNum::newFromString("1Co"); // 10
var_dump($n);
echo $n;
echo "<br />";
echo $n->getAbbrev();

echo ScripturNum::newFromString('3Jo11')->getLongString();
