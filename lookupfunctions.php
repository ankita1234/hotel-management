<?php
session_start();

error_reporting(E_ALL & ~E_NOTICE);
include_once ("queryfunctions.php");
include_once ("functions.php");

switch ($_POST["button"]){
	case "details":
		details();
		break;
	case "documents":
		doc_types();
		break;
	case "transactions":
		trans_types();
		break;
	case "paymode":
		payment_mode();
		break;
	case "roomtype":
		room_type();
		break;
}

function doc_types(){
	echo "<table width=\"100%\"  border=\"0\" cellpadding=\"1\">
	<tr>
	<td colspan=\"2\"><h2>Document Types </h2></td>
	</tr>
	<tr>
	<td width=\"25%\">Document ID. </td>
	<td width=\"75%\"><input type=\"text\" name=\"doc_id\" value=\"$doctypes->doc_id\" readonly=\"\" /></td>
	</tr>
	<tr>
	<td>Document Code </td>
	<td><input type=\"text\" name=\"doc_code\" value=\"$doctypes->doc_code\" /></td>
	</tr>
	<tr>
	<td>Document Type </td>
	<td><input type=\"text\" name=\"doc_type\" value=\"$doctypes->doc_type\" /></td>
	</tr>
	<tr>
	<td>Remarks</td>
	<td><input type=\"text\" name=\"remarks\" value=\"$doctypes->remarks\" /></td>
	</tr>
	<tr>
	<td colspan=\"2\"><h1>Applications applied</h1></td>
	</tr>
	<tr>
	<td>Accounts</td>
	<td><input type=\"checkbox\" name=\"accounts\" value=\"1\" /></td>
	</tr>
	<tr>
	<td>Payroll</td>
	<td><input type=\"checkbox\" name=\"cooperative\" value=\"1\" /></td>
	</tr>
	<tr>
	<td>Cooperative</td>
	<td><input type=\"checkbox\" name=\"payroll\" value=\"1\" /></td>
	</tr>
	<tr>
	<td><input type=\"submit\" name=\"Submit\" value=\"Add Document\" /></td>
	<td><input type=\"button\" name=\"Submit\" value=\"List Documents\" onclick=\"loadHTML('ajaxfunctions.php','RequestDetails','Documents')\"/></td>
	</tr>
	</table>
	<tr bgcolor=\"#66CCCC\" >
        <td align=\"left\" colspan=\"5\"><div id=\"RequestDetails\"></div></td>
     </tr>";
	}

function trans_types(){
	echo "<table width=\"100%\"  border=\"0\" cellpadding=\"1\">
	<tr>
	<td colspan=\"2\"><h2>Types of Transactions</h2></td>
	</tr>
	<tr>
	<td width=\"25%\">Transanction type ID. </td>
	<td width=\"75%\"><input type=\"text\" name=\"trans_id\" value=\"$transtypes->trans_id\" readonly=\"\"/></td>
	</tr>
	<tr>
	<td>Transaction Code </td>
	<td><input type=\"text\" name=\"trans_code\" value=\"$transtypes->trans_code\" /></td>
	</tr>
	<tr>
	<td>Transaction Type </td>
	<td><input type=\"text\" name=\"trans_type\" value=\"$transtypes->trans_type\" /></td>
	</tr>
	<tr>
	<td>Remarks</td>
	<td><input type=\"text\" name=\"remarks\" value=\"$transtypes->remarks\" /></td>
	</tr>
	<tr>
	<td colspan=\"2\"><h1>Applications applied</h1></td>
	</tr>
	<tr>
	<td>Accounts</td>
	<td><input type=\"checkbox\" name=\"accounts\" value=\"1\" /></td>
	</tr>
	<tr>
	<td>Payroll</td>
	<td><input type=\"checkbox\" name=\"cooperative\" value=\"1\" /></td>
	</tr>
	<tr>
	<td>Cooperative</td>
	<td><input type=\"checkbox\" name=\"payroll\" value=\"1\" /></td>
	</tr>
	<tr>
	<td><input type=\"submit\" name=\"Submit\" value=\"Add Transaction Type\" /></td>
	<td><input type=\"button\" name=\"Submit\" value=\"List Transactions\" onclick=\"loadHTML('ajaxfunctions.php','RequestDetails','Transtypes')\"/></td>
	</tr>
	</table>
	<tr bgcolor=\"#66CCCC\" >
        <td align=\"left\" colspan=\"5\"><div id=\"RequestDetails\"></div></td>
     </tr>";
	}

