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
$seats = getAvailableSeat();


if (isset($_POST['post']) && isset($_POST['book1']) && isset($_POST['book2'])) {
	$book1 = sanitizeString ( $_POST ['book1'] );
    $book2 = sanitizeString ( $_POST ['book2'] );

    /*
     * check if the user input is a number, if it is not contains . or ,. If this condition are verified
     * the algorithm for booking start, otherwise warning message are sent to user and no operation is performed
     */
	if (is_numeric($book1) == true && $book1 >0 && strrpos($book1,".") == false && strrpos($book1,",") == false &&
        is_numeric($book2) == true && $book2 >0 && strrpos($book2,".") == false && strrpos($book2,",") == false){
		if ($book1 <=3 && $book2 <=3) {

        /*
         * After all verification on the user input have been done, the algorithm starts to check all possible cases.
         * Is verified each time which calls have been selected by the user.
         */
            if($book1== 1 && $book2== 2) {
                //Case 1: all seats are available, the requested calls are assigned
                if($seats['call1'] <3 && $seats['call2'] <3  ) {
                    bookFirstPreference($_SESSION ['user'], $book1, date("Y-m-d H:i:s", time()));
                    bookSecondPreference($_SESSION ['user'], $book2, date("Y-m-d H:i:s", time()));

                }
                //Case 2: Only one of the requested calls is available and only the possible one is assigned
                //the other one is set to 0
                else if( $seats['call1'] <3 && $seats['call2'] ==3){
                    bookFirstPreference($_SESSION ['user'], $book1, date("Y-m-d H:i:s", time()));

                }
                else if( $seats['call1'] ==3 && $seats['call2'] <3){
                    bookSecondPreference($_SESSION ['user'], $book2, date("Y-m-d H:i:s", time()));

                }

            }
            if($book1== 1 && $book2== 3) {
                if($seats['call1'] <3 && $seats['call3'] <3  ) {
                    bookFirstPreference($_SESSION ['user'], $book1, date("Y-m-d H:i:s", time()));
                    bookSecondPreference($_SESSION ['user'], $book2, date("Y-m-d H:i:s", time()));

                }

                else if( $seats['call1'] <3 && $seats['call3'] ==3){
                    bookFirstPreference($_SESSION ['user'], $book1, date("Y-m-d H:i:s", time()));
                }
                else if( $seats['call1'] ==3 && $seats['call3'] <3){
                    bookSecondPreference($_SESSION ['user'], $book2, date("Y-m-d H:i:s", time()));
                }


            }
            if($book1== 2 && $book2== 1) {
                if($seats['call2'] <3 && $seats['call1'] <3  ) {
                    bookFirstPreference($_SESSION ['user'], $book1, date("Y-m-d H:i:s", time()));
                    bookSecondPreference($_SESSION ['user'], $book2, date("Y-m-d H:i:s", time()));

                }

                else if( $seats['call2'] <3 && $seats['call1'] ==3){
                    bookFirstPreference($_SESSION ['user'], $book1, date("Y-m-d H:i:s", time()));

                }
                else if( $seats['call2'] ==3 && $seats['call1'] <3){
                    bookSecondPreference($_SESSION ['user'], $book2, date("Y-m-d H:i:s", time()));

                }
            }
            if($book1== 2 && $book2== 3) {
                if($seats['call2'] <3 && $seats['call3'] <3  ) {
                    bookFirstPreference($_SESSION ['user'], $book1, date("Y-m-d H:i:s", time()));
                    bookSecondPreference($_SESSION ['user'], $book2, date("Y-m-d H:i:s", time()));

                }

                else if( $seats['call2'] <3 && $seats['call3'] ==3){
                    bookFirstPreference($_SESSION ['user'], $book1, date("Y-m-d H:i:s", time()));

                }
                else if( $seats['call2'] ==3 && $seats['call3'] <3){
                    bookSecondPreference($_SESSION ['user'], $book2, date("Y-m-d H:i:s", time()));

                }

            }
            if($book1== 3 && $book2== 1) {
                if($seats['call3'] <3 && $seats['call1'] <3  ) {
                    bookFirstPreference($_SESSION ['user'], $book1, date("Y-m-d H:i:s", time()));
                    bookSecondPreference($_SESSION ['user'], $book2, date("Y-m-d H:i:s", time()));

                }

                else if( $seats['call3'] <3 && $seats['call1'] ==3){
                    bookFirstPreference($_SESSION ['user'], $book1, date("Y-m-d H:i:s", time()));
                }
                else if( $seats['call3'] ==3 && $seats['call1'] <3){
                    bookSecondPreference($_SESSION ['user'], $book2, date("Y-m-d H:i:s", time()));

                }

            }
            if($book1== 3 && $book2== 2) {

                if ($seats['call3'] < 3 && $seats['call2'] < 3) {
                    bookFirstPreference($_SESSION ['user'], $book1, date("Y-m-d H:i:s", time()));
                    bookSecondPreference($_SESSION ['user'], $book2, date("Y-m-d H:i:s", time()));

                } else if ($seats['call3'] < 3 && $seats['call2'] == 3) {
                    bookFirstPreference($_SESSION ['user'], $book1, date("Y-m-d H:i:s", time()));

                } else if ($seats['call3'] == 3 && $seats['call2'] < 3) {
                    bookSecondPreference($_SESSION ['user'], $book2, date("Y-m-d H:i:s", time()));
                }

            }

			/*
			 * Case 3: User request preferences but no seats are  available
			 * so the booking is not performed and user is advertised
			 */
			else if ($seats['call1'] ==3 && $seats['call2'] ==3 && $seats['call3'] ==3  ){
				$mess="No free seats remained.";
			}
			else if (($book1 ==3 && $book2 ==3) || ($book1 ==1 && $book2 ==1) || ($book1 ==2 && $book2 ==2))
                $mess = "You cannot choose the same exam call.";
		} else { // here the preferences inserted by the user is not valid, so no operation are perfomed
			// and the user is advertised
			$mess="Your preference can be only between 1,2 or 3.";
		}
	}else{
        if($book2==0 || $book1==0) $mess= "You have to express 2 preferences";
        else
	    	$mess="Only positive numbers are allowed.";
	}
}

