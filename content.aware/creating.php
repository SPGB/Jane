<?php
ob_start();

$UPLOAD_ID = '';                                      // Initialize upload id

require_once 'uploader/ubr_ini.php';
require_once 'uploader/ubr_lib.php';
require_once 'uploader/ubr_finished_lib.php';
if ($_GET['type'] != 'null') {
include ('includes/header.html'); //includes
}
if(isset($_GET['upload_id']) && preg_match("/^[a-zA-Z0-9]{32}$/", $_GET['upload_id'])){ $UPLOAD_ID = $_GET['upload_id']; 

//Declare local values
$_XML_DATA = array();                                          // Array of xml data read from the upload_id.redirect file
$_CONFIG_DATA = array();                                       // Array of config data read from the $_XML_DATA array
$_POST_DATA = array();                                         // Array of posted data read from the $_XML_DATA array
$_FILE_DATA = array();                                         // Array of 'FileInfo' objects read from the $_XML_DATA array
$_FILE_DATA_TABLE = '';                                        // String used to store file info results nested between <tr> tags
$_FILE_DATA_EMAIL = '';                                        // String used to store file info results

$xml_parser = new XML_Parser;                                  // XML parser
$xml_parser->setXMLFile('uploader/' . $TEMP_DIR, $_GET['upload_id']);        // Set upload_id.redirect file
$xml_parser->setXMLFileDelete($_INI['delete_redirect_file']);  // Delete upload_id.redirect file when finished parsing
$xml_parser->parseFeed();                                      // Parse upload_id.redirect file

// Display message if the XML parser encountered an error
if($xml_parser->getError()){ kak($xml_parser->getErrorMsg(), 0, __LINE__, $_INI['path_to_css_file']); }

$_XML_DATA = $xml_parser->getXMLData();                        // Get xml data from the xml parser
$_CONFIG_DATA = getConfigData($_XML_DATA);                     // Get config data from the xml data
$_POST_DATA  = getPostData($_XML_DATA);                        // Get post data from the xml data
$_FILE_DATA = getFileData($_XML_DATA);                         // Get file data from the xml data


// Format upload results
$_FORMATTED_UPLOAD_RESULTS = getFormattedUploadResults($_FILE_DATA, $_CONFIG_DATA, $_POST_DATA);

// Create and send email
if($_CONFIG_DATA['send_email_on_upload']){ emailUploadResults($_FILE_DATA, $_CONFIG_DATA, $_POST_DATA); }

}
require ('includes/mysqli_connect.php'); //includes
require_once('salt.rotator.php');

echo '<div class="result" id="result">';
$fileLimit = 100; //limit in MB before culling oldest files, max size for an upload is 5mb so the true limit is 15
$uploadLimit = 1; //limit in GB before culling uploads, true limit is 1.2 GB
$errors = array(); //create errors array 
$b = ($_POST['body'] != '') ? $_POST['body'] : $_POST_DATA['body'];
if (empty($b) OR strlen($b)>10000) { //check for body
	echo 'Please enter a message in under 10,000 characters<br><br><br><br><br>';
	@$file_name = $_FILE_DATA[$i]->getFileInfo('name');
	if (file_exists('uploader/tmp/' . $file_name)) {
	unlink('uploader//' . $file_name);
	}
	exit();
} else {
		$b = preg_replace('@(https?://([-\w\.]+)+(:\d+)?(/([\w/_\.]*(\?\S+)?)?)?)@', '<a href="$1" target="_blank">$1</a>', $b); //replaces urls with links
		$b = nl2br(preg_replace('/\n{4,}/', str_repeat('<br/>', 3), preg_replace('/\r/', '', $b))); //line breaks
		$b = mysqli_real_escape_string($dbc, $b); //serialize mysqli
		$b = preg_replace('/<[^(\/|)(br|a)]/', '<disabled', $b); //serialize htmlchars
		$viewers = ($_POST['viewers'] != '') ? $_POST['viewers'] : $_POST_DATA['viewers'];
		$viewers = mysqli_real_escape_string ($dbc, $viewers);
	}
