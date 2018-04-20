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

$user= $_SESSION['user'];



if (isset($_POST['post']) && isset($_POST['ticket']) && isset($_POST['usergift'])  ) {
    $ticket = sanitizeString ( $_POST ['ticket'] );


    /*
     * check if the user input is a number, if it is not contains . or ,. If this condition are verified
     * the algorithm for booking start, otherwise warning message are sent to user and no operation is performed
     */
    if (is_numeric($ticket) == true && $ticket >0 && strrpos($ticket,".") == false && strrpos($ticket,",") == false){
        $temp= getTotalTicket($_POST['usergift']);
        $tot_ticket = $temp['tot_ticket'];

        /*
        * Case 1: No ticket has been purchased yet from the current user
        * A new tuple is inserted in the table with the correspondent number of ticket
        */
        if($tot_ticket == 0) {
            if($ticket >10)
                $mess = "You cannot buy more than 10 tickets";
            else {

                if(checkUserExistenceGift($_POST['usergift'])== false)
                    $mess="The selected user does not exist";
                else {
                    checkUserExistence($_POST['usergift']);
                    purchaseGiftTicket($_SESSION['user'], $_POST['usergift'], $ticket);
                    updateTicket($ticket, $_POST['usergift']);
                    $mess = "Gift purchasing succesfully done";
                }
            }
        }

        /*
         * Case 2: the maximum number of ticket has been already purchased
         * Nothing is send to the server and the user is alerted with a message
         */
        else if( $tot_ticket > 10){
            $mess="No more ski-pass can be purchased. Limit reached.";
        }
        /*
         * Case 3: A number of ticket has already been purchased, so the requested ticked are
         * updated for the current user. In case are requested more ticket than maximum allowed
         * the ticket hold by the user the booking is not done and the user is alerted
         * with a message
         */
        else {
            $temp_tot = $tot_ticket+$ticket;
            if($temp_tot <11) {
                if(checkUserExistenceGift($_POST['usergift'])== false)
                    $mess="The selected user does not exist";
                else {
                    purchaseGiftTicket($_SESSION['user'], $_POST['usergift'], $ticket);
                    updateTicket($ticket, $_POST['usergift']);
                    $mess = "Gift purchasing succesfully done";
                }
            }
            else
                $mess="The gift cannot be done bacause limit is overwhelmed";

        }
        /*
         * Case 4: User try to buy a ticket without inserting  the number
         */
    }else $mess="You have to insert a positive number in range 1 to 10";



}
else $mess="Insert a number of ticket to buy";


$user= $_SESSION['user'];
if(isset($_SESSION['msg']))
    $msg = $_SESSION['msg'];
$gift= getTotalGiftDone($_SESSION['user']);
$welcome_g= "No gift has been done yet";

?>



<div id='nav'>
    <ul>
        <li><a class='active' href='Gift.php'>Gift</a></li>
        <li><a href='Booking.php'>Purchase</a></li>
        <li><a href='Logout.php'>Logout</a></li>
    </ul>
</div>

<div id='central'>

    <div id='img'>
        <img src='userA.png' alt='user' width='150' height='150'>
    </div>

    <h3>Logged in as: <?php echo $user;?></h3>

    <form method='post' action='Gift.php'>

        <h2>Buy a ski-pass ticket for a friend</h2>
      <!--  <p><?/* showGift($user)*/?></p>
             <select name="usergift"  >
          <?/*
          $list_user = getUsers();
          $arr = $list_user;
          //delete the current user from array before setting into dropdown list
          for($i=0; $i< sizeof($arr); $i++){
              if($arr[$i] == $user)
                  unset($arr[$i]);
          }

          foreach( $arr as $value){ */?>
              <option  value="<?/*echo $value*/?>"><?/*echo $value*/?></option>
         <?/* }*/?>
      </select><br> -->
        <p><? showGift($user);
           /*if($gift['num_ticket'] == 0) {echo $welcome_g; $welcome_g="";}
            else if ($gift['num_ticket'] != 0 ) { echo " You have gifted ". $gift['gifted'] ." with ".$gift['num_ticket']." tickets" ;}*/?></p>
        <input type="text" placeholder="Insert the name of the user that has to receive your gift" name="usergift" required><br>
        <input type="number" placeholder="# of ticket to buy" name="ticket" step='1' min='1' max='10'><br>
        <button id='buttonB' type="submit" name='post'>Buy for a friend</button> <br>
        <p><div id='alert'><?php if(isset($msg)){ echo $msg; unset($msg);} else if(isset($mess)) {echo $mess; $mess=""; }?></div></p>



    </form>
</div>



<div id="footer">
</div>
</body>
</html>