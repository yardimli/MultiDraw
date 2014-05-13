<?php

set_time_limit(120);

$form_data = array(); //test commit from desktop
$form_data['success'] = true;

$link = mysql_pconnect("localhost","DrawUser","DrawPassword"); //B123456a
mysql_select_db("cloudofvoice");
$mysqlresult = mysql_query("SET NAMES utf8");
$mysqlresult = mysql_query("SET CHARACTER_SET utf8");

$SizeMultipler = 2;

if (($_POST["op"]=="sendpng") || ($_GET["op"]=="sendpng"))
{
	$DrawingID = $_POST["DrawingID"];
	if ($DrawingID=="") { $DrawingID = $_GET["DrawingID"]; }
	if (!is_numeric($DrawingID)) { $DrawingID ="1"; }

	$RealWidth = $_POST["RealWidth"];
	if ($RealWidth=="") { $RealWidth = $_GET["RealWidth"]; }
	if (!is_numeric($RealWidth)) { $RealWidth ="400"; }

	$RealHeight = $_POST["RealHeight"];
	if ($RealHeight=="") { $RealHeight = $_GET["RealHeight"]; }
	if (!is_numeric($RealHeight)) { $RealHeight ="400"; }

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
	

//	$overlayImage = imagecreatefromjpeg('macro_photo_1.jpg');
//	imagecopy($image, $overlayImage, 10, 10, 0, 0, imagesx($overlayImage), imagesy($overlayImage));

	// send image to the browser    
	header("Content-Type: image/png");
	imagepng($image);
	exit;
}

if ($_POST["op"]=="getlastid")
{
	$DrawingID = $_POST["DrawingID"];
	if ($DrawingID=="") { $DrawingID = $_GET["DrawingID"]; }
	if (!is_numeric($DrawingID)) { $DrawingID ="1"; }

	$xsqlCommand = "SELECT * FROM Drawings WHERE DrawingID=".$DrawingID." ORDER BY ID DESC LIMIT 1";
	$mysqlresult = mysql_query($xsqlCommand);
	$mysql_rows = mysql_num_rows($mysqlresult);
	
	echo mysql_result($mysqlresult, 0, "ID");
	
}

if ($_POST["op"]=="load")
{
	$DrawingID = "'".AddSlashes(Trim($_POST["DrawingID"]))."'";
	if ($DrawingID=="''") { $DrawingID ="'0'"; }

	$LastID = $_POST["LastID"];
	if (!is_numeric($LastID)) { $LastID=0; }
	
	$RealWidth = $_POST["RealWidth"];
	if (!is_numeric($RealWidth)) { $RealWidth=400; }

	$RealHeight = $_POST["RealHeight"];
	if (!is_numeric($RealHeight)) { $RealHeight=400; }
	
	$PreviewWidth = $_POST["PreviewWidth"];
	if (!is_numeric($PreviewWidth)) { $PreviewWidth=400; }

	$PreviewHeight = $_POST["PreviewHeight"];
	if (!is_numeric($PreviewHeight)) { $PreviewHeight=400; }
	
	$DrawWidth = $_POST["DrawWidth"];
	if (!is_numeric($DrawWidth)) { $DrawWidth=400; }

	$DrawHeight = $_POST["DrawHeight"];
	if (!is_numeric($DrawHeight)) { $DrawHeight=400; }
	
	$LeftPos = $_POST["LeftPos"];
	if (!is_numeric($LeftPos)) { $LeftPos=0; }
	$LeftPos = round($LeftPos * $RealWidth / $PreviewWidth );
	
//	echo  "---".$LeftPos."---";
//	exit();
	

	$TopPos = $_POST["TopPos"];
	if (!is_numeric($TopPos)) { $TopPos=0; }
	$TopPos = round($TopPos * $RealHeight / $PreviewHeight );
	
	
	$SenderID = "'".AddSlashes(Trim($_POST["SenderID"]))."'";
	if ($SenderID=="''") { $SenderID ="'0'"; }
	
	//update any new drawing into mysql
	$jsonData = $_POST['PostData'];
	$jsonData = json_decode($jsonData,true);
	
//	echo (is_array($jsonData))."!!!!";
	if (is_array($jsonData)) {	
	
		foreach($jsonData as $row) {
			//$uses = $item['var1']; //etc
			
			$X1 = $row['X1']; if (!is_numeric($X1)) { $X1=0; }
			$Y1 = $row['Y1']; if (!is_numeric($Y1)) { $Y1=0; }
			$X2 = $row['X2']; if (!is_numeric($X2)) { $X2=0; }
			$Y2 = $row['Y2']; if (!is_numeric($Y2)) { $Y2=0; }
			
			$X1 += $LeftPos;
			$Y1 += $TopPos;
			$X2 += $LeftPos;
			$Y2 += $TopPos;
			
			$xsqlCommand = "INSERT INTO Drawings (SenderID,X1,Y1,X2,Y2,DrawingID,xTime) VALUES (".$SenderID.",".$X1.",".$Y1.",".$X2.",".$Y2.",".$DrawingID.",now())";
			//echo $xsqlCommand;
			$mysqlresult = mysql_query($xsqlCommand);
		}	
	}
	
	$xsqlCommand = "SELECT * FROM Drawings WHERE DrawingID=".$DrawingID." AND ID>". $LastID ." ORDER BY ID ASC";
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
		
//		Send everything new not just the viewport as now the client will cache this data instead
//		if (($X1>=($LeftPos-50)) && ($Y1>=($TopPos-50)) && ($X2<=($LeftPos+$DrawWidth+50)) && ($Y2<=($TopPos+$DrawHeight+50))) {
		$coordinates[] = array("ID"=>$ID,"X1"=>($X1),"Y1"=>($Y1),"X2"=>($X2),"Y2"=>($Y2));
//		}
	}
	
	
	//prepare for JSON 
	echo json_encode($coordinates); //output JSON data
}
?>