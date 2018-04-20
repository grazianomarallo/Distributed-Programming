<?php 
include "util.php";



if (isset($_SESSION['user'])) destroySession();

if (isset($_POST['user']))
{
	$user = sanitizeString($_POST['user']);
	$pass = sanitizePassword($_POST['pass']);
	Register($user, $pass);
	$rmess = $_SESSION['rmess'];
}
include 'Header.php';
?>



<script type="text/javascript">

    function CheckPasswordStrength(password) {
        var password_strength = document.getElementById("strength");

        //TODO verificare correttezza algoritmo password
        var reg1,reg2,passed;
        reg1= /[a-zA-z]|[0-9]|[:,;.?!]/;
        reg2= /[a-zA-z]([0-9]|[:,;.?!])+/;

        if(reg1.test(password) && password.length <= 2) {
            passed = 0;
        }
        if(reg1.test(password) && password.length >= 3) {
            passed = 1;
        }

        if(reg2.test(password) && password.length >= 3) {
            passed = 2;
        }

//Display status.
        var color = "";
        var strength = "";
        switch (passed) {
            case 0:
                strength = "Weak";
                color = "red";
                break;
            case 1:
                strength = "Medium";
                color = "yellow";
                break;
            case 2:
                strength = "Robust";
                color = "green";
                break;
        }
        password_strength.innerHTML = strength;
        password_strength.style.color = color;

    }

	function checkForm(form){
		if(form.user.value == "") {
			alert("Error: Username cannot be blank!");
			form.user.focus();
			return false;
		}

		re = /^\w+@[a-zA-Z_]+?\.[a-zA-Z]{2,3}$/;
		if(form.user.value.length > 50) {
			alert("Error: Username max 50 char!");
			form.user.focus();
			return false;
			}
		if(!re.test(form.user.value)) {
			alert("Error: Username must contain only letters, numbers and underscores!");
			form.user.focus();
			return false;
		}

        if(form.pass.value != form.pass1.value ) {
            alert("Error: Passwords do not corresponds!");
            form.pass.focus();
            return false;
        }

		
		if(form.pass.value != "" && form.pass.value == form.pass1.value) {
			if(form.pass.value == form.user.value) {
				alert("Error: Password must be different from Username!");
				form.pass.focus();
				return false;
			}

			if(form.pass.value.length  >32) {
				alert("Error: password must be maximum 32 char!");
				form.pass.focus();
				return false;
			}

			if(form.pass.value == ""){
                alert("Error: Password field cannot be blank!");
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
    <input type="password" placeholder="Enter Password" name="pass" onkeyup="CheckPasswordStrength(this.value)">
    <b> <div id="strength">  </div> </b><br>
    <label><b>Confirm Password</b></label><br>
    <input type="password" placeholder="Enter Password Again" name="pass1" ><br>
    <button type="submit" >Registration</button>
	<p> After registration you'll be redirect to Login page </p>
	<p><div id='error'><?php if(isset($rmess)) echo $rmess;?></div></p>
	</form>
	</div>

<div id="footer"></div>
</body>
</html>





