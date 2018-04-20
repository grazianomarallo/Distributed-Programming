<?php
include "util.php";
/*
 * Verify if the user is in session, if not redirect to home page
 */
if(userLoggedIn() == false){
	//if the user is not logged in is redirect to main page
	header ( "Location: ". "index.php" );
}

include "Header.php";
$freetime = getFreeTime();
if (isset($_POST['post']) && isset($_POST['book'])) {
	$book = sanitizeString ( $_POST ['book'] );
	if (is_numeric($book) == true && $book >0 && strrpos($book,".") == false && strrpos($book,",") == false)  {	
		if ($book <=180) {
			/*
			 * Case 1: User request a number of minutes lower or equal to the number
			 * of free minutes. All minutes request is assigned
			 */
			if($book <= $freetime['av_time'] && $freetime['av_time'] >0){
				performBooking( $_SESSION ['user'], $book, $book, date ("Y-m-d H:i:s", time ()));
				updateFreeTime($book,$freetime['av_time']);
			
			}
			/*
			 * Case 2: User request a number of minutes greater than the available minutes.
			 * The booking is performed but a reduction to everyone is applied.
			 */
			else if ($freetime['av_time'] >0 && $book >= $freetime['av_time']){
				performBooking( $_SESSION ['user'], $book, $book, date ("Y-m-d H:i:s", time ()));
				updateFreeTime($book,$freetime['av_time']);
				reduceTime();
			}
			/*
			 * Case 3: User request a number of minutes but no minutes are available
			 * so the booking is not performed
			 */
			else if ($freetime['av_time'] ==0){
				$mess="No free spots remained.";
			}
		} else { // here the minutes inserted by the user is not valid, so no operation are perfomed
			// and the user is advertised
			$mess="Your booking cannot be greater than 180 minutes";
		}
	}else{
		$mess="Only positive numbers are allowed.";
	}
}

if (isset($_POST['delete'])) {
	deleteBooking($_SESSION['user']);
	$tot=computeTotalRequest();
	if ($tot > 180)
		reduceTime();
	else 
	computeTime();
	restoreFreeTime($freetime, $tot);
	
}



$user= $_SESSION['user'];
if(isset($_SESSION['msg']))
$msg = $_SESSION['msg'];
$timeRange = getAppointment($user);
$ftime= getFreeTime();


?>
<div id='nav'>
	<ul>
	<li><a class='active' href='Booking.php'>Booking</a></li>
	<li><a href='Show.php'>All Bookings</a></li>
	<li><a href='Logout.php'>Logout</a></li>
	</ul>
</div>

<div id='central'>

	<div id='img'>
		<img src='userA.png' alt='user' width='150' height='150'>
	</div>

	<h3>Logged in as: <?php echo $user;?></h3>

	<form method='post' action='Booking.php'>
		<h2>Your current appointement: <?php if(isset($timeRange))  echo $timeRange; else echo "No booking made yet";?> </h2>
		<h2>Free time left: <?php echo $ftime['av_time']; ?></h2>
		<h2>Insert a number of minutes for your appointement:</h2>
		<input type="number" placeholder="# of minutes" name="book" step='1' min='1' max='180'><br>
		<button id='button' type="submit" name='post'>Confirm booking</button><br>
		<button id='buttonB' type="submit" name='delete'>Delete</button>
		<p><div id='alert'><?php if(isset($msg)){ echo $msg; unset($msg);} else if(isset($mess)) {echo $mess; $mess=""; }?></div></p>

</form>
</div>

<div id="footer"></div>
</body>
</html>