<?php



// ---------------------------------

// move plotting and output code into an include so it can be called by generateRandomMap.php
// mark stairs
// Create session txt file, so can have multiple map drawings. Also could store the direction last turned
// different terrain types are ignored - eg the 'tents' in the template are just seen as non-walkable and blend into the surrounding terrain
// before placing chest - look at pixel colours of the boundaries, and nudge accordingly so that the chest doesn't overlay a wall where the curve has come in slightly
// islands always start at bottom left
// find a way to make the single 1x1 tiles less regular - use bezier curves instead? or an arc in one quadrant?
$protocol = stripos($_SERVER['SERVER_PROTOCOL'],'https') === true ? 'https://' : 'http://';

$debug = false;

if(isset($_GET["debug"])) {
$debug = true;
}

$update = false;
if(isset($_GET["update"])) {
$update = true;
}

$format = "json";
//if(isset($_GET["format"])) {
//$format = $_GET["format"];
//}

$requestedMap = $_GET["requestedMap"];
$playerId=$_GET["playerId"];

$dungeonName="";
$isADungeon = false;
if(isset($_GET["dungeonName"])) {
  if($_GET["dungeonName"]!= "") {

$dungeonNamePlain = $_GET["dungeonName"];
$dungeonName=$dungeonNamePlain."/";
}
}

if($requestedMap<0) {
  $isADungeon = true;
}



/*
//if($format == "xml") {
//$fileToUse = "../data/chr".$playerId."/dungeon/".$dungeonName."/".$requestedMap.".xml";
//} else {
  if($isADungeon) {
$fileToUse = "../data/chr".$playerId."/dungeon/".$dungeonName.$requestedMap.".json";

} else {

  //$fileToUse = "../data/chr".$playerId."/map".$requestedMap.".json";
  // use getMap to pull in seasonal or player housing etc:
  $fileToUse = $protocol.$_SERVER['SERVER_NAME']."/game-world/getMap.php?chr=".$playerId."&map=".$requestedMap;

}
*/
if($isADungeon) {
$fileToUse = $protocol.$_SERVER['SERVER_NAME']."/game-world/getMap.php?chr=".$playerId."&map=".$requestedMap."&dungeonName=".$dungeonNamePlain;
} else {
 $fileToUse = $protocol.$_SERVER['SERVER_NAME']."/game-world/getMap.php?chr=".$playerId."&map=".$requestedMap;
}
//}

$plotChests = false;
if(isset($_GET["plotChests"])) {
$plotChests = true;
}

$useOverlay = true;
if(isset($_GET["overlay"])) {
$useOverlay = false;
}


if (is_numeric($playerId)) {
    if (is_numeric($requestedMap)) {




$mapFilename = "../data/chr".$playerId."/cartography/".$dungeonName.$requestedMap.".jpg";



  if ((is_file($mapFilename)) && (!$debug) && (!$update)) {
  
            header("Location: ".$protocol.$_SERVER['SERVER_NAME'] . "/".$mapFilename);
            
        } else {
           
        


    
        $loadedMapData = array();
    $loadedDoorData = array();
    $loadedItemData = array();




/*
    if($format == "xml") {
      $xmlparser = xml_parser_create();
    $nodeType = "";

    xml_set_element_handler($xmlparser, "XMLMapStartTag", "XMLMapEndTag");
    xml_set_character_data_handler($xmlparser, "XMLMapTagContents");
$fp = fopen($fileToUse, "r");
        while ($data = fread($fp, 4096)) {
            // remove whitespace:
            $data = eregi_replace(">" . "[[:space:]]+" . "<", "><", $data);
            xml_parse($xmlparser, $data, feof($fp));
   
        }

xml_parser_free($xmlparser);
*/
//} else {


  // load json

loadAndParseJSON($fileToUse);


//}






$dungeonArray = array();
for ($i = 0;$i < $mapMaxHeight;$i++) {
  $dungeonArray[$i] = array();
  for ($j = 0;$j < $mapMaxWidth;$j++) {
    $dungeonArray[$i][$j] = "";
  }
}

for ($i = 0;$i < $mapMaxHeight;$i++) {
  $dungeonArray[$i] = $loadedMapData[$i];

}


createCartographicMap();
}


}
}




