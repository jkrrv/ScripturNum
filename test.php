<?php

require_once 'vendor/autoload.php';
use ScripturNum\ScripturNum;


echo "<h2>Usage</h2>";

$s = new ScripturNum('Romans 1-8');
var_dump($s);
echo $s;

$s = new ScripturNum(739119536);
var_dump($s);
echo $s;



echo "<h3>Starting with Other Numerical References</h3>";

//   ScripturNum::newFromInts($book, $startCh, $startV = 1, $endCh = null, $endV = null)
$n = ScripturNum::newFromInts(40, 8, null, 9);
var_dump($n);
echo $n;



echo "<h3>Getting Strings Out: Full Name</h3>";

echo new ScripturNum('3Jo11');



echo "<h3>Getting Strings Out: An Abbreviation</h3>";

echo (new ScripturNum('John 3:16'))->getAbbrev();


