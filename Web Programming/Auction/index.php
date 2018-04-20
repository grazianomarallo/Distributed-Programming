<?php
include 'Header.php';
include 'Navigation.php';

/*
 * Check if the user was logged in and has done a logout
 */
if (isset ( $_SESSION ['logout'] )) {
	$logout = $_SESSION ['logout'];
	if ($logout) {
		?>
<script type="text/javascript">
	alert("You've been logged out.");
	</script>
<?php
		$logout = false;
		session_destroy ();
	}
}


$bid = getBid ();
$maxbid = $bid ['maxbid'];
$userid= $bid['userid'];
$var= getNameHighBid($userid);
$name= $var['user'];
$sessionex = $_SESSION['sessionex'];

if($name== NULL){
	$name= "nobody";
}

echo "
<div id='central'>
<h1>The current acution is on the brand new Iphone 7</h1>
<h3>Highest bid until now: $maxbid €</h3>
<h3>Current offer has been done by : $name</h3>
 
<div id='img'>
<img src='iphone.png' alt='iphone' width='400' height='300'>

<h4>Do you wanna join?</h4>
<p>Login if you're already registered, or Sign up</p>
<p><div id='exceed'><h2> ";
if (isset($sessionex)) echo $sessionex; 
echo "</div><h2></p>
</div>
</div>
</div>

";


include 'Footer.php';
?>


