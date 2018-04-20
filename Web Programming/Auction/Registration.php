<?php 
include 'Header.php';

if (isset($_SESSION['user'])) destroySession();

if (isset($_POST['user']))
{
	$user = sanitizeString($_POST['user']);
	$pass = sanitizeString($_POST['pass']);
	
	Register($user, $pass);
}
	
?>
	
	<script type="text/javascript">
	function checkForm(form){
		if(form.user.value == "") {
			alert("Error: Username cannot be blank!");
			form.user.focus();
			return false;
		}

		re = /^\w+@[a-zA-Z_]+?\.[a-zA-Z]{2,3}$/;
		if(!re.test(form.user.value)) {
			alert("Error: Username must contain only letters, numbers and underscores!");
			form.user.focus();
			return false;
		}
		
		if(form.pass.value != "") {
			if(form.pass.value == form.user.value) {
				alert("Error: Password must be different from Username!");
				form.pass.focus();
				return false;
			}
			re=/([0-9]+[a-zA-z]+|[a-zA-z]+[0-9]+)[a-zA-Z0-9]*/;
			if(!re.test(form.pass.value)) {
				alert("Error: password must contain at least one number and one character!");
				form.pass.focus();
				return false;
			}
			
		} else {
			alert("Error: Please check that you've entered your password!");
			form.pass.focus();
			return false;
		}
		
		return true;
	}
</script>

<div id="nav">
<ul>
<li><a  href="index.php">Home</a></li>
<li><a  href="Login.php">Login</a></li>
<li><a class="active" href="Registration.php">Sign In</a></li>

</ul>
</div>

<div id='login'>
	<h2>Registration is free, do it now!</h2><br>
	
	<div id='img'>
	<img src='user.png' alt='user' width='150' height='150'>
	</div>
	
<form method="post" action="Registration.php" onsubmit="return checkForm(this)" >
	<label><b>Username</b></label><br>
    <input type="text" placeholder="Enter Username: example@p.it" name="user" ><br>

    <label><b>Password</b></label><br>
    <input type="password" placeholder="Enter Password: ( min 1 letter, 1 number)" name="pass" ><br>
    <button type="submit" >Registration</button>
	<p> After registration you'll be redirect to Login page </p>
	</form>
	</div>

<?php 
include 'Footer.php';
?>





