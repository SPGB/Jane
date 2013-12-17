<?php

//selects a rhyme
function selectRhyme ($dbc, $word){ //gets the word that jane associates with coming after
	$word = mysqli_real_escape_string ($dbc, $word);
	if ($word == '') return false;
	$q = "SELECT rhyme FROM wordnet WHERE word='$word'";
	$r = mysqli_query ($dbc, $q) or $errors[] = 'Error:' . mysqli_error($dbc);
	if (mysqli_num_rows($r) == 0) 
		return false;
	$row = mysqli_fetch_array ($r, MYSQLI_ASSOC);
	return $row['rhyme'];
}

//selects synonym
function selectSyn ($dbc, $word){
	$word = mysqli_real_escape_string ($dbc, $word);
	if ($word == '') return false;
	$q = "SELECT synonym FROM wordnet WHERE word='$word'";
	$r = mysqli_query ($dbc, $q) or $errors[] = 'Error:' . mysqli_error($dbc);
	if (mysqli_num_rows($r) == 0) 
		return false;
	$row = mysqli_fetch_array ($r, MYSQLI_ASSOC);
	return $row['synonym'];
}

//selects antonym (oppoite)
function selectAnt ($dbc, $word){
	$word = mysqli_real_escape_string ($dbc, $word);
	if ($word == '') return false;
	$q = "SELECT antonym FROM wordnet WHERE word='$word'";
	$r = mysqli_query ($dbc, $q) or $errors[] = 'Error:' . mysqli_error($dbc);
	if (mysqli_num_rows($r) == 0) 
		return false;
	$row = mysqli_fetch_array ($r, MYSQLI_ASSOC);
	return $row['antonym'];
}

//selects the word type
function selectType ($dbc, $word){
	$word = mysqli_real_escape_string ($dbc, $word);
	if ($word == '') return false;
	$q = "SELECT type FROM wordnet WHERE word='$word'";
	$r = mysqli_query ($dbc, $q) or $errors[] = 'Error:' . mysqli_error($dbc);
	if (mysqli_num_rows($r) == 0) 
		return false;
	$row = mysqli_fetch_array ($r, MYSQLI_ASSOC);
	return $row['type'];
}

//selects a quantifiers
function selectQuant ($dbc, $word){
	$word = mysqli_real_escape_string ($dbc, $word);
	if ($word == '') return false;
	$q = "SELECT quantify FROM wordnet WHERE word='$word'";
	$r = mysqli_query ($dbc, $q) or $errors[] = 'Error:' . mysqli_error($dbc);
	if (mysqli_num_rows($r) == 0) 
		return false;
	$row = mysqli_fetch_array ($r, MYSQLI_ASSOC);
	return $row['quantify'];
}

//selects super
function selectSuper ($dbc, $word){
	$word = mysqli_real_escape_string ($dbc, $word);
	if ($word == '') return false;
	$q = "SELECT super FROM janeRelations WHERE sub='$word' AND super != ''";
	$r = mysqli_query ($dbc, $q) or $errors[] = 'Error:' . mysqli_error($dbc);
	if (mysqli_num_rows($r) == 0) 
		return false;
	$row = mysqli_fetch_array ($r, MYSQLI_ASSOC);
	return $row['super'];
}

//selects super
function selectSub ($dbc, $word){
	$word = mysqli_real_escape_string ($dbc, $word);
	if ($word == '') return false;
	$q = "SELECT sub FROM janeRelations WHERE super='$word' AND sub != ''";
	$r = mysqli_query ($dbc, $q) or $errors[] = 'Error:' . mysqli_error($dbc);
	if (mysqli_num_rows($r) == 0) 
		return false;
	$row = mysqli_fetch_array ($r, MYSQLI_ASSOC);
	return $row['sub'];
}

