<?php

set_time_limit(9600);

$link = mysql_pconnect("localhost","DrawUser","DrawPassword"); //B123456a
mysql_select_db("cloudofvoice");
$mysqlresult = mysql_query("SET NAMES utf8");
$mysqlresult = mysql_query("SET CHARACTER_SET utf8");

$start = microtime(true);

for ($loopi = 0; $loopi < (60*10*24); $loopi++) 
{
	
	
	echo "generating image : ".$loopi;

	$DrawingID ="1";
	$RealWidth ="2000";
	$RealHeight ="2500";

	$image = imagecreatetruecolor($RealWidth,$RealHeight);
	$black = imagecolorallocate($image,0,0,0);
	$white = imagecolorallocate($image,255,255,255);
	imagefill($image,0,0,$white);

	imagesetthickness($image, 2);

	$xsqlCommand = "SELECT * FROM Drawings WHERE DrawingID=".$DrawingID." ORDER BY ID ASC";
	$mysqlresult = mysql_query($xsqlCommand);
	$mysql_rows = mysql_num_rows($mysqlresult);
	
	$coordinates = array();
	
	for ($i=0; $i<$mysql_rows; $i++)
	{
		$ID=mysql_result($mysqlresult, $i, "ID");
		$X1=mysql_result($mysqlresult, $i, "X1");
		$Y1=mysql_result($mysqlresult, $i, "Y1");
		$X2=mysql_result($mysqlresult, $i, "X2");
		$Y2=mysql_result($mysqlresult, $i, "Y2");
		
		imageline($image, $X1,$Y1,$X2,$Y2, $black);
	}
	
	imagepng($image,"preview.png");
	
	time_sleep_until($start + ($loopi*6) + 6);
}
	exit;
