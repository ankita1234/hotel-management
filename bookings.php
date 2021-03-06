<?php
session_start();

error_reporting(E_ALL & ~E_NOTICE);
include_once("login_check.inc.php");
include_once ("queryfunctions.php");
include_once ("functions.php");

access("booking"); //check if user is allowed to access this page
$conn=db_connect(HOST,USER,PASS,DB,PORT);

//if page was visited through a search hyperlin.
if (isset($_GET["search"]) && !empty($_GET["search"])){
	find($_GET["search"]);
}

//consider having this as a function in the functions.php
if (isset($_POST['Navigate'])){
	//echo $_SESSION["strOffSet"];
	$nRecords=num_rows(mkr_query("select * from guests",$conn),$conn);
	paginate($nRecords);
	free_result($results);
	find($_SESSION["strOffSet"]);
}

$guestid=$_POST['guestid'];  //forgotten what this does

if (isset($_POST['Submit'])){
	$action=$_POST['Submit'];
	switch ($action) {
		case 'Book Guest':
			//if guest has not been selected exit
			// instantiate form validator object
			$fv=new formValidator(); //from functions.php
			if (empty($_POST["guestid"])){ //if no guest has been selected no point in displaying other errors
				$fv->validateEmpty('guestid','Sorry no guest information is available for booking');
			}else{
				$fv->validateEmpty('no_adults','Please indicate number of people booking');
				$fv->validateEmpty('booking_type','Please indicate if it\'s a Direct booking or Agent booking.');
				$fv->validateEmpty('meal_plan','Please select Meal Plan');
				$fv->validateEmpty('roomid','Please indicate room being booked');
			}

			if($fv->checkErrors()){
				// display errors
				echo "<div align=\"center\">";
				echo '<h2>Resubmit the form after correcting the following errors:</h2>';
				echo $fv->displayErrors();
				echo "</div>";
			}
			else {
				$booking_type=$_POST["booking_type"];
				$meal_plan=$_POST["meal_plan"];
				$no_adults=$_POST["no_adults"];
				$no_child= !empty($_POST["no_child"]) ? $_POST["no_child"] : 'NULL';
				$checkin_date= "'" . $_POST["checkin_date"] . "'" ;
				$checkout_date=!empty($_POST["checkout_date"]) ? "'" . $_POST["checkout_date"] . "'" : 'NULL';
				$residence_id=$_POST["residence_id"];
				$payment_mode=$_POST["payment_mode"];
				$agents_ac_no=!empty($_POST["agents_ac_no"]) ? $_POST["agents_ac_no"] : 'NULL';
				$roomid=$_POST["roomid"];
				$checkedin_by=1; //$_POST["checkedin_by"];
				$invoice_no=!empty($_POST["invoice_no"]) ? $_POST["invoice_no"] : 'NULL';
				$sql="INSERT INTO booking (guestid,booking_type,meal_plan,no_adults,no_child,checkin_date,checkout_date,
					residence_id,payment_mode,agents_ac_no,roomid,checkedin_by,invoice_no,billed)
				 VALUES($guestid,'$booking_type','$meal_plan',$no_adults,$no_child,$checkin_date,$checkout_date,
					'$residence_id',$payment_mode,$agents_ac_no,$roomid,$checkedin_by,$invoice_no,0)";
				$results=mkr_query($sql,$conn);
				if ((int) $results==0){
					//should log mysql errors to a file instead of displaying them to the user
					echo 'Invalid query: ' . mysql_errno($conn). "<br>" . ": " . mysql_error($conn). "<br>";
					echo "Guests NOT BOOKED.";  //return;
				}else{
					echo "<div align=\"center\"><h1>Guests successful checked in.</h1></div>";
					//create bill - let user creat bill/create bill automatically
					$sql="INSERT INTO bills (book_id,billno,date_billed) select booking.book_id,booking.book_id,booking.checkin_date from booking where booking.billed=0";
					$results=mkr_query($sql,$conn);
					$msg[0]="Sorry no bill created";
					$msg[1]="Bill successfull created";
					AddSuccess($results,$conn,$msg);

					//if bill succesful created update billed to 1 in bookings- todo
					$sql="Update booking set billed=1 where billed=0"; //get the actual updated book_id, currently this simply updates all bookings
					$results=mkr_query($sql,$conn);
					$msg[0]="Sorry Booking not updated";
					$msg[1]="Booking successful updated";
					AddSuccess($results,$conn,$msg);

					//mark room as booked
					$sql="Update rooms set status='B' where roomid=$roomid"; //get the actual updated book_id, currently this simply updates all bookings
					$results=mkr_query($sql,$conn);
					$msg[0]="Sorry room occupation not marked";
					$msg[1]="Room marked as occupied";
					AddSuccess($results,$conn,$msg);
				}
			}
			find($guestid);
			break;
		case 'Find':
			//check if user is searching using name, payrollno, national id number or other fields
			$search=$_POST["search"];
			find($search);
			$sql="Select guests.guestid,guests.lastname,guests.firstname,guests.middlename,guests.pp_no,
			guests.idno,guests.countrycode,guests.pobox,guests.town,guests.postal_code,guests.phone,
			guests.email,guests.mobilephone,countries.country
			From guests
			Inner Join countries ON guests.countrycode = countries.countrycode where pp_no='$search'
			LIMIT $strOffSet,1";
			$results=mkr_query($sql,$conn);
			$bookings=fetch_object($results);
			break;
	}
}

