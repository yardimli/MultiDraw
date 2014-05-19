<?php

set_time_limit(200000);

$link = mysql_pconnect("localhost","DrawUser","DrawPassword"); //B123456a
mysql_select_db("cloudofvoice");
$mysqlresult = mysql_query("SET NAMES utf8");
$mysqlresult = mysql_query("SET CHARACTER_SET utf8");


function convertPNGto8bitPNG($sourcePath, $destPath) {
	
	$srcimage = imagecreatefrompng($sourcePath);
	list($width, $height) = getimagesize($sourcePath);
	
	$img = imagecreatetruecolor($width, $height);
	
	$bga = imagecolorallocatealpha($img, 0, 0, 0, 127);
	
	imagecolortransparent($img, $bga);
	imagefill($img, 0, 0, $bga);
	imagecopy($img, $srcimage, 0, 0, 0, 0, $width, $height);
	
	imagetruecolortopalette($img, false, 255);
	imagesavealpha($img, true);
	
	imagepng($img, $destPath);
	imagedestroy($img);
}
			

$start = microtime(true);

echo "StartTime: ".$start;

$intervaltime = 30;

for ($loopi = 0; $loopi < (60*6*100); $loopi++) 
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

		$xsqlCommand3 = "SELECT * FROM snapshots WHERE DrawingID=".$DrawingID." ORDER BY ID DESC LIMIT 1";
		$mysqlresult3 = mysql_query($xsqlCommand3);
		$mysql_rows3 = mysql_num_rows($mysqlresult3);

		$LastSnapShotID=0;
		if ($mysql_rows3==1) { $LastSnapShotID  = mysql_result($mysqlresult3, 0, "LastID");}
		
		
		$xsqlCommand = "SELECT * FROM Drawings WHERE DrawingID=".$DrawingID." ORDER BY ID ASC";
		$mysqlresult = mysql_query($xsqlCommand);
		$mysql_rows = mysql_num_rows($mysqlresult);	
		
		$LastID = 1;
		if ($mysql_rows>0) { $LastID = mysql_result($mysqlresult, $mysql_rows-1, "ID"); }
		
		
		echo "SnapShot Last Drawing Pos : ". $LastSnapShotID . " Drwing Last Pos: ".$LastID."\n";
		
		if ($LastID>$LastSnapShotID) {
			echo "Generating new image\n";
			$image = imagecreatetruecolor($RealWidth,$RealHeight);
			$black = imagecolorallocate($image,0,0,0);
			$white = imagecolorallocate($image,255,255,255);
			imagefill($image,0,0,$white);

			imagesetthickness($image, 2);
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
			imagepng($image,"temp.png");
			convertPNGto8bitPNG("temp.png",$SnapFileName);
	//		imagepng($image,$SnapFileName);

			$LastID = 0;
			if ($mysql_rows>0) {
				$LastID = mysql_result($mysqlresult, $i-1, "ID");
			}
			$xsqlCommand3 = "INSERT INTO snapshots (LastID,Width,Height,DrawingID,SnapFile,xDate) VALUES (". $LastID . "," . $RealWidth .",". $RealHeight .",". $DrawingID .",'". $SnapFileName ."',now())";
	//		echo $xsqlCommand3;
			$mysqlresult3 = mysql_query($xsqlCommand3);
		}
	}

	echo "waiting from ".$start." till ".($start + ($loopi*$intervaltime) + $intervaltime);
	time_sleep_until($start + ($loopi*$intervaltime) + $intervaltime);
}
	exit;
