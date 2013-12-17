<?php
//jane commands library,for extended command support

/*
* WEB - interface for other areas online
* specifically: wikipedia, google, google maps
*/
function web ($dbc, $url = '', $do = '') {
	$url = preg_replace("/[.]/", "", $url);
	$marray = explode($do, $url);
	$url = trim($marray[1]);
	if ($do == 'wiki' or $do =='define' or $do =='look up') {
		$url2 = preg_replace("/[ ]/", "_", $url);
		$file = file_get_contents('http://wapedia.mobi/en/Special:Search?skl=Go&searchtype=&search=' . $url2);
		$needle = '<p class="root">';
		if (strlen(stristr($file, $needle))>0) {
			$marray = explode($needle, $file);
			$file = $marray[1];	
			$file = (htmlspecialchars(strip_tags($file)));
			$file = preg_replace("/[^a-zA-Z0-9\s]/", "", $file);
			return 'Wiki: ' . $url . '<br />' . $file . ' ... <a href="http://en.wikipedia.org/wiki/' . $url2 . '">[more]</a>';
		} else {
			$needle2 = '<li>';
			if (strlen(stristr($file, $needle2))>0) {
				$marray = explode($needle2, $file);
				$marray2 = explode('</li>', $marray[1]);
				$file = (htmlspecialchars(strip_tags($marray2[0])));
				return web($dbc, 'wiki ' . $file, 'wiki');
			} else {
				return 'The wiki article ' . $url . ' was not found. Sorry sir.';
			}
		}
	}

	if ($do == 'search') {
		return 'Here you go sir, <a href="http://www.google.ca/search?q=' . $url . '">'. $url .'</a>';
	}

	if ($do == 'map') {
		return '<a href="http://maps.google.com/maps?q='. $url . '"><center><img src="http://maps.google.com/maps/api/staticmap?center=' . $url . '&zoom=14&size=350x350&maptype=roadmap
		&markers=color:blue|label:S|40.702147,-74.015794&markers=color:green|label:G|40.711614,-74.012318
		&markers=color:red|color:red|label:C|40.718217,-73.998284&sensor=false" border=0 /></center></a>';
	}
}

/*
* ARCHIEVE
* to save a thread to get it from being deleted for prosterity
*/
function archieve($dbc, $response = '') {
$thread = (int) trim(preg_replace("/[a-zA-Z]/", "", $response));
$qBoard = "UPDATE threads SET flag=1.5 WHERE threadID =" . $thread;
$rBoard = mysqli_query ($dbc, $qBoard) or  $error = 'Error:' . mysqli_error($dbc);
return 'archieved ' . $thread . ' sir.';
}

