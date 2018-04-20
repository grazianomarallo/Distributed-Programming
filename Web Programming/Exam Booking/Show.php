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
$totreq = computeTotalBooking();
$totass =computeGrandTotal();
$user= $_SESSION['user'];
echo "


<div id='nav'>
<ul>
<li><a href='Booking.php'>Booking</a></li>
<li><a class='active' href='Show.php'>All Bookings</a></li>
<li><a href='Logout.php'>Logout</a></li>
</ul>
</div>

<div id='central'>

<h2>Booking infos</h2>";
showBooking();
$calls = computeTotalBooking();
$grandtot =computeGrandTotal();

echo "<table id ='booking'><tr><th>Call #1</th><th> Call #2  </th><th> Call #3</th><th>Grand Total</th></tr>";
echo "<tr><td>" . $calls['call1'] ."</td><td>". $calls['call2']."</td><td>".$calls['call3'] ."</td><td>".$grandtot ."</td></table>";
echo "</div>
<div id='footer'><h4>$user</h4><br></div>
</body>
</html>";
?>