function getTypes ($dbc, array $word) {
	for ($i = 0; $i < count($word); $i++) {
	
		//remove junk suffixs
		if (substr($word[$i], -2, 2) == '\'s') {	$word[$i] = substr($word[$i], 0, -3); } //remove -?
		elseif (substr($word[$i], -2, 2) == '\'m') {	$word[$i] = substr($word[$i], 0, -3); } //remove -'m
		elseif (substr($word[$i], -2, 2) == 'ed' && strlen($word[$i]) > 4) {	$word[$i] = substr($word[$i], 0, -2); } //remove -ed
		elseif (substr($word[$i], -3, 3) == 'ing' && strlen($word[$i]) > 7) {	$word[$i] = substr($word[$i], 0, -3); } //remove -ed
		elseif (substr($word[$i], -1, 1) == 's' && strlen($word[$i]) > 3) {	$word[$i] = substr($word[$i], 0, -1); } //remove -s
		elseif (substr($word[$i], -1, 1) == '?') {	$word[$i] = substr($word[$i], 0, -1); } //remove -?
		
		
		//get each word's type
		$types[$i]=selectType($dbc, $word[$i]);
		
		//set to noun for capitalized
		if (ctype_upper(substr($word[$i], 0, 1))) {	$types[$i] = 'noun'; } //remove -s
		
		//try to figure out some unknowns
		if ($types[$i] == '' && ($types[$i-1] == 'definite article' OR $types[$i-1] == 'indefinte article')) { $types[$i] = 'adjective'; }
		if ($types[$i] == '' && $types[$i-1] == 'adjective') { $types[$i] = 'noun'; }
		if ($types[$i] == '') { $types[$i] = 'unknown'; }
		if ($types[$i] == 'conjunction') { $senType = 'Conjuction'; }
	}
	
	//subject
	if ($types[0] == 'noun' or $types[0] == 'pronoun' or $types[0] == 'definite article' or $types[0] == 'indefinite article' or $types[0] == 'adjective') {
		$j = 0;
		while ($types[$j] == 'noun' OR $types[$j] == 'pronoun' OR $types[$j] == 'definite article'  OR $types[$j] == 'indefinite article' OR $types[$j] == 'adjective' OR ($types[$j] == 'unknown' && $types[$j-1] == 'pronoun') OR ($j == 0 && count($word) > 0)) {
			$wordSub[count($wordSub)] = $word[$j];
			if ($types[$j] == 'unknown')
				$types[$j] = 'noun';
			$j++;
		}
	}
	//predicate
	if ($j > 0) {
		while ($types[$j] == 'verb' or $types[$j] == 'adverb' or $types[$j] == 'preposition') {
			$wordPre[count($wordPre)] = $word[$j];
			$j++;
		}
	}
	//object
	if ($j < count($word)) {
		while ($j < count($word)) {
			$wordObj[count($wordObj)] = $word[$j];
			$j++;
		}
	} else {
		$object = ' (no object)';
	}

	//sentence classifications
	if(count($word) == 1) {
		$senType = 'singleton';
	}
	elseif (count($wordSub) > 0 && ($types[0] == 'noun' OR $types[1] == 'noun' OR $types[1] == 'adjective') && $senType == '') {
		$senType = 'simple';
	} elseif (substr_count($word[count($word) - 1], '?') > 0) {
		$senType = 'question';
	} elseif ($senType == '') {
		$senType = 'broken';
	}
	return array($types, $wordSub, $wordPre, $wordObj, $senType, $word);
}
/*
* RESPONSE
* main engine that generates a response
* word is an array with the sentence divided by spaces
* types is the type of each word
* senType is the sentence type (simple, singleton, broken)
* subject is the subject array
* predicate is the predicate array
* object is the object array
*/
function getResponse ($dbc, $word, $types, $senType = '', $subject = '', $predicate = '', $object = ''){
	$boolVerb = false;
	if ($senType == 'simple') {
	//TODO: move the sentence/response structure to a table
	$start = false;
	foreach ($types as $type) {
		if ($start) {
		$construc .= ',' . $type;
		} else {
			$start = true;
			$construc = $type;
		}
	}
	$q = "SELECT input, output from typenet WHERE input = '$construc' and output != ''";
	$r = mysqli_query($dbc, $q);
	if ((mysqli_num_rows($r) == 0)) {
		$q1 = "INSERT INTO typenet (input, output) VALUES ('$construc', '')";
		$r1 = mysqli_query($dbc, $q1);
		echo 'Could not find a matching structure.';
		return false;
	} 
	$row = mysqli_fetch_array ($r, MYSQLI_ASSOC);
	$outType = $row['output'];
	echo '<!-- output type: ' . $outType . ' -->';
	$responseTypes = explode (',', $outType);
				for ($j = 0; $j <= count($responseTypes)-1; $j++) { //goes through the response set
					$responseTypes[$j] = trim($responseTypes[$j]);
					if ($responseTypes[$j] != '?' && $responseTypes[$j] != '!') { //punctuate
						$response .= ' ';
					}
					if (substr_count($responseTypes[$j], 'antonym(') > 0 ) { //antonym
						$marray = explode('(', $responseTypes[$j]);
						$marray2 = explode(')', $marray[1]);
						$response .=  selectAnt($dbc, $word[$marray2[0]]);
					}
					elseif (substr_count($responseTypes[$j], 'super(') > 0 ) { //super class
						$marray = explode('(', $responseTypes[$j]);
						$marray2 = explode(')', $marray[1]);
						$response .=  selectSuper($dbc, $word[$marray2[0]]);
					}
					elseif (substr_count($responseTypes[$j], 'word(') > 0 ) { //the word
						$marray = explode('(', $responseTypes[$j]);
						$marray2 = explode(')', $marray[1]);
						$response .=  $word[$marray2[0]];
					}
					elseif (substr_count($responseTypes[$j], 'quantifier(') > 0 ) {
						$marray = explode('(', $responseTypes[$j]);
						$marray2 = explode(')', $marray[1]);
						$response .=  selectQuant($dbc, $word[$marray2[0]]);
					}
					elseif (substr_count($responseTypes[$j], 'synonym(') > 0 ) {
						$marray = explode('(', $responseTypes[$j]);
						$marray2 = explode(')', $marray[1]);
						$response .=  selectSyn($dbc, $word[$marray2[0]]);
					}
					elseif (substr_count($responseTypes[$j], 'possessive(') > 0 ) {
						$marray = explode('(', $responseTypes[$j]);
						$marray2 = explode(')', $marray[1]);
						$response .=  $word[$marray2[0]] . '\'s';
					}
					else {
						$response .= $responseTypes[$j];
					}
				}
	} elseif ($senType == 'singleton') {
		$response = selectSyn($dbc, $word[0]);
	}
	elseif ($senType == 'question') {
		$response = 'yes';
	}
	return trim($response);
}