function payment_mode(){
	echo "<table width=\"100%\"  border=\"0\" cellpadding=\"1\">
	<tr>
	<td colspan=\"2\"><h2>Payment Modes</h2></td>
	</tr>
	<tr>
	<td width=\"25%\">Payment Mode ID. </td>
	<td width=\"75%\"><input type=\"text\" name=\"paymentid\" value=\"$paymode->paymentid\" readonly=\"\" /></td>
	</tr>
	<tr>
	<td>Payment Mode </td>
	<td><input type=\"text\" name=\"payment_option\" value=\"$paymode->payment_option\" /></td>
	</tr>
	<td><input type=\"submit\" name=\"Submit\" value=\"Add Payment Mode\" /></td>
	<td><input type=\"button\" name=\"Submit\" value=\"List Payment Modes\" onclick=\"loadHTML('ajaxfunctions.php','RequestDetails','PaymentMode')\"/></td>
	</tr>
	</table>
	<tr bgcolor=\"#66CCCC\" >
        <td align=\"left\" colspan=\"5\"><div id=\"RequestDetails\"></div></td>
     </tr>";
}

function room_type(){
	echo "<table width=\"100%\"  border=\"0\" cellpadding=\"1\">
	<tr>
	<td colspan=\"2\"><h2>Room Type</h2></td>
	</tr>
	<tr>
	<td width=\"25%\">Room Type ID. </td>
	<td width=\"75%\"><input type=\"text\" name=\"paymentid\" value=\"$roomtype->roomtypeid\" readonly=\"\" /></td>
	</tr>
	<tr>
	<td>Room Type</td>
	<td><input type=\"text\" name=\"payment_option\" value=\"$roomtype->roomtype\" /></td>
	</tr>
	<tr>
	<td>Description</td>
	<td><input type=\"text\" name=\"payment_option\" value=\"$roomtype->description\" /></td>
	</tr>
	<td><input type=\"submit\" name=\"Submit\" value=\"Add Room Type\" /></td>
	<td><input type=\"button\" name=\"Submit\" value=\"List Room Types\" onclick=\"loadHTML('ajaxfunctions.php','RequestDetails','Roomtype')\"/></td>
	</tr>
	</table>
	<tr bgcolor=\"#66CCCC\" >
        <td align=\"left\" colspan=\"5\"><div id=\"RequestDetails\"></div></td>
     </tr>";
}


function details(){
	echo "<table width=\"100%\"  border=\"0\" cellpadding=\"1\">
	<tr>
	<td colspan=\"2\"><h2>Transaction Details</h2></td>
	</tr>
	<tr>
	<td width=\"25%\">Item ID.</td>
	<td width=\"75%\"><input type=\"text\" name=\"itemid\" value=\"$item->itemid\" readonly=\"\"/></td>
	</tr>
	<tr>
	<td>Item</td>
	<td><input type=\"text\" name=\"item\"/></td>
	</tr>
	<tr>
	<td>Description </td>
	<td><input type=\"text\" name=\"description\" /></td>
	</tr>
	<tr>
	  <td>Applied in </td>
	  <td colspan=\"2\"><label>
		<input type=\"checkbox\" name=\"sale\" value=\"1\"/>
Sales </label>
		  <label>
		  <input type=\"checkbox\" name=\"expense\" value=\"1\"/>
Expenses </label>
	  </td>
	  <td colspan=\"2\">&nbsp;</td>
	</tr>
	<tr>
	<td><input type=\"submit\" name=\"Submit\" value=\"Add Transaction Details\" /></td>
	<td><input type=\"button\" name=\"Submit\" value=\"List Transaction Details\" onclick=\"loadHTML('ajaxfunctions.php','RequestDetails','transdetails')\"/></td>
	</tr>
	</table>
	<tr bgcolor=\"#66CCCC\" >
        <td align=\"left\" colspan=\"5\"><div id=\"RequestDetails\"></div></td>
     </tr>";
}
?>
