<?php
$dbhost  = 'localhost';    // Unlikely to require changing
$dbname  = 's238159'; //
$dbuser  = 's238159';     // ...variables according
$dbpass  = 'icknobia';     // ...to your installation
$appname = "Online Booking"; // ...and preference

/*
 * Create a new connection to the database
 */
$connection = new mysqli($dbhost, $dbuser, $dbpass, $dbname);
if ($connection->connect_error) 
	die($connection->connect_error);
	//set the autocommit to false for handle the concurrency 
mysqli_autocommit($connection, false);


	
/*
 * Function that actually execute query
 */
function queryMysql($query){
	global $connection;
	$result =mysqli_query($connection,$query);
	return $result;
}

/*
 * This function initialize the freetime to 180 minutes 
 *
 */
function initializeFreeTime(){
	global $connection;
	try {
		$query = "SELECT * FROM available_time FOR UPDATE";
		if (mysqli_num_rows ( queryMysql ( $query ) ) == 0)
			$query = "INSERT into available_time(av_time) VALUES('180')";
			if (! queryMysql ( $query ))
				throw new Exception ( "Insert Failed in update free time-- " );
	} catch ( Exception $e ) {
		mysqli_rollback ( $connection );
		echo "Rollback  " . $e->getMessage ();
	}
	mysqli_commit($connection);
}


/*
 *This function check if the user is in session or not 
 */
function userLoggedIn(){
	if (isset($_SESSION['user']))
		return $user=$_SESSION['user'];
		else return false;
}

/*
 * This function destroy the session
 */ 
function destroySession()
{
	$_SESSION=array();
	session_destroy();
}

/*
 * This function take as parameter a value and sanitize this value
 * coming from the input 
 */ 
function sanitizeString($var){
	global $connection;
	$var = strip_tags($var);
	$var = htmlentities($var);
	$var = stripslashes($var);
	return mysqli_real_escape_string($connection,$var);
}

function sanitizePassword($var){
	global $connection;
	return $var= mysqli_real_escape_string($connection,$var);
}

/**
 * AUTHENTICATION HANDLE
 */

/*
 * This function is used to allow the login of the user.
 * As parameter accepts the username and password, check if is
 * in the db and save in session the username and the associated id.
 * At the last redirect to the booking page
 */
function Login($user,$pass){
	global $connection;
	if ($user == "" || $pass == "") {
		$message= "Not all field have been entered";
		$_SESSION['message']= $message;
	} else {
		$query = "SELECT user,pass FROM members
		WHERE user='$user' AND pass= md5('$pass') FOR UPDATE";

		$result= queryMysql ($query);
		$array = $result->fetch_array(MYSQLI_ASSOC);
		if (mysqli_num_rows($result) == 0) {
		$message= "Wrong username or password";
		$_SESSION['message']= $message;

		} else {
			//save in session the username and userid
			$_SESSION ['user'] = $user;		
			header ( "Location: ". "Booking.php" );
			exit();

		}
	}
}

/*
 * This function is used to register the user and insert it into db
 * The cuncurrency is handle by means of FOR UPDATE in select and performing the commit at the end
 * of the transcation manually
 *  If some error occurs a rollaback of whole operations is performed to avoid problems
 */ 