/*
 * Here is checked if a delete has been done. If so the the current booking for the user in session
 * are retrieved and other verification are done. If just one preference has been accepted is selected and
 * deleted otherwise both the preferences are deleted and the seats accordingly updated
 *
 */
if (isset($_POST['delete'])) {

    $books = getCurrentBooking($_SESSION['user']);
    if($books['first']== 0)
        decrementSingle($books['second']);
    else if($books['second']== 0)
        decrementSingle($books['first']);
    else
    decrementAvailableSeat($books['first'],$books['second']);
	deleteBooking($_SESSION['user']);

}



$user= $_SESSION['user'];
if(isset($_SESSION['msg']))
$msg = $_SESSION['msg'];
$prenotation = getCurrentBooking($_SESSION['user']);
$welcome= "No preferences has been submitted yet";





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

		<h2>Insert two prefereces for exam call:</h2>
        <p><?php if($prenotation['first'] == 0 && $prenotation['second'] == 0) {echo $welcome; $welcome="";}
            else if ($prenotation['first'] == 0 )  echo "You're now booked for call #".$prenotation['second']."";
            else if ($prenotation['second'] == 0 )  echo "You're now booked for call #".$prenotation['first']."";
            else  { echo "You're now booked for call #".$prenotation['first']." and call #".$prenotation['second']  ;}?></p>
		<input type="number" placeholder="Calls #" name="book1" step='1' min='1' max='3'><br>
        <input type="number" placeholder="Calls #" name="book2" step='1' min='1' max='3'><br>
		<button id='button' type="submit" name='post'>Confirm booking</button><br>
		<button id='buttonB' type="submit" name='delete'>Delete</button>
		<p><div id='alert'><?php if(isset($msg)){ echo $msg; unset($msg);} else if(isset($mess)) {echo $mess; $mess=""; }?></div></p>


</form>
</div>

<div id="footer"></div>
</body>
</html>