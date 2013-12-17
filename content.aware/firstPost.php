<?php
//the first post is the most important, as it needs to lure the poster in with a relevant and interesting comment
//we need variance so it doesn't appear to be canned responses!

function first($input, $inputFile) { //returns a string
	if ( preg_match('/(http|download|mb)/i', $input) ) { //they are adding a link
		if ($inputFile != '') {
			switch (rand(0, 10)) {
				case 0: $output = "nice! downloading now"; break;
				case 1: $output = "mirror? ;)"; break;
				case 2: $output = "thanks for adding a link"; break;
				case 3: $output = "any more like this?"; break;
				case 4: $output = "have any more?"; break;
				case 5: $output = "first."; break;
				case 6: $output = ":D"; break;
				case 7: $output = "what is this?"; break;
				case 8: $output = "any other links?"; break;
				case 9: $output = "any similar?"; break;
				case 10: $output = "that's pretty cool"; break;
			}
		} else {
			switch (rand(0, 2)) {
				case 0: $output = "maybe add an image?";  break;
				case 1: $output = "attach a file please"; break;
				case 2: $output = "screens?"; break;
			}
		}
	} else { //the default, we want to avoid this if we can
		switch (rand(0, 5)) {
			case 0: $output = "Hey! my name's jane."; break;
			case 1: $output = "What is your name?."; break;
			case 2: $output = "Hey. hello. hi."; break;
			case 3: $output = "Thanks for posting!"; break;
			case 4: $output = "Nice to meet you."; break;
			case 5: $output = "Hi Hello."; break;
		}
	}
	return $output;
}
?>