function createCartographicMap() {
global $mapMaxWidth, $mapMaxHeight, $dungeonArray, $loadedItemData, $loadedDoorData, $debug, $playerId, $dungeonName, $session, $requestedMap, $plotChests, $update, $useOverlay, $format, $protocol, $doorEntranceX, $doorEntranceY, $isADungeon;


// canvas size should be twice required size as it will be downsampled to anti alias:
$canvaDimension = 492;
  $tileLineDimension = floor($canvaDimension/($mapMaxWidth-1));
// Create the main image
$mapCanvas = imagecreatetruecolor($canvaDimension, $canvaDimension);

//imageantialias($mapCanvas, true);





if($update) {
// use previous map as the ground
$originalMap = imagecreatefromjpeg("../data/chr".$playerId."/cartography/".$dungeonName.$session."/".$requestedMap.".jpg");
// double the size of this to match the newly drawn one:


imagecopyresampled($mapCanvas, $originalMap, 0, 0, 0, 0, $canvaDimension, $canvaDimension, $canvaDimension/2, $canvaDimension/2);



$updateFade = imagecreatefrompng("https://".$_SERVER['SERVER_NAME']."/images/cartography/map-fade.png");
imageAlphaBlending($updateFade, false);
imagecopy($mapCanvas, $updateFade, 0, 0, 0, 0, $canvaDimension, $canvaDimension);


} else {






// Fill the background
$ground = imagecolorallocate($mapCanvas, 222, 213, 156);
$walkableColour = imagecolorallocate($mapCanvas, 253, 243, 178);

imagefilledrectangle($mapCanvas, 0, 0, $canvaDimension, $canvaDimension, $ground);
}

$brush = imagecreate(2,2);

  $brushtrans = imagecolorallocate($brush, 0, 0, 0);
  imagecolortransparent($brush, $brushtrans);

  $colour = imagecolorallocate($brush, 96, 35, 14);
    imagefilledellipse($brush, 1, 1, 2, 2, $colour);
  

  imagesetbrush($mapCanvas, $brush);



$walkable = 10;
// loop through all tiles and check the right and bottom edges to see if they transition from walkable to non-walkable and if so, draw an edge

$stairTiles = 560;
$edges = array();





for ($i = 0;$i < ($mapMaxHeight);$i++) {
  for ($j = 0;$j < ($mapMaxWidth);$j++) {
    $thisTile = $dungeonArray[$i][$j]<=$walkable;
        // 1 is not walkable for random maps:
    if($dungeonArray[$i][$j] == 1) {
    $thisTile = false;
    }
    
    // stairs are walkable:
if($dungeonArray[$i][$j]>=$stairTiles) {
$thisTile = true;
}
    
    
    if($i==0) {
      // edge isn't walkable:
      $thisTileLeft = false;
    } else {
      $thisTileLeft = $dungeonArray[$i-1][$j]<=$walkable;
      // 1 is not walkable for random maps:
      if($dungeonArray[$i-1][$j] == 1) {
        $thisTileLeft = false;
      }
      
      // stairs are walkable:
if($dungeonArray[$i-1][$j]>=$stairTiles) {
$thisTileLeft = true;
}
      


      
    }
    
    
    if($i==($mapMaxHeight-1)) {
      // edge isn't walkable:
      $thisTileRight = false;
    } else {
      $thisTileRight = $dungeonArray[$i+1][$j]<=$walkable;
      // 1 is not walkable for random maps:
      if($dungeonArray[$i+1][$j] == 1) {
        $thisTileRight = false;
      }
         // stairs are walkable:
if($dungeonArray[$i+1][$j]>=$stairTiles) {
$thisTileRight = true;
}
    }
    
    if($j==($mapMaxWidth-1)) {
      // edge isn't walkable:
      $thisTileBottom = false;
    } else {
      $thisTileBottom = $dungeonArray[$i][$j+1]<=$walkable;
      if($dungeonArray[$i][$j+1] == 1) {
        $thisTileBottom = false;
      }
      
        // stairs are walkable:
        if($dungeonArray[$i][$j+1]>=$stairTiles) {
$thisTileBottom = true;
}
      
    }
    
    if($j==0) {
      // edge isn't walkable:
      $thisTileTop = false;
    } else {
      $thisTileTop = $dungeonArray[$i][$j-1]<=$walkable;
      if($dungeonArray[$i][$j-1] == 1) {
        $thisTileTop = false;
      }
      // stairs are walkable:
      if($dungeonArray[$i][$j-1]>=$stairTiles) {
$thisTileTop = true;
}
    }
    
    
    
    
    
    
    
    // to avoid duplicate edges, don't check to see if the adjoining tiles are different, only draw an edge where current tile is walkable and neighbour is not:
    if($thisTile) {
    
      if(!$thisTileLeft) {
       // add left edge coordinates:
      if($i*$tileLineDimension >0) {
       // ...if it's not on the edge of the canvas
       array_push($edges, $i*$tileLineDimension.",".(($mapMaxHeight-$j)*$tileLineDimension)."|".$i*$tileLineDimension.",".($mapMaxHeight-($j+1))*$tileLineDimension);
       }
      }
      if(!$thisTileRight) {
       // add right edge coordinates:
if(($i+1)*$tileLineDimension < $canvaDimension){

//if($debug) {
  //    echo "<br>adding right ".($i+1)*$tileLineDimension." != ".$canvaDimension;
    //   }

        array_push($edges, ($i+1)*$tileLineDimension.",".(($mapMaxHeight-$j)*$tileLineDimension)."|".($i+1)*$tileLineDimension.",".($mapMaxHeight-($j+1))*$tileLineDimension);
      }
      }
      if(!$thisTileBottom) {
       // add bottom edge coordinates:
      if((($mapMaxHeight-($j+1))*$tileLineDimension) >0) {
       
     //   if($debug) {
    //   echo "<br>adding bottom ".(($mapMaxHeight-($j+1))*$tileLineDimension)." != ".$canvaDimension;
    //  }
       
       array_push($edges, $i*$tileLineDimension.",".(($mapMaxHeight-($j+1))*$tileLineDimension)."|".($i+1)*$tileLineDimension.",".($mapMaxHeight-($j+1))*$tileLineDimension);
      } 
      }
      if(!$thisTileTop) {
       // add top edge coordinates:
     if((($mapMaxHeight-$j)*$tileLineDimension) < $canvaDimension) {
       
    //    if($debug) {
     // echo "<br>adding top ".(($mapMaxHeight-$j)*$tileLineDimension)." != ".$canvaDimension;
     //  }
       
           array_push($edges, $i*$tileLineDimension.",".(($mapMaxHeight-$j)*$tileLineDimension)."|".($i+1)*$tileLineDimension.",".($mapMaxHeight-$j)*$tileLineDimension);
       } 
       
      }
    }

    

  }
}




if($debug) {
//echo "<pre>";
//var_dump($edges);
//echo "</pre>";

}





$usedEdges = array();
$unusedEdges = $edges;

if (count($unusedEdges)>0) {

do {

// ---------------------------------------------------
// find a path through points:
// find a start point that's on an edge:
$orderedPoints = array();
$orderedDirections = array();
    $foundStartPoint = false;
for ($i = 0;$i < (count($unusedEdges));$i++) {
  // check all sides to find a start point #####################
  
  $points = explode("|", $unusedEdges[$i]);
  
 
  
  $startPoint = explode(",", $points[0]);
  $endPoint = explode(",", $points[1]);
  



 
  
  if($startPoint[1] >= $canvaDimension) {
  // on bottom edge
     array_push($orderedPoints,array($startPoint[0],$startPoint[1]));
      array_push($usedEdges,$unusedEdges[$i]);
    $direction = "north";
    array_push($orderedDirections,$direction);    
    $foundStartPoint = true;
    break;
  } else if($startPoint[1] <= 0) {
  // on top edge
     array_push($orderedPoints,array($startPoint[0],$startPoint[1]));
      array_push($usedEdges,$unusedEdges[$i]);
    $direction = "south";
    array_push($orderedDirections,$direction);    
    $foundStartPoint = true;
    break;
    
  } else if($startPoint[0] <= 0) {
  // on left edge
     array_push($orderedPoints,array($startPoint[0],$startPoint[1]));
      array_push($usedEdges,$unusedEdges[$i]);
    $direction = "east";
    array_push($orderedDirections,$direction);    
    $foundStartPoint = true;
    break;
  } else if($startPoint[0] >= $canvaDimension) {
  // on right edge
     array_push($orderedPoints,array($startPoint[0],$startPoint[1]));
      array_push($usedEdges,$unusedEdges[$i]);
    $direction = "west";
    array_push($orderedDirections,$direction);    
    $foundStartPoint = true;
    break;
  }
  
}


if(!$foundStartPoint) {
// try again with points reversed to check end points to see if they're on a canvas boundary
for ($i = 0;$i < (count($unusedEdges));$i++) {
  
  $points = explode("|", $unusedEdges[$i]);
  
 
  
  $startPoint = explode(",", $points[1]);
  $endPoint = explode(",", $points[0]);
  

  
  if($startPoint[1] >= $canvaDimension) {
  // on bottom edge
     array_push($orderedPoints,array($startPoint[0],$startPoint[1]));
      array_push($usedEdges,$unusedEdges[$i]);
    $direction = "north";
    array_push($orderedDirections,$direction);    
    $foundStartPoint = true;
    break;
  } else if($startPoint[1] <= 0) {
  // on top edge
     array_push($orderedPoints,array($startPoint[0],$startPoint[1]));
      array_push($usedEdges,$unusedEdges[$i]);
    $direction = "south";
    array_push($orderedDirections,$direction);    
    $foundStartPoint = true;
    break;
    
  } else if($startPoint[0] <= 0) {
  // on left edge
     array_push($orderedPoints,array($startPoint[0],$startPoint[1]));
      array_push($usedEdges,$unusedEdges[$i]);
    $direction = "east";
    array_push($orderedDirections,$direction);    
    $foundStartPoint = true;
    break;
  } else if($startPoint[0] >= $canvaDimension) {
  // on right edge
     array_push($orderedPoints,array($startPoint[0],$startPoint[1]));
      array_push($usedEdges,$unusedEdges[$i]);
    $direction = "west";
    array_push($orderedDirections,$direction);    
    $foundStartPoint = true;
    break;
  }
  
}

}
















if(!$foundStartPoint) {




// just pick first point of remaining edges:
$points = explode("|", $unusedEdges[0]);
    $startPoint = explode(",", $points[0]);
  $endPoint = explode(",", $points[1]);
  

  
  array_push($orderedPoints,array($startPoint[0],$startPoint[1]));
      array_push($usedEdges,$unusedEdges[0]);
      
      
      $startX = $startPoint[0];
$startY = $startPoint[1];
$endX = $endPoint[0];
$endY = $endPoint[1];
$direction = findDirection($startX,$startY,$endX,$endY);
      
      
      
    array_push($orderedDirections,$direction);
}








// get target end point - this is the new start point
// from $direction, determine the 3 possible end point.
// look for the edge that has this start and any of the 3 ends, in either order.
// if found, update direction, and get this end point
// keep track of used edges so don't re-use ####



$stillWorking = true;

do {

// get target end point - this is the new start point:
$thisEdgesStartPoint = $endPoint;


// from $direction, determine the 3 possible end points:
$possibleEdges = array();

if(($direction=="north") || ($direction=="east") || ($direction=="west")) {
// add 'north' point:

 
 array_push($possibleEdges,$thisEdgesStartPoint[0].",".$thisEdgesStartPoint[1]."|".($thisEdgesStartPoint[0]).",".(($thisEdgesStartPoint[1])-$tileLineDimension));
 // and add the edge in reverse in case the path finding is travelling in a different direction to the initial edge detection:
  array_push($possibleEdges,($thisEdgesStartPoint[0]).",".(($thisEdgesStartPoint[1])-$tileLineDimension)."|".$thisEdgesStartPoint[0].",".$thisEdgesStartPoint[1]);
 
}
if(($direction=="south") || ($direction=="east") || ($direction=="west")) {
// add 'south' point:

  array_push($possibleEdges,$thisEdgesStartPoint[0].",".(($thisEdgesStartPoint[1])+$tileLineDimension)."|".($thisEdgesStartPoint[0]).",".($thisEdgesStartPoint[1]));
  array_push($possibleEdges,($thisEdgesStartPoint[0]).",".(($thisEdgesStartPoint[1]))."|".$thisEdgesStartPoint[0].",".($thisEdgesStartPoint[1]+$tileLineDimension));
 
 
 
}
if(($direction=="north") || ($direction=="east") || ($direction=="south")) {
// add 'east' point:

   array_push($possibleEdges,($thisEdgesStartPoint[0]+$tileLineDimension).",".(($thisEdgesStartPoint[1]))."|".($thisEdgesStartPoint[0]).",".($thisEdgesStartPoint[1]));
  array_push($possibleEdges,($thisEdgesStartPoint[0]).",".(($thisEdgesStartPoint[1]))."|".($thisEdgesStartPoint[0]+$tileLineDimension).",".($thisEdgesStartPoint[1]));
 
 
 
}
if(($direction=="north") || ($direction=="south") || ($direction=="west")) {
// add 'west' point:

  array_push($possibleEdges,($thisEdgesStartPoint[0]-$tileLineDimension).",".(($thisEdgesStartPoint[1]))."|".($thisEdgesStartPoint[0]).",".($thisEdgesStartPoint[1]));
  array_push($possibleEdges,($thisEdgesStartPoint[0]).",".(($thisEdgesStartPoint[1]))."|".($thisEdgesStartPoint[0]-$tileLineDimension).",".($thisEdgesStartPoint[1]));

}
// 

$foundNewPoint = array();
// check if any of these are valid edges
for ($i = 0;$i < (count($possibleEdges));$i++) {
  if ((in_array($possibleEdges[$i],$edges)) && (!(in_array($possibleEdges[$i],$usedEdges)))){
    array_push($foundNewPoint,$i);
  }
}




if(count($foundNewPoint) == 0) {
$stillWorking = false;
} else if(count($foundNewPoint) == 1) {
    array_push($usedEdges,$possibleEdges[$foundNewPoint[0]]);
    // add which point isn't the current point to the list, and set up for the next iteration:
    $points = explode("|", $possibleEdges[$foundNewPoint[0]]);
    $startEdge = implode(",",$thisEdgesStartPoint);
    if($points[0] == $startEdge) {
      // add the other point:
      $newPoint = explode(",", $points[1]);
    } else {
      $newPoint = explode(",", $points[0]);
    }
    array_push($orderedPoints,array($newPoint[0],$newPoint[1]));
    $endPoint = $newPoint;

} else {
// choose the direction that involves a turn (ie, isn't in the same direction as the current edge):

  for ($i = 0;$i < (count($foundNewPoint));$i++) {
  // add which point isn't the current point to the list, and set up for the next iteration:
    $points = explode("|", $possibleEdges[$foundNewPoint[$i]]);
    $startEdge = implode(",",$thisEdgesStartPoint);
    if($points[0] == $startEdge) {
      // add the other point:
      $newPoint = explode(",", $points[1]);
    } else {
      $newPoint = explode(",", $points[0]);
    }
  $startX = $thisEdgesStartPoint[0];
$startY = $thisEdgesStartPoint[1];
$endX = $newPoint[0];
$endY = $newPoint[1];
  
  $thisEdgesDirection = findDirection($startX,$startY,$endX,$endY);
  // if not the same direction, or it's the last valid edge:
  if(($thisEdgesDirection != $direction) || ($i == (count($foundNewPoint)-1))) {
  // use this edge:
  
  array_push($usedEdges,$possibleEdges[$foundNewPoint[$i]]);
    
    array_push($orderedPoints,array($newPoint[0],$newPoint[1]));
    $endPoint = $newPoint;
  
  break;
  }
  }

}










if($stillWorking) {

// determine new direction:


$startX = $thisEdgesStartPoint[0];
$startY = $thisEdgesStartPoint[1];
$endX = $newPoint[0];
$endY = $newPoint[1];




$direction = findDirection($startX,$startY,$endX,$endY);





array_push($orderedDirections,$direction);
}

} while ($stillWorking);



if($debug) {
//echo "<pre>";
//var_dump($orderedDirections);
//echo "</pre>";
}



// find any intermediatry directions and convert these to uppercase:
for ($i = 1; $i<(count($orderedDirections)-1); $i++) {
if(strtolower($orderedDirections[$i]) == strtolower($orderedDirections[$i+1])) {
if(strtolower($orderedDirections[$i]) == strtolower($orderedDirections[$i-1])) {
$orderedDirections[$i] = strtoupper($orderedDirections[$i]);
}
}
}

// remove any diagonals
for ($i = 1; $i<(count($orderedDirections)-1); $i++) {
if(strtolower($orderedDirections[$i-1]) == strtolower($orderedDirections[$i+1])) {
$orderedDirections[$i] = strtoupper($orderedDirections[$i]);
}
}








$directionsToRemove = array("NORTH","EAST","SOUTH","WEST");
$tidiedOrderedPoints = array();
for ($i = 0; $i<count($orderedPoints); $i++) {
if (!(in_array($orderedDirections[$i], $directionsToRemove))) {
array_push($tidiedOrderedPoints,array($orderedPoints[$i][0],$orderedPoints[$i][1]));
}
}

if($debug) {
//echo "<hr>";
//echo "<pre>";
//var_dump($tidiedOrderedPoints);
//echo "</pre>";
}


// bezier curves:
// https://stackoverflow.com/questions/7054272/how-to-draw-smooth-curve-through-n-points-using-javascript-html5-canvas





// shouldn't need this - all edges should have 2 points
// ############################
if (count($tidiedOrderedPoints)>1) {

$previousX = $tidiedOrderedPoints[0][0];
$previousY = $tidiedOrderedPoints[0][1];
if($previousX == 0) {
// drawing routine doesn't like zero
$previousX = 0.01;
}
if($previousY == 0) {
$previousY = 0.01;
}




$isASingleTile = false;
if (count($tidiedOrderedPoints) == 4) {
if($tidiedOrderedPoints[0][0] == $tidiedOrderedPoints[3][0]) {
if($tidiedOrderedPoints[0][1] == $tidiedOrderedPoints[3][1]) {
  // is a single 1x1 tile:
$isASingleTile = true;
}
}
}

if(!$isASingleTile) {
  if(count($tidiedOrderedPoints)>3) {
for ($i = 1; $i<count($tidiedOrderedPoints)-2; $i++) {

  $controlX = ($tidiedOrderedPoints[$i][0] + $tidiedOrderedPoints[$i+1][0]) / 2;
  $controlY = ($tidiedOrderedPoints[$i][1] + $tidiedOrderedPoints[$i+1][1]) / 2;
quadBezier($mapCanvas, $previousX, $previousY, $tidiedOrderedPoints[$i][0], $tidiedOrderedPoints[$i][1], $controlX, $controlY);
// thicken some lines:

$thickerOffset = (rand(0,6))-3;
if($thickerOffset != 0) {
quadBezier($mapCanvas, $previousX, $previousY, ($tidiedOrderedPoints[$i][0])+$thickerOffset, ($tidiedOrderedPoints[$i][1])+$thickerOffset, $controlX, $controlY);
}

$previousX = $controlX;
$previousY = $controlY;

}


quadBezier($mapCanvas, $previousX, $previousY,$tidiedOrderedPoints[$i][0], $tidiedOrderedPoints[$i][1], $tidiedOrderedPoints[$i+1][0],$tidiedOrderedPoints[$i+1][1]);
}
} else {

$lineColour = imagecolorallocate($mapCanvas, 96, 35, 14);

// draw elipse at the centre of two opposite corner points:
 imageellipse ( $mapCanvas , ($tidiedOrderedPoints[0][0] + $tidiedOrderedPoints[2][0])/2, ($tidiedOrderedPoints[0][1] + $tidiedOrderedPoints[2][1])/2 , $tileLineDimension , $tileLineDimension , $lineColour );
 // other lines are drawn at a 2px thickness:
 imageellipse ( $mapCanvas , ($tidiedOrderedPoints[0][0] + $tidiedOrderedPoints[2][0])/2, ($tidiedOrderedPoints[0][1] + $tidiedOrderedPoints[2][1])/2 , $tileLineDimension+2 , $tileLineDimension+2 , $lineColour );


// add random thickness to make the circle less regular:

$startAngle = rand(0,360);
$endAngle = rand($startAngle,360);

imagearc (  $mapCanvas , ($tidiedOrderedPoints[0][0] + $tidiedOrderedPoints[2][0])/2, ($tidiedOrderedPoints[0][1] + $tidiedOrderedPoints[2][1])/2 , $tileLineDimension+3 , $tileLineDimension+3 , $startAngle , $endAngle , $lineColour );
imagearc (  $mapCanvas , ($tidiedOrderedPoints[0][0] + $tidiedOrderedPoints[2][0])/2, ($tidiedOrderedPoints[0][1] + $tidiedOrderedPoints[2][1])/2 , $tileLineDimension-1 , $tileLineDimension-1 , $startAngle , $endAngle , $lineColour );

}

} 





// check all edges have been used




$unusedEdges = array();

for ($i = 0;$i < (count($edges));$i++) {
if(!(in_array($edges[$i],$usedEdges))) {
array_push($unusedEdges,$edges[$i]);
}


}





} while (count($unusedEdges)>0);


}





/*
// move to the first point
   ctx.moveTo(points[0].x, points[0].y);


   for (i = 1; i < points.length - 2; i ++)
   {
      var xc = (points[i].x + points[i + 1].x) / 2;
      var yc = (points[i].y + points[i + 1].y) / 2;
      ctx.quadraticCurveTo(points[i].x, points[i].y, xc, yc);
   }
 // curve through the last two points
 ctx.quadraticCurveTo(points[i].x, points[i].y, points[i+1].x,points[i+1].y);
*/


// flood fill from centre of a door tile:


/*
if($format == "xml") {
$firstDoor = explode(",",$loadedDoorData[0]);
$doorX = intval($firstDoor[1])+0.5;
$doorY = intval($firstDoor[2])+0.5;
$doorY = $doorX *$tileLineDimension;
$doorY = ($mapMaxHeight -1 - $doorY)*$tileLineDimension;
} else {
  */
  // reversed becuase of the rotation of the old XML compared to the json
//$doorX = intval($firstDoor[1])+0.5;
//$doorY = intval($firstDoor[0])+0.5;

$doorX = $doorEntranceX;
$doorY = $doorEntranceY;

//echo $doorX.",".$doorY."           ";


// watch for unknowns: "?-1"
if(!is_numeric($doorX)) {
$doorX = 0;
}
if(!is_numeric($doorY)) {
$doorY = 0;
}



$doorX = $doorX *$tileLineDimension;
$doorY = ($mapMaxHeight -1 - $doorY)*$tileLineDimension;
//}

//echo $doorX.",".$doorY."<br>";


/*
echo $tileLineDimension." ####";


echo "<br>".intval($firstDoor[1]). "===";
echo "<br>".intval($firstDoor[2]). "===";

var_dump($firstDoor);
echo "<hr>";



echo $doorX. " --- ". $doorY;
die();
*/

//$doorX = 400;
//$doorY = 200;
if($doorX<0) {
  $doorX=0;
}
if($doorY<0) {
  $doorY=0;
}
if($doorX>$canvaDimension) {
  $doorX=$canvaDimension - 2;
}
if($doorY>$canvaDimension) {
  $doorY=$canvaDimension - 2;
}








// json maps are rotate compared to the old xml maps:
//if($format!="xml") {

   imagefill($mapCanvas, $doorX, $doorY, $walkableColour);
$rotatedImage = imagerotate($mapCanvas, -90, 0);
 imagefilter($rotatedImage, IMG_FILTER_GAUSSIAN_BLUR);
//} else {
//   imagefill($mapCanvas, $doorX, $doorY, $walkableColour);
//imagefilter($mapCanvas, IMG_FILTER_GAUSSIAN_BLUR);
//}









$imageResampled = imagecreatetruecolor($canvaDimension/2, $canvaDimension/2);
//if($format!="xml") {
imagecopyresampled($imageResampled, $rotatedImage, 0, 0, 0, 0, $canvaDimension/2, $canvaDimension/2, $canvaDimension, $canvaDimension);
//} else {
//  imagecopyresampled($imageResampled, $mapCanvas, 0, 0, 0, 0, $canvaDimension/2, $canvaDimension/2, $canvaDimension, $canvaDimension);
//}
// pick a random overlay
$overlayDir = "images/cartography/overlays/";
 $templatesFound = 0;
    if (is_dir($overlayDir)) {
        if ($dirHandle = opendir($overlayDir)) {
            while (($file = readdir($dirHandle)) !== false) {
                if (is_file($overlayDir . $file)) {
                $templatesFound++;
            }
            
        }
    }
    closedir($dirHandle);
  }
    $templateOverlayToUse = rand(1,$templatesFound);



$overlayTexture = imagecreatefrompng($protocol.$_SERVER['SERVER_NAME']."/images/cartography/overlays/".$templateOverlayToUse.".png");
imageAlphaBlending($overlayTexture, false);
if($useOverlay) {
imagecopy($imageResampled, $overlayTexture, 0, 0, 0, 0, $canvaDimension, $canvaDimension);
}



if($plotChests) {

/*
if($debug) {
echo "<pre>";
var_dump($loadedItemData);
echo "</pre>";
die();
}
*/

$chestLocator = imagecreatefrompng($protocol.$_SERVER['SERVER_NAME']."/images/cartography/map-location.png");
imageAlphaBlending($chestLocator, false);


//imagecopy($imageResampled, $chestLocator, ($doorX), $doorY, 0, 0, 7, 7);

for ($i = 0;$i < (count($loadedItemData));$i++) {

$thisItem = explode(",",$loadedItemData[$i]);

if($thisItem[2] == 22) {
// is a chest
$chestX = $thisItem[0];
$chestY = $thisItem[1];
imagecopy($imageResampled, $chestLocator, ($chestX)*$tileLineDimension/2, ($mapMaxHeight -1 - $chestY)*$tileLineDimension/2, 0, 0, 7, 7);








if($debug) {
//echo "<pre>item    ";
//var_dump($thisItem);
//echo "</pre>";
}

}





}

}






if (!file_exists("../data/chr".$playerId."/cartography")) {
    mkdir("../data/chr".$playerId."/cartography", 0777, true);
}


if($isADungeon) {
if (!file_exists("../data/chr".$playerId."/cartography/".$dungeonName)) {
    mkdir("../data/chr".$playerId."/cartography/".$dungeonName, 0777, true);
}
}


if($isADungeon) {
$mapFilename = "../data/chr".$playerId."/cartography/".$dungeonName.$requestedMap.".jpg";
} else {
  $mapFilename = "../data/chr".$playerId."/cartography/map".$requestedMap.".jpg";
}






if(!$debug) {
//save image:


  imagejpeg($imageResampled,$mapFilename,100);


}

if($update) {
// Output response to the browser

 print "changeswassuccess=true";
} else {
// Output image to the browser

// if(!$debug) {
header('Content-type: image/jpg');
//}
  imagejpeg($imageResampled,null,100);

}



imagedestroy($mapCanvas);
imagedestroy($overlayTexture);
imagedestroy($imageResampled);
imagedestroy($brush);
//if($format!="xml") {
imagedestroy($rotatedImage);
//}

if($plotChests) {
imagedestroy($chestLocator);
}
if($update) {
imagedestroy($updateFade);
imagedestroy($originalMap);

}


}