//adds a word to the database
function wordAdd ($dbc, $word, $type, $syn = '', $ant = '', $rhyme = '', $quant = ''){
	if ($word == '') return false;
	$q = 'INSERT INTO wordnet (word, type, synonym, antonym, rhyme, quantify) VALUES ("' . trim($word) . '", "' . $type . '", "' . $syn . '", "' . $ant . '", "' . $rhyme . '", "' . $quant . '")';
	$r = mysqli_query ($dbc, $q) or $errors[] = 'Error:' . mysqli_error($dbc);
	if (mysqli_affected_rows($dbc) == 1) 
		return true;
	return false;
}

//add super
function addSuper ($dbc, $word, $super){
	if ($word == '') return false;
	$q = 'INSERT INTO janeRelations (sub, super) VALUES ("' . trim($word) . '", "' . trim($super) . '")';
	$r = mysqli_query ($dbc, $q) or $errors[] = 'Error:' . mysqli_error($dbc);
	if (mysqli_affected_rows($dbc) == 1) 
		return true;
	return false;
}

//add sub
function addSub ($dbc, $word, $sub){
	if ($word == '') return false;
	$q = 'INSERT INTO janeRelations (sub, super) VALUES ("' . trim($sub) . '", "' . trim($word) . '")';
	$r = mysqli_query ($dbc, $q) or $errors[] = 'Error:' . mysqli_error($dbc);
	if (mysqli_affected_rows($dbc) == 1) 
		return true;
	return false;
}

function getRandomVerb ($dbc) {
	$q = "SELECT word from wordnet WHERE type = 'verb' and rhyme != '' ORDER BY RAND() LIMIT 1";
	$r = mysqli_query($dbc, $q);
	if ((mysqli_num_rows($r) == 1)) {
		$row = mysqli_fetch_array ($r, MYSQLI_ASSOC);
		return $row['word'];
	}
	return false;
}

function getRandomNoun ($dbc) {
	$q = "SELECT word from wordnet WHERE type = 'noun' and rhyme != '' ORDER BY RAND() LIMIT 1";
	$r = mysqli_query($dbc, $q);
	if ((mysqli_num_rows($r) == 1)) {
		$row = mysqli_fetch_array ($r, MYSQLI_ASSOC);
		return $row['word'];
	}
	return false;
}

function getRandomAdj ($dbc) {
	$q = "SELECT word from wordnet WHERE type = 'adjective' and rhyme != '' ORDER BY RAND() LIMIT 1";
	$r = mysqli_query($dbc, $q);
	if ((mysqli_num_rows($r) == 1)) {
		$row = mysqli_fetch_array ($r, MYSQLI_ASSOC);
		return $row['word'];
	}
	return false;
}