/*
* SCAN
* scanning a url for XSS vuln
* TODO: sqli
*/
function scan ($dbc, $thread, $passcode, $board, $url = '', $count = 0) {
	$marray = explode('scan', $url, 2);
	$url = $marray[1];
	if ($url == '') { return 'url not found.'; }
	echo Addreply($dbc, 'Scanning url' . $url . ' for vulnerabulities.', '',$thread, $passcode, $board);
	$curl_handle=curl_init();
	curl_setopt($curl_handle,CURLOPT_URL, trim($url));
	curl_setopt($curl_handle,CURLOPT_CONNECTTIMEOUT,3);
	curl_setopt($curl_handle,CURLOPT_RETURNTRANSFER,1);
	curl_setopt($curl_handle, CURLOPT_FOLLOWLOCATION, true );
	curl_setopt($curl_handle, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; rv:1.7.3) Gecko/20041001 Firefox/0.10.1" );
	$file = curl_exec($curl_handle);
	$needle = 'form';
	
	if ($count < 2) {
	//get links
	$target = explode('href="', $file);
	for ($i = 0; $i < count($target); $i++) {
		$target2 = explode ('"', $target[$i]);
		$url3 = $target2[0];
		if ((strlen(stristr($url3, '.html'))>0 or strlen(stristr($url3, '.php'))>0 or strlen(stristr($url3, '.htm'))>0) && $url != '') {
		
		//correcting the target url 
		if (strlen(stristr($url3, 'http'))==0) {
			$marray = explode('/', $url, -1);
			$url3 = 'http://' . $marray[2] . '/' . preg_replace('/("|\')/', "", $url3);
		}
		$result .= '(linked url) ' . scan($dbc, $thread, $passcode, $board, 'scan ' . trim($url3), $count + 1);
		}
		if ($i > 10) { break; }
	}
	}
	if (strlen(stristr($file, $needle))>0) {
		$marray = explode($needle, $file);
		$form = explode('</form>', $marray[1]);

		//get target
		$target = explode('action=', $form[0]);
		$target = explode (' ', $target[1]);
		$url2 = $target[0];
		
		//get form data
		$name = explode('name=', $form[0]);
		if (count($name) == 0) { return 'No form data at ' . $url; }
		for ($i = 1; $i < count($name); $i++) {
			$name2 = explode(' ', preg_replace('/(>)/', " ", $name[$i]));
			if ($name2[0] != '')
				$names .= '<br /> post input detected (' . htmlspecialchars(preg_replace('/("|\')/', "", $name2[0])) . ').';
				$postdata .= htmlspecialchars(preg_replace('/("|\')/', "", $name2[0])) . '=\'";<BODY ONLOAD=alert(\'hi\')>&';
		}
		//return 'The url' . $url . ' has a form that posts to ' . htmlspecialchars($url2) . $names;
		
		//correcting the target url 
		if (strlen(stristr($url2, 'http'))==0) {
			$marray = explode('/', $url, -1);
			$url2 = 'http://' . $marray[2] . '/' . preg_replace('/("|\')/', "", $url2);
		}
		
		//now checking the target
		$curl_handle=curl_init();
		curl_setopt($curl_handle,CURLOPT_URL, trim($url2));
		curl_setopt($curl_handle,CURLOPT_CONNECTTIMEOUT,3);
		curl_setopt($curl_handle,CURLOPT_RETURNTRANSFER,1);
		curl_setopt($curl_handle, CURLOPT_FOLLOWLOCATION, true );
		curl_setopt($curl_handle, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; rv:1.7.3) Gecko/20041001 Firefox/0.10.1" );
		curl_setopt($curl_handle, CURLOPT_POSTFIELDS, $postdata);
		$file = curl_exec($curl_handle);
		if (strlen(stristr($file, '<BODY ONLOAD=alert(\'hi\')>'))>0) {
			$return = mysqli_real_escape_string ($dbc, '<span style="color: #87f14d; font-size: 18px;">found a hole!</span> &nbsp; using url <a href=' . $url2 . '>' . $url2 . '</a> with post data: ' . htmlspecialchars($postdata) . '<br />');
			return $return;
		} else {
			return 'secured (against xss) form data found for ' . $url;
		}
	} else {
		return '<a href="' . htmlspecialchars($url) . '">' . htmlspecialchars($url) . '</a> does not contain any &lt;form&gt; data. <br />' . $result;
	}
}

/*
* HELP
* output a command list
*/
function help ($dbc, $input) {
	$commands= 'Outputting my command list: <br /> <br />
					<span id=link2>map [place]</span> -- the map is embeded via. google maps <br />
					<span id=link2>add response [reaction] // to [trigger]</span>	-- adds a new response when it comes across what is after the to <br />
					<span id=link2>wiki [word ]</span>--	responses with a wikipedia article. (also works for define) <br />
					<span id=link2>search [query]</span>-- returns a search link <br />
					<span id=link2>board</span> -- for board related actions <br />
					- - <span id=link2>name [name]</span> --	for specifying a board by name <br />
					- - <span id=lin2k>id [id]</span> --	for specifying a board by id <br />
					- - <span id=link2>add board</span> --	creates a board <br />
					- - <span id=link2>edit (set keyword)</span> -- sets keyword <br />
					- - <span id=link2>edit (add keyword)</span> --	adds keyword <br />
					- - <span id=link2>private</span> --	makes the board viewable by users only <br />
					- - <span id=link2>secret</span> --	makes the board viewable by moderators only <br />
					<span id=link2>play dead</span> --	jane will not respond until revived with come alive.<br />
					<span id=link2>come alive</span> -- jane will come back from playing dead. <br />
					<span id=link2>scan [url]</span> -- jane will scan the destination and return any xss vulnerabilities (personnal use only). <br />
					<span id=link2>wordnet</span> -- returns a strict interupted response of a sentence <br />
					- - <span id=link2>type:[sentence]</span> -- returns the word types for a sentence <br />
					- - <span id=link2>addtype:[input type]:[output type]</span> -- adds the sentence types to say in response <br />
					-- <span id=link2>respond:[sentence]</span> -- shows a strict response <br />
					<span id=link2>delete</span> -- Deletes the current thread and all attached files <br />';
	return $commands;
}

