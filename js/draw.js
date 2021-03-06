//---------------------------------------------------------------------------------------------------------------------------------------------------
function erase() {
    var m = confirm("Want to clear");
    if (m) {
        ctx.clearRect(0, 0, w, h);
        document.getElementById("canvasimg").style.display = "none";
    }
}


//---------------------------------------------------------------------------------------------------------------------------------------------------11
function save() {
    document.getElementById("canvasimg").style.border = "2px solid";
    var dataURL = canvas.toDataURL();
    document.getElementById("canvasimg").src = dataURL;
    document.getElementById("canvasimg").style.display = "inline";
}


var StopUpdating = false;

var RealWidth = 2000;
var RealHeight = 2500;

var DrawWidth = 500;
var DrawHeight = 375;

var PreviewWidth = 300;
var PreviewXRatio = (RealWidth / PreviewWidth);
var PreviewHeight = Math.round(RealHeight / PreviewXRatio );

var DragBoxWidth = Math.round( PreviewWidth * (DrawWidth / RealWidth) );
var DragBoxHeight = Math.round( PreviewHeight * (DrawHeight / RealHeight) );


var dataToSend = [];
var dataToSendTemp = [];

var DrawingCache = [];

var isiPad = false;
var isiPadFirstTimeLoad = false;

var ua = navigator.userAgent.toLowerCase();
var isiPad_ = navigator.userAgent.match(/iPad/i) != null;
var isAndroid = ua.indexOf("android") > -1; //&& ua.indexOf("mobile");
var ArticlePanel = $("#ArticlePanel");

var consoldebug = false;

var CurrentDrawingFrame = 1;

if ((isiPad_) || (isAndroid)) { isiPad = true; }

var DrawCanvas, ctx, flag = false,
    prevX = 0,
    currX = 0,
    prevY = 0,
    currY = 0,
    dot_flag = false;
	
var LastID = 0;	

var TopPos = 0;
var LeftPos = 0;
var RealTopPos = 0;
var RealLeftPos = 0;
var RedrawBigPNGCounter = 0;

//---------------------------------------------------------------------------------------------------------------------------------------------------
function init() {
    w = DrawCanvas.width;
    h = DrawCanvas.height;
	
    if (isiPad) {
        DrawCanvas.addEventListener("touchmove", function (e) { e.preventDefault(); findxy('move', e) }, false);
        DrawCanvas.addEventListener("touchstart", function (e) { e.preventDefault(); findxy('down', e) }, false);
        DrawCanvas.addEventListener("touchend", function (e) { e.preventDefault(); findxy('up', e) }, false);
        DrawCanvas.addEventListener("touchleave", function (e) { e.preventDefault(); findxy('out', e) }, false);
    } else
    {
        DrawCanvas.addEventListener("mousemove", function (e) { e.preventDefault(); findxy('move', e) }, false);
        DrawCanvas.addEventListener("mousedown", function (e) { e.preventDefault(); findxy('down', e) }, false);
        DrawCanvas.addEventListener("mouseup", function (e) { e.preventDefault(); findxy('up', e) }, false);
        DrawCanvas.addEventListener("mouseout", function (e) { e.preventDefault(); findxy('out', e) }, false);
    }
	
    $.ajax({
		type: 'POST',
		url: "getdraw.php",
		data: { "op":"getallcanvas"},
		dataType: "json",
		success: function(resultData) {
			if (resultData.length>0)
			{
				if (consoldebug) { console.log(resultData.length); }
				$("#drawingsdiv").html("");
				for (i=0; i<resultData.length; i++)
				{
					$("#drawingsdiv").append('<div data-id="'+ resultData[i].DrawingID +'" id="Drawing_'+ resultData[i].DrawingID +'" class="wpframeDrawing" style="width:100px; height:125px; display:inline-block; margin-right:5px;"><img src="'+ resultData[i].FilePath +'" style="100px; height:125px;"></div>');
					
					if (resultData[i].DrawingID==CurrentDrawingFrame) {
						$("#Drawing_"+resultData[i].DrawingID).addClass("SelectedFrame");
					}
				}
				
				$(".wpframeDrawing").click(function (){
					CurrentDrawingFrame = $(this).data("id");
					$(".wpframeDrawing").removeClass("SelectedFrame");
					$(this).addClass("SelectedFrame");
					//alert( $(this).data("id") );

					dataToSend = [];
					dataToSendTemp = [];

					DrawingCache = [];
					
					$('#bigimage').attr("src", "getdraw.php?op=sendpngfile&DrawingID="+CurrentDrawingFrame+"&rnd="+Math.floor((Math.random() * 100000) + 1));

					//reposition the png behind the drawing viewport
					$("#copyimage").attr("src", $('#bigimage').attr('src'));
					$("#copyimage").css({"left": (0 - RealLeftPos )+"px",  "top": (0 - RealTopPos )+"px" });
				
					ctx.clearRect(0, 0, w, h);
					RedrawCanvas();
					
					var PostValues = { "op":"getlastsnapid", "DrawingID" : CurrentDrawingFrame };
					$.ajax({
						type: 'POST',
						url: "getdraw.php",
						data: PostValues,
						dataType: "html",
						success: function(resultData) { 
							LastID = resultData;
							if (consoldebug) { console.log(resultData); }
						},
						error: function(xhr, status, error) {
						  if (consoldebug) { console.log("ERROR:"+xhr.responseText+" "+status+" "+error); }
						}
					});
					
				});
			}
		},
		error: function(xhr, status, error) {
		  if (consoldebug) { console.log("ERROR:"+xhr.responseText+" "+status+" "+error); }
		}
    });
	

    var PostValues = { "op":"getlastsnapid", "DrawingID" : CurrentDrawingFrame };
    $.ajax({
		type: 'POST',
		url: "getdraw.php",
		data: PostValues,
		dataType: "html",
		success: function(resultData) { 
			LastID = resultData;
			if (consoldebug) { console.log(resultData); }
		},
		error: function(xhr, status, error) {
		  if (consoldebug) { console.log("ERROR:"+xhr.responseText+" "+status+" "+error); }
		}
    });

	setInterval(function() { 
		LoadAndUpdateDrawing();

		if (!StopUpdating) { 
			RedrawBigPNGCounter++;
			if (RedrawBigPNGCounter>30)//15sec
			{
				//DrawingCache = []; //clear local drawing cache
				
				RedrawBigPNGCounter=0;
				$('#bigimage').attr("src", "getdraw.php?op=sendpngfile&DrawingID="+CurrentDrawingFrame+"&rnd="+Math.floor((Math.random() * 100000) + 1));
				
				//reposition the png behind the drawing viewport
				$("#copyimage").attr("src", $('#bigimage').attr('src'));
				$("#copyimage").css({"left": (0 - RealLeftPos )+"px",  "top": (0 - RealTopPos )+"px" });
			}
		}
	}, 500);
}


