<?php
include 'util.php';


if (isset ( $_POST ['user'] )) {
	$user = sanitizeString ( $_POST ['user'] );
	$pass = sanitizeString ( $_POST ['pass'] );
	Login($user, $pass)	;
	$message = $_SESSION['message'];
}
include 'Header.php';
?>
<div id="nav">
<ul>
<li><a  href="index.php">Home</a></li>
<li><a class="active" href="Login.php">Login</a></li>
<li><a href="Registration.php">Sign In</a></li>

</ul>
</div>

<div id='login'>
	<h2>Login </h2><br>
	
	<div id='img'>
	<img src='user.png' alt='user' width='150' height='150'>
	</div>
	
	<form method='post' action='Login.php'>
	<label><b>Username</b></label><br>
    <input type="text" placeholder="Enter Username" name="user" required><br>

    <label><b>Password</b></label><br>
    <input type="password" placeholder="Enter Password" name="pass" required><br>

    <button type="submit">Login</button>
	<p><div id='error'><?php if(isset($message)) echo $message;?></div> </p>
	</form>
	</div>

<div id="footer"></div>
</body>
</html>