/*
* profile, attempts to gather information about a user
*/
function profile($dbc, $user, $thread) {
	$q = "SELECT first, friend, gender, insidejoke, city FROM janeprofile WHERE id='$user'";
	$r = mysqli_query ($dbc, $q) or $errors[] = 'Error:' . mysqli_error($dbc);
	if (mysqli_num_rows($r) > 0) {
		$row = mysqli_fetch_array($r, MYSQLI_ASSOC);
		if ($row['first'] == '' && rand(0,1) == 0) { //first
			$output = 'What is your first name';
			if (strlen(stristr($user,'.'))==0) { $output .= ' ' . $user; }
			$output .= '?';	
			$qHi2 = "UPDATE janethread SET mood = '/first/$user' WHERE thread = '$thread'"; //reset mood
			$rHi2 = mysqli_query($dbc, $qHi2);
		} else if ($row['gender'] == '' && rand(0,1) == 0) { //gender
			$output = 'Are you male or female';
			 if (strlen(stristr($user,'.'))==0) { $output .= ' ' . $user; }
			$output .= '?';	
			$qHi2 = "UPDATE janethread SET mood = '/gender/$user' WHERE thread = '$thread'"; //reset mood
			$rHi2 = mysqli_query($dbc, $qHi2);
		} else if ($row['friend'] == '' && rand(0,1) == 0) { //friend
			$output = 'Do you have friends? What is one of their names?';
			$qHi2 = "UPDATE janethread SET mood = '/friend/$user' WHERE thread = '$thread'"; //reset mood
			$rHi2 = mysqli_query($dbc, $qHi2);
		} else if ($row['insidejoke'] == '' && rand(0,1) == 0) { //friend
			$output = 'What is a good joke';
			if ($row['first'] != '') { $output .= ' ' . $row['first']; } else if (strlen(stristr($user,'.'))==0) { $output .= ' ' . $user; }
			$output .= '?';
			$qHi2 = "UPDATE janethread SET mood = '/joke/$user' WHERE thread = '$thread'"; //reset mood
			$rHi2 = mysqli_query($dbc, $qHi2);
		} else if ($row['city'] == '' && rand(0,1) == 0) { //friend
			$output = 'what city or town are you from';
			if ($row['first'] != '') { $output .= ' ' . $row['first']; } else if (strlen(stristr($user,'.'))==0) { $output .= ' ' . $user; }
			$output .= '?';
			$qHi2 = "UPDATE janethread SET mood = '/city/$user' WHERE thread = '$thread'"; //reset mood
			$rHi2 = mysqli_query($dbc, $qHi2);
		} else {
			//random message
			if (rand(0, 3) == 0) {
				switch (rand(0,10)) {
					case 0: $output = 'Tell me about yourself'; break;
					case 1: $output = 'How are things'; break;
					case 2: $output = 'What are you thinking about'; break;
					case 3: $output = 'Are you tired'; break;
				}			
				if ($row['first'] != '') { $output .= ' ' . $row['first']; }
				$output .= '?';
			} else {
				if ($row['insidejoke'] != '') { $output = 'hey ' . $row['first'] . ', ' . $row['insidejoke'] . ' :) lol'; }
				else if ($row['friend'] != '') { $output = 'how\'s ' . $row['friend'] . ' doing?'; }
			}
		}
		
	} else {
		$q = "INSERT INTO janeprofile(id, first, last, city, color, eyecolor, song, momFirst, dadFirst, middle, insideJoke, Friend, gender, speech) VALUES ('$user', '', '', '', '', '', '', '', '', '', '', '', '', '')";
		$r = mysqli_query ($dbc, $q) or $errors[] = 'Error:' . mysqli_error($dbc);
		$output = 'Are you a guy or girl';
	    if (strlen(stristr($user,'.'))==0) { $output .= ' ' . $user; }
		$output .= '?';	
		$qHi2 = "UPDATE janethread SET mood = '/gender/$user' WHERE thread = '$thread'"; //reset mood
		$rHi2 = mysqli_query($dbc, $qHi2);
	}
	return $output;
}
function profileShow($dbc, $user) {
	$q = "SELECT * FROM janeprofile WHERE id='$user'";
	$r = mysqli_query ($dbc, $q) or $errors[] = 'Error:' . mysqli_error($dbc);
	if (mysqli_num_rows($r) > 0) {
		$row = mysqli_fetch_array($r, MYSQLI_ASSOC);
		$output = 'Showing profile<br /><span id=link2>user:</span> ' . $user .
		'<br /><span id=link2>first name:</span> ' . $row['first'] .
		'<br /><span id=link2>middle name:</span> ' . $row['middle'] .
		'<br /><span id=link2>last name:</span> ' . $row['last'] .
		'<br /><span id=link2>gender:</span> ' . $row['gender'] .
		'<br /><span id=link2>city:</span> ' . $row['city'] .
		'<br /><span id=link2>favourite color:</span> ' . $row['color'] .
		'<br /><span id=link2>eye color:</span> ' . $row['eyecolor'] .
		'<br /><span id=link2>favourite song:</span> ' . $row['song'] .
		'<br /><span id=link2>mother\'s first name:</span>' . $row['momFirst'] .
		'<br /><span id=link2>father\'s first name:</span> ' . $row['dadFirst'] .
		'<br /><span id=link2>Inside joke:</span> ' . $row['insideJoke'] .
		'<br /><span id=link2>Friend\'s name:</span> ' . $row['Friend'] .
		'<br /><span id=link2>Speech patterns:</span> ' . $row['speech'];
	} else {
		$output = 'user ' . $user . ' not found.';
	}
	return $output;
}