function getRandomAdv ($dbc) {
	$q = "SELECT word from wordnet WHERE type = 'adverb' ORDER BY RAND() LIMIT 1";
	$r = mysqli_query($dbc, $q);
	if ((mysqli_num_rows($r) == 1)) {
		$row = mysqli_fetch_array ($r, MYSQLI_ASSOC);
		return $row['word'];
	}
	return false;
}

//a basic poem
function poemBasic ($dbc, $word = '') {
	if ($word == '') {
		$word = getRandomNoun($dbc);
	}
	$rhyme1 = selectRhyme($dbc, $word);
	$i = 0;
		$randomNoun = getRandomNoun($dbc);
		$randomNoun2 = getRandomNoun($dbc);
		$randomVerb = getRandomVerb($dbc);
		$randomVerb2 = getRandomVerb($dbc);
		$randomAdj = getRandomAdj($dbc);
		$randomAdv 	= getRandomAdv($dbc);
		$randomAdj2 = getRandomAdj($dbc);
		$poem .= '
		' . $randomNoun . ' ' . $randomVerb2 . ', a ' . $randomAdv . 'ing ' . $word. '<br /><br />the '
		.  selectRhyme($dbc, $randomVerb) . ' ' . $randomAdj .  ' ' . selectRhyme($dbc, $randomNoun) . '<br /><br />'
		. $randomNoun2 . ' ' . $randomVerb . ' a ' .   $randomAdj2 . ' ' . selectRhyme($dbc, $word) . '<br /><br />';
		$i++;
	return $poem;
}

//extracts data from a sentence
function readTo ($dbc, $sentence) {
$marray= explode(' ', htmlspecialchars($sentence)); //split into individual sentences
if (count($marray) == 3) { //basic sentence 1
	echo '<hr>';
	for ($i = 0; $i < 3; $i++) {
		$type = selectType($dbc, $marray[$i]);
		if ($type != '') {
			echo stripslashes($marray[$i]) . ' is a ' . $type . '<br />';
		} else {	
			switch($i) {
			case 0: $type = 'noun'; break; //sub
			case 1: $type = 'verb'; break; //pre
			case 2: $type = 'noun'; break; //obj
			}
			echo stripslashes($marray[$i]) . ' is a ' . $type . '<br />';
			if (wordAdd ($dbc, $marray[$i], $type)) {
				echo '<span style="float: right;">added</span>';
			}
		}
	}
	return true;
}
elseif (count($marray) == 4) { //basic sentence 2
	echo '<hr>';
	for ($i = 0; $i < 4; $i++) {
	$type = selectType($dbc, $marray[$i]);
	if ($type != '') {
		echo stripslashes($marray[$i]) . ' is a ' . $type . '<br />';
	} else {
		switch($i) {
		case 0: $type = 'pronoun'; break;
		case 1: $type = 'noun'; break;
		case 2: $type = 'verb'; break;
		case 3: $type = 'noun'; break;
		}
		echo stripslashes($marray[$i]) . ' is a ' . $type . '<br />';
		if (wordAdd ($dbc, $marray[$i], $type)) {
			echo '<span style="float: right;">added</span>';
		}
	}
	}
	return true;
}
elseif (in_array(selectType($dbc, $marray[ceil(count($marray)/2)-1]), array('verb', 'adverb')) && count($marray) < 10) {
	for ($i = 0; $i < count($marray); $i++) { //for every word in the sentence
		if ($marray[$i] != '') {
			if ($i < floor(count($marray)/2)) { //everything before the verb (subject)
				$type = selectType($dbc, $marray[$i]);
				if ($type == '') {
					$type = 'noun'; 
					if (wordAdd ($dbc, $marray[$i], $type)) {
						echo '<span style="float: right;">added</span>';
					}
				} elseif ($i > floor(count($marray)/2)) { //everything after (approximate object)
					$type = selectType($dbc, $marray[$i]);
					if ($type == '') {
						if ($i < count($marray)-1) { $type = 'pronoun';	} 
						else { $type = 'noun';	}
						if (wordAdd ($dbc, $marray[$i], $type)) {
							echo '<span style="float: right;">added</span>';
						}
					}
				} else { //for the approximate predicate
					$type = selectType($dbc, $marray[$i]);
					if ($type == '') {
						$type = 'verb';	
						if (wordAdd ($dbc, $marray[$i], $type)) {
							echo '<span style="float: right;">added</span>';
						}
					}	
				}
			echo $marray[$i] . ' is a ' . $type . '<br />';
			}
		}
	}
	}
return false;
}
?>