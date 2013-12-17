<?php 
/*
spider.php
crawls through public listing looking for messages where the response confidence integer (corcha) = 50% - 100%
*/
include ('../includes/mysqli_connect.php');
echo '
<html><head><title>Jane</title></head>';
require_once 'twitter.php';
require_once('jane.inc.php'); 

/*
if (isset($_GET['qaz34'])) { //test
	email('avonwodahs', 'avonwodahs@gmail.com');
}

if (isset($_GET['qaz12'])) {
	if($stmt = $dbc -> prepare("SELECT email, username FROM users where email!=''")) {
		$stmt -> execute();
		$stmt -> bind_result($email, $user); 
		while ($stmt -> fetch()){
			echo $user . ' | ' . $email . ' | ' . email(trim($user), $email);;
		}
		$stmt -> close();
	}
	exit();
}
//debug
$stitched = stitch($dbc, 'I have a pet rock steve');
print_r($stitched);
exit();
//end debug
*/

$twitter = new Twitter('3QyS3BwiCbQnzWYlxTBRIA', 'uHDDUCMV6ib9f0ov2MkJq40Ny6UCDx8wcVtR0eK0o');
$twitter->setOAuthToken('261449600-cYuR9sAVcS15hbRyvhVlcFuX0OCM7U7zIDOfoAak');
$twitter->setOAuthTokenSecret('7Vl5VdJe8yPFNL5QvQHTxtqzOIkAhNCR286pqVc0vHw');
$rate = $twitter->accountRateLimitStatus();
echo '<span id="hits" style="margin: 10px;">' . $rate['remaining_hits'] . '</span><br />';
if ($rate['remaining_hits'] > 0) {
	if (isset($_GET['retweet'])) {
		$response = $twitter->statusesMentions(); 
	} else {
		$response = $twitter->statusesPublicTimeline();
	}
	for ($i = 0; $i < 20; $i++) {
	$tweet = $response[$i];
	$user = $tweet['user'];
	echo '<span class="twit"><div style="padding: 5px; margin: 20px; border: 1px solid grey; display: inline-block;">' . $tweet['text'] . '</div><br />';
	if ($user['screen_name'] != 'jane_darkchan')
		$reply = stimuli($dbc, '', $tweet['text'], '', '', $user['screen_name'], $tweet['id_str']);
	if ($reply != '') 
		echo '<div style="border: 1px solid green; display: inline-block; margin: 20px; padding: 5px;">' . $reply . '</div>';
	echo '</span>
	
	<a href="http://atoplist.net/"><img src="http://atoplist.net/button.php?u=avonwodahs" border="0" style="width: 0px;" /></a>
	<a href="http://interchan.org/"><img src="http://interchan.org/button.php?u=avonwodahs" border="0" style="width: 0px;" /></a>
	<a href="http://rankingchan.info/"><img src="http://rankingchan.info/button.php?u=avonwodah" border="0" style="width: 0px;" /></a>
	
	';
	}
} 


function email($user, $email){


	$message = '<div style="background: #555; width: 100%; height: 40px; background-image: url(http://darkchan.com/aesthetics/bg.png); background-repeat: repeat-x;"><img src="http://darkchan.com/aesthetics/logo.png" /></div>';
	$subject = 'Darkchan misses you ' . $user . '. :(';
	$headers = 'Content-type: text/html; charset=utf-8; format=flowed' . "\r\n";
	$end_char = "\r\n";


	// add config data to email
	$headers .= "From: Darkchan <invites@darkchan.com>" . $end_char;
	$message.= '
	<br /><br /><br />
	<center style="font-size: 26px;">We Miss you ' . $user . '!</center><br />
	<a href="http://darkchan.com">Darkchan</a> is now better than ever! It has almost everything (and what it doesn\'t have, it\'s getting). But we want you to become a part of the community! <br />
	<br /><b>Whats new?</b><br />
	<ul>
		<li>Redesigned to make it easier for you to browse and create threads</li>
		<li>Faster</li>
		<li>Easier</li>
		<li>New trophies</li>
	</ul>
	&nbsp;
	&nbsp;
	&nbsp;
	So what are you waiting for? Come help us make Darkchan better than ever. Want a feature that you dont see? <a href="http://darkchan.com/feedback">tell us about it!</a>
	<br /><br />
	<center>Do not respond to this message, it will not be recieved, email contact@darkchan.com instead.</center>';
	if (mail($email, $subject, $message, $headers)) {
		echo 'sent to' . $email . '<br />';
	}
}	
exit();

?>