$submitted = ($_POST['submitted'] != '') ? $_POST['submitted'] : $_POST_DATA['submitted'];
$replying = ($_POST['replying'] != '') ? $_POST['replying'] : $_POST_DATA['replying'];
if ($replying == '' ) { //definging variables for the new thread
	$boardID = 0;
	$v = (integer) $viewers;
	$q1 = "SELECT boardName, boardID from boards WHERE boardKey='$viewers'";
	$r1 = mysqli_query ($dbc, $q1) or  $errors[] = 'Error:' . mysqli_error($dbc);
	$row1 = mysqli_fetch_array ($r1, MYSQLI_ASSOC);			
	if (mysqli_num_rows($r1) == 1){
		switch (rand(1,10)) { //get extension
			case '1' : $boardText="Thanks for your post.";break;
			case '2' : $boardText="Successfully.";break;
			case '3' : $boardText="be alert. something is amiss.";break;
			case '4' : $boardText="live long and prosper.";break;
			case '5' : $boardText="Sic Semper Tyranus.";break;
			case '6' : $boardText="I hope it's relevant to the board.";break;
			case '7' : $boardText=".";break;
			default : $boardText="Thank you sir.";break;
		}	
		$errors[] = 'Posted under <b>' . $row1['boardName']. '</b>. ' . $boardText; 
		$boardID = $row1['boardID'];
	} 
	if ($viewers == 'sn' OR $viewers == 'SN') { //for providing source code
		session_start();
		if ($_SESSION['userLVL'] != 3) {
			echo 'Access rights to post in site news <font color=red>denied</font>. Session possibly expired, please relog.<br><br><br><br><br><br><br><br>';
			echo '</div>';
			include('includes/footer.html');
			exit();
		}
		$errors[] = 'Access rights to post in site news are valid'; 
		$boardID = 102;
	} 
$listing = ($_POST['listing'] != '') ? $_POST['listing'] : $_POST_DATA['listing'];	
	if ($listing == true){ //post to public listing
		$l = 100; //starting popularity of public threads
	} else {
		$l = 0; 
		$errors[] = 'Created a private thread. Please remember your thread id and thread password.';
	}
	$e = rand(1000000,9999999); //create thread ID
	$pkey = rand(1000000,9999999);
	vars();
	$passkey = "AES_ENCRYPT('{$pkey}', '{$salt_cur}')";
	$parID = 0;
	$usernum = 1;
	
	$evalid = 0; //will continue to run until no threads which match the thread ID are returned/
	while ($evalid != 1) {
	$q = "SELECT threadID from threads WHERE threadID='$e'";
	$r = mysqli_query ($dbc, $q) or  $errors[] = 'Error:' . mysqli_error($dbc);
	if (mysqli_num_rows($r) != 0) { //exits if the threadID is taken
		$e = rand(1000000,9999999); 
	} else {
	$evalid = 1;
	$evalid = 1;
	}
	}
	
} else { //variables for replying
session_start();
	$e = htmlspecialchars(strip_tags(mysqli_real_escape_string ($dbc, $replying)));
	$e2 = $_SESSION['thread' . $e];
	$q = "SELECT threadID, flag, popularity, boardID from threads WHERE threadID='$e' AND passcode='$e2' LIMIT 1";
	$r = mysqli_query ($dbc, $q) or  $errors[] = 'Error:' . mysqli_error($dbc);
	if (@mysqli_num_rows($r) == 0) { //check if thread exists
	echo 'We have you currently logged into another thread. Please rejoin.';
	include('includes/footer.html');
	exit(); 
} else {
	$parID = $e; //parent ID
	$row = mysqli_fetch_array ($r, MYSQLI_ASSOC);
	$boardID = $row['boardID'];
	if ($row['popularity'] == 0) { //determines if it will be private
	$l = 0;
	} else {
	$l = 1;
	}
	if ($row['flag'] == 3) { //if it is inactive 	
	$q = "UPDATE threads SET flag = 0 WHERE threadID='$e'";
	$r = mysqli_query ($dbc, $q) or  $errors[] = 'Error:' . mysqli_error($dbc);
	}
	}


$usernum = $_SESSION['usernumber' . md5($e)];
if ($usernum != '') { //checks to make sure credentials are set
	if (is_numeric($usernum)) {
		$errors[] = 'Your user number is ' . $usernum;
	}
} else {
	$_SESSION['usernumber' . md5($e)] = $data['user_number'] + 1; 
	$usernum = $data['úser_number'] + 1; }
	$v = 0;
	$passkey = '\'' . $_SESSION['thread' . $e] . '\'';
}

