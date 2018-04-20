<?php
$dbhost  = 'localhost';    // Unlikely to require changing
$dbname  = 's238159'; //
$dbuser  = 's238159';     // ...variables according
$dbpass  = 'icknobia';     // ...to your installation
$appname = "Online Auctions"; // ...and preference

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
function sanitizeString($var)
{
	
	global $connection;
	$var = strip_tags($var);
	$var = htmlentities($var);
	$var = stripslashes($var);
	return mysqli_real_escape_string($connection,$var);
}

/**
 * AUTHENTICATION HANDLE
 */

/*
 * This function is used to allow the login of the user.
 * As parameter accepts the username and password, check if is
 * in the db and save in session the username and the associated id.
 * At the last redirect to the bid page
 */
function Login($user,$pass){
	global $connection;
	if ($user == "" || $pass == "") {
		?>
<script type="text/javascript">
			alert("Not all field have been entered");
		</script>
<?php
	} else {
		$query = "SELECT user,pass,id FROM members
		WHERE user='$user' AND pass= md5('$pass')";

		$result= queryMysql ($query);
		$array = $result->fetch_array(MYSQLI_ASSOC);
		if (mysqli_num_rows($result) == 0) {
		$message= "Wrong username or password";
		$_SESSION['message']= $message;
			?>
<script type="text/javascript">
			alert("Wrong username or password. If you're not registered, please sign up!");
		</script>

<?php
		} else {
			//save in session the username and userid
			$_SESSION ['user'] = $user;		
			$_SESSION['userid'] = $array['id'];
			header ( "Location: ". "Bid.php" );

		}
	}
}

/*
 * This function is used to register the user and insert it into db
 * The cuncurrency is handle by means of startTransaction and
 * closeTransaction. If some error occurs a rollaback of whole
 * operations is performed to avoid problems
 */ 
function Register($user, $pass) {
	global $connection;
	try{
		$query = "SELECT * FROM members WHERE user='$user' FOR UPDATE";
		if (mysqli_num_rows (queryMysql($query))) {
			echo "<script type=\"text/javascript\"><!--
					alert(\"That username already exists. Please choose another one. \")
					</script>";
		} else {
			$reg = preg_match ( '/^\w+@[a-zA-Z_]+?\.[a-zA-Z]{2,3}$/', $user );
			$regp = preg_match ( "/([0-9]+[a-zA-z]+|[a-zA-z]+[0-9]+)[a-zA-Z0-9]*/", $pass );
			if ($reg > 0 && $regp > 0) {
				$query = "INSERT INTO members (user,pass) VALUES('$user', md5('$pass'))";
				if(!queryMysql($query))
					throw new Exception("Insert Failed");
					header ( "Location:"."Login.php" );
					
			} else {
				echo "<script type=\"text/javascript\"><!--
					alert(\"Error: Registration not completed successfully. \")
					</script>";
			}
		}
	}catch (Exception $e){
		mysqli_rollback($connection);
		echo "Rollback  ". $e->getMessage();
	}
	mysqli_commit($connection);
}


/**
 * GETTER
 */
/*
 * This function verify if the BID is set, if not the system 
 * set to 1.00 by default.
 */
function InsertSystemBid(){
	global $connection;
	$query = "SELECT maxbid,userid FROM bid where id='1'";
	if (mysqli_num_rows ( queryMysql ( $query ) ) == 0) {
		$query = "INSERT into bid (userid,maxbid) Values(NULL, 1.00)";
		queryMysql($query);
		mysqli_commit($connection);
	}
}


/*
 * This function simply select the BID and associated user from the
 * correspondent table and fetch the result into an array
 */
function getBid(){
	$query = "SELECT maxbid,userid FROM bid;";
	$result = queryMysql($query);
	return mysqli_fetch_array($result,MYSQLI_ASSOC); 
}

/*
 * Select the maximum value of thr between the user along whith the date
 * and the id associated and fetch result into an array
 */
function getMaxThr(){
	$query = "SELECT thr,userid,date FROM thr where thr in(Select Max(thr) from thr )";
	$result = queryMysql($query);
	return mysqli_fetch_array($result,MYSQLI_ASSOC); 
}

/*
 *This function retrieve the id associated to a user
 */
function getId($user){
	$query = "SELECT id,user  FROM members where user='$user'";
	$result = queryMysql($query);
	return mysqli_fetch_array($result,MYSQLI_ASSOC);
}


/*
 * Select the thr fomr the passed user
 */
function getThr($userid){
	$query = "SELECT thr FROM thr where userid='$userid'";
	$result= queryMysql($query);
	return mysqli_fetch_array($result,MYSQLI_ASSOC);
	
}

/*
 * Select the name of the highest bidder fetching resul into an array
 */
function getNameHighBid($userid){
	$query = "SELECT user FROM members where id='$userid'";
	$result= queryMysql($query);
	return mysqli_fetch_array($result,MYSQLI_ASSOC);
}

/*
 * This function return the thr for the user currently in session
 */
function getCurrentThr($user){
	$temp= getId($user);
	$usrid = $temp['id'];
	$tmp= getThr($usrid);
	return $tmp['thr'];
}


/**
 * BID AUCTION HANDLES
 */

/*
 * This function perform the update in the table where the maxBid is stored
 */
function insertBid($user, $value) {
	global $connection;
	try {
		$query = "UPDATE  bid set maxbid='$value', userid='".$user."'";
		if (! queryMysql ( $query ))
			throw new Exception ( "Update Failed in insert BID-- " );
	} catch ( Exception $e ) {
		mysqli_rollback ( $connection );
		echo "Rollback  " . $e->getMessage ();
	}
	mysqli_commit($connection);
}


/*
 * This function is used to insert the new thr from the user
 * Is verified if the row already exist, if not is inserted a new record in the table
 * while if the record is alredy present an update is performed.
 * Concurrency is handle with start and close Transaction
 */
function insertThr($user, $value, $date) {
	global $connection;
	try {
		
		$query = "SELECT userid,thr,date FROM thr
		WHERE userid='$user' FOR UPDATE";
		if (mysqli_num_rows ( queryMysql ( $query ) ) == 0) {
			$query = "INSERT into thr(userid,thr,date) Values('$user', '$value', '$date')";
		
			if (! queryMysql ( $query ))
				throw new Exception ( "Insert Failed in insert maxthr--" );
		}
		else {
			$query = "UPDATE thr set thr= '$value' , date='$date' where userid='$user'";
			if(!queryMysql ( $query ))
				throw new Exception ( "Update Failed in insert maxthr--" );
		}
	}
	catch ( Exception $e ) {
		mysqli_rollback ( $connection );
		echo "Rollback  " . $e->getMessage ();
	}
	mysqli_commit($connection);
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
			$sessionex = "Session expired, please log in again";
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

<script type="text/javascript">
//Check cookie in javascript

function cookieEnabled(){
	if (!navigator.cookieEnabled) {
		window.alert("Cookie must be enabled!");
		window.stop();
	}
}
</script>


