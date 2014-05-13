<?php

set_time_limit(9600);

$link = mysql_pconnect("localhost","DrawUser","DrawPassword"); //B123456a
mysql_select_db("cloudofvoice");
$mysqlresult = mysql_query("SET NAMES utf8");
$mysqlresult = mysql_query("SET CHARACTER_SET utf8");

$start = microtime(true);

for ($loopi = 0; $loopi < (60*6*48); $loopi++) 
{
	$TimeStamp = date("YmdHis",time());
	echo "generating image(s) for iteration: ".$loopi." TimeStamp:" .$TimeStamp."\n";

	$xsqlCommand2 = "SELECT * FROM Canvas WHERE active=1";
	$mysqlresult2 = mysql_query($xsqlCommand2);
	$mysql_rows2 = mysql_num_rows($mysqlresult2);

	for ($i2=0; $i2<$mysql_rows2; $i2++)
	{
		echo "generating image for canvas with ID:". mysql_result($mysqlresult2, $i2, "ID") ."\n";
		
		$DrawingID  = mysql_result($mysqlresult2, $i2, "ID");
		$RealWidth  = mysql_result($mysqlresult2, $i2, "CanvasWidth");
		$RealHeight = mysql_result($mysqlresult2, $i2, "CanvasHeight");

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
		
		$SnapFileName = "preview/".$DrawingID."-".$TimeStamp."-preview.png";
		imagepng($image,$SnapFileName);
		
		$xsqlCommand3 = "INSERT INTO snapshots (LastID,Width,Height,DrawingID,SnapFile,xDate) VALUES (". mysql_result($mysqlresult, $i-1, "ID") . "," . $RealWidth .",". $RealHeight .",". $DrawingID .",'". $SnapFileName ."',now())";
//		echo $xsqlCommand3;
		$mysqlresult3 = mysql_query($xsqlCommand3);
		
	}
	
	
	
	time_sleep_until($start + ($loopi*15) + 15);
}
	exit;
