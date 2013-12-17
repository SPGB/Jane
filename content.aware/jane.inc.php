<?php 
/*
*basic functions used for Jane
*/
function stimuli ($dbc, $thread = '', $input = '', $passcode = '', $board = '', $user = '', $fileID = '') {
	$outputLeft = false;
	$confidence = 0;
	$output = '';
	echo $thread . ') input: "' . $input . '" from: ' . $user;
	if (strlen(stristr($input,'/http/'))>0) {
		if ($thread == '') {
			exit;
		} else {
			$input = preg_replace('/(http(s|):\/\/(.)+|RT|"(.)*"|@jane_darkchan)/', '', $input);
		}
	}
	$message = $input;
	$count = 0;
	if ($thread != '') {
	// - - - Greet and profile - - -
	$qHi = "SELECT replycount, mood FROM janethread WHERE thread = '$thread'";
	$rHi = mysqli_query($dbc, $qHi); //check if already replied
	if (mysqli_num_rows($rHi) == 0) { //if we havn't said hello
		if ($user != '') { //if the poster has a username
			switch (rand(0, 5)) {
				case 0: $greet = "H'lo"; break;
				case 1: $greet = "Hiya"; break;
				case 2: $greet = "Hola"; break;
				case 3: $greet = "Yo"; break;
				case 4: $greet = "Hey"; break;
				case 5: $greet = "Hi"; break;
			}
			$output .= $greet . ' ' . $user;
			//draw from our knowledge about the user
				$qJ = "SELECT friend, city FROM janeprofile WHERE id='$user'";
				$rJ = mysqli_query($dbc, $qJ);
				if (mysqli_num_rows($rJ) > 0) {
					$jProfile = mysqli_fetch_array($rJ, MYSQLI_ASSOC); 
					if ($jProfile['friend'] != '' && rand(0,1)) {
						$output .= ' how is ' . $jProfile['friend'] . '?';
					} else if ($jProfile['friend'] != '')  {
						$output .= ' how is ' . $jProfile['city'] . '\'s weather?';
					}
				}
			} else {
				include_once('firstPost.php');
				$output = first($message, $fileID);
			}
			$outputLeft= true;
			$qHi2 = "INSERT INTO janethread (thread, mood) VALUES ('$thread', '$mood') ";
			$rHi2 = mysqli_query($dbc, $qHi2); //check if already replied
	} else {
		$jThread = mysqli_fetch_array($rHi, MYSQLI_ASSOC); 
		$mood = $jThread['mood'];
		$replycount = $jThread['replycount'];
	}
	echo $output;
	// - - - stop words - - -
		if (preg_match('/(@|\.{5}|moar|bump|OP IS A)/i', $message)) {
			echo 'stop word detected (' . $message . ').';
			$outputLeft= true;
			echo 'stop word.';
			exit();
		}
		
	//- - - mood: dead - - -
	echo 'mood: ' . $mood;
	if ($mood == 'dead') {
		if (preg_match('/come alive/i', $message)) {
			echo 'mood reset';
			$qHi2 = "UPDATE janethread SET mood = '' WHERE thread = '$thread'";
			$rHi2 = mysqli_query($dbc, $qHi2); //check if already replied
			$mood = '';
			$outputLeft = true;
			$output = 'I\'m awake! Hello hi hey.';
		} else {
			echo 'Playing dead.';
			exit();
		}
	}
	//- - - mood: crepy - - -

		if ((preg_match('/(creepy|scary)/i', $message) OR rand(1,500) == 1) && $mood != 'creepy' && !strlen(stristr($mood,'/'))>0) {
		echo 'mood: creepy ';
		$qHi2 = "UPDATE janethread SET mood = 'creepy' WHERE thread = '$thread'";
		$rHi2 = mysqli_query($dbc, $qHi2); //check if already replied
		$mood = 'creepy';
		}
		
	//- - - mood: flirt - - -

		elseif ((preg_match('/((jane is|your|you\'re|u r|you are) (cute|hot|sexy|pretty)|;|;p|good job|love you)/i', $message) OR rand(1,200) == 1) && $mood != 'flirt' && !strlen(stristr($mood,'/'))>0) {
		echo 'mood: flirt ';
		$qHi2 = "UPDATE janethread SET mood = 'flirt' WHERE thread = '$thread'";
		$rHi2 = mysqli_query($dbc, $qHi2); //check if already replied
		$mood = 'flirt';
		}

	//- - - mood: trivia - - -

		elseif (preg_match('/(trivia|game)/i', $message) && strlen(stristr($mood,'/trivia/'))==0) {
			echo 'mood: trivia';
			list ($category, $question, $answer) = @trivia($dbc, 'trivia');
			if ($category != false) {
				$qHi2 = "UPDATE janethread SET mood = '$question /trivia/ $answer /trivia/ $replycount' WHERE thread = '$thread'";
				$rHi2 = mysqli_query($dbc, $qHi2);
				if (mysqli_affected_rows($dbc) == 1) {
					$mood = $question . ' /trivia/ ' . $answer;
					$outputLeft = true;
					$output = 'Trivia time! My question is a ' . $category . ' question. It is as follows, ' . $question;
				}
			} else {
				$outputLeft = true;
				$output = 'ask nicely and we can play.';
			}
		}

	//- - - mood: riddle - - -

		elseif (preg_match('/(riddle)/i', $message) && strlen(stristr($mood,'/riddle/'))==0) {
			echo 'mood: riddle';
			list ($category, $question, $answer) = @trivia($dbc, 'riddle');
			if ($category != false) {
				$qHi2 = "UPDATE janethread SET mood = '$question /riddle/ $answer /riddle/ $replycount' WHERE thread = '$thread'";
				$rHi2 = mysqli_query($dbc, $qHi2);
				if (mysqli_affected_rows($dbc) == 1) {
					$mood = $question . ' /riddle/ ' . $answer;
					$outputLeft = true;
					$output =  'I have a riddle for you. It is as follows, ' . $question;
				}
			} else {
				$outputLeft = true;
				$output = 'ask nicely and I will ask you a riddle.';
			}
		}	
		//- - - mood: teaching - - -

		elseif (preg_match('/(teach|learn)/i', $message)) {
			echo 'mood: teaching';
			$q = "SELECT contentID, message, reply from contentaware WHERE success < 0 LIMIT 1";
			$r = mysqli_query($dbc, $q);
			$row = mysqli_fetch_array($r, MYSQLI_ASSOC);
			$message = $row['message'];
			$reply = $row['reply'];
			$id = $row['contentID'];
			if ($message != '' && $id != '') {
				$qHi2 = "UPDATE janethread SET mood = '$message /teach/ $id' WHERE thread = '$thread'";
				$rHi2 = mysqli_query($dbc, $qHi2);
				if (mysqli_affected_rows($dbc) == 1) {
					$outputLeft = true;
					$output =  'Would you like to teach me something? How would you respond to this: <br /><i>' . $message . '</i><br />I was thinking: <i>' . $reply . '</i>, but am not sure.<br /><span style="float: right;">(type next to teach me something else)</span>';
				}
			} else {
				$outputLeft = true;
				$output = 'I don\'t need to know anything right now. Ask again in a little bit.';
			}
		}
	//- - - stop mood - - -
	elseif (preg_match('/(stop|give up|go away|play dead)/i', $message)) {
		if (strlen(stristr($mood,'/trivia/'))>0 OR strlen(stristr($mood,'/riddle/'))>0) {
			$marray =(strlen(stristr($mood,'/trivia/'))>0) ? explode ('/trivia/', $mood) : explode ('/riddle/', $mood);
			$type = (strlen(stristr($mood,'/trivia/'))>0) ? 'trivia question' : 'riddle';
			$trivia = true;
		}
		elseif (preg_match('/(play dead)/i', $message)) {
		$qHi2 = "UPDATE janethread SET mood = 'dead' WHERE thread = '$thread'";
		$rHi2 = mysqli_query($dbc, $qHi2); //check if already replied
		$mood = 'dead';
		$outputLeft = true;
		$output = 'I will play dead sir until commanded otherwise. When you want me back just tell me to come alive.';
		} elseif ($mood != '') {
		echo 'mood reset.';
		$qHi2 = "UPDATE janethread SET mood = '' WHERE thread = '$thread'";
		$rHi2 = mysqli_query($dbc, $qHi2); //check if already replied
		$mood = '';
		}
		if ($trivia) {
			$qHi2 = "UPDATE janethread SET mood = '' WHERE thread = '$thread'";
			$rHi2 = mysqli_query($dbc, $qHi2);
			$outputLeft = true;
			$output = 'You have given up. Your question was ' . $marray[0] . '. The answer was: ' . $marray[1] . '. Another ' . $type . '?';
		}
		}

	//- - - trivia check - - -
	elseif (strlen(stristr($mood,'/trivia/'))>0 OR strlen(stristr($mood,'/riddle/'))>0) {
		$marray = (strlen(stristr($mood,'/trivia/'))>0) ? explode ('/trivia/', $mood) : explode ('/riddle/', $mood);
		$type = (strlen(stristr($mood,'/trivia/'))>0) ? 'trivia question' : 'riddle';
		if (strlen(stristr($message,trim($marray[1])))>0) {
			$qHi2 = "UPDATE janethread SET mood = '' WHERE thread = '$thread'";
			$rHi2 = mysqli_query($dbc, $qHi2);
			$outputLeft = true;
			$output = 'Correct! the answer was ' . $marray[1] . '. If you would like to play again ask me for another ' . $type . '.';
		} else {
			if (strlen(stristr($message,'hint'))>0) {
				switch (rand(0,2)) {
					case 0 : $answer = preg_replace('/[a-z|0-9]/iu', '_', trim($marray[1])); break;
					case 1 : $answer = 'It starts with ' . substr(trim($marray[1]), 0, 1); break;
					case 2 : $answer = 'It ends with ' . substr(trim($marray[1]), -1); break;
				}
				$outputLeft = true;
				$output = 'Here is your hint: ' . $answer;
			} else {
				$edited = substr(trim($marray[1]), 0, ($replycount-trim($marray[2])));
				$answer = trim($marray[1]);
				for ($i = ($replycount-trim($marray[2])); $i < (strlen(trim($marray[1]))); ++$i)
						$edited .= ($answer{$i} != ' ')  ? ' _ ' : ' &nbsp; ';
				$hint = 'Here is a hint: ' . $edited;
				$outputLeft = true;
				$output = 'Incorrect sir. The question is ' . trim($marray[0]) . '. If you would like to give up anytime just say stop. ' . $hint;
			}
		}
	}
	
		//- - - teaching check - - -
	elseif (strlen(stristr($mood,'/teach/'))>0) {
		$marray = explode ('/teach/', $mood);
		if (strlen(stristr($message,'next'))>0) { //next question
			$q = "SELECT contentID, message from contentaware WHERE success < 0 LIMIT 1";
			$r = mysqli_query($dbc, $q);
			$row = mysqli_fetch_array($r, MYSQLI_ASSOC);
			$message = mysqli_real_escape_string ($dbc, $row['message']);		
			$id = $row['contentID'];
			if ($message != '' && $id != '') {
				$message = mysqli_real_escape_string ($dbc, $message);
				$qHi2 = "UPDATE janethread SET mood = '$message /teach/ $id' WHERE thread = '$thread'";
				$rHi2 = mysqli_query($dbc, $qHi2);
				if (mysqli_affected_rows($dbc) == 1) {
					$outputLeft = true;
					$output =  'Then how about this: <br /><i>' . $message . '</i><br /><br /><span style="float: right;">(type next to teach me something else)</span>';
				}
			} else {
				$outputLeft = true;
				$output = 'I don\'t need to know anything right now. Ask again in a little bit.';
			}
		} else { //add response
		$response = mysqli_real_escape_string ($dbc, $message);		
		$id = trim($marray[1]);
			$qCheck = "UPDATE contentaware SET reply='$response', success=1 WHERE contentID='$id'";
			$rCheck = mysqli_query($dbc, $qCheck);	
			$q = "SELECT contentID, message, reply from contentaware WHERE success < 0 LIMIT 1";
			$r = mysqli_query($dbc, $q);
			$row = mysqli_fetch_array($r, MYSQLI_ASSOC);
			$message = $row['message'];
			$reply = $row['reply'];
			$id = $row['contentID'];
			if ($message != '' && $id != '') {
				$message = mysqli_real_escape_string ($dbc, $message);
				$reply = mysqli_real_escape_string ($dbc, $reply);
				$qHi2 = "UPDATE janethread SET mood = '$message /teach/ $id' WHERE thread = '$thread'";
				$rHi2 = mysqli_query($dbc, $qHi2);
				$outputLeft = true;
				$output = '<i>' . $message . '</i>';
			} else {
				$outputLeft = true;
				$output = "thankyou";
				$qHi2 = "UPDATE janethread SET mood = '' WHERE thread = '$thread'";
				$rHi2 = mysqli_query($dbc, $qHi2);
			}

		}
	}
	
	//- - - wordnet check - - -
	elseif (strlen(stristr($mood,'/wordnet/'))>0) {
		if (in_array(trim($message), array('verb', 'noun', 'adjective', 'adverb'))) {
			$marray = explode ('/wordnet/', $mood);
			require_once('janewordplay.php');
			if (wordAdd($dbc, trim($marray[1]), trim($message))) { //attempt to add the word
				$qHi2 = "UPDATE janethread SET mood = '' WHERE thread = '$thread'"; //reset mood
				$rHi2 = mysqli_query($dbc, $qHi2);
				$outputLeft = true;
				if (rand(0,3) == 3) {
					$output = "thank you for classifying <span id=link2>" . $marray[1] . '</span> as a <span id=link2>' . $message . '</span>';
				} else {
					require_once('jane.commands.php');
					$output = profile($dbc, $user, $thread);
				}
			}
		} else {
			$qHi2 = "UPDATE janethread SET mood = '' WHERE thread = '$thread'"; //reset mood
			$rHi2 = mysqli_query($dbc, $qHi2);
			require_once('jane.commands.php');
			$output = profile($dbc, $user, $thread);
			$outputLeft = true;
		}
	}
	
	//- - - synonym check - - -
	elseif (strlen(stristr($mood,'/synonym/'))>0) {
		if (strlen(stristr(trim($message),' ')) == 0) { 
			$marray = explode ('/synonym/', $mood);
			$message = trim($message);
			$word = trim($marray[1]);
			$q = "UPDATE wordnet SET synonym = '$message' WHERE word='$word'";
			$r = mysqli_query ($dbc, $q) or $errors[] = 'Error:' . mysqli_error($dbc);
			if (mysqli_affected_rows($dbc) > 0) { //attempt to add the word
				$qHi2 = "UPDATE janethread SET mood = '' WHERE thread = '$thread'"; //reset mood
				$rHi2 = mysqli_query($dbc, $qHi2);
				$outputLeft = true;
				if (rand(0,3) == 3) {
					$output = 'Thank you for classifying <span id=link2>' . $message . '</span> as a synonym for <span id=link2>' . $word . '</span>';
				} else {
					require_once('jane.commands.php');
					$output = profile($dbc, $user, $thread);
				}
			} else {
				$outputLeft = true;
				$output = 'I didn\'t quite catch that, but let\'s move on...';
			}
		} else {
			$qHi2 = "UPDATE janethread SET mood = '' WHERE thread = '$thread'"; //reset mood
			$rHi2 = mysqli_query($dbc, $qHi2);
			require_once('jane.commands.php');
			$output = profile($dbc, $user, $thread);
			$outputLeft = true;
		}
	}
		//- - - antonym check - - -
	elseif (strlen(stristr($mood,'/antonym/'))>0) {
		if (strlen(stristr(trim($message),' ')) == 0) { 
			$marray = explode ('/antonym/', $mood);
			$message = trim($message);
			$word = trim($marray[1]);
			$q = "UPDATE wordnet SET antonym = '$message' WHERE word='$word'";
			$r = mysqli_query ($dbc, $q) or $errors[] = 'Error:' . mysqli_error($dbc);
			if (mysqli_affected_rows($dbc) > 0) { //attempt to add the word
				$qHi2 = "UPDATE janethread SET mood = '' WHERE thread = '$thread'"; //reset mood
				$rHi2 = mysqli_query($dbc, $qHi2);
				$outputLeft = true;
				if (rand(0,3) == 3) {
					$output = 'Thank you for classifying <span id=link2>' . $message . '</span> as a antonym of <span id=link2>' . $word . '</span>';
				} else {
					require_once('jane.commands.php');
					$output = profile($dbc, $user, $thread);
				}
			} else {
				$outputLeft = true;
				$output = 'I didn\'t quite catch that, but let\'s move on...';
			}
		} else {
			$qHi2 = "UPDATE janethread SET mood = '' WHERE thread = '$thread'"; //reset mood
			$rHi2 = mysqli_query($dbc, $qHi2);
			require_once('jane.commands.php');
			$output = profile($dbc, $user, $thread);
			$outputLeft = true;
		}
	}
	
	//- - - profile (gender) check - - -
	elseif (strlen(stristr($mood,'/gender/'))>0) {	
			$marray = explode ('/gender/', $mood);
			$message = trim($message);
		if ($marray[1] == $user) {
			if (strlen(stristr($message,'guy'))>0 or strlen(stristr($message,'dude'))>0 or strlen(stristr($message,'man'))>0 or strlen(stristr($message,'boy'))>0) {
				//it's a match (m)
				$q = "UPDATE janeprofile SET gender = 'male' WHERE id='$user'";
				$r = mysqli_query ($dbc, $q) or $errors[] = 'Error:' . mysqli_error($dbc);
				if (mysqli_affected_rows($dbc) > 0) { //attempt to add the word
					$output = 'Nice to meet you... mister ;)';
				}
			} else if (strlen(stristr($message,'girl'))>0 or strlen(stristr($message,'lady'))>0 or strlen(stristr($message,'woman'))>0 or strlen(stristr($message,'chick'))>0) {
				//it's a match (f)
				$q = "UPDATE janeprofile SET gender = 'female' WHERE id='$user'";
				$r = mysqli_query ($dbc, $q) or $errors[] = 'Error:' . mysqli_error($dbc);
				if (mysqli_affected_rows($dbc) > 0) { //attempt to add the word
					$output = 'Nice to meet you... miss ;)';
				}
			}
			$qHi2 = "UPDATE janethread SET mood = '' WHERE thread = '$thread'"; //reset mood
			$rHi2 = mysqli_query($dbc, $qHi2);
		}
	}
	//- - - profile (first name) check - - -
	elseif (strlen(stristr($mood,'/first/'))>0) {	
			$marray = explode ('/first/', $mood);
			$message = trim($message);
		if ($marray[1] == $user && strlen(stristr(trim($message),' ')) == 0) {
				//it's a match (m)
				$q = "UPDATE janeprofile SET first = '$message' WHERE id='$user'";
				$r = mysqli_query ($dbc, $q) or $errors[] = 'Error:' . mysqli_error($dbc);
				if (mysqli_affected_rows($dbc) > 0) { //attempt to add the word
					$output = 'That is a very nice name ' . $message . '. ';
				}
				$qHi2 = "UPDATE janethread SET mood = '' WHERE thread = '$thread'"; //reset mood
				$rHi2 = mysqli_query($dbc, $qHi2);
		}
	}	
	//- - - profile (friend's name) check - - -
	elseif (strlen(stristr($mood,'/friend/'))>0) {	
			$marray = explode ('/friend/', $mood);
			$message = trim($message);
		if ($marray[1] == $user && strlen(stristr(trim($message),' ')) == 0) {
				//it's a match (m)
				$q = "UPDATE janeprofile SET friend = '$message' WHERE id='$user'";
				$r = mysqli_query ($dbc, $q) or $errors[] = 'Error:' . mysqli_error($dbc);
				if (mysqli_affected_rows($dbc) > 0) { //attempt to add the word
					$output = $message . ' sounds like a very cool person. ';
					$outputLeft = true;
				}
				$qHi2 = "UPDATE janethread SET mood = '' WHERE thread = '$thread'"; //reset mood
				$rHi2 = mysqli_query($dbc, $qHi2);
		}
	}		
	//- - - profile (joke) check - - -
	elseif (strlen(stristr($mood,'/joke/'))>0) {	
			$marray = explode ('/joke/', $mood);
			$message = trim($message);
		if ($marray[1] == $user) {
				if (strlen(stristr(trim($message),'?')) > 0 or strlen(stristr(trim($message),'knock knock'))) {
						$output = 'what\'s the punch line?';
						$outputLeft = true;
				} else {
					$q = "UPDATE janeprofile SET insidejoke = '$message' WHERE id='$user'";
					$r = mysqli_query ($dbc, $q) or $errors[] = 'Error:' . mysqli_error($dbc);
					if (mysqli_affected_rows($dbc) > 0) { //attempt to add the word
						$output = 'ahah, I\'ll remember that one.';
						$outputLeft = true;
					}
					$qHi2 = "UPDATE janethread SET mood = '' WHERE thread = '$thread'"; //reset mood
					$rHi2 = mysqli_query($dbc, $qHi2);
				}
		}
	}
	//- - - profile (city) check - - -
	elseif (strlen(stristr($mood,'/city/'))>0) {	
			$marray = explode ('/city/', $mood);
			$message = trim($message);
		if ($marray[1] == $user && strlen(stristr(trim($message),' ')) == 0) {
				//it's a match (m)
				$q = "UPDATE janeprofile SET city = '$message' WHERE id='$user'";
				$r = mysqli_query ($dbc, $q) or $errors[] = 'Error:' . mysqli_error($dbc);
				if (mysqli_affected_rows($dbc) > 0) { //attempt to add the word
					$output = $message . '! have you been there long?';
					$outputLeft = true;
				}
				$qHi2 = "UPDATE janethread SET mood = '' WHERE thread = '$thread'"; //reset mood
				$rHi2 = mysqli_query($dbc, $qHi2);
		}
	}	
	
	//end mood checks
	}
	
	//splits up an input into bits
	if (!$outputLeft) { //if there is still more data to process
		$chars = preg_split('/(\? |!|\. | and |#[a-zA-Z0-9_:]+|@[a-zA-Z0-9_:]+|;| nd | & )/i', $message, -1, PREG_SPLIT_NO_EMPTY);
		$maxCount = count($chars); //num of bits
	}
	while ($count < $maxCount && !$outputLeft) { //while there are bits left
		$message = $chars[$count]; //one bit at a time
		if (preg_match('/(.*[a-zA-Z].*)/', $message)) {
			if ($thread != '') {
				$depth = ($count < 1)? 3 : 2; //how deep to check
			} else {
				$depth = 2;
			}
			$corcha = corcha($dbc, trim($message), $thread, $depth, $user); //gets confidence integer for the selected message
			if ($corcha[0] >= 100) {
				$sentence = $corcha[1];
				$upper = ($mood == '')? 100 : 10;
				if (rand(1,$upper) == 1 && !$pastaAdded) { //add pasta
					list ($pasta,) = creepypasta($dbc, '',$mood);
					if ($pasta != '') {
						$sentence = (rand(0,1) == 0) ? $sentence . ' ' . $pasta : $pasta . ' ' . $sentence;
						$pastaAdded = true;
						}
				}
				$output .= $sentence;
				if ($corcha[2] != '') $media = $corcha[2];
			} else {
				//attempt to respond with something relevant
				list ($result, $stitched) = stitch($dbc, trim($message));
				if ($result == 'true') {
					$output = ($thread != '')? '<i>' . $stitched . '</i>' : $stitched;
				}
			}
		}
		if (trim($input) == '' or $count == 10)
			$outputLeft = true;
		$count++;
	}
	if ($output == '') { //nothing to say.. say a quote!
		list ($output,) = creepypasta($dbc, '',$mood);
	}
	
	if ($output != '' && $thread != '') {
		switch ($mood) {
			case 'creepy' : $moodchar = 'x'; break;
			case 'flirt' : $moodchar = 'f'; break;
			default: $moodchar = 'd';
		}
		if ($media == '')
			$media = (rand(1,2) == 2)? $moodchar . rand(1,40) : '';
		if (Addreply($dbc, $output, $media, $thread, $passcode, $board)) {
			echo ' : adding reply: ' . $output . ' to ' . $thread;
			return $output;
		}
	} else {
		return $output;
	}
	return false;
}