// FOR UPLOADING
// if larger then 5,000 kb > redirect to uploads page.
if (count($_FILE_DATA) > 0 OR $_FILES['rimage']['name'] != '') {
global $fileID;
$fileID = md5(rand(0, 9999) . date("c"));
if (count($_FILE_DATA) > 0) {
for($i = 0; $i < count($_FILE_DATA); $i++){
	$file_slot = $_FILE_DATA[$i]->getFileInfo('slot');
	$file_name = $_FILE_DATA[$i]->getFileInfo('name');
	$file_size = $_FILE_DATA[$i]->getFileInfo('size');
	$file_type = $_FILE_DATA[$i]->getFileInfo('type');
	$file_status = $_FILE_DATA[$i]->getFileInfo('status');
	$file_status_desc = $_FILE_DATA[$i]->getFileInfo('status_desc');
    if (!createImage($dbc, $file_name, $file_type, $uploader_type, $fileID)) { echo 'error creating your image' . $file_name; }	
}
} else if ((isset($_FILES['rimage']['name']) && (!(isset($_POST['upload']))))){
$file_name = $_FILES['rimage']['tmp_name'];
$file_size = filesize($_FILES['rimage']['tmp_name']);
$file_type = $_FILES['rimage']['type'];
$uploader_type = 'basic';
if (!createImage($dbc, $file_name, $file_type, $uploader_type, $fileID)) { echo 'error creating your image' . $file_name; }	
}

}
  
if ($_COOKIE['loggedin'] != '') {
	session_start();
	$userid = $_SESSION['userID'];
	$userpass = $_SESSION['userPASS'];
	if ($_SESSION['displayusername'] != false) {
		$uid = mysqli_real_escape_string ($dbc, $_COOKIE['userID']); 
		$errors[] = 'posting as ' . $uid;
		$q = "UPDATE users SET posts=(posts+1) WHERE username='$uid' OR (email='$uid' AND activated=2)";
		$r = mysqli_query ($dbc, $q) or $errors[] = 'Error:' . mysqli_error($dbc);
	} else {
		$uid = '';
	}	
} else { 
	$uid = '';
}

//add thread to database


$q = "INSERT INTO threads (threadID, passcode, parentID, user_number, username, body, fileID, file, ext, popularity, max_viewers, date_made, boardID) VALUES ('$e', $passkey, '$parID', '$usernum', '$uid', '$b', '$fileID', '', '', '$l', '$v', now(), '$boardID')";
$r = mysqli_query ($dbc, $q) or $errors[] = 'Error:' . mysqli_error($dbc);
if (mysqli_affected_rows($dbc) == 1) { //checks if it went through
if (isset($replying)) {
	//check if we need to alert anyone
	$qS = "SELECT username from usersSaved WHERE upper(recent) LIKE upper('%$e%')";
	$rS = mysqli_query($dbc, $qS);
	if (mysqli_num_rows($rS) > 0) {
		while ($saved = mysqli_fetch_array ($rS, MYSQLI_ASSOC)) {
			echo 'alerting ' . $saved['username'];
			$qS2 = "UPDATE users SET recent = recent + 1 WHERE username = '$saved[username]'";
			$rS2 = mysqli_query($dbc, $qS2);
		}
	}
	$qS = "SELECT username, email from usersSaved WHERE upper(threads) LIKE upper('%$e%')";
	$rS = mysqli_query($dbc, $qS);
	if (mysqli_num_rows($rS) > 0) {
		while ($saved = mysqli_fetch_array ($rS, MYSQLI_ASSOC)) {
			echo 'alerting ' . $saved['username'];
			$qS2 = "UPDATE users SET threads = threads + 1 WHERE username = '$saved[username]'";
			$rS2 = mysqli_query($dbc, $qS2);
			//send out email
		}
	}
	//end check
	$q1 = "UPDATE listing SET replies=replies + 1 WHERE threadID = $e LIMIT 1";
	$r1 = mysqli_query ($dbc, $q1) or $errors[] = 'Error:' . mysqli_error($dbc);
	echo 'replying to ' . $e;
	include ('includes/footer.html');
}	else {
$q1 = "INSERT INTO listing (threadID) VALUES ('$e')";
$r1 = mysqli_query ($dbc, $q1) or $errors[] = 'Error:' . mysqli_error($dbc);
$q1 = "UPDATE boards SET threadCount=threadCount + 1 WHERE boardID='$boardID'";
$r1 = mysqli_query ($dbc, $q1) or  $errors[] = 'Error:' . mysqli_error($dbc);
setcookie (md5($e), 1); //if creating a new thread
echo '
<script language="javascript" src="reflection.js" type="text/javascript"></script>
<center>
<table style="min-width: 350px;">
<tr><td colspan=2 align=center>Thread Created. <a href="enter.php?ID=' . $e . "&pass=" . $pkey . '&boardID=' . $boardID . '" target="_blank"> [join] </a></td></tr>
<tr>
<td>thread ID </td><td align=right> thread password </td></tr><tr>
<td>' . $e . '</td><td align=right>' . $pkey . '</td>
</tr><tr><td colspan=2 align=center><div style="display: inline-block; margin-left: auto; margin-right: auto;">' . $extThumb . '</div></td>
</table></center></center>';
echo '</div>';
if ($_GET['type'] != 'null') {
include ('includes/footer.html');
}
exit();
}
} else {
$errors[] = "Thread unable to be added.";
echo '</div>';
include ('includes/footer.html');
}
mysqli_close($dbc);
ob_flush();