//---------------------------------------------------------------------------------------------------------------------------------------------------
function findxy(res, e) {
    if (res == 'down') {
        prevX = currX;
        prevY = currY;
        currX = e.pageX - $("#can").offset().left;
        currY = e.pageY - $("#can").offset().top;
		
		StopUpdating = true;

//		$("#debugdiv").append( e.pageX+ " "+ e.pageY + ", ");

        flag = true;
        dot_flag = true;
        if (dot_flag) {
            draw(currX, currY,currX+1,currY);
			PostValues = { "X1" : currX, "Y1" : currY, "X2" : currX+1, "Y2" : currY};
			dataToSend.push(PostValues);
            dot_flag = false;
        }
    }
	
    if (res == 'up' || res == "out") {
        flag = false;
		StopUpdating = false;
		RedrawBigPNGCounter = 1000; //make next update refresh preview big image too
    }
	
    if (res == 'move') {
        if (flag) {
            prevX = currX;
            prevY = currY;
            currX = (e.pageX) - $("#can").offset().left;
            currY = (e.pageY) - $("#can").offset().top;
            draw(prevX, prevY,currX, currY);
			PostValues = { "X1" : prevX, "Y1" : prevY, "X2" : currX, "Y2" : currY};
			dataToSend.push(PostValues);
        }
    }
}

//---------------------------------------------------------------------------------------------------------------------------------------------------
function draw(x1,y1,x2,y2) {
    ctx.beginPath();
    ctx.moveTo(x1,y1);
    ctx.lineTo(x2,y2);
    ctx.strokeStyle = "black";
    ctx.lineWidth = 2;
    ctx.stroke();
    ctx.closePath();
}

//---------------------------------------------------------------------------------------------------------------------------------------------------
function RedrawCanvas()
{
	if (consoldebug) { console.log("redraw"); }
    ctx.beginPath();
    ctx.strokeStyle = "black";
    ctx.lineWidth = 2;

	for (i=0; i<DrawingCache.length; i++)
	{
		ctx.moveTo(DrawingCache[i].X1 - RealLeftPos ,DrawingCache[i].Y1 - RealTopPos);
		ctx.lineTo(DrawingCache[i].X2 - RealLeftPos ,DrawingCache[i].Y2 - RealTopPos);
	}
	
    ctx.stroke();
    ctx.closePath();
}

