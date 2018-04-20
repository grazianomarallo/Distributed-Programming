<?php
$dbhost  = 'localhost';    // Unlikely to require changing
$dbname  = 's238159'; //
$dbuser  = 'root';     // ...variables according
$dbpass  = '';//'icknobia';     // ...to your installation
$appname = "Ski-Pass"; // ...and preference

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

function getTotalTicket($user){
    global $connection;
    $empty =0;
    $query = "SELECT * FROM purchased WHERE user='$user' FOR UPDATE";
    if (mysqli_num_rows ( queryMysql ( $query ) ) == 0)
        return $empty;
    $result = queryMysql ( $query );
    $result = mysqli_fetch_array ( $result, MYSQLI_ASSOC );
    return $result;
    mysqli_free_result ( $result );
    mysqli_commit ( $connection );
}

function getTotalGift($user){
    global $connection;
    $empty =0;
    $query = "SELECT * FROM gift WHERE gifted='$user' FOR UPDATE";
    if (mysqli_num_rows ( queryMysql ( $query ) ) == 0)
        return $empty;
    $result = queryMysql ( $query );
    $result = mysqli_fetch_array ( $result, MYSQLI_ASSOC );
    return $result;
    mysqli_free_result ( $result );
    mysqli_commit ( $connection );
}

function getUsers(){
    global $connection;
    $empty =0;
    $query = "SELECT user FROM members";
    if (mysqli_num_rows ( queryMysql ( $query ) ) == 0)
        return $empty;
    $result = queryMysql ( $query );
    while ( $row = mysqli_fetch_array ( $result, MYSQLI_ASSOC ) )
        $arr [] = $row ['user'];
    return $arr;
    mysqli_free_result ( $result );
    mysqli_commit ( $connection );
}






/*
 * This function increment and update the single exam call seat checking all the 3 possibilities
 */
function updateTicket($ticket,$user){
    global $connection;

    try {
            $query = "UPDATE purchased set tot_ticket =tot_ticket+ '$ticket'  WHERE user='$user'";
            if (!queryMysql($query))
                throw new Exception ("Update Failed in update ticket--");
    }
    catch ( Exception $e ) {
        mysqli_rollback ( $connection );
        echo "Rollback  " . $e->getMessage ();
    }
    mysqli_commit($connection);
}


function updateGift($ticket,$user){
    global $connection;

    try {
        $query = "UPDATE gift set num_ticket =num_ticket+ '$ticket' WHERE gifted='$user'";
        if (!queryMysql($query))
            throw new Exception ("Update Failed in update gift ticket--");
    }
    catch ( Exception $e ) {
        mysqli_rollback ( $connection );
        echo "Rollback  " . $e->getMessage ();
    }
    mysqli_commit($connection);
}

function checkUserExistence($user){
    global $connection;

    try {
            $query = "SELECT * FROM purchased WHERE user='$user' FOR UPDATE";
            $row= mysqli_fetch_array(queryMysql($query),MYSQLI_ASSOC);
            if($row == 0) {
                $query = "INSERT INTO purchased (user,p_ticket,tot_ticket)
			VALUES ('$user','0','0') ";
                if (!queryMysql($query))
                    throw new Exception ("Insert Failed in existing user --");
            }

    } catch ( Exception $e ) {
        mysqli_rollback ( $connection );
        echo "Rollback  " . $e->getMessage ();
    }
    mysqli_commit ( $connection );
}



/**
 *  PURCHASE HANDLES
 */