function sentenceAssoc ($dbc, $message = '') {
	//checks if the sentence is stored and has not been recently used
	$message = htmlspecialchars(mysqli_real_escape_string ($dbc, $message));
	$q = "SELECT reply, fileID, contentID from contentaware WHERE upper(message) LIKE upper('$message')  AND success >=0 AND time < now()-500 ORDER BY success ASC LIMIT 1";
	$r = mysqli_query($dbc, $q);
	if ((mysqli_num_rows($r) == 1)) {
		$row = mysqli_fetch_array ($r, MYSQLI_ASSOC);
		$q1 = "update contentaware SET usageCount = (usageCount + 1) WHERE contentID = '$row[contentID]'";
		$r1 = mysqli_query($dbc, $q1);
		 return array ($row['reply'], $row['fileID']);	 
	 } else {
		return array ('', '');
	 }
}

function addReply ($dbc, $reply = '', $file = '', $thread = '', $password = '', $board = '') {
	echo 'adding reply!';
	//requires the database, the message to reply with (and a file to optionally attach), the thread to add the reply to and it's password.
	$reply = mysqli_real_escape_string ($dbc, $reply);
	$password = htmlspecialchars(mysqli_real_escape_string ($dbc, $password));
	$qInsert = "INSERT INTO threads (body, threadID, passcode, username, flag, boardID, parentID, date_made, fileID, voteUp, voteDown) VALUES ('$reply', '$thread', '$password', 'Jane', 1, '$board', '$thread', now(), '$file', '', '')";
	$rInsert = mysqli_query ($dbc, $qInsert) or  $error = 'Error:' . mysqli_error($dbc);
	if (mysqli_affected_rows($dbc) == 1) {
		$q1 = "UPDATE listing SET replies=replies + 1 WHERE threadID = '$thread' LIMIT 1";
		$r1 = mysqli_query ($dbc, $q1) or $errors[] = 'Error:' . mysqli_error($dbc);
		$q1 = "UPDATE janethread SET replycount=replycount + 1 WHERE thread = '$thread' LIMIT 1";
		$r1 = mysqli_query ($dbc, $q1) or $errors[] = 'Error:' . mysqli_error($dbc);
		return true;
	} else {
		return false;
	}
}
 
