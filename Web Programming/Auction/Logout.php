<?php
include 'functions.php';
include 'Header.php';


if (isset($_SESSION['user']))
{
	session_unset();
	destroySession();
	session_start();
	$logout= true;
	$_SESSION['logout'] = $logout;
	header ( "Location: ". "index.php" );
	
}else{
	//user not logged in , cannot do logout
	header ( "Location: ". "index.php" );


}

?>