function find($search){
	global $conn,$bookings;
	$search=$search;
	$strOffSet=!empty($_POST["strOffSet"]) ? $_POST["strOffSet"] : 0; //offset value peacked on all pages with pagination - logical error

	//check on wether search is being done on idno/ppno/guestid/guestname
	$sql="Select guests.guestid,concat_ws(' ',guests.firstname,guests.middlename,guests.lastname) as guest,guests.pp_no,
		guests.idno,guests.countrycode,guests.pobox,guests.town,guests.postal_code,guests.phone,guests.email,guests.mobilephone,
		countries.country,booking.book_id,booking.guestid,booking.booking_type,booking.meal_plan,booking.no_adults,booking.no_child,
		booking.checkin_date,booking.checkout_date,booking.residence_id,booking.payment_mode,booking.agents_ac_no,booking.roomid,
		booking.checkedin_by,booking.invoice_no,booking.billed,booking.checkoutby,booking.codatetime,DATEDIFF(booking.checkout_date,booking.checkin_date) as no_nights
		From guests
		Inner Join countries ON guests.countrycode = countries.countrycode
		Inner Join booking ON guests.guestid = booking.guestid
		where booking.book_id='$search'";
	$results=mkr_query($sql,$conn);
	$bookings=fetch_object($results);
}

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<link href="css/new.css" rel="stylesheet" type="text/css">
<title>Hotel Management Information System</title>

<script type="text/javascript">
<!--
var request;
var dest;

function loadHTML(URL, destination){
    dest = destination;
	if (window.XMLHttpRequest){
        request = new XMLHttpRequest();
        request.onreadystatechange = processStateChange;
        request.open("GET", URL, true);
        request.send(null);
    } else if (window.ActiveXObject) {
        request = new ActiveXObject("Microsoft.XMLHTTP");
        if (request) {
            request.onreadystatechange = processStateChange;
            request.open("GET", URL, true);
            request.send();
        }
    }
}

function processStateChange(){
    if (request.readyState == 4){
        contentDiv = document.getElementById(dest);
        if (request.status == 200){
            response = request.responseText;
            contentDiv.innerHTML = response;
        } else {
            contentDiv.innerHTML = "Error: Status "+request.status;
        }
    }
}

