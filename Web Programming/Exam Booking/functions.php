<?php
$dbhost  = 'localhost';    // Unlikely to require changing
$dbname  = 's238159'; //
$dbuser  = 'root';     // ...variables according
$dbpass  = '';//'icknobia';     // ...to your installation
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
	if ($user == "" || $pass == "") {
		$message= "Not all field have been entered";
		$_SESSION['message']= $message;
	} else {
		$query = "SELECT user,pass FROM members
		WHERE user='$user' AND pass= md5('$pass') FOR UPDATE";

		$result= queryMysql ($query);
		$array = $result->fetch_array(MYSQLI_ASSOC);
		if (mysqli_num_rows($result) == 0) {
		$message= "Username or password are not correct.Type again";
		$_SESSION['message']= $message;

		} else {
			//save in session the username
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
			//$regp =preg_match("/[0-9]/",$pass)!= 0 && preg_match("/[a-z]/",$pass)!= null && preg_match("/[A-Z]/",$pass)!= null;
			if ($reg > 0  && strlen($user) <=50 && strlen($pass) <= 32) {
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

/** GETTER AND UPDATER */

function getCurrentBooking($user){
    global $connection;
    $empty =0;
    $query = "SELECT first,second  FROM booking WHERE user='$user' FOR UPDATE";
    if (mysqli_num_rows ( queryMysql ( $query ) ) == 0)
        return $empty;
    $result = queryMysql ( $query );
    $result = mysqli_fetch_array ( $result, MYSQLI_ASSOC );
    return $result;
    mysqli_free_result ( $result );
    mysqli_commit ( $connection );
}


/*
 * This function compute the total booking for each call and return it in a fetched array
 */
function computeTotalBooking() {
	global $connection;
    $empty =0;
	$query = "SELECT call1,call2,call3  FROM calls FOR UPDATE";
	if (mysqli_num_rows ( queryMysql ( $query ) ) == 0)
		return $empty;
	$result = queryMysql ( $query );
    $result = mysqli_fetch_array ( $result, MYSQLI_ASSOC );

    return $result;
	mysqli_free_result ( $result );
	mysqli_commit ( $connection );
}
	
/*
 * This function compute the grand total of booking for the course
 */
function computeGrandTotal() {
	global $connection;
    $grandtot =0;
	$query = "SELECT  call1,call2,call3  FROM calls FOR UPDATE";
	if (mysqli_num_rows ( queryMysql ( $query ) ) == 0)
		return  $grandtot =0;
	else {
		$result = queryMysql ( $query );
		while ( $row = mysqli_fetch_array ( $result, MYSQLI_ASSOC ) )
			$grandtot = $row['call1'] + $row['call2']+$row['call3'] ;
		return $grandtot;
	}
	mysqli_free_result ( $result );
	mysqli_commit ( $connection );
}

/*
 * This function return the number of seat for each exam call and stores into a fetched array
 */
function getAvailableSeat(){
    global $connection;
    $empty =0;
    $query = "SELECT call1,call2,call3  FROM calls FOR UPDATE";
    if (mysqli_num_rows ( queryMysql ( $query ) ) == 0)
        return $empty;
    $result = queryMysql ( $query );
    $result = mysqli_fetch_array ( $result, MYSQLI_ASSOC );

    return $result;
    mysqli_free_result ( $result );
    mysqli_commit ( $connection );
}

/*
 * This function increments and update the available seat in exams call checking each combination
 */
function updateAvailableSeat($book1,$book2){
    global $connection;

    try {
        if($book1== 1 && $book2== 2) {
            $query = "UPDATE calls set call1= call1+1 , call2= call2+1";
            if (!queryMysql($query))
                throw new Exception ("Update Failed in insert calls--");
        }
        if($book1== 1 && $book2== 3) {
            $query = "UPDATE calls set call1= call1+1 , call3= call3+1";
            if (!queryMysql($query))
                throw new Exception ("Update Failed in insert calls--");
        }
        if($book1== 2 && $book2== 1) {
            $query = "UPDATE calls set call2= call2+1 , call1= call1+1";
            if (!queryMysql($query))
                throw new Exception ("Update Failed in insert calls--");
        }
        if($book1== 2 && $book2== 3) {
            $query = "UPDATE calls set call2= call2+1 , call3= call3+1";
            if (!queryMysql($query))
                throw new Exception ("Update Failed in insert calls--");
        }
        if($book1== 3 && $book2== 1) {
            $query = "UPDATE calls set call3= call3+1 , call1= call1+1";
            if (!queryMysql($query))
                throw new Exception ("Update Failed in insert calls--");
        }
        if($book1== 3 && $book2== 2) {
            $query = "UPDATE calls set call3= call3+1 , call2= call2+1";
            if (!queryMysql($query))
                throw new Exception ("Update Failed in insert calls--");
        }
    }
    catch ( Exception $e ) {
        mysqli_rollback ( $connection );
        echo "Rollback  " . $e->getMessage ();
    }
    mysqli_commit($connection);
}

/*
 * This function increment and update the single exam call seat checking all the 3 possibilities
 */
function updateSingle($book){
    global $connection;

    try {
        if($book== 1 ) {
            $query = "UPDATE calls set call1= call1+1 ";
            if (!queryMysql($query))
                throw new Exception ("Update Failed in insert calls--");
        }
        if($book== 2 ) {
            $query = "UPDATE calls set call2= call2+1 ";
            if (!queryMysql($query))
                throw new Exception ("Update Failed in insert calls--");
        }
        if($book== 3 ) {
            $query = "UPDATE calls set call3= call3+1 ";
            if (!queryMysql($query))
                throw new Exception ("Update Failed in insert calls--");
        }

    }
    catch ( Exception $e ) {
        mysqli_rollback ( $connection );
        echo "Rollback  " . $e->getMessage ();
    }
    mysqli_commit($connection);
}

/*
 * This function decrement and update the available seat in exams call checking each combination
 */
function decrementAvailableSeat($book1,$book2){
    global $connection;

    try {
        if($book1== 1 && $book2== 2) {
            $query = "UPDATE calls set call1= call1-1 , call2= call2-1";
            if (!queryMysql($query))
                throw new Exception ("Update Failed in insert calls--");
        }
        if($book1== 1 && $book2== 3) {
            $query = "UPDATE calls set call1= call1-1 , call3= call3-1";
            if (!queryMysql($query))
                throw new Exception ("Update Failed in insert calls--");
        }
        if($book1== 2 && $book2== 1) {
            $query = "UPDATE calls set call2= call2-1 , call1= call1-1";
            if (!queryMysql($query))
                throw new Exception ("Update Failed in insert calls--");
        }
        if($book1== 2 && $book2== 3) {
            $query = "UPDATE calls set call2= call2-1 , call3= call3-1";
            if (!queryMysql($query))
                throw new Exception ("Update Failed in insert calls--");
        }
        if($book1== 3 && $book2== 1) {
            $query = "UPDATE calls set call3= call3-1 , call1= call1-1";
            if (!queryMysql($query))
                throw new Exception ("Update Failed in insert calls--");
        }
        if($book1== 3 && $book2== 2) {
            $query = "UPDATE calls set call3= call3-1 , call2= call2-1";
            if (!queryMysql($query))
                throw new Exception ("Update Failed in insert calls--");
        }
    }
    catch ( Exception $e ) {
        mysqli_rollback ( $connection );
        echo "Rollback  " . $e->getMessage ();
    }
    mysqli_commit($connection);
}

/*
 * This function decrements
 * and update the single exam call seat checking all the 3 possibilities
 */
function decrementSingle($book){
    global $connection;

    try {
        if($book== 1 ) {
            $query = "UPDATE calls set call1= call1-1 ";
            if (!queryMysql($query))
                throw new Exception ("Update Failed in insert calls--");
        }
        if($book== 2 ) {
            $query = "UPDATE calls set call2= call2-1 ";
            if (!queryMysql($query))
                throw new Exception ("Update Failed in insert calls--");
        }
        if($book== 3 ) {
            $query = "UPDATE calls set call3= call3-1 ";
            if (!queryMysql($query))
                throw new Exception ("Update Failed in insert calls--");
        }

    }
    catch ( Exception $e ) {
        mysqli_rollback ( $connection );
        echo "Rollback  " . $e->getMessage ();
    }
    mysqli_commit($connection);
}



/**
 *  BOOKING HANDLES
 */

/*
 * The two following functions are used to insert a new  preference from the current user
 * Is verified if the row already exist, if not is inserted a new record in the table
 * while if the record is already present the action is prevented and user alerted.
 * In order to allow simultaneous transactions between user, two function are necessary.
 * The first select and lock the needed table where the user is the one in session and after
 * that insert or update the record with the preference. The second function
 * does basically the same, but is verifed also if the firs preference is already been inserted
 * and locking the tuples. Calling this two function sequentially allows the simultaneous transaction
 *
 */
function bookFirstPreference($user, $book, $date) {
    global $connection;
    try {
        $query = "SELECT * FROM booking WHERE user='$user' FOR UPDATE";
        if (mysqli_num_rows ( queryMysql ( $query ) )) {
            $msg = "You have already made your a booking";
            $_SESSION ['msg'] = $msg;
        } else {
            if($user != null){
                $query = "INSERT INTO booking (user,first,date)
			VALUES ('$user','$book','$date') ON DUPLICATE KEY UPDATE user='$user', date='$date', first ='$book' ";
                updateSingle($book);
                $msg = "Booking succesfully done";
                $_SESSION ['msg'] = $msg;
                if (! queryMysql ( $query ))
                    throw new Exception ( "Insert Failed in first preferece--" );
            }
        }
    } catch ( Exception $e ) {
        mysqli_rollback ( $connection );
        echo "Rollback  " . $e->getMessage ();
    }
    mysqli_commit ( $connection );
}

function bookSecondPreference($user, $book, $date) {
    global $connection;
    try {
        $query = "SELECT * FROM booking WHERE user='$user' AND second != 0  FOR UPDATE";
        if (mysqli_num_rows ( queryMysql ( $query ) )) {
            $msg = "You have already made your a booking";
            $_SESSION ['msg'] = $msg;
        } else {
            if($user != null){
                $query = "INSERT INTO booking (user,second,date)
			VALUES ('$user','$book','$date') ON DUPLICATE KEY UPDATE user='$user', date='$date', second ='$book'";
                updateSingle($book);
                $msg = "Booking succesfully done";
                $_SESSION ['msg'] = $msg;
                if (! queryMysql ( $query ))
                    throw new Exception ( "Insert Failed in second preference--" );
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
			$msg = "Preferences deleted succesfully";
			$_SESSION ['msg'] = $msg;
			if (! queryMysql ( $query ))
				throw new Exception ( "Delete Failed --" );
		} else {
			$msg = "No preferences to delete";
			$_SESSION ['msg'] = $msg;
		}
	} catch ( Exception $e ) {
		mysqli_rollback ( $connection );
		echo "Rollback  " . $e->getMessage ();
	}
	mysqli_commit ( $connection );
}

/*
 *This function shows all the the infos about the booking made by the user and fill dinamically
 *a table to store them in order to show them on home page.
 *
 */
function showBooking(){
	global $connection;
	$query = "SELECT user, first, second, date FROM booking ORDER BY date ASC FOR UPDATE";
	if (mysqli_num_rows ( queryMysql ( $query ) ) == 0)
		echo "<h2> No booking has been done yet</h2>  ";
	else {
		$result = queryMysql ( $query );
		echo "<table id ='booking'><tr><th>First Call</th><th>Second Call</th><th> Third Call</th><th>Booked at:</th></tr>";
		while ( $row = mysqli_fetch_array ( $result, MYSQLI_ASSOC ) ) {
			echo "<tr><td>"; if($row['first']==1 || $row['second']==1) echo $row ['user'];
			else echo "-";  echo"<td>"; if($row['first']==2 || $row['second']==2) echo $row ['user']; else echo "-";echo "</td><td>";
			if($row['first']==3|| $row['second']==3) echo $row ['user']; else echo "-" ."</td>";
			echo "<td>" . $row['date'] . "</td>";
		}
		echo "</table>";
	}
	//mysqli_free_result ( $result );
	mysqli_commit ( $connection ); //not necessary 
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