<?php
include 'util.php';


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
include 'Header.php';
include 'Navigation.php';




if(isset($_SESSION['sessionex']))
	$sessionex = $_SESSION['sessionex'];



echo "
<div id='central'>
<h3></h3>";
showBooking();



 echo "
<div id='img'>
<img src='logo.png' alt='iphone' width='1170' height='250'>
<p>Login if you're already registered, or Sign up</p>
</div>
</div>
<div id='footer'>
<p><div id='error'><h4> ";
 if (isset($sessionex)) echo $sessionex; unset($_SESSION['sessionex']); 
echo "</div><h4></p>
</div>
</div>
</body>
</html>

";