function loadHTMLPost(URL, destination, button){
    dest = destination;
	//if (document.getElementById('roomid')==""){
		room = document.getElementById('roomid').value;
		str ='roomid='+ room;

		//get occupancy (no of adults/children) - should not be empty
		str =str + '&no_adults=' + (document.getElementById('no_adults').value);

		//get meal plan - should not be empty
		for (i=0;i<document.bookings.meal_plan.length;i++){
		if (document.bookings.meal_plan[i].checked==true)
			//alert(document.bookings.meal_plan[i].value);
			str =str + '&meal_plan=' + document.bookings.meal_plan[i].value;
		}

		//get direct/agent booking - should not be empty
		for (i=0;i<document.bookings.booking_type.length;i++){
		if (document.bookings.booking_type[i].checked==true)
			//alert(document.bookings.booking_type[i].value);
			str =str + '&booking_type=' + document.bookings.booking_type[i].value;
		}

		//alert(document.getElementById['agent']);
		/*if (document.getElementById('agent')!==null){
			s=document.getElementById['agent'].value;
		}*/

	//}
	var str = str + '&button=' + button;

	if (window.XMLHttpRequest){
        request = new XMLHttpRequest();
        request.onreadystatechange = processStateChange;
        request.open("POST", URL, true);
        request.setRequestHeader("Content-Type","application/x-www-form-urlencoded; charset=UTF-8");
		request.send(str);
    } else if (window.ActiveXObject) {
        request = new ActiveXObject("Microsoft.XMLHTTP");
        if (request) {
            request.onreadystatechange = processStateChange;
            request.open("POST", URL, true);
            request.send();
        }
    }
}

function RatesPeacker(){
	window.open ('rates.html', 'newwindow', config='height=100,width=400, toolbar=no, menubar=no, scrollbars=no, resizable=no,location=no, directories=no, status=no');
}

//have this in cal2.js - get date differences
function nights(){
date2=(document.getElementById('departuredate').value);
date1=(document.getElementById('arrivaldate').value);
document.getElementById('no_nights').value=date2-date1;
}
//-->
</script>
<script language="javascript" src="js/cal2.js">
/*
Xin's Popup calendar script-  Xin Yang (http://www.yxscripts.com/)
Script featured on/available at http://www.dynamicdrive.com/
This notice must stay intact for use
*/
</script>
<script language="javascript" src="js/cal_conf2.js"></script>
</head>

