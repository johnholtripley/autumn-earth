<?php

include_once($_SERVER['DOCUMENT_ROOT']."/includes/signalnoise.php");
include_once($_SERVER['DOCUMENT_ROOT']."/includes/connect.php");
include_once($_SERVER['DOCUMENT_ROOT']."/includes/functions.php");
$pagenumber = 1;
if(isset($_GET['page'])) {
	$pagenumber = $_GET['page'];
	
$pos = strrpos($pagenumber, "-");
if ($pos !== false) {
	// is a range
	$startpagenumber = substr($pagenumber,0,$pos);
	$endpagenumber = substr($pagenumber,$pos+1);

} else {
	$startpagenumber = $pagenumber;
	$endpagenumber = $pagenumber;
}



} else {
	$startpagenumber = 1;
	$endpagenumber = 1;
}

$query = "select * from tblplants ORDER BY timeCreated desc";
$result = mysql_query($query) or die ("couldn't execute query");
$numberOfEntries = mysql_num_rows($result);


$resultsperpage = 12;
$totalpages = ceil($numberOfEntries/$resultsperpage);
if($endpagenumber > $totalpages) {
$endpagenumber = $totalpages;
}
if($startpagenumber < 1) {
$startpagenumber = 1;
}
if (($startpagenumber>0) && ($endpagenumber <= $totalpages)) {
	$startpoint = ($startpagenumber - 1) * $resultsperpage;
	$endpoint = $endpagenumber * $resultsperpage;
	if ($endpoint > $numberOfEntries) {
		$endpoint = $numberOfEntries;
	}
	if ($numberOfEntries > 0) {
		$rowcount = 0;
		$animationOffset = 0;
		$htmlOutput = "";
		$i = 1;
		if(($numberOfEntries>0) && (isset($isInitialPageRequest))) {
	echo '<ul id="herbariumCatalogue" class="row medium-2up wide-4up widest-5up equalHeights paginatedBlock">';
}
		while ($row = mysql_fetch_array($result)) {
			if (($rowcount>= $startpoint) && ($rowcount<$endpoint)) {
				extract($row);

$additionalClass="";
$pictureArray = array(150,277);
if(($i%$resultsperpage == 1) || ($i%$resultsperpage == 8)) {
$additionalClass=" spotlight";
$pictureArray = array(300,604);
}
if(($i%$resultsperpage == 9) || ($i%$resultsperpage == 10) || ($i%$resultsperpage == 11) || ($i%$resultsperpage == 0)) {
$additionalClass=" inbetweenRow";
$pictureArray = array(150,310);
}

				if(isset($isInitialPageRequest)) {
					
				} else {
					$additionalClass .= ' animateIn animateOffset'.$animationOffset;
					$animationOffset ++;
				}
				$htmlOutput .= '<li class="column'.$additionalClass.'" data-aquatic="'.$isAquatic.'"><div>';
				$htmlOutput .= '<a href="/herbarium/'.$plantUrl.'/">';

picture('/images/herbarium/plants/'.$plantUrl.'.jpg', $latinName, $pictureArray, true, $htmlOutput);

	$htmlOutput .= '<h4>'.$latinName.'</h4><h5>'.$commonNames.'</h5><p>'.$plantDesc.'</p><p>Catalogued '.lcfirst(relativePastDate( strtotime( $timeCreated ))).'</p></a></div></li>';




			}
			$i++;
			$rowcount++;
		}
		if(isset($isInitialPageRequest)) {
		echo $htmlOutput;
		echo '</ul>';
	} else {
		// construct JSON response:


// only need to escape double quotes, not single:
echo '{"markup": ["'.addcslashes($htmlOutput, '"\\/').'"],"resultsRemaining": ["'.($numberOfEntries-$endpoint).'"]}';



	}
	}
}


?>