function  findDirection($startX,$startY,$endX,$endY) {
if ($startY == $endY) {
if ($startX < $endX) {
return "east";
} else {
return "west";
}
} else {

if ($startY < $endY) {
return "south";
} else {
return "north";
}

}
}





function quadBezier($im, $x1, $y1, $x2, $y2, $x3, $y3) {
// php draw quad bezier:
// https://spottedsun.com/quadratic-bezier-curve-in-php/
    $b = $pre1 = $pre2 = $pre3 = 0;
    $prevx = 0;
    $prevy = 0;
    $d = sqrt(($x1 - $x2) * ($x1 - $x2) + ($y1 - $y2) * ($y1 - $y2)) + sqrt(($x2 - $x3) * ($x2 - $x3) + ($y2 - $y3) * ($y2 - $y3));
    $resolution = (1/$d) * 10;
    for ($a = 1; $a >0; $a-=$resolution) {
        $b=1-$a;
        $pre1=($a*$a);
        $pre2=2*$a*$b;
        $pre3=($b*$b);
        $x = $pre1*$x1 + $pre2*$x2  + $pre3*$x3;
        $y = $pre1*$y1 + $pre2*$y2 + $pre3*$y3;
        if ($prevx != 0 && $prevy != 0)
            imageline ($im, $x, $y, $prevx,$prevy, IMG_COLOR_BRUSHED);
        $prevx = $x;
        $prevy = $y;
    }
    imageline ($im, $prevx, $prevy, $x3, $y3, IMG_COLOR_BRUSHED);
}