<body>
<form action="bookings.php" method="post" name="bookings" enctype="multipart/form-data">
<table width="100%"  border="0" cellpadding="1" bgcolor="#66CCCC" align="center">
  <tr valign="top">
    <td width="19%" bgcolor="#FFFFFF">
	<table width="100%"  border="0" cellpadding="1">
	  <tr>
    <td width="15%" bgcolor="#66CCCC">
		<table cellspacing=0 cellpadding=0 width="100%" align="left" bgcolor="#FFFFFF">
      <tr><td width="110" align="center"><a href="index.php"><img src="images/titanic1.gif" width="70" height="74" border="0"/><br>
          Home</a></td>
      </tr>
      <tr><td>&nbsp; </td>
      </tr>
      <tr>
        <td align="center">
		<?php signon(); ?>
		</td></tr>
	  </table></td></tr>
		<?php require_once("menu_header.php"); ?>
    </table>
	</td>

    <td width="81%" bgcolor="#FFFFFF"><table width="100%"  border="0" cellpadding="1">

      <tr>
        <td>
		<H2>GUEST REGISTRATION CARD (BOOKINGS)</H2>
		</td>
      </tr>
      <tr>
        <td><div id="Requests">  <table width="100%"  border="0" cellpadding="1" align="left">
    <tr>
      <td colspan="3">
	    <table border="1" cellpadding="0">
          <tr>
            <td width="78"><input type="submit" name="Navigate" id="First" style="cursor:pointer" title="first page" value="<<"/>
                <input type="submit" name="Navigate" id="Previous" style="cursor:pointer" title="previous page" value="<"/>
            </td>
            <td width="241" align="center" bgcolor="#FFFFFF"><h3><?php echo trim($bookings->guest); ?></h3></td>
            <td width="79"><input type="submit" name="Navigate" id="Next" style="cursor:pointer" title="next page" value=">"/>
                <input type="submit" name="Navigate" id="Last" style="cursor:pointer" title="last page" value=">>"/>
            </td>
          </tr>
        </table></td>
	<td width="35%"><input type="button" name="Submit" value="Booking List" onclick="self.location='bookings_list.php'"/>
	  <input type="button" name="Submit" value="Booking Calendar" onclick="self.location='bookings_calendar.php'"/></td>
	</tr>
    <tr>
      <td width="13%">Card No.</td>
      <td width="35%"><input type="text" name="book_id" value="<?php echo trim($bookings->book_id); ?>" size="10" readonly=""/><input name="guestid" type="hidden" value="<?php echo trim($bookings->guestid); ?>" />
         </td>
      <td colspan="2"><table width="100%"  border="0" cellpadding="1">
        <tr>
          <td width="10%">Adult</td>
          <td width="15%"><input type="text" name="no_adults" id="no_adults" size="8" value="<?php echo trim($bookings->no_adults); ?>"/></td>
          <td width="10%">Child</td>
          <td width="16%"><input type="text" name="no_child" id="no_child" size="8" value="<?php echo trim($bookings->no_child); ?>"/></td>
		  <td width="22%">Total in Pty</td>
          <td width="27%"><input type="text" name="total_pty" size="8"/></td>
        </tr>
      </table></td>
    </tr>
    <tr>
      <td>Address</td>
      <td bgcolor="#66CCCC" colspan="2">
	  <?php
	   echo "P. O. Box " . trim($bookings->pobox) ."<br>";
	   echo trim($bookings->town) . "-" . trim($bookings->postal_code);
	   ?>
		</td>
      <td>&nbsp;</td>
    </tr>
    <tr>
      <td>Passport/ID No. </td>
      <td><input type="text" name="pp_id_no" readonly="" value="<?php echo (!is_null($bookings->pp_no) ? $bookings->pp_no : $bookings->idno); ?>" /></td>
      <td width="17%">Arrival  Date </td>
      <td><input type="text" name="checkin_date" id="arrivaldate" readonly="" value="<?php echo (isset($_POST["checkin_date"]) ? $_POST["checkin_date"] : trim($bookings->checkin_date)); ?>"/>
        <a href="javascript:showCal('Calendar3')"> <img src="images/ew_calendar.gif" width="16" height="15" border="0"/></a></td>
    </tr>
    <tr>
      <td>Nationality</td>
      <td><input type="text" name="country" readonly="" value="<?php echo trim($bookings->country); ?>"/></td>
      <td>Departure  Date</td>
      <td><input type="text" name="checkout_date" id="departuredate" readonly="" value="<?php echo trim($bookings->checkout_date); ?>" onblur="nights()"/>
          <small><a href="javascript:showCal('Calendar4')"> <img src="images/ew_calendar.gif" width="16" height="15" border="0"/></a></small></td>
    </tr>
    <tr>
      <td>Telephone</td>
      <td><input type="text" name="phone" readonly="" value="<?php echo trim($bookings->phone); ?>"/></td>
      <td>No. of Nights </td>
      <td><input type="text" name="no_nights" id="no_nights" size="10" readonly="" value="<?php echo trim($bookings->no_nights); ?>"/></td>
    </tr>
    <tr>
      <td>Remarks</td>
      <td><table width="108%"  border="0" cellpadding="1">
        <tr>
          <td width="28%"><label><input type="radio" name="meal_plan" id="mealplan" value="BO" <?php echo ($bookings->meal_plan=="BO" ? "checked=\"checked\"" : ""); ?> />
            BO</label></td>
          <td width="25%"><label><input type="radio" name="meal_plan" id="mealplan" value="BB" <?php echo ($bookings->meal_plan=="BB" ? "checked=\"checked\"" : ""); ?> />
            BB</label></td>
          <td width="24%"><label><input type="radio" name="meal_plan" id="mealplan" value="HB" <?php echo ($bookings->meal_plan=="HB" ? "checked=\"checked\"" : ""); ?> />
            HB</label></td>
          <td width="23%"><label><input type="radio" name="meal_plan" id="mealplan" value="FB" <?php echo ($bookings->meal_plan=="FB" ? "checked=\"checked\"" : ""); ?> />
            FB</label></td>
        </tr>
      </table></td>
	  <td>Country of Residence</td>
	  <td><select name="residence_id">
        <option value="">Select Country</option>
        <?php populate_select("countries","countrycode","country",$booking->residence_id);?>
      </select></td>
    </tr>
    <tr>
      <td>Accounts will be settled by </td>
      <td colspan="3">
        <label><input type="radio" name="payment_mode" value="1" <?php echo ($bookings->payment_mode=="1" ? "checked=\"checked\"" : ""); ?> />i) Cash</label><br />
        <label><input type="radio" name="payment_mode" value="2" <?php echo ($bookings->payment_mode=="2" ? "checked=\"checked\"" : ""); ?> />ii) Credit Card</label><br />
        <label><input type="radio" name="payment_mode" value="3" <?php echo ($bookings->payment_mode=="3" ? "checked=\"checked\"" : ""); ?> />iii) Cheque by prior arrangements</label><br />
        <label><input type="radio" name="payment_mode" value="4" <?php echo ($bookings->payment_mode=="4" ? "checked=\"checked\"" : ""); ?> />iv) Charge to Co.</label>
      </td>
    </tr>
    <tr>
      <td>Booking Type</td>
      <td><p>
        <label>
