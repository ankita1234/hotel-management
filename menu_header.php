<?php
session_start();

error_reporting(E_ALL & ~E_NOTICE);
include_once ("queryfunctions.php");
include_once ("functions.php");

$loginname=$_SESSION["loginname"];
$sql = "select userid,admin,guest,reservation,booking,agents,rooms,billing,rates,lookup,reports from users where loginname='$loginname'";
$conn=db_connect(HOST,USER,PASS,DB,PORT);
$results=mkr_query($sql,$conn);
$msg[0]="";
$msg[1]="";
AddSuccess($results,$conn,$msg);
$access = fetch_object($results);

//will be used to set access on pages
/*if !isset($_SESSION["access"]){
	$_SESSION["access"]
}*/

if($access->admin==1) echo "<tr><td><a href=\"admin.php\">Admin</a></td></tr>";
if($access->guest==1) echo "<tr><td><a href=\"guests.php\">Guests</a></td></tr>";
if($access->reservation==1) echo "<tr><td><a href=\"reservations.php\">Reservations</a></td></tr>";
if($access->booking==1) echo "<tr><td><a href=\"bookings.php\">Bookings</a></td></tr>";
if($access->agents==1) echo "<tr><td><a href=\"agents.php\">Agents</a></td></tr>";
if($access->rooms==1) echo "<tr><td><a href=\"rooms.php\">Rooms</a></td></tr>";
if($access->billing==1) echo "<tr><td><a href=\"billings.php\">Guest Bill</a></td></tr>";
if($access->rates==1) echo "<tr><td><a href=\"rates.php\">Rates</a></td></tr>";
if($access->lookup==1) echo "<tr><td><a href=\"lookup.php\">Lookups</a></td></tr>";
if($access->reports==1) echo "<tr><td><a href=\"reports.php\">Reports</a></td></tr>";
free_result($access);
?>