function loadAndParseJSON($whichfileToUse) {
  global $loadedMapData, $loadedItemData, $loadedDoorData, $protocol, $doorEntranceX, $doorEntranceY, $mapMaxWidth, $mapMaxHeight, $isADungeon;


//$whichfileToUse = urlencode($whichfileToUse);

$str = file_get_contents($whichfileToUse);
$json = json_decode($str, true);




//var_dump($json['mapData']['map']['collisions']);

for($i=0;$i<count($json['mapData']['map']['collisions']);$i++) {
  array_push($loadedMapData, str_ireplace(" ", "", $json['mapData']['map']['collisions'][$i]));
}



// for the purposes of drawing the map, make all walkable water, non walkable, so an edge is drawn:
for($i=0;$i<count($json['mapData']['map']['collisions'][0]);$i++) {
for($j=0;$j<count($json['mapData']['map']['collisions']);$j++) {
// if($json['mapData']['map']['properties']) {

if(count($json['mapData']['map']['properties'][$j][$i]) > 0) {

if(isset($json['mapData']['map']['properties'][$j][$i]["waterDepth"])) {
if($json['mapData']['map']['properties'][$j][$i]["waterDepth"] > 0) {
$loadedMapData[$j][$i] = '1';
}
}


}


 //}
  }
}





// replace door's "d" with 0 for being walkable:




foreach ($json['mapData']['map']['doors'] as $key => $value) {
array_push($loadedDoorData, $key);
}


 if(isset($json['mapData']['map']['entrance'])) {
$doorEntranceX = $json['mapData']['map']['entrance'][1];
$doorEntranceY = $json['mapData']['map']['entrance'][0];
} else {
  // get first door:
  if(count($json['mapData']['map']['doors'])>0) {
  reset($json['mapData']['map']['doors']);
$firstKey = key($json['mapData']['map']['doors']);
$doorEntranceX = $json['mapData']['map']['doors'][$firstKey]['startX'];
$doorEntranceY = $json['mapData']['map']['doors'][$firstKey]['startY'];
}
}



//  if($isADungeon) {
//    $mapMaxWidth = 70;
//    $mapMaxHeight = 70;
//  } else {
     $mapMaxWidth = count($json['mapData']['map']['terrain'][0]);
    $mapMaxHeight = count($json['mapData']['map']['terrain']);
//  }



}







/*


function XMLMapStartTag($parser, $name) {
global $nodeType;

$nodeType = $name;


}
function XMLMapEndTag($parser, $name) {
  global $nodeType;
  $nodeType = "";
}
function XMLMapTagContents($parser, $data) {
   global $loadedMapData, $loadedItemData, $loadedDoorData, $nodeType;
   
   if ($nodeType == "ROW") {

   array_push($loadedMapData, str_ireplace(" ", "", $data));
} else if ($nodeType == "ITEM") {

   array_push($loadedItemData, str_ireplace(" ", "", $data));
} else if ($nodeType == "DOOR") {
    array_push($loadedDoorData, str_ireplace(" ", "", $data));
    }
   
   
   
}

*/







?>