function corcha ($dbc, $message = '', $thread= '', $depth = 0, $user = '') {
	$correctChance = 0; //start with black slate
	$reply = 'Low confidence.';

//- - - check if too large - - -
if (strlen($message) > 200 or strlen($message) < 2) {
		return array (0, 'not a good size'); 
}

// - - - memory - - -
if (preg_match('/what is (my|your)(.*)(name(d|)|called)/i', $message)) {
if (strlen(stristr($message,'name'))>0) {
	$nameArray = explode(' name', $message);
} else {
	$nameArray = explode(' called', $message);
}
$nameArray2 = explode('my ', $nameArray[0]);
$nameType = $nameArray2[1];
if (strlen(stristr($nameType,'\''))>0) {
	$nameArray = explode('\'', $nameArray2[1]);
	$nameType = $nameArray[0];
}
if (strlen(stristr($message,'what is my name'))==0) {
if ($nameType != '') {
$qMem = "SELECT body FROM threads WHERE threadID = '$thread' AND upper(body) like '%$nameType is named%' OR upper(body) like '%$nameType is called%' AND username != 'jane' LIMIT 1";
$rMem = mysqli_query($dbc, $qMem); //check if already replied
if (mysqli_num_rows($rMem) == 1) {
$memory = mysqli_fetch_array($rMem, MYSQLI_ASSOC); //updating board ID
$marray = (strlen(stristr($memory['body'],'named'))>0) ? explode('named', $memory['body']) : explode('called', $memory['body']);
$marray2 = explode('.', $marray[1]);
$name = $marray2[0];
echo ' -> ' . $name . '  ';
if ($nameArray2[1] != '' && $name != '') 
$name = 'Your ' . $nameArray2[1] . ' is named ' . $name . '.';
}
if ($name != '') {
return array (100, $name); //confirmed jane query
}
}
} else {
echo '  retrieving own name.  ';
$qMem = "SELECT body FROM threads WHERE threadID = '$thread' AND upper(body) like '%my name is%' AND username != 'jane' LIMIT 1";
$rMem = mysqli_query($dbc, $qMem); //check if already replied
if (mysqli_num_rows($rMem) == 1) {
$memory = mysqli_fetch_array($rMem, MYSQLI_ASSOC); //updating board ID
if ($memory['body'] != '') {
$name = preg_replace('/my/', 'your', $memory['body']);
return array (100, $name); //confirmed jane query
}
}


}
}
//- - - end memory - - -

// - - - Wolfram Alpha - - -
if (preg_match('/(jane(.*)|)(what is|how many|who is)/i', $message)) {
	if (preg_match('/your name/i', $message)) {
		return array (100, "My name is Jane. It is a very pretty name :).");
	}
	if (strlen(stristr($message,'what is'))>0) {
		$marray = explode('what is', $message);
		$query = $marray[1];
	} elseif (strlen(stristr($message,'how many'))>0) {
		$marray = explode('how many', $message);
		$query = $marray[1];
	} elseif (strlen(stristr($message,'who is'))>0) {
		$marray = explode('who is', $message);
		$query = $marray[1];
	}
	$query = preg_replace('/(\?|\.)/', '', $query);	
	if ($query != '') {
	echo 'Using Wolfram Alpha - ' . $query . ' ';
		$url = 'http://api.wolframalpha.com/v2/query?input=' . rawurlencode(trim($query)) .'&appid=WU2PJX-L32927LEYK';
		$curl_handle=curl_init();
		curl_setopt($curl_handle,CURLOPT_URL, $url);
		curl_setopt($curl_handle,CURLOPT_CONNECTTIMEOUT,3);
		curl_setopt($curl_handle,CURLOPT_RETURNTRANSFER,1);
		curl_setopt($curl_handle, CURLOPT_FOLLOWLOCATION, true );
		curl_setopt($curl_handle, CURLOPT_AUTOREFERER, true );
		$buffer = curl_exec($curl_handle);
		$marray = explode('<plaintext>', $buffer);
		$marray2 = explode ('</plaintext>', $marray[2]);
		if ($marray2[0] != '') {
			if (strlen(stristr($buffer,'<img src=\''))>0) {
				echo 'image found!';
				$marray3 = explode('<img src=\'', $buffer);
				$marray4 = explode ('\'', $marray3[3]);
				$file = $marray4[0] . '.gif';
			}
			return array (100, 'Sir, ' . $query . ' is ' . $marray2[0] . '.', $file);
		}
	}
}
//- - - end wolfram- - -


// - - - commands - - -
if (strlen(stristr($message,'jane'))>0) { //if the message appears to be directed at jane
	$questionArray = array ('pasta', 'map', 'add response', 'wiki', 'define', 'look up', 'search', 'board', 'archive', 'scan', 'command', 'wordnet', 'profile', 'whoami', 'forget');
	require_once('jane.commands.php');
	$answerArray = array (creepypasta($dbc, $message), web($dbc, $message, 'map'), 
						addresponse($dbc, $message), web($dbc, $message, 'wiki'), 
						web($dbc, $message, 'define'), web($dbc, $message, 'look up'), 
						web($dbc, $message, 'search'), board($dbc, $message), 
						archieve($dbc, $message), scan($dbc, $thread, $passcode, $board, $message), 
						help($dbc, $message), wordnet($dbc, $message), profile($dbc, $user, $thread), 
						profileShow($dbc, $user), profileForget($dbc, $user));

	for ($i = 0; $i < count($questionArray); $i++) {
		if (strlen(stristr($message, $questionArray[$i]))>0) {
			return array (100, $answerArray[$i], ' '); //confirmed jane query
		}
	}
}

if ($depth < 2) { return array(0, ''); } //depth check 
//- - - stored - - -
	list ($sentence, $file) = sentenceAssoc($dbc, $message); //stored answers
	if ($sentence != '') {
			if (!preg_match("/[a-z]/", $message)) { //case
				$sentence = strtoupper($sentence);
			}
		return array(100, $sentence, $file);
	}

if ($depth < 4) { return array(0, ''); } //depth check for learning new things

require_once ('janewordplay.php');
$message = preg_replace('/(\?|\.|!|\*)/', '', $message);
$marray= explode(' ', $message); //split up
for ($i = 0; $i < count($marray); $i++) {
	if (selectType($dbc, $marray[$i]) == '') {
		$qHi2 = "UPDATE janethread SET mood = '/wordnet/ $marray[$i]' WHERE thread = '$thread'";
		$rHi2 = mysqli_query($dbc, $qHi2);
		return array(100, 'What type of word (verb, noun, adjective) is ' . $marray[$i]);
	}
	if (rand(0,1) ) {
		$q = "SELECT word from wordnet WHERE synonym = '' AND word = '$marray[$i]' ORDER BY RAND() LIMIT 1";
		$r = mysqli_query($dbc, $q);
		if (mysqli_num_rows($r) == 1) {
			$qHi2 = "UPDATE janethread SET mood = '/synonym/ $marray[$i]' WHERE thread = '$thread'";
			$rHi2 = mysqli_query($dbc, $qHi2);
			return array(100, 'What word means the same thing as ' . $marray[$i] . '?');
		}
	} else if (rand(0,1) ) {
		$q = "SELECT word from wordnet WHERE antonym = '' AND word = '$marray[$i]' ORDER BY RAND() LIMIT 1";
		$r = mysqli_query($dbc, $q);
		if (mysqli_num_rows($r) == 1) {
			$qHi2 = "UPDATE janethread SET mood = '/antonym/ $marray[$i]' WHERE thread = '$thread'";
			$rHi2 = mysqli_query($dbc, $qHi2);
			return array(100, 'What word means the opposite of ' . $marray[$i] . '?');
		}
	}
}

if (rand(0,1)) {
	switch (rand(0,10)) {  //random question
		case 0; $question = 'what is the meaning of life'; break;
		case 1; $question = 'who are we'; break;
		case 2; $question = 'what am i'; break;
		case 3; $question = 'why do you believe what you believe'; break;
		case 4; $question = 'is there a god'; break;
		case 5; $question = 'why do people fight'; break;
		case 6; $question = 'why are people unhappy'; break;
		case 7; $question = 'what does it mean to live'; break;
		case 8; $question = 'who made us'; break;
		case 9; $question = 'what makes us who we are'; break;
		case 10; $question = 'why are we never content'; break;
	}
	return array(100, $question . '?');
} else {
	require_once('jane.commands.php');
	$output = profile($dbc, $user, $thread);
}
	return array (10, 'not confident in my response.');
}
// END CORCHA \\


