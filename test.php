<?php



require_once 'src/ScripturNum.class.php';

use ScripturNum\ScripturNum;

$int = (44 << 24) + (0 << 12) + 224; // = 738197728 = Romans 1:1 through 8:39

var_dump($int);


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

echo $n;


// Revelation.  The whole thing.
$n = ScripturNum::newFromInts(66, 1, 1, 22);

var_dump($n);

echo $n;
