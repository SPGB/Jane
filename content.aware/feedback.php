<?php 
/*
feedback.php for spider
evaliates spider
*/
ob_start();
include ('../includes/mysqli_connect.php');
include ('../includes/header.html');
if ($_GET['id'] != '' && $_GET['like'] != '') {

if ($_GET['like'] == 'yes') { $like = 1; }
if ($_GET['like'] == 'no') { $like = (-1); }
$r = mysqli_query($dbc, "SELECT body FROM threads WHERE postID='$_GET[id]'");
$thread = mysqli_fetch_array($r, MYSQLI_ASSOC);
$rUpdate = mysqli_query($dbc, "UPDATE contentaware SET success=success+'$like' WHERE reply='$thread[body]'");
echo 'Thank you for your input.';

}
include ('../includes/footer.html');
ob_flush();
?>