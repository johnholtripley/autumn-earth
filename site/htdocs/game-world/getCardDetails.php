<?php

include($_SERVER['DOCUMENT_ROOT']."/includes/signalnoise.php");
include($_SERVER['DOCUMENT_ROOT']."/includes/connect.php");



$query = "SELECT * FROM tblCards";
$result = mysql_query($query) or die ();

$outputJson = '{ "cards":[';
$outputJson .= '[null, null, null],';

while ($row = mysql_fetch_array($result)) {
	extract($row);
$outputJson .= '["'.$cardAttack.'", "'.$cardDefense.'", "'.$cardName.'"],';
}

// remove last comma:
$outputJson = rtrim($outputJson, ",");

$outputJson .= ']}';

echo $outputJson;
?>