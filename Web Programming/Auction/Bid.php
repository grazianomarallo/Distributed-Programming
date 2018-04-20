<?php
include "Header.php";



/*
 * Verify if the user is in session, if not redirect to home page
 */
if(userLoggedIn() == false){
	//if the user is not logged in is redirect to main page
	header ( "Location: ". "index.php" );
}


if ($_SERVER ["REQUEST_METHOD"] == "POST") {
	$postbid = sanitizeString ( $_POST ['bid'] );
	if (is_numeric ( $postbid) == true && strlen(substr(strrchr($postbid, "."), 1)) <= 2) {
		$bid = getBid ();
		$maxbid = $bid ['maxbid'];
		
		if (((float)$postbid) > ((float)$maxbid)) {
			/*
			 * After verified if the posted bid is greater than the system one, we can check
			 * each case
			 */
			// Case 1: No bid has been done yet, so the bid is inserted in the db, the maxBid is updated
			// and the user became the highest bidder
			if (! isset ( $bid ['userid'] )) {
				insertBid ( $_SESSION ['userid'], (( float ) $maxbid) );
				insertThr ( $_SESSION ['userid'], $postbid, date ( "Y-m-d H:i:s", time () ) );
				mysqli_commit ( $connection );
				$ismaxBidder = "You're the highest bidder";
			} else {
				/*
				 * Case 2: Two user have done the same offer, the one who did first
				 * remain the highest bidder and thr is not increased by 0.01
				 * The user in session is also advertised for not beeing the highest bidder
				 */
				
				$maxthr = getMaxThr ();
				if ($postbid == $maxthr ['thr']) {
					insertBid ( $bid ['userid'], $maxthr ['thr'] );
					$bidExeceed = "Bid exceeded";
				}				/*
				 * Case 3: The user continue to insert a thr higher than maxBid. The thr is updated
				 * but the maxBid remains fixed to the last value
				 */
				elseif ($postbid > $bid ['maxbid'] && $_SESSION ['userid'] == $bid ['userid']) {
					insertBid ( $_SESSION ['userid'], $bid ['maxbid'] );
					$ismaxBidder = "You're the highest bidder";
				}				
				/*
				 * Case 4: The inserted thr is smaller than maxBid, so the current thr is increased
				 * by 0.01 and is inserted as BID
				 */
				elseif ($postbid < $maxthr ['thr']) {
					insertBid ( $bid ['userid'], $postbid + 0.01 );
					$bidExeceed = "Bid exceeded";
				}				/*
				 * Case 5: the inserted thr is greater than maxBid, so the maxThr is increased by 0.01
				 * and inserted as maxBid
				 */
				elseif ($postbid > $maxthr ['thr']) {
					insertBid ( $_SESSION ['userid'], $maxthr ['thr'] + 0.01 );
					$ismaxBidder = "You're the highest bidder";
				}
				// After one on the 5 cases is verified the posted thr is inserted in db
				insertThr ( $_SESSION ['userid'], $postbid, date ( "Y-m-d H:i:s", time () ) );
				mysqli_commit ( $connection );
			}
		} else { // here the bid inserted by the user is not valid, so no operation are perfomed
		       // and the user is advertised with a pop up message
			echo "
		<script type='text/javascript'>
			alert('Bid cannot be smaller than current one.');
		</script>
			";
			$errmsg="Bid cannot be smaller than current one";
		}
	}else{
		echo "
		<script type='text/javascript'>
			alert('Insert a valid number!.');
		</script>
			";
	}
}


//Update the value of maxBid and current thr for the user in session
//after all the operation of the system has been performed
$bid = getBid ();
$maxbid = $bid ['maxbid'];
$user= $_SESSION['user'];
$thr= getCurrentThr($user);
?>
<div id='nav'>
	<ul>
	<li><a class='active' href='Bid.php'>Bid</a></li>
	<li><a href='Logout.php'>Logout</a></li>
	</ul>
</div>

<div id='central'>

	<div id='img'>
	<img src='userA.png' alt='user' width='150' height='150'>
	</div> 
	
	<h3>Logged in as: <?php echo $user;?></h3>
	
	<form method='post' action='Bid.php'>
	<h2>Current maximum bid is: <?php echo $maxbid;?> € done by <?php  $var= getNameHighBid($bid['userid']);
	 $name= $var['user']; if(isset($name)) echo $name; else echo "nobody";?></h2>
	<h2>Your current bid: <?php if(isset($thr))  echo $thr; else echo 0;?> €</h2>

	<h2>Set a new bid</h2>
	<input type="number" placeholder="0.00" name="bid" step='0.01' min='0'> €<br>
	<button id='buttonB'  type="submit">Make your bid</button>
	

		<?php 
	if(isset($ismaxBidder))
		echo " 
				<div id=high>
		<h3> $ismaxBidder </h3>

		</div>";
		elseif(isset($bidExeceed))
		echo "
			<div id=exceed>
		<h3> $bidExeceed</h3>
			</div>	";
			
		if(isset($errmsg))
			echo "
			<p> $errmsg	</p>";
		
	?>
	
	
	</form>
</div>
<?php include "Footer.php";?>
		
