<?php
require_once 'src/ScripturNum.class.php';

$int = (44 << 24) + (0 << 12) + 224; // = 738197728 = Romans 1:1 through 8:39


var_dump($int);

$book = 0;

ScripturNum\ScripturNum::int2refRange($int, $book, $A, $B, $C, $D);

ScripturNum\ScripturNum::int2concats($int, $E, $F);

var_dump($book);
