<?php
$title="Autumn Earth Forum";
include($_SERVER['DOCUMENT_ROOT']."/includes/session.php");
include($_SERVER['DOCUMENT_ROOT']."/includes/signalnoise.php");
include($_SERVER['DOCUMENT_ROOT']."/includes/connect.php");
include($_SERVER['DOCUMENT_ROOT']."/includes/functions.php");



if(isset($_GET["cleaned"])) {

$forumID = $_GET["forum"];



$query = "select * from tblforums WHERE cleanurl = '".$forumID."'";



$result = mysqli_query($connection, $query) or die ("couldn't execute query");

if (mysqli_num_rows($result) > 0) {


$row = mysqli_fetch_array($result);

$forumID = $row["forumID"];

}




} else {


$forumID = $_GET["forum"];
}
// check that a valid number has been passed:








if (is_numeric($forumID)) {

	
	//
	// get forum title information:
	$query = "SELECT * FROM tblforums WHERE forumid = " . $forumID;
	$result = mysqli_query($connection, $query) or die ("couldn't execute query");
	
	$numberofrows = mysqli_num_rows($result);
	// check that something is returned
	if ($numberofrows == "1") {
		$row = mysqli_fetch_array($result);
		extract($row);
	
	
	
	
		if ($status > 0) {
		
		
		$pagetitle = "Autumn Earth Community Forum - ".stripslashes($title);
		$metadesc = "Autumn Earth Community Forum - discussions in the " . stripslashes($title) . " forum";
	include($_SERVER['DOCUMENT_ROOT']."/includes/header.php");
	
	
	// show breadcrumb:
	echo '<div id="BreadCrumb"><a href="/forum/" title="Forum Lists">Forum</a> &gt; '.stripslashes($title).'</div>';
		
			// if the forum is live:
			echo '<h1>' . stripslashes($title) . '</h1>'."\n";
		//	echo '<img src="' . $imagePath . '" width="24" height="24" class="forumIcon" alt="" />'."\n"; 
			
			
			//
	// get threads from this forum:
	
	
	if ($status > 1) {
	// can have new threads:
	echo '<br /><p><a href="/forum/CreateThread.php?forum=' . $forumID . '" title="Create a new thread">Create a new thread</a></p>'."\n";
	}
	
	$query = "SELECT tblthreads.*, tblacct.accountname AS userName
FROM tblacct
INNER JOIN tblthreads on tblacct.accountid = tblthreads.accountid
WHERE tblthreads.forumid = " . $forumID . " AND tblthreads.status>0  ORDER BY sticky DESC, tblthreads.latestpostid DESC";
	$result = mysqli_query($connection, $query) or die ("couldn't execute query");
	
		
	$numberofrows = mysqli_num_rows($result);
	// check that something is returned
	if ($numberofrows > 0) {
	
	
	
	$totalpages = ceil($numberofrows/$resultsperpage);
		
		// check if this page is paginated:
		if(isset($_GET['page'])) {
		$pagenumber = $_GET['page'];
		} else {
		$pagenumber = 1;
		}
		
		if ($pagenumber > $totalpages) {
		$pagenumber = 1;
		}
			
		$startpoint = ($pagenumber - 1) * $resultsperpage;
		$endpoint = $pagenumber * $resultsperpage;
		if ($endpoint > $numberofrows) {
		$endpoint = $numberofrows;
		}
		
		$isfirsttime = true;
		
		$rowcount = 0;
		
		while ($row = mysqli_fetch_array($result)) {
		if (($rowcount>= $startpoint) && ($rowcount<$endpoint)) {
		// show these results
		extract($row);



$CreationTime = strtotime($CreationTime);

			
			echo '<p>'."\n";
			echo '<a href="/forum/' . $cleanURL . '/" title="Click to view this thread">'."\n";
					
			if ($sticky == "1") {
			echo '<span class="Sticky">';
			}
			
			echo '<strong>' . stripCode(stripslashes($title)) . '</strong>';
			
			if ($sticky == "1") {
			echo '</span>';
			}
			
			echo '</a>';
			
			echo ' - posted by ' . $userName . " on " . date('jS F Y',$CreationTime) .' at '.  date('G:i',$CreationTime). '<br />'."\n";
			echo 'viewed ' . $viewCount . ' times.';
			echo ' Replies: '.($postcount-1);
			$latestpage = ceil($postcount/$resultsperpage);
			echo ' <a href="/forum/ViewThread.php?thread=' . $threadID . '&amp;page='.$latestpage.'#post'.$latestPostID.'" title="click to view the latest post in this thread">view latest post</a>';
			
			echo '</p>';
			
			echo '<hr />'."\n";
			
			}
			$rowcount++;
			}
		
	
	// display page numbers if required
		if ($totalpages>1) {
		
			// determine URL (but ignore &page - if one exists)
		
			$getdata = "";
			foreach($HTTP_GET_VARS as $key => $value) {

				if ($key != "page") {
				
				if ($getdata != "") {
					// if there's more than one piece of GET data, then need to add an &
					$getdata .= "&";
				}
				$getdata .= $key . "=" . $value;
				}
			}
			// create full URL with GET data added (if any)
			$thisurl = $_SERVER['PHP_SELF'];
			if ($getdata != "") {
				$thispageurl .= "?".$getdata;
			}
					
		
			echo '<hr />| ';
			for($i = 1; $i <= $totalpages; $i++) {
				if($i == $pagenumber) { 
				echo $i.' | ';
				} else {
				echo '<a href="'.$thispageurl.'&page='.$i.'" title="View page '.$i.'">'.$i.'</a> | ';
				}
			} 
			echo '<br /><hr />'."\n";
		}
	
	
	
	} else {
	
		echo '<p>There are no threads to view in this forum</p>';
		echo '<a href="http://www.autumnearth.com" title="Click to return to the homepage">Return to the homepage</a>'."\n";
	}
			
			
		} else {
		include($_SERVER['DOCUMENT_ROOT']."/includes/header.php");
include($_SERVER['DOCUMENT_ROOT']."/includes/login.php");
include($_SERVER['DOCUMENT_ROOT']."/includes/search.php");
			echo '<p>This forum has been closed.</p>';
			echo '<a href="http://www.autumnearth.com" title="Click to return to the homepage">Return to the homepage</a>'."\n";
		}
	
	} else {
	include($_SERVER['DOCUMENT_ROOT']."/includes/header.php");
include($_SERVER['DOCUMENT_ROOT']."/includes/login.php");
include($_SERVER['DOCUMENT_ROOT']."/includes/search.php");
		echo '<div class="Error">forum could not be located</div>'."\n";
	}
	
	
	

} else {
include($_SERVER['DOCUMENT_ROOT']."/includes/header.php");
include($_SERVER['DOCUMENT_ROOT']."/includes/login.php");
include($_SERVER['DOCUMENT_ROOT']."/includes/search.php");
echo 'not a valid forum id';
}



include($_SERVER['DOCUMENT_ROOT']."/includes/footer.php");
?>