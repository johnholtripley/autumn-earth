<?php

ob_start('compress');

if(isset($_GET["searchterms"])) {
if(!(isset($_GET["searchcleaned"]))) {

// user has searched for something, construct clean URL and re-direct
	header("Location: /search/".cleanURL($_GET["searchterms"]));
}
}




// check if submit button has been pressed:
// (use hidden form field not submit button as IE doesn't recognise the 'value'
if (isset($_POST["logincheck"])) {
if ($_POST["logincheck"] == 'posted') {

	$processedlogin = htmlCharsToEntities(trim($_POST["loginname"]));
	$processedpassword = htmlCharsToEntities(trim($_POST["pword"]));
	$pwordkey = "AsOiD" . strtolower(substr($processedlogin, 0, 2));
	 $query = "SELECT * FROM tblAcct WHERE accountName='" . $processedlogin . "' AND password=AES_ENCRYPT('" . 		$processedpassword . "','" . $pwordkey . "')";
	// $query = "SELECT * FROM tblAcct WHERE accountName='" . $processedlogin . "'";
	$result = mysqli_query($connection, $query) or die ("couldn't execute query");
	
	$returned = mysqli_num_rows($result);
	



	if ($returned > 0) {
		// name exists, and passwords match
		// check account type:
		$row = mysqli_fetch_array($result);
		extract($row);

		switch ($accountType) {
		case "1":
			$ismod = true;
			$isadmin = false;
		break;
		case "2":
			$ismod = false;
			$isadmin = true;
		break;
		default:
			$ismod = false;
			$isadmin = false;
		}
		
		// check for mail:
			$query = "SELECT tblMail.*, tblacct.accountID, tblacct.accountName as useracctid FROM tblMail INNER JOIN tblacct on tblMail.characterID = tblacct.currentCharID WHERE tblacct.accountName='".$processedlogin."' and tblmail.mailread = '0'";
	$result = mysqli_query($connection, $query) or die ("couldn't execute query2");
	
		$returned = mysqli_num_rows($result);

	if ($returned > 0) {
		// user has new mail
		$hasmail = "true";
	} else {
		$hasmail = "false";
	}
		
	// set session values



	$_SESSION['username'] = $processedlogin;
	$_SESSION['ismod'] = $ismod;
	$_SESSION['isadmin'] = $isadmin;
	$_SESSION['hasmail'] = $hasmail;
	



	// check for remember me feature - cookies need to be set before any output
if(isset($_POST["rememberme"])) {
	if ($_POST["rememberme"] == "yes") {
		// set cookie:
		// expires in 30 days (60*60*24*30)
		// encrypt password for cookie:
		$processedpassword = crypt(trim($_POST["pword"]), $cryptSalt);
		$processedlogin = trim($_POST["loginname"]);




		//$cookiepwd = crypt($processedpassword);
		setcookie("remembername", $processedlogin, time()+2592000, "/");
		setcookie("rememberp", $processedpassword, time()+2592000, "/");










	}
}

		
	} else {
	 
	session_destroy();
	//unset($username, $_SESSION['username'], $isadmin, $_SESSION['isadmin'], $ismod, $_SESSION['ismod'], $hasmail, $_SESSION['hasmail']);
	

	$loginerror = "account name and password were incorrect - please try again";

	}

}
} 






$loggedout = false;

if (@ $_POST["subbutton"] == 'log out') {
	// remove cookies:
	setcookie("remembername", "", time()-2592000, "/");
	setcookie("rememberp", "", time()-2592000, "/");
	// log out and remove session
	session_destroy();
	unset($username, $_SESSION['username'], $isadmin, $_SESSION['isadmin'], $ismod, $_SESSION['ismod'], $hasmail, $_SESSION['hasmail']);
	$loggedout = true;
}


// check for voting on a poll:
if (isset($_POST["mainPollButton"])) {
if ($_POST["mainPollButton"] == "Vote") {
	$vote = $_POST["pollOption"];
	$query = "update tblmainpollchoices set voteCount=voteCount+1 where choiceID='".$vote."'";
	$result = mysqli_query($connection, $query) or die ("couldn't execute poll query");
	header("Location: ViewPollResults.php?poll=".$_POST["pollID"]);
	
}
}



include($_SERVER['DOCUMENT_ROOT']."/includes/html-header.php");
include($_SERVER['DOCUMENT_ROOT']."/includes/pagehead.php");
?>