<input type="radio" name="booking_type" value="D" <?php echo ($bookings->booking_type=="D" ? "checked=\"checked\"" : ""); ?> onclick="loadHTMLPost('ajaxfunctions.php','agentoption','Direct')"/>
Direct booking </label>
        <label>
        <input type="radio" name="booking_type" value="A" <?php echo ($bookings->booking_type=="A" ? "checked=\"checked\"" : ""); ?> onclick="loadHTMLPost('ajaxfunctions.php','agentoption','Agent')"/>
  Agent booking</label>
      </p></td>
	  <td colspan="2"><div id="agentoption"></div></td>
    </tr>
    <tr>
      <td>Room No. </td>
      <td>
	  <div id="showrates"></div>
	  <select name="roomid" id="roomid" onchange="loadHTMLPost('ajaxfunctions.php','showrates','GetRates')">
        <option value="" >Select Room</option>
        <?php populate_select("rooms","roomid","roomno",$bookings->roomid);?>
      </select> </td>
      <td>Rate Kshs.</td>
      <td><input type="text" name="rates" /></td>
    </tr>
    <tr>
      <td colspan="2"><h2>Checked in By</h2></td>
      <td colspan="2">&nbsp;</td>
    </tr>
    <tr>
      <td>Name</td>
      <td><input type="text" name="checkedin_by" value="<?php echo $_SESSION["employee"]; ?>" /></td>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
    </tr>
    <tr>
      <td>Invoice No. </td>
      <td><input type="text" name="invoice_no" value="<?php echo $_SESSION["invoice_no"]; ?>" /></td>
      <td><input type="submit" name="Submit" value="Book Guest"/></td>
      <td><input type="button" name="Submit" value="Prepare Bill" onclick="RatesPeacker()"/></td>
    </tr>
  </table>
		</div></td>

      </tr>
	  <tr bgcolor="#66CCCC" >
        <td align="left">
		<div id="booking_list"></div>
		</td>
      </tr>
    </table></td>
  </tr>
   <?php require_once("footer1.php"); ?>
</table>
</form>
</body>
</html>