function purchaseTicket($user, $p_ticket, $tot_ticket) {
    global $connection;

    try {
            if($user != null && $tot_ticket <= 10) {
                $query = "SELECT * FROM purchased WHERE user='$user' FOR UPDATE";
                $row= mysqli_fetch_array(queryMysql($query),MYSQLI_ASSOC);
                if($row == 0) {
                    $query = "INSERT INTO purchased (user,p_ticket,tot_ticket)
			VALUES ('$user','$p_ticket','$tot_ticket') ON DUPLICATE KEY UPDATE user='$user', p_ticket ='$p_ticket', tot_ticket='$tot_ticket' ";
                    if (!queryMysql($query))
                        throw new Exception ("Insert Failed in purchasing ticket--");
                }
                else{
                    $query = "UPDATE purchased set tot_ticket =tot_ticket+ '$tot_ticket',p_ticket=p_ticket +'$p_ticket' WHERE user='$user'";
                    if (!queryMysql($query))
                        throw new Exception ("Update Failed in update ticket--");
                }
            }
    } catch ( Exception $e ) {
        mysqli_rollback ( $connection );
        echo "Rollback  " . $e->getMessage ();
    }
    mysqli_commit ( $connection );
}

function purchaseGiftTicket($from, $to, $num_ticket) {
    global $connection;
    try {

        if($from != null && $num_ticket <= 10){
            $query = "SELECT * FROM gift WHERE gifter='$from' AND gifted='$to' FOR UPDATE";
            $row= mysqli_fetch_array(queryMysql($query),MYSQLI_ASSOC);
            if($row == 0) {
                $query = "INSERT INTO gift (gifter,gifted,num_ticket)
			VALUES ('$from','$to','$num_ticket') ON DUPLICATE KEY UPDATE gifter='$from', gifted='$to', num_ticket ='$num_ticket'";
                if (!queryMysql($query))
                    throw new Exception ("Insert Failed in gift purchasing --");

            }
            else{
                $query = "UPDATE gift set num_ticket =num_ticket+ '$num_ticket'";
                if (!queryMysql($query))
                    throw new Exception ("Update Failed in update gift ticket--");

            }
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
    $query = "SELECT user,p_ticket, tot_ticket FROM purchased ORDER BY user ASC FOR UPDATE";
    if (mysqli_num_rows ( queryMysql ( $query ) ) == 0)
        echo "<h2> No purchasing has been done yet</h2>  ";
    else {
        $result = queryMysql ( $query );
        echo "<table id ='booking'><tr><th>User</th><th>Purchased ticket</th><th> Total ticket</th></tr>";
        while ( $row = mysqli_fetch_array ( $result, MYSQLI_ASSOC ) ) {
            echo "<tr><td>" . $row ['user'] . "<td>" . $row ['p_ticket'] . "</td><td>" . $row ['tot_ticket']  . "</td>";
        }
        echo "</table>";
    }
    //mysqli_free_result ( $result );
    mysqli_commit ( $connection ); //not necessary
}

function showPurchasedTicket($user){
    global $connection;
    $query = "SELECT * FROM purchased  WHERE user='$user' ORDER BY user ASC FOR UPDATE ";
    if (mysqli_num_rows ( queryMysql ( $query ) ) == 0)
        echo "<p> No purchasing has been done yet</p>  ";
    else {
        $result = queryMysql ( $query );
        $ticket = getTotalTicket($_SESSION['user']);
        echo "<p><h3>You have earned ". $ticket['tot_ticket']. " total tickets:</p></h3> ";
        while ( $row = mysqli_fetch_array ( $result, MYSQLI_ASSOC ) )
            echo  "<p>". $row ['p_ticket'] . " tickets have been purchased by you ".  "</p>";

    }
    //mysqli_free_result ( $result );
    mysqli_commit ( $connection ); //not necessary
}

function showGift($user){
    global $connection;
    $query = "SELECT * FROM gift  WHERE gifter='$user' ORDER BY gifted ASC FOR UPDATE ";
    if (mysqli_num_rows ( queryMysql ( $query ) ) == 0)
        echo "<p2> No gift has been done yet</p2>  ";
    else {
        $result = queryMysql ( $query );
        echo "<p><h3>You have buyed:</p></h3> ";
        while ( $row = mysqli_fetch_array ( $result, MYSQLI_ASSOC ) )
            echo  "<p>". $row ['num_ticket'] . " tickets for user " . $row ['gifted'] . "</p>";

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
        if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > 20000)) {
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