//functions
function createImage ($dbc, $file_name = '', $file_type = '', $uploader_type = '', $fileID = '') {
$errors = array();
	//check to make sure there is room:
	$q0 = "SELECT file, ext FROM threads where ext != '' AND file != '' AND ext != ''";
	$r = mysqli_query ($dbc, $q0) or $errors[] = 'Error:' . mysqli_error($dbc);
	while (($messages = mysqli_fetch_array($r, MYSQLI_ASSOC))) {
	$file  = "uploads/{$messages[file]}{$messages[ext]}";
	if (file_exists($file) && is_file($file)) {
	$sum2 = $sum2 + filesize($file);
	}
	}
	if (($sum2 / 1024 / 1024) > $fileLimit) { //delete inactive
	$r = mysqli_query($dbc, "SELECT file, ext, threadID FROM threads WHERE ext !='' AND ext !='.upload' ORDER BY date_made ASC LIMIT 1"); //get files
	$file = mysqli_fetch_array($r, MYSQLI_ASSOC);
	if (file_exists('thumbs/' . $file['file'] . 's.jpg') && file_exists('uploads/' . $file['file'] . $file['ext'])) {
	unlink('thumbs/' . $file['file'] . 's.jpg');
	unlink('uploads/' . $file['file'] . $file['ext']);
	$r2 = mysqli_query($dbc, "UPDATE threads SET file='removed', ext='' WHERE file='$file[file]'"); //delete thread
	}
	}
	//end check to see if there is room
				global $tim;
				global $ext;
				global $extThumb;
				$tim = md5($file_name);
					switch ($file_type) { //get extension
						case 'text/plain' : $ext=".txt";break;
						case 'application/pdf' : $ext=".pdf";break;
						case 'application/msword' : $ext=".doc";break;
						case 'audio/mpeg' : $ext=".mp3";break;
						case 'application/x-bittorrent' : $ext=".torrent";break;
						case 'image/jpeg' : $ext=".jpg"; break;
						case 'image/png' : $ext=".png"; break;
						case 'image/gif' : $ext=".gif"; break;
					}

				$temp ='uploads/' . $tim . $ext;
				if ($uploader_type == 'basic') {
				move_uploaded_file($_FILES['rimage']['tmp_name'], $temp);
				} else {
				rename('uploader/tmp/' . $file_name, $temp);
				}
			if ( !file_exists('uploads/' . $tim . $ext)) {
			$errors[] = 'File unsuccessfully moved.';
			return false;
			}
			//thumbnails
			if($ext == '.jpg' OR $ext == '.png' OR $ext == '.gif'){
				$success = createThumbFile('uploads/', $tim . $ext, 'thumbs/', $tim . 's.jpg', 150, 150); 
				if (file_exists('thumbs/' . $tim . 's.jpg')) {
					$errors[] = '<font color="green">Image successfully uploaded.</font>';
					if ($ext == '.jpg' OR $ext == '.png' OR $ext == '.gif') {
						$extThumb .= '<img src="thumbs/' . $tim . 's.jpg" class="reflect" title="Thumbnail" style="float: left;" />';
					} else {
						$extThumb .= '<img src="filetypes/' . $ext . '.png" title="file type" style="float: left;" />';
					}
						} else {
					$errors[] = '<font color="red">the image could not be uploaded.</font>';
					return false;
			}
			} else {
			if ($ext != '') {
			$errors[] = 'file type: ' . $ext;
			}
			}
$q = "INSERT INTO files (fileID, file, ext) VALUES ('$fileID', '$tim', '$ext')";
$r = mysqli_query ($dbc, $q) or $errors[] = 'Error:' . mysqli_error($dbc);
if (mysqli_affected_rows($dbc) == 1) { //checks if it went through
return true;
}
}
?>