function Register($user, $pass) {
	global $connection;
	try{
		$query = "SELECT * FROM members WHERE user='$user' FOR UPDATE";
		if (mysqli_num_rows (queryMysql($query))) {
			echo "<script type=\"text/javascript\"><!--
					alert(\"That username already exists. Please choose another one. \")
					</script>";
			$rmess = "That username already exists. Please choose another one.";
			$_SESSION['rmess']= $rmess;
			
		} else {
			$reg = preg_match ( '/^\w+@[a-zA-Z_]+?\.[a-zA-Z]{2,3}$/', $user );
			$regp =preg_match("/[0-9]/",$pass)!= 0 && preg_match("/[a-z]/",$pass)!= null && preg_match("/[A-Z]/",$pass)!= null;
			if ($reg > 0 && $regp && strlen($user) <=50 && strlen($pass) <= 32) {
				$query = "INSERT INTO members (user,pass) VALUES('$user', md5('$pass'))";
				if(!queryMysql($query))
					throw new Exception("Insert Failed");
					header ( "Location:"."Login.php" );
					
			} else {
				echo "<script type=\"text/javascript\"><!--
					alert(\"Error: Registration not completed successfully. \")
					</script>";
				$rmess = "Error: Registration not completed successfully.";
				$_SESSION['rmess']= $rmess;
			}
		}
	}catch (Exception $e){
		mysqli_rollback($connection);
		echo "Rollback  ". $e->getMessage();
	}
	mysqli_commit($connection);
}


/**
 * HANDLE FOR FREETIME
 */

/*
 *This function simply get the freetime and store it
 */
function getFreeTime(){
	$query = "SELECT av_time FROM available_time FOR UPDATE";
	$result = queryMysql($query);
	return mysqli_fetch_array($result,MYSQLI_ASSOC); 
}

function updateFreeTime($book, $freetime) {
	global $connection;
	try {
		$freetime -= $book;
		if ($freetime < 0)
			$freetime = 0;
		$query = "UPDATE available_time  set av_time='$freetime'";
		if (! queryMysql ( $query ))
			throw new Exception ( "Update Failed in update free time-- " );
	} catch ( Exception $e ) {
		mysqli_rollback ( $connection );
		echo "Rollback  " . $e->getMessage ();
	}
	 mysqli_commit($connection);
}

function restoreFreeTime($freetime, $totreq) {
	global $connection;
	try {
		$freetime = 180 - $totreq;
		if ($freetime < 0)
			$freetime = 0;
		$query = "UPDATE available_time  set av_time='$freetime'";
		if (! queryMysql ( $query ))
			throw new Exception ( "Update Failed in update free time-- " );
	} catch ( Exception $e ) {
		mysqli_rollback ( $connection );
		echo "Rollback  " . $e->getMessage ();
	}
	 mysqli_commit($connection);
}

/*
 * This function compute the total of the requested time and return it
 */
function computeTotalRequest() {
	global $connection;
	$totreq = 0;
	$query = "SELECT request, assigned, date FROM booking ORDER BY date ASC FOR UPDATE";
	if (mysqli_num_rows ( queryMysql ( $query ) ) == 0)
		return $totreq;
	$result = queryMysql ( $query );
	while ( $row = mysqli_fetch_array ( $result, MYSQLI_ASSOC ) )
		$arr [] = $row ['request'];
	foreach ( $arr as $value ) {
		$entries = explode ( '-', $value );
		$totreq += $entries [0];
	}
	return $totreq;
	mysqli_free_result ( $result );
	mysqli_commit ( $connection );
}
	
	/*
 * This function compute the total of the assigned time and return it
 */
function computeTotalAssign() {
	global $connection;
	$totass = 0;
	$query = "SELECT  request, assigned, date FROM booking ORDER BY date ASC FOR UPDATE";
	if (mysqli_num_rows ( queryMysql ( $query ) ) == 0)
		return $totass;
	else {
		$result = queryMysql ( $query );
		while ( $row = mysqli_fetch_array ( $result, MYSQLI_ASSOC ) )
			$arr [] = $row ['assigned'];
		
		foreach ( $arr as $value ) {
			$entries = explode ( '-', $value );
			$totass += $entries [0];
		}
		return $totass;
	}
	mysqli_free_result ( $result );
	mysqli_commit ( $connection );
}

/**
 *  BOOKING HANDLES
 */

/*
 * This function is used to insert a new booking from the current user
 * Is verified if the row already exist, if not is inserted a new record in the table
 * while if the record is alredy present the action is prevented and user alerted
 */
function performBooking($user, $request, $assign, $date) {
	global $connection;
	try {
		$query = "SELECT * FROM booking WHERE user='$user' FOR UPDATE";
		if (mysqli_num_rows ( queryMysql ( $query ) )) {
			$msg = "You already have a booking";
			$_SESSION ['msg'] = $msg;
		} else {
			if($user != null){
			$query = "INSERT INTO booking (user,request,assigned,date)
			VALUES ('$user','$request','$assign', '$date')";
			$msg = "Booking succesfully done";
			$_SESSION ['msg'] = $msg;
			if (! queryMysql ( $query ))
				throw new Exception ( "Insert Failed in insert new booking--" );
			}
		}
	} catch ( Exception $e ) {
		mysqli_rollback ( $connection );
		echo "Rollback  " . $e->getMessage ();
	}
	mysqli_commit ( $connection );
}
	
	/*
 * This function perform the delete of a booking for the user currently in session
 * Is verified if the the record exists, if so the booking is deleted
 * otherwise the user is advertised that his prenotation does not exist
 */
function deleteBooking($user) {
	global $connection;
	
	try {
		$query = "SELECT * FROM booking WHERE user='$user' FOR UPDATE";
		if (mysqli_num_rows ( queryMysql ( $query ) )) {
			$query = "DELETE from booking WHERE user='$user'";
			$msg = "Booking deleted succesfully";
			$_SESSION ['msg'] = $msg;
			if (! queryMysql ( $query ))
				throw new Exception ( "Delete Failed in insert new booking--" );
		} else {
			$msg = "No booking to delete";
			$_SESSION ['msg'] = $msg;
		}
	} catch ( Exception $e ) {
		mysqli_rollback ( $connection );
		echo "Rollback  " . $e->getMessage ();
	}
	mysqli_commit ( $connection );
}

/*
 *This function shows all the the infos about the time and fill dinamically
 *a table to store them in order to show them on home page.
 *
 */
function showBooking(){
	global $connection;
	$count = 0;
	$query = "SELECT user, request, assigned, date FROM booking ORDER BY date ASC FOR UPDATE";
	if (mysqli_num_rows ( queryMysql ( $query ) ) == 0)
		echo "<h2> No appointment has been registered yet</h2>  ";
	else {
		$result = queryMysql ( $query );
		echo "<table id ='booking'><tr><th>User</th><th>Requested</th><th>Assigned</th><th>Time Range</th></tr>";
		while ( $row = mysqli_fetch_array ( $result, MYSQLI_ASSOC ) ) {
			if ($count == 0) {
				$starthour = date ( "H:i", mktime ( 14, 0 ) );
				$endhour = computeHourFormat ( $starthour, $row ['assigned'] );
				$count ++;
				$finaltime = $starthour . '-' . $endhour;
			} else {
				$starthour = $endhour;
				$endhour = computeHourFormat ( $starthour, $row ['assigned'] );
				$finaltime = $starthour . '-' . $endhour;
			}
			echo "<tr><td>" . $row ['user'] . "<td>" . $row ['request'] . " minutes" . "</td><td>" . $row ['assigned'] . " minutes" . "</td>";
			echo "<td>" . $finaltime . "</td>";
		}
		echo "</table>";
	}
	//mysqli_free_result ( $result );
	mysqli_commit ( $connection ); //not necessary 
}

/*
 * This function compute the time range for the user currently in session
 */
function getAppointment($user) {
	global $connection;
	$count = 0;
	$endhour = 0;
	$query = "SELECT user, request, assigned, date FROM booking ORDER BY date ASC FOR UPDATE";
	$result = queryMysql ( $query );
	while ( $row = mysqli_fetch_array ( $result, MYSQLI_ASSOC ) ) {
		if ($count == 0) {
			$starthour = date ( "H:i", mktime ( 14, 0 ) );
			$endhour = computeHourFormat ( $starthour, $row ['assigned'] );
			$count ++;
		} else {
			$starthour = $endhour;
			$endhour = computeHourFormat ( $starthour, $row ['assigned'] );
		}
		
		if ($row ['user'] == $user)
			return $finaltime = $starthour . '-' . $endhour;
	}
}


/*
 * This function is used to compute the reduction of minutes when a user perform
 * a booking of minutes greater than the free time.
 * The total request is computed, than is performed this operation (requestValue/totrequest)*maxtime
 * except for the last one that is computed by taking the remaing time 
 * At each iteration the new value is update in the table 
 */
function reduceTime() {
	global $connection;
	$totreq = computeTotalRequest ();
	$partial = 0;
	try {
		$query = "SELECT user,request, assigned, date FROM booking ORDER BY date ASC FOR UPDATE";
		$result = queryMysql ( $query );
		while ( $row = mysqli_fetch_array ( $result, MYSQLI_ASSOC ) ) {
			$arr [] = $row ['request'];
			$usr [] = $row ['user'];
		}
		if(empty($arr))
			return;
		for($i = 0, $size = count ( $arr ); $i < $size; $i ++) {
			if ($i != $size - 1) {
				$arr [$i] = (int) round(($arr [$i] / $totreq) * 180);
				$partial += $arr [$i];
			} else
				$arr [$i] = 180 - $partial;
			$query = "UPDATE booking set assigned='$arr[$i]' WHERE user='$usr[$i]'";
			if (! queryMysql ( $query ))
				throw new Exception ( "--Update Failed in booking table--" );
		}
	} catch ( Exception $e ) {
		mysqli_rollback ( $connection );
		echo "Rollback  " . $e->getMessage ();
	}
	mysqli_commit ( $connection );
}

/*
 *This function compute the new free time and redistribute the time for the users 
 *(used in case some booking is deleted)
 */
function computeTime() {
	global $connection;
	try {
		$totreq = computeTotalRequest ();
		$query = "SELECT user,request, assigned, date FROM booking ORDER BY date ASC FOR UPDATE";
		$result = queryMysql ( $query );
		while ( $row = mysqli_fetch_array ( $result, MYSQLI_ASSOC ) ) {
			$arr [] = $row ['request'];
			$usr [] = $row ['user'];
		}
		if (empty ( $arr ))
			return;
		for($i = 0, $size = count ( $arr ); $i < $size; $i ++) {
			$query = "UPDATE booking set assigned='$arr[$i]' WHERE user='$usr[$i]'";
			if (! queryMysql ( $query ))
				throw new Exception ( "--Update Failed in booking table--" );
		}
	} catch ( Exception $e ) {
		mysqli_rollback ( $connection );
		echo "Rollback  " . $e->getMessage ();
	}
	mysqli_commit ( $connection );
}

/*
 * This function converts the minutes inserted by the user in the actual hour format
 * and return it.
 */
function computeHourFormat($start_time,$minute){
	$currentDate = strtotime($start_time);
	$futureDate = $currentDate+(60*$minute); 
	$newDate = date("H:i", $futureDate);
	return $newDate;
	
}


/**
 *	HANDLE FOR SECURITY ISSUES 
 */

/*
 * Used to redirect to https if is not used
 */
function forceHttps(){
	if(empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] == "off"){
		$redirect = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
		header('HTTP/1.1 301 Moved Permanently');
		header('Location: ' . $redirect);
		exit();
	}
}

/*
 * This function is used to verify if the session is inactive for more
 * than 2 minutes. If so the session is destroyed and the user
 * is redirect to login page in order to perform any operation
 */
function checkSession(){
	if(userLoggedIn()){
		if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > 120)) {
			session_unset();     // remove session variable
			session_destroy();   // destroy current session
			session_start();
			$sessionex = "Session expired";
			$_SESSION['sessionex']= $sessionex;
			header ( "Location:"."Login.php" );
		
		}
		$_SESSION['LAST_ACTIVITY'] = time(); // update last activity time stamp
	}
}

/*
 * This function check if the cookie are enabled by getting a msg
 * and verify the msg and if the cookie is set.In case this condition fails
 * the server does not allow the navigation otherwhise set a new cookie
 * with a year duration and redirect to the page that called
 */
function cookieCheck(){
	if(isset($_GET['msg']) && $_GET['msg']=="cookieCheck" && !isset($_COOKIE['cookieCheck'])){
		die("Sorry: Your browser does not support or has disabled cookie.Please enable cookie support to allow the correct execution of the website.");
	}
	if(!isset($_GET['msg']) && !isset($_COOKIE['cookieCheck'])){
		setcookie("cookieCheck", "true",time()+(3600*24*365), '/');
		$redirect = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] .'?msg=cookieCheck'; 
		header('Location: ' . $redirect);
		
	}
	
}



?>