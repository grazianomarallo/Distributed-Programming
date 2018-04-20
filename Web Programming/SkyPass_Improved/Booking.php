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



if (isset($_POST['post']) && isset($_POST['ticket'])) {
    $ticket = sanitizeString ( $_POST ['ticket'] );

    /*
     * check if the user input is a number, if it is not contains . or ,. If this condition are verified
     * the algorithm for booking start, otherwise warning message are sent to user and no operation is performed
     */
    if ((is_numeric($ticket) == true && $ticket >0 && strrpos($ticket,".") == false && strrpos($ticket,",") == false)){
        $temp = getTotalTicket($_SESSION['user']);
        $tot_ticket = $temp['tot_ticket'];

        /*
         * Case 1: No ticket has been purchased yet from the current user
         * A new tuple is inserted in the table with the correspondent number of ticket
         */
        if($tot_ticket == 0) {
            if($ticket >10)
                $mess = "You cannot buy more than 10 tickets";
            else {
                purchaseTicket($_SESSION['user'], $ticket, $ticket);
                $mess = "Purchasing succesfully done";
            }
        }
        /*
         * Case 2: the maximum number of ticket has been already purchased
         * Nothing is send to the server and the user is alerted with a message
         */
        else if( $tot_ticket > 10) {
            $mess = "No more ski-pass can be purchased. Limit reached.";
        }
        /*
        * Case 3: A number of ticket has already been purchased, so the requested ticked are
        * updated for the current user. In case are requested more ticket than maximum allowed
        * the ticket hold by the user the booking is not done and the user is alerted
        * with a message
        */
        else{
            $temp_tot = $tot_ticket+$ticket;
            if($temp_tot < 11) {
                purchaseTicket($_SESSION['user'], $ticket, $ticket);
            }
            else  $mess="The purchase cannot be done because limit is overwhelmed";

        }

    }

    /*
     * Case 4: User try to buy a ticket without inserting  the number
     */
    else $mess="You have to insert a positive number in range 1 to 10";
}
else $mess="Insert a number of ticket to buy";


$user= $_SESSION['user'];
if(isset($_SESSION['msg']))
    $msg = $_SESSION['msg'];
$welcome= "No ticket has been purchased yet";
$welcome_g= "No gift has been received yet";
$num_ticket = getTotalTicket($_SESSION['user']);
$gift= getTotalGift($_SESSION['user']);



?>
<div id='nav'>
    <ul>
        <li><a class='active' href='Booking.php'>Purchase</a></li>
        <li><a href='Gift.php'>Make a gift</a></li>
        <li><a href='Logout.php'>Logout</a></li>
    </ul>
</div>

<div id='central'>

    <div id='img'>
        <img src='userA.png' alt='user' width='150' height='150'>
    </div>

    <h3>Logged in as: <?php echo $user;?></h3>

    <form method='post' action='Booking.php'>

        <h2>Buy a ski-pass ticket</h2>
        <p><?php showPurchasedTicket($user);
            if($gift['num_ticket'] == 0) {echo $welcome_g; $welcome_g="";}
            else if ($gift['num_ticket'] != 0 ) { echo $gift['num_ticket']." tickets have been gifted from ". $gift['gifter'];}?></p>
        <input type="number" placeholder="# of ticket to buy" name="ticket" step='1' min='1' max='10' ><br>
        <button id='button' type="submit" name='post'> Purchase now!</button><br>
        <p><div id='alert'><?php if(isset($msg)){ echo $msg; unset($msg);} else if(isset($mess)) {echo $mess; $mess=""; }?></div></p>

    </form>
</div>

<div id="footer">
</div>
</body>
</html>