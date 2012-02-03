<?php
/*
           _     _                _           _     
          | |   (_)              | |         | |    
 _ __ ___ | |__  _ _ __ ______ __| | __ _ ___| |__  
| '__/ _ \| '_ \| | '_ \______/ _` |/ _` / __| '_ \ 
| | | (_) | |_) | | | | |    | (_| | (_| \__ \ | | |
|_|  \___/|_.__/|_|_| |_|     \__,_|\__,_|___/_| |_|

robin-dash: Centralized Controller for Robin-Mesh networking devices
Copyright (C) 2010-2011 Cody Cooper.

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU Affero General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU Affero General Public License for more details.

You should have received a copy of the GNU Affero General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/


if(file_exists("settings.php")) {require("settings.php");}
else {header("Location: oobe.php");exit;}
?>

<html>
<head>
<title>Donate to <?php echo $brand; ?></title>
<link rel="stylesheet" type="text/css" href="<?php echo $wdir; ?>resources/style.css">
<link rel="shortcut icon" href="<?php echo $wdir; ?>resources/favicon.ico"/>
</head>
<body>

<?php
if(isset($_GET['type'])) {echo "<div style=\"margin-top:20%;text-align:center;\"><h1>Processing Donation..</h1>Please wait whilst you are redirected to PayPal.</div>";}

if(isset($_GET['type']) && $_GET['type'] == "once-off") {
echo "<form action=\"https://www.paypal.com/cgi-bin/webscr\" id=\"paypal\" method=\"post\">
<input type=\"hidden\" name=\"cmd\" value=\"_donations\">
<input type=\"hidden\" name=\"business\" value=\"PCLCSS8H5XP9Q\">
<input type=\"hidden\" name=\"lc\" value=\"NZ\">
<input type=\"hidden\" name=\"item_name\" value=\"robin-dash Once-Off Donation\">
<input type=\"hidden\" name=\"item_number\" value=\"rd-don\">
<input type=\"hidden\" name=\"amount\" value=\"" . $_GET['amount'] . "\">
<input type=\"hidden\" name=\"currency_code\" value=\"NZD\">
<input type=\"hidden\" name=\"no_note\" value=\"1\">
<input type=\"hidden\" name=\"no_shipping\" value=\"1\">
<input type=\"hidden\" name=\"bn\" value=\"PP-DonationsBF:btn_donateCC_LG.gif:NonHosted\">
</form>";
}
else if(isset($_GET['type']) && $_GET['type'] == "subscription") {
echo "<form action=\"https://www.paypal.com/cgi-bin/webscr\" id=\"paypal\" method=\"post\">
<input type=\"hidden\" name=\"cmd\" value=\"_xclick-subscriptions\">
<input type=\"hidden\" name=\"business\" value=\"PCLCSS8H5XP9Q\">
<input type=\"hidden\" name=\"lc\" value=\"NZ\">
<input type=\"hidden\" name=\"item_name\" value=\"robin-dash Recurring Donation\">
<input type=\"hidden\" name=\"item_number\" value=\"rd-rec\">
<input type=\"hidden\" name=\"no_note\" value=\"1\">
<input type=\"hidden\" name=\"no_shipping\" value=\"1\">
<input type=\"hidden\" name=\"src\" value=\"1\">
<input type=\"hidden\" name=\"a3\" value=\"" . $_GET['amount'] . "\">
<input type=\"hidden\" name=\"p3\" value=\"1\">
<input type=\"hidden\" name=\"t3\" value=\"M\">
<input type=\"hidden\" name=\"currency_code\" value=\"NZD\">
<input type=\"hidden\" name=\"bn\" value=\"PP-SubscriptionsBF:btn_subscribeCC_LG.gif:NonHosted\">
</form>";
}
else {
echo "<h1 style=\"margin-top:10%;margin-left:30%;\">Donate to " . $brand . "</h1>
<p style=\"margin-left:30%;\"\">Your donation will help cover hosting and development costs</p>

<form action=\"donate.php\" method=\"GET\" style=\"margin-top:2%;margin-left:30%;margin-right:35%;\">
<input type=\"radio\" name=\"type\" value=\"once-off\" checked /> Once-off<br />
<input type=\"radio\" name=\"type\" value=\"subscription\" /> Recurring (Monthly)
<hr />
<label for=\"amount\">Donation Amount</label>
<input type=\"text\" name=\"amount\" value=\"\" />
<hr />
<input type=\"submit\" name=\"sent\" value=\"Donate\" />
</form>";
}

if(isset($_GET['type'])) {echo "<script type=\"text/javascript\">window.onload = function() {document.getElementById(\"paypal\").submit();}</script>";}
?>

</body>
</html>