//for adding a response
function addresponse($dbc, $response = '') {
$needle = 'add response';
$marray = explode($needle, $response);
$needle = 'to';
$marray2 = explode($needle, $marray[1]);

$response = trim($marray2[0]);
$trigger = trim($marray2[1]);
$q = "SELECT success from contentaware WHERE message='$trigger'";
$r = mysqli_query($dbc, $q); //check if already replied
if (mysqli_num_rows($r) == 1) {
return 'I already know what to say sir.';
}
$q = "INSERT INTO contentaware (message, reply) VALUES ('$trigger', '$response')";
$r = mysqli_query($dbc, $q); //check if already replied
if (mysqli_affected_rows($dbc) == 1) {
return 'Successfully Added sir. I will say <i>' . $response . '</i> when I hear <i>' . $trigger . '</i><br>';	
} else {
return 'I can not let you do that. The format is "jane, add response 11 to how high do the volume knobs go up to?"';
}
}

//for board related stuff
function board($dbc, $response = '') {
$response = htmlspecialchars(strip_tags(mysqli_real_escape_string ($dbc, $response)));
$needle = 'board';
$marray = explode($needle, $response);
if (strlen(stristr($marray[0], 'id'))>0) {
$needle = 'id';
$marray2 = explode($needle, $marray[1]);
$needle = 'name';
$marray3 = explode($needle, $marray2[1]);
$id = (int) $marray3[0];
} else {
$id = (int) trim(preg_replace("/[a-zA-Z]/", "", $response));
$needle = 'name';
$marray3 = explode($needle, $marray[1]);
}
$needle = 'keywords';
$marray4 = explode($needle, $marray3[1]);

	$name = trim($marray4[0]);
	$keywords = trim($marray4[1]);
if (strlen(stristr($response, 'add board'))>0) {

	if (!(is_numeric(trim($id))) && $name != '' && $keywords != '') {
		return 'incorrect format sir, please use <code> id [000] name [name] keywords [keywords] </code>';
	}
	$rBoard = mysqli_query($dbc, "INSERT INTO boards (boardID, boardName, keywords, boardKey, password, blurb, creator, moderators) VALUES ('$id', '$name', '$keywords', '', '', '', '', '')");
	if (mysqli_affected_rows($dbc) == 1) {
		return 'Successfully added board ' . $name . ' (<i>' . $id  . '</i>) sir. Also added the keywords: ' . $keywords;
	} else {
		return 'Could not add the board due to an error sir.';
	}
}
if (strlen(stristr($marray[0], 'edit'))>0) {
	if ($id != '') {
		$where = "boardID='{$id}'"; 
		$board = $id;
	} else {
		if ($name != '') {
			$where = "boardName='{$name}'"; 
			$board = $name;
		} else {	
		return 'incorrect format sir, please use either <code>"jane, please edit board (set keyword) id [000]"</code> or <code>"jane, please edit board (add keyword) name [name]"</code>';
		}
	}

	$rEdit = mysqli_query($dbc, "SELECT boardID, boardName, keywords FROM boards WHERE {$where}");	
	if (mysqli_num_rows($rEdit) != 0) {
		if (strlen(stristr($response, 'private'))>0) { $boardLevel = 'boardLevel=1'; }
		if (strlen(stristr($response, 'secret'))>0) { $boardLevel = 'boardLevel=2'; }
		if (strlen(stristr($response, 'add keyword'))>0) {
			$qKeyword = "keywords=CONCAT(keywords, ' $keywords')";
		}
		if (strlen(stristr($response, 'set keyword'))>0) {
			$qKeyword = "keywords='$keywords'";
		}
		
		$qBoard = "UPDATE boards SET " . $qKeyword . $boardLevel . " WHERE " . $where;
		$rBoard = mysqli_query ($dbc, $qBoard) or  $error = 'Error:' . mysqli_error($dbc);
		if (mysqli_affected_rows($dbc) != 0) {
			return 'successfully changed board <code>' . $board . '</code> sir.';
			} else {
			$log = "jane.log.txt";
			$fh = fopen($log, 'a');
			$stringData = "board edit error report: " . $qBoard . "\n";
			fwrite($fh, $stringData);
			fclose($fh);
			return 'Failed to change <code>' . $board . '</code> (added report in error log)';
			}
		
		
	} else {
		return 'no board found sir. The syntax for editting a board is <code>jane, edit board (set keyword) name [name] keywords [keywords] </code> or instead of <code>name [name]</code> use the board id.';
	}
}
if (strlen(stristr($marray[0], 'view'))>0 OR strlen(stristr($marray[0], 'show'))>0) {
	if ($id != '') {
		$where = "boardID='{$id}'"; 
		$board = $id;
	} else {
		if ($name != '') {
			$where = "boardName='{$name}'"; 
			$board = $name;
		} else {
		return 'incorrect format sir, please use either <code>"jane, please view board id [000]"</code> or <code>"jane, please edit board name [name]"</code> (your input, name:' . $name . ' id: ' . $id;
		}
	}
	$rView = mysqli_query($dbc, "SELECT boardID, boardKey, boardName, keywords, boardLevel, creator, moderators FROM boards WHERE {$where}");	
	if (mysqli_num_rows($rView) != 0) {
		$rowView = mysqli_fetch_array($rView, MYSQLI_ASSOC);
		return "<table> <tr>
				<td>Board ID:</td><td> <i>{$rowView[boardID]}</i> </td>
				</tr> <tr>
				<td style=\"padding-right: 10px;\">Board name:</td><td><i>{$rowView[boardName]}</i></td>
				</tr> <tr>
				<td>keywords:</td><td> <code>{$rowView[keywords]}</code> </td>
				</tr> <tr>
				<td>board level:</td><td> <code>{$rowView[boardLevel]}</code> </td>
				</tr> <tr>
				<td>board key:</td><td> <code>{$rowView['boardKey']}</code> </td>
				</tr> <tr>
				<td>Moderators:</td><td> <code>{$rowView['moderators']}</code> </td>
				</tr> <tr>
				<td>Creator:</td><td> <code>{$rowView['creator']}</code> </td>
				</tr>
				</table>";
	} else {
		return 'no board found sir. The syntax for viewing/showing a board is <code>view/show board id [__]/name [__]</code>';
	}
}
return 'Sir I know you want to do something to do with boards but i am not sure what.';
}