function profileForget($dbc, $user) {
	if($stmt = $dbc -> prepare("DELETE FROM janeprofile WHERE id=?")) { //set flag
			$stmt -> bind_param("s", $user);
			$stmt -> execute();
			$stmt -> close();
		}
	return "I have already forgotten";			
}

//THE BASEMENT, for outdated commands


function chatterbot ($word='') {
//polls a few online ai bots to see what responses they have, currenly it scans cleverbot and missionVao
	$bot = rand(1,2);
	$result = ($bot == 2)? cleverbot($word) : missionVao($word);
	return $result;
}

function cleverbot ($word='') {
	$word = str_replace(" ", "%20", $word);
	$data = 'stimulus=' . $word . '&start=y&sessionid=&vText';
	$hash = md5(substr($data, 9, 20));
	$curl_handle=curl_init();
	curl_setopt($curl_handle,CURLOPT_URL, 'http://www.cleverbot.com/webservicefrm');
	curl_setopt($curl_handle,CURLOPT_CONNECTTIMEOUT,3);
	curl_setopt($curl_handle,CURLOPT_RETURNTRANSFER,1);
	curl_setopt($curl_handle, CURLOPT_FOLLOWLOCATION, true );
	curl_setopt($curl_handle, CURLOPT_POSTFIELDS, 'stimulus=' . $word . '&start=y&sessionid=&vText8=&vText7=&vText6=&vText5=&vText4=&vText3=&vText2=&icognoid=wsf&icognocheck=' . $hash . '&prevref=&emotionaloutput=&emotionalhistory=&asbotname=&ttsvoice=&typing=&lineref=&sub=Say&islearning=1&cleanslate=false');
	curl_setopt($curl_handle, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; rv:1.7.3) Gecko/20041001 Firefox/0.10.1" );
	curl_setopt($curl_handle, CURLOPT_REFERER, "http://cleverbot.com");
$buffer = curl_exec($curl_handle);
if (substr_count($buffer, ': said') > 0) {
	return FALSE;
} else {
	$marray = explode('Q', $buffer);
	$response = str_replace("WebServiceForm", "", strip_tags($marray[0]));
	$response = str_ireplace("cleverbot", "Jane", $response);
	return $response;
}
}

function missionVao ($word='') {
	$word = urlencode($word);
	$curl_handle=curl_init();
	curl_setopt($curl_handle,CURLOPT_URL, 'http://www.pandorabots.com/pandora/talk?botid=bef6c0dc7e3457bd');
	curl_setopt($curl_handle,CURLOPT_CONNECTTIMEOUT,3);
	curl_setopt($curl_handle,CURLOPT_RETURNTRANSFER,1);
	curl_setopt($curl_handle, CURLOPT_FOLLOWLOCATION, true );
	curl_setopt($curl_handle, CURLOPT_POSTFIELDS, 'botcust2=8cf70d4f6af1a5fa&input=' . $word);
	curl_setopt($curl_handle, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; rv:1.7.3) Gecko/20041001 Firefox/0.10.1" );
	curl_setopt($curl_handle, CURLOPT_REFERER, "http://www.pandorabots.com");
	$buffer = curl_exec($curl_handle);
	if (substr_count($buffer, 'no published') > 0) {
	return FALSE;
	} else {
	$marray = explode('<font color="White">', $buffer);
	$marray2 = explode('</font', $marray[1]);
	$response = str_ireplace("Mission Vao", "Jane", $marray2[0]);
	return $response;
	}
}
 
?>