//---------------------------------------------------------------------------------------------------------------------------------------------------
function LoadAndUpdateDrawing()
{
    var PostValues = { "op":"load", "SenderID" : "1", "DrawingID" : CurrentDrawingFrame , "LastID" : LastID, 
                                            "LeftPos" : LeftPos, "TopPos" : TopPos, "DrawWidth" : DrawWidth, "DrawHeight" : DrawHeight, 
                                            "RealWidth" : RealWidth , "RealHeight" : RealHeight ,
                                            "PreviewWidth" : PreviewWidth , "PreviewHeight" : PreviewHeight ,
                                            "PostData":  JSON.stringify(dataToSend)};
    dataToSendTemp = dataToSend;
    dataToSend = [];
    $.ajax({
		type: 'POST',
		url: "getdraw.php",
		data: PostValues,
		dataType: "json",
		success: function(resultData) {
			if (resultData.length>0)
			{
				if (consoldebug) { console.log(resultData.length); }

				for (i=0; i<resultData.length; i++)
				{
					DrawingCache.push( { "ID":resultData[i].ID , "X1":resultData[i].X1 , "Y1":resultData[i].Y1,"X2":resultData[i].X2,"Y2":resultData[i].Y2 } );
				}
				
				//if any new data has arrived then draw
				if (LastID !== DrawingCache[DrawingCache.length-1].ID )
				{
					RedrawCanvas();
					LastID = DrawingCache[DrawingCache.length-1].ID;
				}
			}
		},
		error: function(xhr, status, error) {
		  if (consoldebug) { console.log("ERROR:"+xhr.responseText+" "+status+" "+error); }

		  //in case of error copy the temp stored data back into dataToSend
		  dataToSendTemp.push.apply(dataToSendTemp,dataToSend);
		  dataToSend = dataToSendTemp;
		}
    });
}


//---------------------------------------------------------------------------------------------------------------------------------------------------
$(document).ready(function() {
	DrawCanvas = $("#can").get(0);
	ctx = DrawCanvas.getContext("2d");

	ctx.canvas.width = DrawWidth;
	ctx.canvas.height = DrawHeight;

	$("#canvascontainer").css({"width":DrawWidth+"px","height":DrawHeight+"px"});

	$("#bigscreen").css({"left": ($("#canvascontainer").position().left+$("#canvascontainer").width()+20 )+"px" , "width":PreviewWidth+"px","height":PreviewHeight+"px"});
	$("#bigimage").css({"width":PreviewWidth+"px","height":PreviewHeight+"px"});
	$("#smallscreen").css({"width":DragBoxWidth+"px","height":DragBoxHeight+"px"});
	$('#smallscreen').animatedBorder({size: 1, color: '#A92546'}); 	

	$("#searchicon").css({"left":($("#bigscreen").position().left+$("#bigscreen").width()-20 )+"px" });
	
	$("#thumbs").css({"top":($("#canvascontainer").position().top + $("#canvascontainer").height()+20)+"px", "width":($("#canvascontainer").width()+$("#bigscreen").width()+20-10 )+"px" });

	$("#historyicon").css({"left":($("#thumbs").position().left+$("#thumbs").width()-20 )+"px" , "top":($("#thumbs").position().top-20 )+"px" });

	init();

//	$("#savebtn").click(function() { save(); });

	$( "#smallscreen" ).
		draggable({ 
			containment: "#bigscreen",
			start: function() {
				StopUpdating = true;
			},
			stop: function() {
				StopUpdating = false;
				LeftPos = $(this).position().left;
				TopPos = $(this).position().top;
				RealLeftPos = Math.round(LeftPos * RealWidth / PreviewWidth );
				RealTopPos = Math.round(TopPos * RealHeight / PreviewHeight );

				if (consoldebug) { console.log(RealLeftPos+" "+RealTopPos); }

				//reposition the png behind the drawing viewport
				$("#copyimage").css({"left": (0 - RealLeftPos )+"px",  "top": (0 - RealTopPos )+"px" });

				RedrawBigPNGCounter=1000;
				ctx.clearRect(0, 0, w, h);
				RedrawCanvas();
			}
		});
			
			
	RedrawBigPNGCounter = 1000; //force refresh image in the beginning
});