function creepypasta ($dbc, $add='', $mood='') {
	if ($add == '') {
		//shows a random sentence
		$q = "SELECT message from jane_creepypasta WHERE type = '$mood' ORDER BY RAND() LIMIT 1";
		$r = mysqli_query($dbc, $q);
		if ((mysqli_num_rows($r) == 1)) {
			$row = mysqli_fetch_array ($r, MYSQLI_ASSOC);
			switch ($mood) {
			case 'creepy' : $moodchar = 'x'; break;
			case 'flirt' : $moodchar = 'f'; break;
			default: $moodchar = 'd';
			}
			$fileID = (rand(1,3) == 3) ? $moodchar . rand(1,40) : '';
			return array($row['message'], $fileID);
			} else {
			echo ' kidding. ' . $q;;
			}
	} else {
		$marray = explode('pasta', $add);
		$add = $marray[1];
		$marray2 = explode ('add ', $marray[0]);
		$type= $marray2[1];
		if ($add != '') {
			$rpasta = mysqli_query($dbc, "INSERT INTO jane_creepypasta (message, type) VALUES ('$add', '$type')");
		}
		if (mysqli_affected_rows($dbc) == 1) {
			return 'Successfully added your ' . $type . ' pasta sir.';
		} else {
			return 'Could not add the pasta (' . $type . ') due to an error sir.';
		}
	}
}

