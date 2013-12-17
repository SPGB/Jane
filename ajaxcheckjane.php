<?php
if (@$_REQUEST['checkthread'] != '' && isset($_SERVER['HTTP_X_REQUESTED_WITH'])) { jane($_REQUEST['checkthread']); }

function jane($thread='') {
	require_once ('includes/mysqli_connect.php'); // need the database connection
	if($stmt = $dbc -> prepare("SELECT passcode, body, boardID, username, fileID FROM threads WHERE threadID=? AND flag=0 AND username != 'jane' ORDER BY date_made DESC LIMIT 1")) {
		$stmt -> bind_param("i", $thread);
		$stmt -> execute();
		$stmt -> store_result();
		if ($stmt -> num_rows() > 0) {
			$stmt -> bind_result($pass, $body, $boardId, $username, $fileID);
			$stmt -> fetch();
			if($stmt2 = $dbc -> prepare("UPDATE threads SET flag=1 WHERE flag=0 AND threadID=?")) { //set flag
				$stmt2 -> bind_param("i", $thread);
				$stmt2 -> execute();
				$stmt2 -> close();
			}	
			require_once('content.aware/jane.inc.php');
			echo stimuli($dbc, $thread, strip_tags(htmlspecialchars_decode($body)), $pass, $boardId, $username, $fileID);
		}
		$stmt -> close();
	}
	mysqli_close($dbc);
}
?>