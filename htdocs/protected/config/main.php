<?php

$ar = [];
$ar[] = require("general.php");
$ar[] = require("local.php");
$ar[] = require("import.php");
$ar[] = require("params.php");
$ar[] = require("params-plugins.php");
$ar[] = require("params-communications.php");
$ar[] = require("params-calls.php");

$array = [];
foreach($ar as $r){
    $array = CMap::mergeArray($array, $r);
}

return $array;
