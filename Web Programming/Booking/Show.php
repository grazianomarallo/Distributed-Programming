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
$totreq = computeTotalRequest();
$totass =computeTotalAssign();
$user= $_SESSION['user'];
echo"


<div id='nav'>
<ul>
<li><a href='Booking.php'>Booking</a></li>
<li><a class='active' href='Show.php'>All Bookings</a></li>
<li><a href='Logout.php'>Logout</a></li>
</ul>
</div>

<div id='central'>

<h2>All appointements</h2>";
showBooking();
echo "
<h3>Total number of minutes requested: $totreq </h3>
<h3>Total number of minutes assigned: $totass </h3>
</div>
<div id='footer'><h4>$user</h4><br></div>
</body>
</html>";
?>