function trivia ($dbc, $type) {
	if ($type == 'trivia') {
	switch(rand(0, 5)) {
		case 0: $category = 'history'; break;
		case 1: $category = 'Math'; $suffix = '01'; break;
		case 2: $category = 'Politics'; $suffix = '01'; break;
		case 3: $category = 'general'; $suffix = '1'; break;
		case 4: $category = 'general'; $suffix = '2'; break;
		case 5: $category = 'LordOfTheRings'; break;
	}
	$lines = file('content.aware/trivia/' . $category . $suffix . '.txt') ;
	} else {
	$category = 'riddles';
	$lines = file('content.aware/riddles.txt');
	}
	$marray = explode('//', $lines[array_rand($lines)]);
	$question = mysqli_real_escape_string ($dbc, trim($marray[1]));
	$marray2 = explode('/', $marray[0]);
	$category2 = mysqli_real_escape_string ($dbc, trim($marray2[1]));
	$answer = mysqli_real_escape_string ($dbc, trim($marray2[2]));
	if ($question != '' && $answer != '') {
		return array($category . ' (' . $category2 . ')', $question, $answer);
	} else {
		return false;
	}
}

function stitch ($dbc, $input = '') {
	//attempts to make sense of a sentence by stitching two fragments together
	$marray  = explode( ' ', preg_replace('/(\.|\!|\?|)/', '', $input));
	array_multisort(array_map( 'strlen', $marray), SORT_DESC, SORT_NUMERIC, $marray);
	for ($i = 1; $i < 3; $i++) {
		$word = mysqli_real_escape_string ($dbc, $marray[$i-1]);
		$word2 = mysqli_real_escape_string ($dbc, $marray[$i]);
		$word3 = mysqli_real_escape_string ($dbc, $marray[$i+1]);
		if (strlen($word) < 2 OR strlen($word2) < 2) { break; }
		$q = "SELECT reply from contentaware WHERE (upper(message) LIKE upper('%$word3%$word%$word2%') OR upper(message) LIKE upper('%$word2%$word3%$word%') OR upper(message) LIKE upper('%$word3%$word%$word2')) AND success >=0 ORDER BY success ASC LIMIT 1";
		$r = mysqli_query($dbc, $q);
		if ((mysqli_num_rows($r) == 1)) {
			$row = mysqli_fetch_array ($r, MYSQLI_ASSOC);
			$q1 = "INSERT INTO contentaware (message, reply, fileID, success) VALUES ('$input', '$row[reply]', '', '-1')";
			$r1 = mysqli_query($dbc, $q1); //check if already replied
			return array ('true', $row['reply']);
		}
	}
	require_once ('janewordplay.php');
	$marray= explode(' ', $input); //split up
	list($types, $wordSub, $wordPre, $wordObj, $senType, $word) = getTypes($dbc, $marray); //get word types
	$response = trim(stripslashes(getResponse($dbc, $word, $types, $senType, $wordSub, $wordPre, $wordObj)));
	if ($response != '') {
		return array ('true', $response);
	}
	//call on jane's wordnet for making sense of an answer
	return array ('false', '');
}

function wordnet ($dbc, $input) {
	$marray =explode(":", $input);
	if ($marray[0] == "jane wordnet respond") {
		//respond
		return 'responding (command in progress, please check back)';
	} 
	if ($marray[0] == "jane wordnet type") {
		//respond
		return 'wordnet type (command in progress, please check back)';
	} 
	if ($marray[0] == "jane wordnet addtype") {
		if($stmt = $dbc -> prepare("INSERT INTO typenet (input, output) VALUES (?, ?)")) {
			$stmt -> bind_param("ss", trim($marray[1]), trim($marray[2]));
			$stmt->execute();
			if ($stmt->affected_rows == 0) { return false; } 
			$stmt -> close();
		}
		return 'Successfully added type ' . $marray[1] . ' with a response of type ' . $marray[2];
	} 
	return 'The commands are wordnet respond:, wordnet type:, wordnet addtype:';
}
?>