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


$networkname = $_SESSION['user'];

if(isset($_GET['page'])) {$page = strtolower($_GET['page']);}
else {$page = "general";}

$xmlp = simplexml_load_file($dir . "data/" . $networkname . ".xml");
$fc = file_get_contents($dir . "data/" . $networkname . ".xml");

setTimezoneByOffset($xmlp->management->enable_gmt_offset);




if(!isset($page)) {die("Something strange happened...");}
else if($page == "general" && isset($_POST['submit'])) {
// If the posted password is the same as the current one,
// don't change it. This prevents robin-dash from hashing
// the current hashed password into another. :)

if($_POST['networkpass'] == "") {echo "";}
else if($_SESSION['user'] == "test") {echo "";}
else {
	$fc = str_replace("<password>" . $xmlp->robindash->password . "</password>", "<password>" . md5($_POST['networkpass']) . "</password>", $fc);
	$_SESSION['pass'] = md5($_POST['networkpass']);
}

if($xmlp->robindash->editmode == "") {
	if(!$_POST['editmode']) {$editmode = "advanced";}
	else {$editmode = $_POST['editmode'];}
	
	$fc = str_replace("<enable_custom_firmware>" . $xmlp->robindash->enable_custom_firmware . "</enable_custom_firmware>", "<enable_custom_firmware>" . $xmlp->robindash->enable_custom_firmware . "</enable_custom_firmware>\n<editmode>" . $editmode . "</editmode>", $fc);
}
else {$fc = str_replace("<editmode>" . $xmlp->robindash->editmode . "</editmode>", "<editmode>" . $_POST['editmode'] . "</editmode>", $fc);}

if($_POST['forwardcheck'] == "on") {$forwardcheck = "1";}
else {$forwardcheck = "0";}

$fc = str_replace("<enable_gmt_offset>" . $xmlp->management->enable_gmt_offset . "</enable_gmt_offset>", "<enable_gmt_offset>" . $_POST['timezone'] . "</enable_gmt_offset>", $fc);
$fc = str_replace("<enable_country_code>" . $xmlp->management->enable_country_code . "</enable_country_code>", "<enable_country_code>" . $_POST['enable_country_code'] . "</enable_country_code>", $fc);
$fc = str_replace("<notifymail>" . $xmlp->robindash->notifymail . "</notifymail>", "<notifymail>" . $_POST['notifymail'] . "</notifymail>", $fc);
$fc = str_replace("<forwardcheck>" . $xmlp->robindash->forwardcheck . "</forwardcheck>", "<forwardcheck>" . $forwardcheck . "</forwardcheck>", $fc);

if(strlen($xmlp->robindash->location) > 0 && $_POST['location'] == "Already Set") {echo "";}
else if(strlen($xmlp->robindash->location) > 0 && isset($_POST['location'])) {
	$result = simplexml_load_string(file_get_contents("http://maps.googleapis.com/maps/api/geocode/xml?sensor=false&address=" . urlencode($_POST['location'])));

	$lat = $result->result->geometry->location->lat;
	$lng = $result->result->geometry->location->lng;
	
	$location = $lat . "," . $lng;
	$fc = str_replace("<location>" . $xmlp->robindash->location . "</location>", "<location>" . $location . "</location>", $fc);
}
else if(isset($_POST['location'])) {
	$result = simplexml_load_string(file_get_contents("http://maps.googleapis.com/maps/api/geocode/xml?sensor=false&address=" . urlencode($_POST['location'])));

	$lat = $result->result->geometry->location->lat;
	$lng = $result->result->geometry->location->lng;
	
	$location = $lat . "," . $lng;
	$fc = str_replace("<forwardcheck>" . $xmlp->robindash->forwardcheck . "</forwardcheck>", "<forwardcheck>" . $xmlp->robindash->forwardcheck . "</forwardcheck>\n<location>" . $location . "</location>", $fc);
}
else {echo "";}

$fh = fopen($dir . "data/" . $networkname . ".xml", 'w') or die("Can't write to the data file.");
fwrite($fh, $fc);
fclose($fh);

$fh = fopen($dir . "data/cid/" . $_SESSION['user'] . ".txt", 'w') or die("Can't write to the data file.");
fwrite($fh, "-\n");
fclose($fh);

if(!$_SERVER['HTTP_REFERER']) {echo "response({\"status\" : \"ok\"})";exit;}
else {$status = "done";}
}





else if($page == "public-network" && isset($_POST['submit'])) {
$fc = str_replace("<public_ssid>" . $xmlp->wireless->public_ssid . "</public_ssid>", "<public_ssid>" . str_replace(" ", "*", $_POST['publicname']) . "</public_ssid>", $fc);

if($_POST['captiveportal'] == "none") {$fc = str_replace("<main_which_handler>" . $xmlp->cp_switch->main_which_handler . "</main_which_handler>", "<main_which_handler>0</main_which_handler>", $fc);}
else if($_POST['captiveportal'] == "nodogsplash") {$fc = str_replace("<main_which_handler>" . $xmlp->cp_switch->main_which_handler . "</main_which_handler>", "<main_which_handler>1</main_which_handler>", $fc);}
else if($_POST['captiveportal'] == "coova") {
	$fc = str_replace("<main_which_handler>" . $xmlp->cp_switch->main_which_handler . "</main_which_handler>", "<main_which_handler>5</main_which_handler>", $fc);
	$fc = str_replace("<agent_service>" . $xmlp->chilli->agent_service . "</agent_service>", "<agent_service>coova_om</agent_service>", $fc);
}
else if($_POST['captiveportal'] == "wifirush") {
	$fc = str_replace("<main_which_handler>" . $xmlp->cp_switch->main_which_handler . "</main_which_handler>", "<main_which_handler>4</main_which_handler>", $fc);
	$fc = str_replace("<agent_service>" . $xmlp->chilli->agent_service . "</agent_service>", "<agent_service>wificpa_enterprise</agent_service>", $fc);
}
else if($_POST['captiveportal'] == "wifidog") {$fc = str_replace("<main_which_handler>" . $xmlp->cp_switch->main_which_handler . "</main_which_handler>", "<main_which_handler>3</main_which_handler>", $fc);}
else {$fc = str_replace("<main_which_handler>" . $xmlp->cp_switch->main_which_handler . "</main_which_handler>", "<main_which_handler>0</main_which_handler>", $fc);}


if($_POST['splashpage'] == "on") {$splashpage = 0;}
else {$splashpage = 1;}

if($_POST['usenodename'] == "on") {$usenodename = 1;}
else {$usenodename = 0;}

if(strpos($fc, '<usenodename>') !==FALSE) {$fc = str_replace("<usenodename>" . $xmlp->robindash->usenodename . "</usenodename>", "<usenodename>" . $usenodename . "</usenodename>", $fc);}
else {$fc = str_replace("</robindash>", "<usenodename>" . $usenodename . "</usenodename>\n</robindash>", $fc);}


// coova/wifirush
$fc = str_replace("<agent_radiusserver1>" . $xmlp->chilli->agent_radiusserver1 . "</agent_radiusserver1>", "<agent_radiusserver1>" . $_POST['radiusserver1'] . "</agent_radiusserver1>", $fc);
$fc = str_replace("<agent_radiusserver2>" . $xmlp->chilli->agent_radiusserver2 . "</agent_radiusserver2>", "<agent_radiusserver2>" . $_POST['radiusserver2'] . "</agent_radiusserver2>", $fc);
$fc = str_replace("<agent_uamserver>" . $xmlp->chilli->agent_uamserver . "</agent_uamserver>", "<agent_uamserver>" . $_POST['radiusuamserver'] . "</agent_uamserver>", $fc);
$fc = str_replace("<agent_uamurl>" . $xmlp->chilli->agent_uamurl . "</agent_uamurl>", "<agent_uamurl>" . $_POST['radiusuamurl'] . "</agent_uamurl>", $fc);
$fc = str_replace("<agent_uamsecret>" . $xmlp->chilli->agent_uamsecret . "</agent_uamsecret>", "<agent_uamsecret>" . $_POST['radiusuamsecret'] . "</agent_uamsecret>", $fc);
$fc = str_replace("<agent_radiussecret>" . $xmlp->chilli->agent_radiussecret . "</agent_radiussecret>", "<agent_radiussecret>" . $_POST['radiussecret'] . "</agent_radiussecret>", $fc);
$fc = str_replace("<agent_radiusnasid>" . $xmlp->chilli->agent_radiusnasid . "</agent_radiusnasid>", "<agent_radiusnasid>" . $_POST['radiusnasid'] . "</agent_radiusnasid>", $fc);
$fc = str_replace("<agent_admusr>" . $xmlp->chilli->agent_admusr . "</agent_admusr>", "<agent_admusr>" . $_POST['radiusadmusr'] . "</agent_admusr>", $fc);
$fc = str_replace("<agent_admpwd>" . $xmlp->chilli->agent_admpwd . "</agent_admpwd>", "<agent_admpwd>" . $_POST['radiusadmpwd'] . "</agent_admpwd>", $fc);
$fc = str_replace("<agent_uamdomain>" . $xmlp->chilli->agent_uamdomain . "</agent_uamdomain>", "<agent_uamdomain>" . $_POST['radiusdomains'] . "</agent_uamdomain>", $fc);
$fc = str_replace("<agent_macpasswd>" . $xmlp->chilli->agent_macpasswd . "</agent_macpasswd>", "<agent_macpasswd>" . $_POST['macpasswd'] . "</agent_macpasswd>", $fc);


// nodogsplash
if(!$_POST['downspeed']) {$downspeed = "22";}
else {$downspeed = $_POST['downspeed'];}

if(!$_POST['upspeed']) {$upspeed = "11";}
else {$upspeed = $_POST['upspeed'];}

$fc = str_replace("<DownloadLimit>" . $xmlp->nodog->DownloadLimit . "</DownloadLimit>", "<DownloadLimit>" . $downspeed . "</DownloadLimit>", $fc);
$fc = str_replace("<UploadLimit>" . $xmlp->nodog->UploadLimit . "</UploadLimit>", "<UploadLimit>" . $upspeed . "</UploadLimit>", $fc);
$fc = str_replace("<RedirectURL>" . $xmlp->nodog->RedirectURL . "</RedirectURL>", "<RedirectURL>" . $_POST['redirecturl'] . "</RedirectURL>", $fc);
$fc = str_replace("<AuthenticateImmediately>" . $xmlp->nodog->AuthenticateImmediately . "</AuthenticateImmediately>", "<AuthenticateImmediately>" . $splashpage . "</AuthenticateImmediately>", $fc);
$fc = str_replace("<TrustedMACList>" . $xmlp->nodog->TrustedMACList . "</TrustedMACList>", "<TrustedMACList>" . $_POST['whitelist'] . "</TrustedMACList>", $fc);


// WiFiDog
if($_POST['MainAuthServer_SSLAvailable'] == "on") {$MainAuthServer_SSLAvailable = 1;}
else {$MainAuthServer_SSLAvailable = 0;}

if($_POST['SecAuthServer_SSLAvailable'] == "on") {$SecAuthServer_SSLAvailable = 1;}
else {$SecAuthServer_SSLAvailable = 0;}

$fc = str_replace("<gateway_TrustedMACList>" . $xmlp->wifidog->gateway_TrustedMACList . "</gateway_TrustedMACList>", "<gateway_TrustedMACList>" . $_POST['gateway_TrustedMACList'] . "</gateway_TrustedMACList>", $fc);

$fc = str_replace("<MainAuthServer_Hostname>" . $xmlp->wifidog->MainAuthServer_Hostname . "</MainAuthServer_Hostname>", "<MainAuthServer_Hostname>" . $_POST['MainAuthServer_Hostname'] . "</MainAuthServer_Hostname>", $fc);
$fc = str_replace("<MainAuthServer_SSLAvailable>" . $xmlp->wifidog->MainAuthServer_SSLAvailable . "</MainAuthServer_SSLAvailable>", "<MainAuthServer_SSLAvailable>" . $MainAuthServer_SSLAvailable . "</MainAuthServer_SSLAvailable>", $fc);
$fc = str_replace("<MainAuthServer_Path>" . $xmlp->wifidog->MainAuthServer_Path . "</MainAuthServer_Path>", "<MainAuthServer_Path>" . $_POST['MainAuthServer_Path'] . "</MainAuthServer_Path>", $fc);

$fc = str_replace("<SecAuthServer_Hostname>" . $xmlp->wifidog->SecAuthServer_Hostname . "</SecAuthServer_Hostname>", "<SecAuthServer_Hostname>" . $_POST['SecAuthServer_Hostname'] . "</SecAuthServer_Hostname>", $fc);
$fc = str_replace("<SecAuthServer_SSLAvailable>" . $xmlp->wifidog->SecAuthServer_SSLAvailable . "</SecAuthServer_SSLAvailable>", "<SecAuthServer_SSLAvailable>" . $SecAuthServer_SSLAvailable . "</SecAuthServer_SSLAvailable>", $fc);
$fc = str_replace("<SecAuthServer_Path>" . $xmlp->wifidog->SecAuthServer_Path . "</SecAuthServer_Path>", "<SecAuthServer_Path>" . $_POST['SecAuthServer_Path'] . "</SecAuthServer_Path>", $fc);


if($_POST['transparentbridge'] == "on") {$transparentbridge = 1;}
else {$transparentbridge = 0;}

$fc = str_replace("<enable_transparent_bridge>" . $xmlp->management->enable_transparent_bridge . "</enable_transparent_bridge>", "<enable_transparent_bridge>" . $transparentbridge . "</enable_transparent_bridge>", $fc);


$fh = fopen($dir . "data/" . $networkname . ".xml", 'w') or die("Can't write to the data file.");
fwrite($fh, $fc);
fclose($fh);

$fh = fopen($dir . "data/cid/" . $_SESSION['user'] . ".txt", 'w') or die("Can't write to the data file.");
fwrite($fh, "-\n");
fclose($fh);

if(!$_SERVER['HTTP_REFERER']) {echo "response({\"status\" : \"ok\"})";exit;}
else {$status = "done";}
}





else if($page == "private-network" && isset($_POST['submit'])) {
$fc = str_replace("<private_key>" . $xmlp->wireless->private_key . "</private_key>", "<private_key>" . $_POST['privatepass'] . "</private_key>", $fc);
$fc = str_replace("<private_ssid>" . $xmlp->wireless->private_ssid . "</private_ssid>", "<private_ssid>" . str_replace(" ", "*", $_POST['privatename']) . "</private_ssid>", $fc);

if($_POST['privateenable'] == "on") {$privateenable = 1;}
else {$privateenable = 0;}

$fc = str_replace("<Myap_up>" . $xmlp->mesh->Myap_up . "</Myap_up>", "<Myap_up>" . $privateenable . "</Myap_up>", $fc);


$fh = fopen("data/" . $networkname . ".xml", 'w') or die("Can't write to the data file.");
fwrite($fh, $fc);
fclose($fh);

$fh = fopen($dir . "data/cid/" . $_SESSION['user'] . ".txt", 'w') or die("Can't write to the data file.");
fwrite($fh, "-\n");
fclose($fh);

if(!$_SERVER['HTTP_REFERER']) {echo "response({\"status\" : \"ok\"})";exit;}
else {$status = "done";}
}





else if($page == "radio" && isset($_POST['submit'])) {
$fc = str_replace("<channel_alternate>" . $xmlp->radio->channel_alternate . "</channel_alternate>", "<channel_alternate>" . $_POST['radiochannel'] . "</channel_alternate>", $fc);

$fh = fopen($dir . "data/" . $networkname . ".xml", 'w') or die("Can't write to the data file.");
fwrite($fh, $fc);
fclose($fh);

$fh = fopen($dir . "data/cid/" . $_SESSION['user'] . ".txt", 'w') or die("Can't write to the data file.");
fwrite($fh, "-\n");
fclose($fh);

if(!$_SERVER['HTTP_REFERER']) {echo "response({\"status\" : \"ok\"})";exit;}
else {$status = "done";}
}





else if($page == "security" && isset($_POST['submit'])) {
$fc = str_replace("<enable_rootpwd>" . $xmlp->management->enable_rootpwd . "</enable_rootpwd>", "<enable_rootpwd>" . $_POST['rootpwd'] . "</enable_rootpwd>", $fc);


$fh = fopen($dir . "data/" . $networkname . ".xml", 'w') or die("Can't write to the data file.");
fwrite($fh, $fc);
fclose($fh);

$fh = fopen($dir . "data/cid/" . $_SESSION['user'] . ".txt", 'w') or die("Can't write to the data file.");
fwrite($fh, "-\n");
fclose($fh);

if(!$_SERVER['HTTP_REFERER']) {echo "response({\"status\" : \"ok\"})";exit;}
else {$status = "done";}
}





else if($page == "firmware" && isset($_POST['submit'])) {
if($_POST['freeze_version'] == "on") {$freeze_version = "1";}
else {$freeze_version = "0";}

$fc = str_replace("<freeze_version>" . $xmlp->management->freeze_version . "</freeze_version>", "<freeze_version>" . $freeze_version . "</freeze_version>", $fc);
$fc = str_replace("<services_upgd_srv>" . $xmlp->general->services_upgd_srv . "</services_upgd_srv>", "<services_upgd_srv>" . $_POST['services_upgd_srv'] . "</services_upgd_srv>", $fc);


$fh = fopen($dir . "data/" . $networkname . ".xml", 'w') or die("Can't write to the data file.");
fwrite($fh, $fc);
fclose($fh);

$fh = fopen($dir . "data/cid/" . $_SESSION['user'] . ".txt", 'w') or die("Can't write to the data file.");
fwrite($fh, "-\n");
fclose($fh);

if(!$_SERVER['HTTP_REFERER']) {echo "response({\"status\" : \"ok\"})";exit;}
else {$status = "done";}
}





else if($page == "miscellaneous" && isset($_POST['submit'])) {
$fc = str_replace("<enable_public_dns>" . $xmlp->management->enable_public_dns . "</enable_public_dns>", "<enable_public_dns>" . $_POST['public_dns'] . "</enable_public_dns>", $fc);

if($_POST['enable_force_reboot'] == "on") {$enable_force_reboot = "1";}
else {$enable_force_reboot = "0";}

$fc = str_replace("<enable_force_reboot>" . $xmlp->management->enable_force_reboot . "</enable_force_reboot>", "<enable_force_reboot>" . $enable_force_reboot . "</enable_force_reboot>", $fc);

if($enable_force_reboot == "0") {echo "";}
else {
	$fc = str_replace("<enable_force_reboot_date>" . $xmlp->management->enable_force_reboot_date . "</enable_force_reboot_date>", "<enable_force_reboot_date>" . $_POST['enable_force_reboot_date'] . "</enable_force_reboot_date>", $fc);
	$fc = str_replace("<enable_force_reboot_time>" . $xmlp->management->enable_force_reboot_time . "</enable_force_reboot_time>", "<enable_force_reboot_time>" . $_POST['enable_force_reboot_time'] . "</enable_force_reboot_time>", $fc);
}

if($_POST['filter_SMTP_rdir'] == "on") {$filter_SMTP_rdir = "1";}
else {$filter_SMTP_rdir = "0";}

$fc = str_replace("<filter_SMTP_dest>" . $xmlp->iprules->filter_SMTP_dest . "</filter_SMTP_dest>", "<filter_SMTP_dest>" . $_POST['filter_SMTP_dest'] . "</filter_SMTP_dest>", $fc);
$fc = str_replace("<filter_SMTP_rdir>" . $xmlp->iprules->filter_SMTP_rdir . "</filter_SMTP_rdir>", "<filter_SMTP_rdir>" . $filter_SMTP_rdir . "</filter_SMTP_rdir>", $fc);


$fh = fopen($dir . "data/" . $networkname . ".xml", 'w') or die("Can't write to the data file.");
fwrite($fh, $fc);
fclose($fh);

$fh = fopen($dir . "data/cid/" . $_SESSION['user'] . ".txt", 'w') or die("Can't write to the data file.");
fwrite($fh, "-\n");
fclose($fh);

if(!$_SERVER['HTTP_REFERER']) {echo "response({\"status\" : \"ok\"})";exit;}
else {$status = "done";}
}

else {echo "";}

if(isset($_GET['action']) && $_GET['action'] == "delete-sshkey") {unlink($dir . "data/uploads/" . $networkname . "/ssh.key");}

// We now have to reload the config as it may have changed up above
$xmlp = simplexml_load_file($dir . "data/" . $networkname . ".xml");
?>

<html>
<head>
<title>Edit Network (Easy): <?php echo $brand; ?></title>

<link rel="stylesheet" type="text/css" href="<?php echo $wdir; ?>resources/style.css">
<link rel="shortcut icon" href="<?php echo $wdir; ?>resources/favicon.ico"/>
</head>
<body>
<div id="wrapper">
	<div id="header">
		<div id="logo"><?php echo $brand; ?></div>
	</div>
	<div id="content">
		<div id="page-content">
		<div id="main"><input type="button" onclick="window.location = '<?php echo $wdir; ?>overview.php';" value="Network Overview">&nbsp;<input type="button" onclick="window.location = '<?php echo $wdir; ?>logout.php';" value="Logout"></div>
		
		<table style="width:100%;">
			<tr>
				<td id="td"><a href="<?php echo $wdir; ?>edit.php?page=general"<?php if($_GET['page'] == "general" || !isset($_GET['page'])) {echo " style=\"font-weight:bold;\"";} ?>>General</a></td>
				<td id="td"><a href="<?php echo $wdir; ?>edit.php?page=public-network"<?php if($_GET['page'] == "public-network") {echo " style=\"font-weight:bold;\"";} ?>>Public<br>Network</a></td>
				<td id="td"><a href="<?php echo $wdir; ?>edit.php?page=private-network"<?php if($_GET['page'] == "private-network") {echo " style=\"font-weight:bold;\"";} ?>>Private<br>Network</a></td>
				<td id="td"><a href="<?php echo $wdir; ?>edit.php?page=security"<?php if($_GET['page'] == "security") {echo " style=\"font-weight:bold;\"";} ?>>Security</a></td>
				<td id="td"><a href="<?php echo $wdir; ?>edit.php?page=radio"<?php if($_GET['page'] == "radio") {echo " style=\"font-weight:bold;\"";} ?>>Radio</a></td>
				<td id="td"><a href="<?php echo $wdir; ?>edit.php?page=firmware"<?php if($_GET['page'] == "firmware") {echo " style=\"font-weight:bold;\"";} ?>>Firmware</a></td>
				<td id="td"><a href="<?php echo $wdir; ?>edit.php?page=miscellaneous"<?php if($_GET['page'] == "miscellaneous") {echo " style=\"font-weight:bold;\"";} ?>>Miscellaneous</a></td>
			</tr>
		</table>

		<br>

		<?php
		if(isset($status) && $status == "done") {echo "<div id=\"done\">Settings have been successfully updated.</div>\n<br>\n\n";}
		else if(isset($_GET['status']) && $_GET['status'] == "importdone") {echo "<div id=\"done\">Nodes have been successfully imported.</div>\n<br>\n\n";}
		else {/* We have nothing to say */}

		if($page == "general") {echo "<form action=\"" . $wdir . "edit.php?page=general\" method=\"POST\">
		<table>
		<tr>
		<td id=\"name\">Make a Donation</td>
		<td id=\"data\"><input type=\"button\" value=\"Donate via. PayPal\" style=\"margin-bottom:0%;margin-left:10%;width:80%;\" onclick=\"window.location = '" . $wdir . "donate.php';\"></td>
		<td id=\"desc\"><b>Help support the ROBIN projects.</b><br>Your donation helps with development &amp; hosting costs.</td>
		</tr>
		
		<tr><td colspan=\"3\"><hr></td></tr>
		
		<tr>
		<td id=\"name\">Modify Nodes</td>
		<td id=\"data\"><input type=\"button\" value=\"Modify Nodes\" style=\"margin-bottom:0%;margin-left:10%;width:80%;\" onclick=\"popup();\"></td>
		<td id=\"desc\">Allows " . $brand . " to identify which nodes are yours.</td>
		</tr>
		
		<tr><td colspan=\"3\"><hr></td></tr>
		
		<tr>
		<td id=\"name\">Dashboard Username</td>
		<td id=\"data\"><input type=\"text\" name=\"networkname\" value=\"" . $xmlp->node->general_net . "\" readonly></td>
		<td id=\"desc\">The username to login to this dashboard with. This can not be changed once you have signed up.</td>
		</tr>

		<tr>
		<td id=\"name\">Dashboard Password</td>
		<td id=\"data\"><input type=\"password\" name=\"networkpass\" value=\"\"></td>
		<td id=\"desc\">The password to login to this dashboard with.</td>
		</tr>
		
		<tr>
		<td id=\"name\">Network Location</td>
		<td id=\"data\"><input type=\"text\" name=\"location\" value=\"";
		
		if(strlen($xmlp->robindash->location) > 0) {echo "Already Set";}
		else {echo "";}
		
		echo "\"></td>
		<td id=\"desc\">Used to determine the initial placing of the map.</td>
		</tr>

		<tr>
		<td id=\"name\">Time Zone</td>
		<td id=\"data\">
		<select name=\"timezone\">
			<option value=\"-12\""; if($xmlp->management->enable_gmt_offset == "-12") {echo " selected";} echo">(GMT -12:00) Eniwetok, Kwajalein</option>
			<option value=\"-11\""; if($xmlp->management->enable_gmt_offset == "-11") {echo " selected";} echo">(GMT -11:00) Midway Island, Samoa</option>
			<option value=\"-10\""; if($xmlp->management->enable_gmt_offset == "-10") {echo " selected";} echo">(GMT -10:00) Hawaii</option>
			<option value=\"-9\""; if($xmlp->management->enable_gmt_offset == "-9") {echo " selected";} echo">(GMT -9:00) Alaska</option>
			<option value=\"-8\""; if($xmlp->management->enable_gmt_offset == "-8") {echo " selected";} echo">(GMT -8:00) Pacific Time (US &amp; Canada)</option>
			<option value=\"-7\""; if($xmlp->management->enable_gmt_offset == "-7") {echo " selected";} echo">(GMT -7:00) Mountain Time (US &amp; Canada)</option>
			<option value=\"-6\""; if($xmlp->management->enable_gmt_offset == "-6") {echo " selected";} echo">(GMT -6:00) Central Time (US &amp; Canada), Mexico City</option>
			<option value=\"-5\""; if($xmlp->management->enable_gmt_offset == "-5") {echo " selected";} echo">(GMT -5:00) Eastern Time (US &amp; Canada), Bogota, Lima</option>
			<option value=\"-4\""; if($xmlp->management->enable_gmt_offset == "-4") {echo " selected";} echo">(GMT -4:00) Atlantic Time (Canada), Caracas, La Paz</option>
			<option value=\"-3.5\""; if($xmlp->management->enable_gmt_offset == "-3.5") {echo " selected";} echo">(GMT -3:30) Newfoundland</option>
			<option value=\"-3\""; if($xmlp->management->enable_gmt_offset == "-3") {echo " selected";} echo">(GMT -3:00) Brazil, Buenos Aires, Georgetown</option>
			<option value=\"-2\""; if($xmlp->management->enable_gmt_offset == "-2") {echo " selected";} echo">(GMT -2:00) Mid-Atlantic</option>
			<option value=\"-1\""; if($xmlp->management->enable_gmt_offset == "-1") {echo " selected";} echo">(GMT -1:00 hour) Azores, Cape Verde Islands</option>
			<option value=\"0\""; if($xmlp->management->enable_gmt_offset == "0") {echo " selected";} echo">(GMT) Western Europe Time, London, Lisbon, Casablanca</option>
			<option value=\"1\""; if($xmlp->management->enable_gmt_offset == "1") {echo " selected";} echo">(GMT +1:00 hour) Brussels, Copenhagen, Madrid, Paris</option>
			<option value=\"2\""; if($xmlp->management->enable_gmt_offset == "2") {echo " selected";} echo">(GMT +2:00) Kaliningrad, South Africa</option>
			<option value=\"3\""; if($xmlp->management->enable_gmt_offset == "3") {echo " selected";} echo">(GMT +3:00) Baghdad, Riyadh, Moscow, St. Petersburg</option>
			<option value=\"3.5\""; if($xmlp->management->enable_gmt_offset == "3.5") {echo " selected";} echo">(GMT +3:30) Tehran</option>
			<option value=\"4\""; if($xmlp->management->enable_gmt_offset == "4") {echo " selected";} echo">(GMT +4:00) Abu Dhabi, Muscat, Baku, Tbilisi</option>
			<option value=\"4.5\""; if($xmlp->management->enable_gmt_offset == "4.5") {echo " selected";} echo">(GMT +4:30) Kabul</option>
			<option value=\"5\""; if($xmlp->management->enable_gmt_offset == "5") {echo " selected";} echo">(GMT +5:00) Ekaterinburg, Islamabad, Karachi, Tashkent</option>
			<option value=\"5.5\""; if($xmlp->management->enable_gmt_offset == "5.5") {echo " selected";} echo">(GMT +5:30) Bombay, Calcutta, Madras, New Delhi</option>
			<option value=\"5.75\""; if($xmlp->management->enable_gmt_offset == "5.75") {echo " selected";} echo">(GMT +5:45) Kathmandu</option>
			<option value=\"6\""; if($xmlp->management->enable_gmt_offset == "6") {echo " selected";} echo">(GMT +6:00) Almaty, Dhaka, Colombo</option>
			<option value=\"7\""; if($xmlp->management->enable_gmt_offset == "7") {echo " selected";} echo">(GMT +7:00) Bangkok, Hanoi, Jakarta</option>
			<option value=\"8\""; if($xmlp->management->enable_gmt_offset == "8") {echo " selected";} echo">(GMT +8:00) Beijing, Perth, Singapore, Hong Kong</option>
			<option value=\"9\""; if($xmlp->management->enable_gmt_offset == "9") {echo " selected";} echo">(GMT +9:00) Tokyo, Seoul, Osaka, Sapporo, Yakutsk</option>
			<option value=\"9.5\""; if($xmlp->management->enable_gmt_offset == "9.5") {echo " selected";} echo">(GMT +9:30) Adelaide, Darwin</option>
			<option value=\"10\""; if($xmlp->management->enable_gmt_offset == "10") {echo " selected";} echo">(GMT +10:00) Eastern Australia, Guam, Vladivostok</option>
			<option value=\"11\""; if($xmlp->management->enable_gmt_offset == "11") {echo " selected";} echo">(GMT +11:00) Magadan, Solomon Islands, New Caledonia</option>
			<option value=\"12\""; if($xmlp->management->enable_gmt_offset == "12") {echo " selected";} echo">(GMT +12:00) Auckland, Fiji, Kamchatka</option>
		</select>
		</td>
		<td id=\"desc\">The Time Zone you would like your network to be in.</td>
		</tr>
		
		<tr>
		<td id=\"name\">Wireless Country Code</td>
		<td id=\"data\">
		<select name=\"enable_country_code\">
			<option value=\"0\""; if($xmlp->management->enable_country_code == "0") {echo " selected";} echo " disabled>NOT SET!
			<option value=\"004\""; if($xmlp->management->enable_country_code == "004") {echo " selected";} echo ">AFGHANISTAN
			<option value=\"355\""; if($xmlp->management->enable_country_code == "355") {echo " selected";} echo ">ALBANIA
			<option value=\"012\""; if($xmlp->management->enable_country_code == "012") {echo " selected";} echo ">ALGERIA
			<option value=\"016\""; if($xmlp->management->enable_country_code == "016") {echo " selected";} echo ">AMERICAN SAMOA
			<option value=\"020\""; if($xmlp->management->enable_country_code == "020") {echo " selected";} echo ">ANDORRA
			<option value=\"024\""; if($xmlp->management->enable_country_code == "024") {echo " selected";} echo ">ANGOLA
			<option value=\"660\""; if($xmlp->management->enable_country_code == "660") {echo " selected";} echo ">ANGUILLA
			<option value=\"010\""; if($xmlp->management->enable_country_code == "010") {echo " selected";} echo ">ANTARCTICA
			<option value=\"028\""; if($xmlp->management->enable_country_code == "028") {echo " selected";} echo ">ANTIGUA AND BARBUDA
			<option value=\"032\""; if($xmlp->management->enable_country_code == "032") {echo " selected";} echo ">ARGENTINA
			<option value=\"051\""; if($xmlp->management->enable_country_code == "051") {echo " selected";} echo ">ARMENIA
			<option value=\"533\""; if($xmlp->management->enable_country_code == "533") {echo " selected";} echo ">ARUBA
			<option value=\"036\""; if($xmlp->management->enable_country_code == "036") {echo " selected";} echo ">AUSTRALIA
			<option value=\"040\""; if($xmlp->management->enable_country_code == "040") {echo " selected";} echo ">AUSTRIA
			<option value=\"031\""; if($xmlp->management->enable_country_code == "031") {echo " selected";} echo ">AZERBAIJAN
			<option value=\"044\""; if($xmlp->management->enable_country_code == "044") {echo " selected";} echo ">BAHAMAS
			<option value=\"048\""; if($xmlp->management->enable_country_code == "048") {echo " selected";} echo ">BAHRAIN
			<option value=\"050\""; if($xmlp->management->enable_country_code == "050") {echo " selected";} echo ">BANGLADESH
			<option value=\"052\""; if($xmlp->management->enable_country_code == "052") {echo " selected";} echo ">BARBADOS
			<option value=\"112\""; if($xmlp->management->enable_country_code == "112") {echo " selected";} echo ">BELARUS
			<option value=\"056\""; if($xmlp->management->enable_country_code == "056") {echo " selected";} echo ">BELGIUM
			<option value=\"084\""; if($xmlp->management->enable_country_code == "084") {echo " selected";} echo ">BELIZE
			<option value=\"204\""; if($xmlp->management->enable_country_code == "204") {echo " selected";} echo ">BENIN
			<option value=\"060\""; if($xmlp->management->enable_country_code == "060") {echo " selected";} echo ">BERMUDA
			<option value=\"064\""; if($xmlp->management->enable_country_code == "064") {echo " selected";} echo ">BHUTAN
			<option value=\"068\""; if($xmlp->management->enable_country_code == "068") {echo " selected";} echo ">BOLIVIA
			<option value=\"070\""; if($xmlp->management->enable_country_code == "070") {echo " selected";} echo ">BOSNIA AND HERZEGOVINA
			<option value=\"072\""; if($xmlp->management->enable_country_code == "072") {echo " selected";} echo ">BOTSWANA
			<option value=\"074\""; if($xmlp->management->enable_country_code == "074") {echo " selected";} echo ">BOUVET ISLAND
			<option value=\"076\""; if($xmlp->management->enable_country_code == "076") {echo " selected";} echo ">BRAZIL
			<option value=\"086\""; if($xmlp->management->enable_country_code == "086") {echo " selected";} echo ">BRITISH INDIAN OCEAN TERRITORY
			<option value=\"096\""; if($xmlp->management->enable_country_code == "096") {echo " selected";} echo ">BRUNEI DARUSSALAM
			<option value=\"100\""; if($xmlp->management->enable_country_code == "100") {echo " selected";} echo ">BULGARIA
			<option value=\"854\""; if($xmlp->management->enable_country_code == "854") {echo " selected";} echo ">BURKINA FASO
			<option value=\"108\""; if($xmlp->management->enable_country_code == "108") {echo " selected";} echo ">BURUNDI
			<option value=\"116\""; if($xmlp->management->enable_country_code == "116") {echo " selected";} echo ">CAMBODIA
			<option value=\"120\""; if($xmlp->management->enable_country_code == "120") {echo " selected";} echo ">CAMEROON
			<option value=\"124\""; if($xmlp->management->enable_country_code == "124") {echo " selected";} echo ">CANADA
			<option value=\"132\""; if($xmlp->management->enable_country_code == "132") {echo " selected";} echo ">CAPE VERDE
			<option value=\"136\""; if($xmlp->management->enable_country_code == "136") {echo " selected";} echo ">CAYMAN ISLANDS
			<option value=\"140\""; if($xmlp->management->enable_country_code == "140") {echo " selected";} echo ">CENTRAL AFRICAN REPUBLIC
			<option value=\"148\""; if($xmlp->management->enable_country_code == "148") {echo " selected";} echo ">CHAD
			<option value=\"152\""; if($xmlp->management->enable_country_code == "152") {echo " selected";} echo ">CHILE
			<option value=\"156\""; if($xmlp->management->enable_country_code == "156") {echo " selected";} echo ">CHINA
			<option value=\"162\""; if($xmlp->management->enable_country_code == "162") {echo " selected";} echo ">CHRISTMAS ISLAND
			<option value=\"166\""; if($xmlp->management->enable_country_code == "166") {echo " selected";} echo ">COCOS (KEELING) ISLANDS
			<option value=\"170\""; if($xmlp->management->enable_country_code == "170") {echo " selected";} echo ">COLOMBIA
			<option value=\"174\""; if($xmlp->management->enable_country_code == "174") {echo " selected";} echo ">COMOROS
			<option value=\"178\""; if($xmlp->management->enable_country_code == "178") {echo " selected";} echo ">CONGO
			<option value=\"184\""; if($xmlp->management->enable_country_code == "184") {echo " selected";} echo ">COOK ISLANDS
			<option value=\"188\""; if($xmlp->management->enable_country_code == "188") {echo " selected";} echo ">COSTA RICA
			<option value=\"384\""; if($xmlp->management->enable_country_code == "384") {echo " selected";} echo ">COTE D'IVOIRE
			<option value=\"191\""; if($xmlp->management->enable_country_code == "191") {echo " selected";} echo ">CROATIA (Hrvatska)
			<option value=\"192\""; if($xmlp->management->enable_country_code == "192") {echo " selected";} echo ">CUBA
			<option value=\"196\""; if($xmlp->management->enable_country_code == "196") {echo " selected";} echo ">CYPRUS
			<option value=\"203\""; if($xmlp->management->enable_country_code == "203") {echo " selected";} echo ">CZECH REPUBLIC
			<option value=\"208\""; if($xmlp->management->enable_country_code == "208") {echo " selected";} echo ">DENMARK
			<option value=\"262\""; if($xmlp->management->enable_country_code == "262") {echo " selected";} echo ">DJIBOUTI
			<option value=\"212\""; if($xmlp->management->enable_country_code == "212") {echo " selected";} echo ">DOMINICA
			<option value=\"214\""; if($xmlp->management->enable_country_code == "214") {echo " selected";} echo ">DOMINICAN REPUBLIC
			<option value=\"626\""; if($xmlp->management->enable_country_code == "626") {echo " selected";} echo ">EAST TIMOR
			<option value=\"218\""; if($xmlp->management->enable_country_code == "218") {echo " selected";} echo ">ECUADOR
			<option value=\"818\""; if($xmlp->management->enable_country_code == "818") {echo " selected";} echo ">EGYPT
			<option value=\"222\""; if($xmlp->management->enable_country_code == "222") {echo " selected";} echo ">EL SALVADOR
			<option value=\"226\""; if($xmlp->management->enable_country_code == "226") {echo " selected";} echo ">EQUATORIAL GUINEA
			<option value=\"232\""; if($xmlp->management->enable_country_code == "232") {echo " selected";} echo ">ERITREA
			<option value=\"233\""; if($xmlp->management->enable_country_code == "233") {echo " selected";} echo ">ESTONIA
			<option value=\"210\""; if($xmlp->management->enable_country_code == "210") {echo " selected";} echo ">ETHIOPIA
			<option value=\"238\""; if($xmlp->management->enable_country_code == "238") {echo " selected";} echo ">FALKLAND ISLANDS (MALVINAS)
			<option value=\"234\""; if($xmlp->management->enable_country_code == "234") {echo " selected";} echo ">FAROE ISLANDS
			<option value=\"242\""; if($xmlp->management->enable_country_code == "242") {echo " selected";} echo ">FIJI
			<option value=\"246\""; if($xmlp->management->enable_country_code == "246") {echo " selected";} echo ">FINLAND
			<option value=\"250\""; if($xmlp->management->enable_country_code == "250") {echo " selected";} echo ">FRANCE
			<option value=\"249\""; if($xmlp->management->enable_country_code == "249") {echo " selected";} echo ">FRANCE, METROPOLITAN
			<option value=\"254\""; if($xmlp->management->enable_country_code == "254") {echo " selected";} echo ">FRENCH GUIANA
			<option value=\"258\""; if($xmlp->management->enable_country_code == "258") {echo " selected";} echo ">FRENCH POLYNESIA
			<option value=\"260\""; if($xmlp->management->enable_country_code == "260") {echo " selected";} echo ">FRENCH SOUTHERN TERRITORIES
			<option value=\"266\""; if($xmlp->management->enable_country_code == "266") {echo " selected";} echo ">GABON
			<option value=\"270\""; if($xmlp->management->enable_country_code == "270") {echo " selected";} echo ">GAMBIA
			<option value=\"268\""; if($xmlp->management->enable_country_code == "268") {echo " selected";} echo ">GEORGIA
			<option value=\"276\""; if($xmlp->management->enable_country_code == "276") {echo " selected";} echo ">GERMANY
			<option value=\"288\""; if($xmlp->management->enable_country_code == "288") {echo " selected";} echo ">GHANA
			<option value=\"292\""; if($xmlp->management->enable_country_code == "292") {echo " selected";} echo ">GIBRALTAR
			<option value=\"300\""; if($xmlp->management->enable_country_code == "300") {echo " selected";} echo ">GREECE
			<option value=\"304\""; if($xmlp->management->enable_country_code == "304") {echo " selected";} echo ">GREENLAND
			<option value=\"308\""; if($xmlp->management->enable_country_code == "308") {echo " selected";} echo ">GRENADA
			<option value=\"312\""; if($xmlp->management->enable_country_code == "312") {echo " selected";} echo ">GUADELOUPE
			<option value=\"316\""; if($xmlp->management->enable_country_code == "316") {echo " selected";} echo ">GUAM
			<option value=\"320\""; if($xmlp->management->enable_country_code == "320") {echo " selected";} echo ">GUATEMALA
			<option value=\"324\""; if($xmlp->management->enable_country_code == "324") {echo " selected";} echo ">GUINEA
			<option value=\"624\""; if($xmlp->management->enable_country_code == "624") {echo " selected";} echo ">GUINEA-BISSAU
			<option value=\"328\""; if($xmlp->management->enable_country_code == "328") {echo " selected";} echo ">GUYANA
			<option value=\"332\""; if($xmlp->management->enable_country_code == "332") {echo " selected";} echo ">HAITI
			<option value=\"334\""; if($xmlp->management->enable_country_code == "334") {echo " selected";} echo ">HEARD ISLAND &amp; MCDONALD ISLANDS
			<option value=\"340\""; if($xmlp->management->enable_country_code == "340") {echo " selected";} echo ">HONDURAS
			<option value=\"344\""; if($xmlp->management->enable_country_code == "344") {echo " selected";} echo ">HONG KONG
			<option value=\"348\""; if($xmlp->management->enable_country_code == "348") {echo " selected";} echo ">HUNGARY
			<option value=\"352\""; if($xmlp->management->enable_country_code == "352") {echo " selected";} echo ">ICELAND
			<option value=\"356\""; if($xmlp->management->enable_country_code == "356") {echo " selected";} echo ">INDIA
			<option value=\"360\""; if($xmlp->management->enable_country_code == "360") {echo " selected";} echo ">INDONESIA
			<option value=\"364\""; if($xmlp->management->enable_country_code == "364") {echo " selected";} echo ">IRAN, ISLAMIC REPUBLIC OF
			<option value=\"368\""; if($xmlp->management->enable_country_code == "368") {echo " selected";} echo ">IRAQ
			<option value=\"372\""; if($xmlp->management->enable_country_code == "372") {echo " selected";} echo ">IRELAND
			<option value=\"376\""; if($xmlp->management->enable_country_code == "376") {echo " selected";} echo ">ISRAEL
			<option value=\"380\""; if($xmlp->management->enable_country_code == "380") {echo " selected";} echo ">ITALY
			<option value=\"388\""; if($xmlp->management->enable_country_code == "388") {echo " selected";} echo ">JAMAICA
			<option value=\"392\""; if($xmlp->management->enable_country_code == "392") {echo " selected";} echo ">JAPAN
			<option value=\"400\""; if($xmlp->management->enable_country_code == "400") {echo " selected";} echo ">JORDAN
			<option value=\"398\""; if($xmlp->management->enable_country_code == "398") {echo " selected";} echo ">KAZAKHSTAN
			<option value=\"404\""; if($xmlp->management->enable_country_code == "404") {echo " selected";} echo ">KENYA
			<option value=\"296\""; if($xmlp->management->enable_country_code == "296") {echo " selected";} echo ">KIRIBATI
			<option value=\"408\""; if($xmlp->management->enable_country_code == "408") {echo " selected";} echo ">KOREA, DEMOCRATIC PEOPLE'S REPUBLIC OF
			<option value=\"410\""; if($xmlp->management->enable_country_code == "410") {echo " selected";} echo ">KOREA, REPUBLIC OF
			<option value=\"414\""; if($xmlp->management->enable_country_code == "414") {echo " selected";} echo ">KUWAIT
			<option value=\"417\""; if($xmlp->management->enable_country_code == "417") {echo " selected";} echo ">KYRGYZSTAN
			<option value=\"418\""; if($xmlp->management->enable_country_code == "418") {echo " selected";} echo ">LAO PEOPLE'S DEMOCRATIC REPUBLIC
			<option value=\"428\""; if($xmlp->management->enable_country_code == "428") {echo " selected";} echo ">LATVIA
			<option value=\"422\""; if($xmlp->management->enable_country_code == "422") {echo " selected";} echo ">LEBANON
			<option value=\"426\""; if($xmlp->management->enable_country_code == "426") {echo " selected";} echo ">LESOTHO
			<option value=\"430\""; if($xmlp->management->enable_country_code == "430") {echo " selected";} echo ">LIBERIA
			<option value=\"434\""; if($xmlp->management->enable_country_code == "434") {echo " selected";} echo ">LIBYAN ARAB JAMAHIRIYA
			<option value=\"438\""; if($xmlp->management->enable_country_code == "438") {echo " selected";} echo ">LIECHTENSTEIN
			<option value=\"440\""; if($xmlp->management->enable_country_code == "440") {echo " selected";} echo ">LITHUANIA
			<option value=\"442\""; if($xmlp->management->enable_country_code == "442") {echo " selected";} echo ">LUXEMBOURG
			<option value=\"446\""; if($xmlp->management->enable_country_code == "446") {echo " selected";} echo ">MACAU
			<option value=\"807\""; if($xmlp->management->enable_country_code == "807") {echo " selected";} echo ">MACEDONIA, THE FORMER YUGOSLAV REPUBLIC OF
			<option value=\"450\""; if($xmlp->management->enable_country_code == "450") {echo " selected";} echo ">MADAGASCAR
			<option value=\"454\""; if($xmlp->management->enable_country_code == "454") {echo " selected";} echo ">MALAWI
			<option value=\"458\""; if($xmlp->management->enable_country_code == "458") {echo " selected";} echo ">MALAYSIA
			<option value=\"462\""; if($xmlp->management->enable_country_code == "462") {echo " selected";} echo ">MALDIVES
			<option value=\"466\""; if($xmlp->management->enable_country_code == "466") {echo " selected";} echo ">MALI
			<option value=\"470\""; if($xmlp->management->enable_country_code == "470") {echo " selected";} echo ">MALTA
			<option value=\"584\""; if($xmlp->management->enable_country_code == "584") {echo " selected";} echo ">MARSHALL ISLANDS
			<option value=\"474\""; if($xmlp->management->enable_country_code == "474") {echo " selected";} echo ">MARTINIQUE
			<option value=\"478\""; if($xmlp->management->enable_country_code == "478") {echo " selected";} echo ">MAURITANIA
			<option value=\"480\""; if($xmlp->management->enable_country_code == "480") {echo " selected";} echo ">MAURITIUS
			<option value=\"175\""; if($xmlp->management->enable_country_code == "175") {echo " selected";} echo ">MAYOTTE
			<option value=\"484\""; if($xmlp->management->enable_country_code == "484") {echo " selected";} echo ">MEXICO
			<option value=\"583\""; if($xmlp->management->enable_country_code == "583") {echo " selected";} echo ">MICRONESIA, FEDERATED STATES OF
			<option value=\"498\""; if($xmlp->management->enable_country_code == "498") {echo " selected";} echo ">MOLDOVA, REPUBLIC OF
			<option value=\"492\""; if($xmlp->management->enable_country_code == "492") {echo " selected";} echo ">MONACO
			<option value=\"496\""; if($xmlp->management->enable_country_code == "496") {echo " selected";} echo ">MONGOLIA
			<option value=\"500\""; if($xmlp->management->enable_country_code == "500") {echo " selected";} echo ">MONTSERRAT
			<option value=\"504\""; if($xmlp->management->enable_country_code == "504") {echo " selected";} echo ">MOROCCO
			<option value=\"508\""; if($xmlp->management->enable_country_code == "508") {echo " selected";} echo ">MOZAMBIQUE
			<option value=\"104\""; if($xmlp->management->enable_country_code == "104") {echo " selected";} echo ">MYANMAR
			<option value=\"516\""; if($xmlp->management->enable_country_code == "516") {echo " selected";} echo ">NAMIBIA
			<option value=\"520\""; if($xmlp->management->enable_country_code == "520") {echo " selected";} echo ">NAURU
			<option value=\"524\""; if($xmlp->management->enable_country_code == "524") {echo " selected";} echo ">NEPAL
			<option value=\"528\""; if($xmlp->management->enable_country_code == "528") {echo " selected";} echo ">NETHERLANDS
			<option value=\"530\""; if($xmlp->management->enable_country_code == "530") {echo " selected";} echo ">NETHERLANDS ANTILLES
			<option value=\"540\""; if($xmlp->management->enable_country_code == "540") {echo " selected";} echo ">NEW CALEDONIA
			<option value=\"554\""; if($xmlp->management->enable_country_code == "554") {echo " selected";} echo ">NEW ZEALAND
			<option value=\"558\""; if($xmlp->management->enable_country_code == "558") {echo " selected";} echo ">NICARAGUA
			<option value=\"562\""; if($xmlp->management->enable_country_code == "562") {echo " selected";} echo ">NIGER
			<option value=\"566\""; if($xmlp->management->enable_country_code == "566") {echo " selected";} echo ">NIGERIA
			<option value=\"570\""; if($xmlp->management->enable_country_code == "570") {echo " selected";} echo ">NIUE
			<option value=\"574\""; if($xmlp->management->enable_country_code == "574") {echo " selected";} echo ">NORFOLK ISLAND
			<option value=\"580\""; if($xmlp->management->enable_country_code == "580") {echo " selected";} echo ">NORTHERN MARIANA ISLANDS
			<option value=\"578\""; if($xmlp->management->enable_country_code == "578") {echo " selected";} echo ">NORWAY
			<option value=\"512\""; if($xmlp->management->enable_country_code == "512") {echo " selected";} echo ">OMAN
			<option value=\"586\""; if($xmlp->management->enable_country_code == "586") {echo " selected";} echo ">PAKISTAN
			<option value=\"585\""; if($xmlp->management->enable_country_code == "585") {echo " selected";} echo ">PALAU
			<option value=\"591\""; if($xmlp->management->enable_country_code == "591") {echo " selected";} echo ">PANAMA
			<option value=\"598\""; if($xmlp->management->enable_country_code == "598") {echo " selected";} echo ">PAPUA NEW GUINEA
			<option value=\"600\""; if($xmlp->management->enable_country_code == "600") {echo " selected";} echo ">PARAGUAY
			<option value=\"604\""; if($xmlp->management->enable_country_code == "604") {echo " selected";} echo ">PERU
			<option value=\"608\""; if($xmlp->management->enable_country_code == "608") {echo " selected";} echo ">PHILIPPINES
			<option value=\"612\""; if($xmlp->management->enable_country_code == "612") {echo " selected";} echo ">PITCAIRN
			<option value=\"616\""; if($xmlp->management->enable_country_code == "616") {echo " selected";} echo ">POLAND
			<option value=\"620\""; if($xmlp->management->enable_country_code == "620") {echo " selected";} echo ">PORTUGAL
			<option value=\"630\""; if($xmlp->management->enable_country_code == "630") {echo " selected";} echo ">PUERTO RICO
			<option value=\"634\""; if($xmlp->management->enable_country_code == "634") {echo " selected";} echo ">QATAR
			<option value=\"638\""; if($xmlp->management->enable_country_code == "638") {echo " selected";} echo ">REUNION
			<option value=\"642\""; if($xmlp->management->enable_country_code == "642") {echo " selected";} echo ">ROMANIA
			<option value=\"643\""; if($xmlp->management->enable_country_code == "643") {echo " selected";} echo ">RUSSIAN FEDERATION
			<option value=\"646\""; if($xmlp->management->enable_country_code == "646") {echo " selected";} echo ">RWANDA
			<option value=\"659\""; if($xmlp->management->enable_country_code == "659") {echo " selected";} echo ">SAINT KITTS AND NEVIS
			<option value=\"662\""; if($xmlp->management->enable_country_code == "662") {echo " selected";} echo ">SAINT LUCIA
			<option value=\"670\""; if($xmlp->management->enable_country_code == "670") {echo " selected";} echo ">SAINT VINCENT AND THE GRENADINES
			<option value=\"882\""; if($xmlp->management->enable_country_code == "882") {echo " selected";} echo ">SAMOA
			<option value=\"674\""; if($xmlp->management->enable_country_code == "674") {echo " selected";} echo ">SAN MARINO
			<option value=\"678\""; if($xmlp->management->enable_country_code == "678") {echo " selected";} echo ">SAO TOME AND PRINCIPE
			<option value=\"682\""; if($xmlp->management->enable_country_code == "682") {echo " selected";} echo ">SAUDI ARABIA
			<option value=\"686\""; if($xmlp->management->enable_country_code == "686") {echo " selected";} echo ">SENEGAL
			<option value=\"381\""; if($xmlp->management->enable_country_code == "381") {echo " selected";} echo ">SERBIA
			<option value=\"690\""; if($xmlp->management->enable_country_code == "690") {echo " selected";} echo ">SEYCHELLES
			<option value=\"694\""; if($xmlp->management->enable_country_code == "694") {echo " selected";} echo ">SIERRA LEONE
			<option value=\"702\""; if($xmlp->management->enable_country_code == "702") {echo " selected";} echo ">SINGAPORE
			<option value=\"703\""; if($xmlp->management->enable_country_code == "703") {echo " selected";} echo ">SLOVAKIA (Slovak Republic)
			<option value=\"705\""; if($xmlp->management->enable_country_code == "705") {echo " selected";} echo ">SLOVENIA
			<option value=\"90\""; if($xmlp->management->enable_country_code == "90") {echo " selected";} echo ">SOLOMON ISLANDS
			<option value=\"706\""; if($xmlp->management->enable_country_code == "706") {echo " selected";} echo ">SOMALIA
			<option value=\"710\""; if($xmlp->management->enable_country_code == "710") {echo " selected";} echo ">SOUTH AFRICA
			<option value=\"724\""; if($xmlp->management->enable_country_code == "724") {echo " selected";} echo ">SPAIN
			<option value=\"144\""; if($xmlp->management->enable_country_code == "144") {echo " selected";} echo ">SRI LANKA
			<option value=\"654\""; if($xmlp->management->enable_country_code == "654") {echo " selected";} echo ">SAINT HELENA
			<option value=\"666\""; if($xmlp->management->enable_country_code == "666") {echo " selected";} echo ">SAINT PIERRE AND MIQUELON
			<option value=\"736\""; if($xmlp->management->enable_country_code == "736") {echo " selected";} echo ">SUDAN
			<option value=\"740\""; if($xmlp->management->enable_country_code == "740") {echo " selected";} echo ">SURINAME
			<option value=\"744\""; if($xmlp->management->enable_country_code == "744") {echo " selected";} echo ">SVALBARD AND JAN MAYEN ISLANDS
			<option value=\"748\""; if($xmlp->management->enable_country_code == "748") {echo " selected";} echo ">SWAZILAND
			<option value=\"752\""; if($xmlp->management->enable_country_code == "752") {echo " selected";} echo ">SWEDEN
			<option value=\"756\""; if($xmlp->management->enable_country_code == "756") {echo " selected";} echo ">SWITZERLAND
			<option value=\"760\""; if($xmlp->management->enable_country_code == "760") {echo " selected";} echo ">SYRIAN ARAB REPUBLIC
			<option value=\"158\""; if($xmlp->management->enable_country_code == "158") {echo " selected";} echo ">TAIWAN, PROVINCE OF CHINA
			<option value=\"762\""; if($xmlp->management->enable_country_code == "762") {echo " selected";} echo ">TAJIKISTAN
			<option value=\"834\""; if($xmlp->management->enable_country_code == "834") {echo " selected";} echo ">TANZANIA, UNITED REPUBLIC OF
			<option value=\"764\""; if($xmlp->management->enable_country_code == "764") {echo " selected";} echo ">THAILAND
			<option value=\"768\""; if($xmlp->management->enable_country_code == "768") {echo " selected";} echo ">TOGO
			<option value=\"772\""; if($xmlp->management->enable_country_code == "772") {echo " selected";} echo ">TOKELAU
			<option value=\"776\""; if($xmlp->management->enable_country_code == "776") {echo " selected";} echo ">TONGA
			<option value=\"780\""; if($xmlp->management->enable_country_code == "780") {echo " selected";} echo ">TRINIDAD AND TOBAGO
			<option value=\"788\""; if($xmlp->management->enable_country_code == "788") {echo " selected";} echo ">TUNISIA
			<option value=\"792\""; if($xmlp->management->enable_country_code == "792") {echo " selected";} echo ">TURKEY
			<option value=\"795\""; if($xmlp->management->enable_country_code == "795") {echo " selected";} echo ">TURKMENISTAN
			<option value=\"796\""; if($xmlp->management->enable_country_code == "796") {echo " selected";} echo ">TURKS AND CAICOS ISLANDS
			<option value=\"798\""; if($xmlp->management->enable_country_code == "798") {echo " selected";} echo ">TUVALU
			<option value=\"800\""; if($xmlp->management->enable_country_code == "800") {echo " selected";} echo ">UGANDA
			<option value=\"804\""; if($xmlp->management->enable_country_code == "804") {echo " selected";} echo ">UKRAINE
			<option value=\"784\""; if($xmlp->management->enable_country_code == "784") {echo " selected";} echo ">UNITED ARAB EMIRATES
			<option value=\"826\""; if($xmlp->management->enable_country_code == "826") {echo " selected";} echo ">UNITED KINGDOM
			<option value=\"840\""; if($xmlp->management->enable_country_code == "840") {echo " selected";} echo ">UNITED STATES
			<option value=\"581\""; if($xmlp->management->enable_country_code == "581") {echo " selected";} echo ">UNITED STATES MINOR OUTLYING ISLANDS
			<option value=\"598\""; if($xmlp->management->enable_country_code == "598") {echo " selected";} echo ">URUGUAY
			<option value=\"860\""; if($xmlp->management->enable_country_code == "860") {echo " selected";} echo ">UZBEKISTAN
			<option value=\"548\""; if($xmlp->management->enable_country_code == "548") {echo " selected";} echo ">VANUATU
			<option value=\"336\""; if($xmlp->management->enable_country_code == "336") {echo " selected";} echo ">VATICAN CITY STATE (HOLY SEE)
			<option value=\"862\""; if($xmlp->management->enable_country_code == "862") {echo " selected";} echo ">VENEZUELA
			<option value=\"704\""; if($xmlp->management->enable_country_code == "704") {echo " selected";} echo ">VIET NAM
			<option value=\"92\""; if($xmlp->management->enable_country_code == "92") {echo " selected";} echo ">VIRGIN ISLANDS (BRITISH)
			<option value=\"850\""; if($xmlp->management->enable_country_code == "850") {echo " selected";} echo ">VIRGIN ISLANDS (U.S.)
			<option value=\"876\""; if($xmlp->management->enable_country_code == "876") {echo " selected";} echo ">WALLIS AND FUTUNA ISLANDS
			<option value=\"732\""; if($xmlp->management->enable_country_code == "732") {echo " selected";} echo ">WESTERN SAHARA
			<option value=\"887\""; if($xmlp->management->enable_country_code == "887") {echo " selected";} echo ">YEMEN
			<option value=\"891\""; if($xmlp->management->enable_country_code == "891") {echo " selected";} echo ">YUGOSLAVIA
			<option value=\"180\""; if($xmlp->management->enable_country_code == "180") {echo " selected";} echo ">ZAIRE
			<option value=\"894\""; if($xmlp->management->enable_country_code == "894") {echo " selected";} echo ">ZAMBIA
			<option value=\"716\""; if($xmlp->management->enable_country_code == "716") {echo " selected";} echo ">ZIMBABWE
		</select>
		</td>
		<td id=\"desc\">This option sets the Country Code for your nodes. This ensures they are compliant with local laws.</td>
		</tr>

		<tr>
		<td id=\"name\">Notification Email</td>
		<td id=\"data\"><input type=\"text\" name=\"notifymail\" value=\"" . $xmlp->robindash->notifymail . "\"></td>
		<td id=\"desc\">The email to sent node alerts to.</td>
		</tr>
		
		<tr>
		<td id=\"name\">Account Type</td>
		<td id=\"data\">
			<input type=\"radio\" name=\"editmode\" value=\"easy\""; if($xmlp->robindash->editmode == "easy") {echo " checked";} echo ">Easy
			<br />
			<input type=\"radio\" name=\"editmode\" value=\"advanced\""; if($xmlp->robindash->editmode == "advanced") {echo " checked";} echo ">Advanced
		</td>
		<td id=\"desc\">Easy: Ideal for people only needing to set basic options.<br />Advanced: Ideal for people who need more advanced functionality.</td>
		</tr>
		
		<tr>
		<td id=\"name\">CloudTrax Forwarding</td>
		<td id=\"data\"><input type=\"checkbox\" name=\"forwardcheck\""; if($xmlp->robindash->forwardcheck == "1") {echo " checked";} echo "></td>
		<td id=\"desc\">Forwards your nodes checkins to the CloudTrax dashboard.</td>
		</tr>
		
		<tr><td colspan=\"3\"><hr></td></tr>
		
		<tr>
		<td id=\"name\">Delete Account</td>
		<td id=\"data\"><input type=\"button\" value=\"Delete My Account\" style=\"margin-left:10%;width:80%;\" onclick=\"check_delete();\"></td>
		<td id=\"desc\">Click the button to send a request to delete your account.<br>This will take around 24 hours.</td>
		</tr>

		<tr><td id=\"submit\" colspan=\"3\"><br><input type=\"submit\" name=\"submit\" value=\"Save Settings!\"><br><br></td></tr>
		</table>
		</form>
		
		<script>
		function popup() {window.open('" . $wdir . "edit-node.php', 'popup', 'toolbar=0,scrollbars=1,location=0,statusbar=0,menubar=0,resizable=1,width=1024,height=768');}
		function popup2() {window.open('" . $wdir . "edit-firewall.php', 'popup', 'toolbar=0,scrollbars=1,location=0,statusbar=0,menubar=0,resizable=1,width=1024,height=768');}
		function error() {alert('The Firewall is not available on your account.\\n\\nYou must have NoDogSplash enabled as your Captive Portal. Additionally, you must have created your robin-dash account after the 28th February 2011. If this is not the case, get your account upgraded to the new format by emailing us at: support@robin-dash.net');}
		function check_delete() {
			var result = prompt(\"Are you sure that you would like to delete your account?\\nIf so, please type the following code: "; $confirm_string = rand(1000, 9999); echo $confirm_string . ", then press OK.\");
			if(result === \"" . $confirm_string . "\") {window.location = '" . $wdir . "?action=delete-account';}
		}
		</script>";}



		else if($page == "public-network") {echo "<form action=\"" . $wdir . "edit.php?page=public-network\" method=\"POST\">
		<table>
		<tr>
		<td id=\"name\">Network Name</td>
		<td id=\"data\"><input type=\"text\" name=\"publicname\" value=\"" . str_replace("*", " ", $xmlp->wireless->public_ssid) . "\"></td>
		<td id=\"desc\">The name (SSID) that will be displayed to the end-user.</td>
		</tr>
		
		<tr>
		<td id=\"name\">Use Node Name</td>
		<td id=\"data\"><input type=\"checkbox\" name=\"usenodename\""; if($xmlp->robindash->usenodename == "1") {echo " checked";} echo "></td>
		<td id=\"desc\">The node name is shown instead of the network name</td>
		</tr>

		<tr><td colspan=\"3\"><hr></td></tr>
		
		<tr>
		<td id=\"name\"><b>NoDogSplash</b></td>
		<td id=\"data\"><input type=\"radio\" name=\"captiveportal\" value=\"nodogsplash\" onclick=\"window.location = '" . $wdir . "edit.php?page=public-network&captiveportal=nodogsplash';\" ";

		if($_GET['captiveportal'] == "nodogsplash") {echo " checked";}
		else if($xmlp->robindash->captivename == "nodogsplash" && !$_GET['captiveportal']) {echo " checked";}
		else if($xmlp->robindash->captivename == "none" && !$_GET['captiveportal']) {echo " checked";}
		
		echo "></td>
		<td id=\"desc\">Free, easy to use splash page, bandwidth and user control.</td>
		</tr>";
		
		if($xmlp->nodog->AuthenticateImmediately == "0") {$nds_splashpage = " checked";}
		else {$nds_splashpage = "";}
		

		$nds = "<tr>
		<td id=\"name\">Enable Splash Page</td>
		<td id=\"data\"><input type=\"checkbox\" name=\"splashpage\" id=\"splashpage\" " . $nds_splashpage ."></td>
		<td id=\"desc\">The Splash Page is shown when the user first joins, and then has to click a link to logon.<br><a href=\"#\" onclick=\"popup();\" id=\"a\">Customize your Splash Page</a></td>
		</tr>

		<tr>
		<td id=\"name\">Redirect URL</td>
		<td id=\"data\"><input type=\"text\" name=\"redirecturl\" id=\"redirecturl\" value=\"" . $xmlp->nodog->RedirectURL . "\"></td>
		<td id=\"desc\">You can enter a page to be shown after the user visits the Splash Page, leave blank to disable.</td>
		</tr>

		<tr>
		<td id=\"name\">Download Speed</td>
		<td id=\"data\"><input type=\"text\" name=\"downspeed\" value=\"" . $xmlp->nodog->DownloadLimit . "\"></td>
		<td id=\"desc\">Download Speed limit in Kilobits/sec</td>
		</tr>

		<tr>
		<td id=\"name\">Upload Speed</td>
		<td id=\"data\"><input type=\"text\" name=\"upspeed\" value=\"" . $xmlp->nodog->UploadLimit . "\"></td>
		<td id=\"desc\">Upload Speed limit in Kilobits/sec</td>
		</tr>
		
		<tr>
		<td id=\"name\">White List</td>
		<td id=\"data\"><textarea name=\"whitelist\">" . str_replace("\"\"", "", $xmlp->nodog->TrustedMACList) . "</textarea></td>
		<td id=\"desc\">Useful for clients that cannot view the Splash Page.<br>e.g. VoIP ATA's, Gaming Consoles, etc. Enter one per line.</td>
		</tr>
		
		<!--tr>
		<td id=\"name\">Access Control<br>List</td>
		<td id=\"data\"><textarea name=\"accesscontrol\">" . str_replace("\"\"", "", $xmlp->nodog->TrustedMACList) . "</textarea></td>
		<td id=\"desc\">MAC Address's of devices allowed to use the network. All other users will be blocked. Enter one per line. Leave blank to enable all users.</td>
		</tr-->";
		
		if(isset($_GET['captiveportal']) && $_GET['captiveportal'] == "nodogsplash") {echo $nds;}
		else if($xmlp->robindash->captivename == "nodogsplash" && !isset($_GET['captiveportal'])) {echo $nds;}
		else if($xmlp->robindash->captivename == "none" && !isset($_GET['captiveportal'])) {echo $nds;}
		else {echo "";}

		echo "<tr>
		<td id=\"name\"><b>Coova</b></td>
		<td id=\"data\"><input type=\"radio\" name=\"captiveportal\" value=\"coova\" onclick=\"window.location = '" . $wdir . "edit.php?page=public-network&captiveportal=coova';\" ";

		if($_GET['captiveportal'] == "none" || $_GET['captiveportal'] == "nodogsplash" || $_GET['captiveportal'] == "wifidog") {echo "";}
		else if($_GET['captiveportal'] == "coova") {echo " disabled";}
		else if($xmlp->robindash->captivename == "coova") {echo " disabled";}
		else if($xmlp->robindash->captivename == "wifirush") {echo " disabled";}
		
		echo "></td>
		<td id=\"desc\">Using a Coova server gives you more flexibility over users access.</td>
		</tr>";
		
		$coova = "<tr>
		<td id=\"name\">Type</td>
		<td id=\"data\"><input type=\"radio\" name=\"captiveportal\" value=\"coova\""; if($xmlp->robindash->captivename == "coova") {$coova = $coova . " checked";} else if($xmlp->robindash->captivename == "nodogsplash") {$coova = $coova . " checked";} else if($xmlp->robindash->captivename == "wifidog") {$coova = $coova . " checked";} else if($xmlp->robindash->captivename == "none") {$coova = $coova . " checked";} $coova = $coova . "> Coova.net/CoovaOM<br /><input type=\"radio\" name=\"captiveportal\" value=\"wifirush\""; if($xmlp->robindash->captivename == "wifirush") {$coova = $coova . " checked";} $coova = $coova . "> WifiRush</td>
		<td id=\"desc\">Sets the type of service to use with Coova.</td>
		</tr>
		
		<tr>
		<td id=\"name\">Radius Server/s</td>
		<td id=\"data\"><input type=\"text\" name=\"radiusserver1\" value=\"" . $xmlp->chilli->agent_radiusserver1 . "\"><input type=\"text\" name=\"radiusserver2\" id=\"radiusserver2\" value=\"" . $xmlp->chilli->agent_radiusserver2 . "\"></td>
		<td id=\"desc\">Server #1<br><br>Server #2</td>
		</tr>

		<tr>
		<td id=\"name\">Radius NAS ID</td>
		<td id=\"data\"><input type=\"text\" name=\"radiusnasid\" value=\"" . $xmlp->chilli->agent_radiusnasid . "\"></td>
		<td id=\"desc\"></td>
		</tr>

		<tr>
		<td id=\"name\">UAM Server</td>
		<td id=\"data\"><input type=\"text\" name=\"radiusuamserver\" value=\"" . $xmlp->chilli->agent_uamserver . "\"></td>
		<td id=\"desc\"></td>
		</tr>
		
		<tr>
		<td id=\"name\">UAM URL</td>
		<td id=\"data\"><input type=\"text\" name=\"radiusuamurl\" value=\"" . $xmlp->chilli->agent_uamurl . "\"></td>
		<td id=\"desc\"></td>
		</tr>

		<tr>
		<td id=\"name\">UAM Secret</td>
		<td id=\"data\"><input type=\"text\" name=\"radiusuamsecret\" value=\"" . $xmlp->chilli->agent_uamsecret . "\"></td>
		<td id=\"desc\"></td>
		</tr>
		
		<tr>
		<td id=\"name\">Radius Secret</td>
		<td id=\"data\"><input type=\"text\" name=\"radiussecret\" value=\"" . $xmlp->chilli->agent_radiussecret . "\"></td>
		<td id=\"desc\"></td>
		</tr>

		<tr>
		<td id=\"name\">Allowed Domains</td>
		<td id=\"data\"><input type=\"text\" name=\"radiusdomains\" value=\"" . $xmlp->chilli->agent_uamdomain . "\"></td>
		<td id=\"desc\"></td>
		</tr>
		
		<tr>
		<td id=\"name\">Admin User</td>
		<td id=\"data\"><input type=\"text\" name=\"radiusadmusr\" value=\"" . $xmlp->chilli->agent_admusr . "\"></td>
		<td id=\"desc\"><b>Needed for Coova.net services</b></td>
		</tr>
		
		<tr>
		<td id=\"name\">Admin Password</td>
		<td id=\"data\"><input type=\"text\" name=\"radiusadmpwd\" value=\"" . $xmlp->chilli->agent_admpwd . "\"></td>
		<td id=\"desc\"><b>Needed for Coova.net services</b></td>
		</tr>
		
		<tr>
		<td id=\"name\">MAC Password</td>
		<td id=\"data\"><input type=\"text\" name=\"macpasswd\" value=\"" . $xmlp->chilli->agent_macpasswd . "\"></td>
		<td id=\"desc\"><b>Needed for WifiRush services</b></td>
		</tr>";

		if(isset($_GET['captiveportal']) && $_GET['captiveportal'] == "coova") {echo $coova;}
		else if(!isset($_GET['captiveportal']) && $xmlp->robindash->captivename == "coova") {echo $coova;}
		else {echo "";}
		
		echo "<tr>
		<td id=\"name\"><b>wifidog</b></td>
		<td id=\"data\"><input type=\"radio\" name=\"captiveportal\" value=\"wifidog\" onclick=\"window.location = '" . $wdir . "edit.php?page=public-network&captiveportal=wifidog';\" ";

		if($_GET['captiveportal'] == "wifidog") {echo " checked";}
		else if($xmlp->robindash->captivename == "wifidog" && !$_GET['captiveportal']) {echo " checked";}
		
		echo "></td>
		<td id=\"desc\">Using a WiFiDog server gives you more flexibility over users access.</td>
		</tr>";
		

		$wifidog = "<tr>
		<td id=\"name\">Server #1: Hostname</td>
		<td id=\"data\"><input type=\"text\" name=\"MainAuthServer_Hostname\" value=\"" . $xmlp->wifidog->MainAuthServer_Hostname . "\"></td>
		<td id=\"desc\">e.g. http://<b>auth1.yourserver.com</b>/authpuppy/</td>
		</tr>
		
		<tr>
		<td id=\"name\">Server #1: Path</td>
		<td id=\"data\"><input type=\"text\" name=\"MainAuthServer_Path\" value=\"" . $xmlp->wifidog->MainAuthServer_Path . "\"></td>
		<td id=\"desc\">e.g. http://auth1.yourserver.com<b>/authpuppy/</b></td>
		</tr>
		
		<tr>
		<td id=\"name\">Server #1: Has SSL?</td>
		<td id=\"data\"><input type=\"checkbox\" name=\"MainAuthServer_SSLAvailable\""; if($xmlp->wifidog->MainAuthServer_SSLAvailable == "1") {$wifidog = $wifidog . " checked";} $wifidog = $wifidog . "></td>
		<td id=\"desc\">&nbsp;</td>
		</tr>
		
		<tr><td colspan=\"3\"><hr /></td></tr>
		
		<tr>
		<td id=\"name\">Server #2: Hostname</td>
		<td id=\"data\"><input type=\"text\" name=\"SecAuthServer_Hostname\" value=\"" . $xmlp->wifidog->SecAuthServer_Hostname . "\"></td>
		<td id=\"desc\">e.g. http://<b>auth2.yourserver.com</b>/authpuppy/</td>
		</tr>
		
		<tr>
		<td id=\"name\">Server #2: Path</td>
		<td id=\"data\"><input type=\"text\" name=\"SecAuthServer_Path\" value=\"" . $xmlp->wifidog->SecAuthServer_Path . "\"></td>
		<td id=\"desc\">e.g. http://auth2.yourserver.com<b>/authpuppy/</b></td>
		</tr>
		
		<tr>
		<td id=\"name\">Server #2: Has SSL?</td>
		<td id=\"data\"><input type=\"checkbox\" name=\"SecAuthServer_SSLAvailable\""; if($xmlp->wifidog->SecAuthServer_SSLAvailable == "1") {$wifidog = $wifidog . " checked";} $wifidog = $wifidog . "></td>
		<td id=\"desc\">&nbsp;</td>
		</tr>
		
		<tr><td colspan=\"3\"><hr /></td></tr>
		
		<tr>
		<td id=\"name\">Trusted MAC List</td>
		<td id=\"data\"><textarea name=\"gateway_TrustedMACList\">" . str_replace("\"\"", "", $xmlp->wifidog->gateway_TrustedMACList) . "</textarea></td>
		<td id=\"desc\">Useful for clients that cannot view the Splash Page.<br>e.g. VoIP ATA's, Gaming Consoles, etc.</td>
		</tr>";
		
		if(isset($_GET['captiveportal']) && $_GET['captiveportal'] == "wifidog") {echo $wifidog;}
		else if($xmlp->robindash->captivename == "wifidog" && !isset($_GET['captiveportal'])) {echo $wifidog;}
		else {echo "";}
		

		echo "<tr><td id=\"submit\" colspan=\"3\"><br><input type=\"submit\" name=\"submit\" id=\"button\" value=\"Save Settings!\"><br><br></td></tr>
		</table>
		</form>


		<script>
		function popup() {window.open('" . $wdir . "edit-nds.php', '" . rand(1, 9999999) . "', 'toolbar=0,scrollbars=1,location=0,statusbar=0,menubar=0,resizable=1,width=' + screen.availWidth + ', height=' + screen.availHeight + '');}
		</script>";}



		else if($page == "private-network") {echo "<form action=\"" . $wdir . "edit.php?page=private-network\" method=\"POST\">
		<table>
		<tr>
		<td id=\"name\">Enable</td>
		<td id=\"data\"><input type=\"checkbox\" name=\"privateenable\""; if($xmlp->mesh->Myap_up == "1") {echo " checked";} echo "></td>
		<td id=\"desc\">Enables the Private wireless network.</td>
		</tr>

		<tr>
		<td id=\"name\">Network Name</td>
		<td id=\"data\"><input type=\"text\" name=\"privatename\" value=\"" . str_replace("*", " ", $xmlp->wireless->private_ssid). "\"></td>
		<td id=\"desc\">The name (SSID) that will be displayed to the end-user.</td>
		</tr>

		<tr>
		<td id=\"name\">Network Password</td>
		<td id=\"data\"><input type=\"password\" name=\"privatepass\" id=\"privatepass\" value=\"" . $xmlp->wireless->private_key . "\" style=\"width:80%;\" autocomplete=\"off\"><input type=\"checkbox\" onclick=\"pwdchanger('privatepass');\" style=\"width:10%;margin-top:-14%;margin-left:85%;\" title=\"Show/Hide Password\"></td>
		<td id=\"desc\">Password greater than 8 characters for securing the network.</td>
		</tr>

		<tr><td id=\"submit\" colspan=\"3\"><br><input type=\"submit\" name=\"submit\" value=\"Save Settings!\"><br><br></td></tr>
		</table>
		</form>";}



		else if($page == "security") {echo "<form action=\"" . $wdir . "edit.php?page=security\" method=\"POST\" enctype=\"multipart/form-data\">
		<table>
		<tr>
		<td id=\"name\">Node Password</td>
		<td id=\"data\"><input type=\"password\" name=\"rootpwd\" id=\"rootpwd\" value=\"" . $xmlp->management->enable_rootpwd . "\" style=\"width:80%;\" autocomplete=\"off\"><input type=\"checkbox\" onclick=\"pwdchanger('rootpwd');\" style=\"width:10%;margin-top:-14%;margin-left:85%;\" title=\"Show/Hide Password\"></td>
		<td id=\"desc\">Password to use for the nodes on this network for SSH and the Local Web Pages.</td>
		</tr>

		<tr><td id=\"submit\" colspan=\"3\"><br><input type=\"submit\" name=\"submit\" value=\"Save Settings!\"><br><br></td></tr>
		</table>
		</form>";}



		else if($page == "radio") {echo "<form action=\"" . $wdir . "edit.php?page=radio\" method=\"POST\">
		<table>
		<tr>
		<td id=\"name\">2.4GHz Channel</td>
		<td id=\"data\">
		<select name=\"radiochannel\" value=\"" . $xmlp->radio->channel_alternate . "\">
			<option value=\"1\" "; if($xmlp->radio->channel_alternate == "1") {echo " selected";} echo ">1
			<option value=\"2\" "; if($xmlp->radio->channel_alternate == "2") {echo " selected";} echo ">2
			<option value=\"3\" "; if($xmlp->radio->channel_alternate == "3") {echo " selected";} echo ">3
			<option value=\"4\" "; if($xmlp->radio->channel_alternate == "4") {echo " selected";} echo ">4
			<option value=\"5\" "; if($xmlp->radio->channel_alternate == "5") {echo " selected";} echo ">5
			<option value=\"6\" "; if($xmlp->radio->channel_alternate == "6") {echo " selected";} echo ">6
			<option value=\"7\" "; if($xmlp->radio->channel_alternate == "7") {echo " selected";} echo ">7
			<option value=\"8\" "; if($xmlp->radio->channel_alternate == "8") {echo " selected";} echo ">8
			<option value=\"9\" "; if($xmlp->radio->channel_alternate == "9") {echo " selected";} echo ">9
			<option value=\"10\" "; if($xmlp->radio->channel_alternate == "10") {echo " selected";} echo ">10
			<option value=\"11\" "; if($xmlp->radio->channel_alternate == "11") {echo " selected";} echo ">11
		</select>
		</td>
		<td id=\"desc\">2.4GHz channel selection for the wireless network (1-11).</td>
		</tr>

		<tr><td id=\"submit\" colspan=\"3\"><br><input type=\"submit\" name=\"submit\" value=\"Save Settings!\"><br><br></td></tr>
		</table>
		</form>";}



		else if($page == "firmware") {echo "<form action=\"" . $wdir . "edit.php?page=firmware\" method=\"POST\" name=\"form\">
		<table>
		<tr>
		<tr>
		<td id=\"name\">Disable automatic updates</td>
		<td id=\"data\"><input type=\"checkbox\" name=\"freeze_version\""; if($xmlp->management->freeze_version == "1") {echo " checked";} echo "></td>
		<td id=\"desc\">Check the box to freeze at the current version</td>
		</tr>
		
		<tr>
		<td id=\"name\">Hosted Firmware</td>
		<td id=\"data\">
		<select name=\"services_upgd_srv\">
			<option disabled>Choose your Firmware</option>
			<option disabled>--------------------------------------------------------------</option>
			<option value=\"svn6.assembla.com/svn/RobinMesh/downloads/firmware/stable/r2690/\""; if($xmlp->general->services_upgd_srv == "svn6.assembla.com/svn/RobinMesh/downloads/firmware/stable/r2690/") {echo " selected";} echo ">Robin-Mesh: Stable</option>
			<option value=\"svn6.assembla.com/svn/RobinMesh/downloads/firmware/development/\""; if($xmlp->general->services_upgd_srv == "svn6.assembla.com/svn/RobinMesh/downloads/firmware/development/") {echo " selected";} echo ">Robin-Mesh: Development</option>
		</select>
		</td>
		<td id=\"desc\">Sets the Hosted Firmware to use on your network.</td>
		</tr>
		
		<tr><td id=\"submit\" colspan=\"3\"><br><input type=\"submit\" name=\"submit\" value=\"Save Settings!\"><br><br></td></tr>
		</table>
		</form>";}



		else if($page == "miscellaneous") {echo "<form action=\"" . $wdir . "edit.php?page=miscellaneous\" method=\"POST\" name=\"form\">
		<table>		
		<tr>
		<td id=\"name\">Enable SMTP Redirection</td>
		<td id=\"data\"><input type=\"checkbox\" name=\"filter_SMTP_rdir\""; if($xmlp->iprules->filter_SMTP_rdir == "1") {echo " checked";} echo " ></td>
		<td id=\"desc\">Enables the redirection of SMTP to an external server.</td>
		</tr>
		
		<tr>
		<td id=\"name\">SMTP Redirection Server</td>
		<td id=\"data\"><input type=\"text\" name=\"filter_SMTP_dest\" value=\"" . $xmlp->iprules->filter_SMTP_dest . "\"></td>
		<td id=\"desc\">The server to redirect SMTP traffic to.</td>
		</tr>

		<tr><td colspan=\"3\"><hr></td></tr>

		<tr>
		<td id=\"name\">Public DNS Server</td>
		<td id=\"data\">
		<select name=\"public_dns\">
			<option value=\"0\""; if($xmlp->management->enable_public_dns == "0") {echo " selected";} echo ">Your ISP
			<option value=\"1\""; if($xmlp->management->enable_public_dns == "1") {echo " selected";} echo ">OpenDNS
			<option value=\"2\""; if($xmlp->management->enable_public_dns == "2") {echo " selected";} echo ">OpenDNS FamilyShield
			<option value=\"3\""; if($xmlp->management->enable_public_dns == "3") {echo " selected";} echo ">Google Public DNS
			<option value=\"4\""; if($xmlp->management->enable_public_dns == "4") {echo " selected";} echo ">ScrubIT
			<option value=\"5\""; if($xmlp->management->enable_public_dns == "5") {echo " selected";} echo ">DNS Advantage
		</select>
		</td>
		<td id=\"desc\">Sets the DNS server to use for your network.</td>
		</tr>

		<tr><td colspan=\"3\"><hr></td></tr>

		<tr>
		<td id=\"name\">Enable Scheduled Reboot</td>
		<td id=\"data\"><input type=\"checkbox\" name=\"enable_force_reboot\""; if($xmlp->management->enable_force_reboot == "1") {echo " checked";} echo "></td>
		<td id=\"desc\">Enables a Schedule that says when the nodes will reboot.</td>
		</tr>
		
		<tr>
		<td id=\"name\">Scheduled Reboot<br>Date</td>
		<td id=\"data\">
			<select name=\"enable_force_reboot_date\">
				<option value=\"never\""; if($xmlp->management->enable_force_reboot_date == "never") {echo " selected";} echo ">Never
				<option value=\"24\""; if($xmlp->management->enable_force_reboot_date == "24") {echo " selected";} echo ">Daily
				<option value=\"48\""; if($xmlp->management->enable_force_reboot_date == "48") {echo " selected";} echo ">Bi-Daily
				<option value=\"w\""; if($xmlp->management->enable_force_reboot_date == "w") {echo " selected";} echo ">Weekly
				<option value=\"m\""; if($xmlp->management->enable_force_reboot_date == "m") {echo " selected";} echo ">Monthly
			</select>
		</td>
		<td id=\"desc\">Enter the day to perform a Scheduled Reboot.</td>
		</tr>
		
		<tr>
		<td id=\"name\">Scheduled Reboot<br>Time</td>
		<td id=\"data\">
			<select name=\"enable_force_reboot_time\">
				<option value=\"0\""; if($xmlp->management->enable_force_reboot_time == "0") {echo " selected";} echo ">0
				<option value=\"1\""; if($xmlp->management->enable_force_reboot_time == "1") {echo " selected";} echo ">1
				<option value=\"2\""; if($xmlp->management->enable_force_reboot_time == "2") {echo " selected";} echo ">2
				<option value=\"3\""; if($xmlp->management->enable_force_reboot_time == "3") {echo " selected";} echo ">3
				<option value=\"4\""; if($xmlp->management->enable_force_reboot_time == "4") {echo " selected";} echo ">4
				<option value=\"5\""; if($xmlp->management->enable_force_reboot_time == "5") {echo " selected";} echo ">5
				<option value=\"6\""; if($xmlp->management->enable_force_reboot_time == "6") {echo " selected";} echo ">6
				<option value=\"7\""; if($xmlp->management->enable_force_reboot_time == "7") {echo " selected";} echo ">7
				<option value=\"8\""; if($xmlp->management->enable_force_reboot_time == "8") {echo " selected";} echo ">8
				<option value=\"9\""; if($xmlp->management->enable_force_reboot_time == "9") {echo " selected";} echo ">9
				<option value=\"10\""; if($xmlp->management->enable_force_reboot_time == "10") {echo " selected";} echo ">10
				<option value=\"11\""; if($xmlp->management->enable_force_reboot_time == "11") {echo " selected";} echo ">11
				<option value=\"12\""; if($xmlp->management->enable_force_reboot_time == "12") {echo " selected";} echo ">12
				<option value=\"13\""; if($xmlp->management->enable_force_reboot_time == "13") {echo " selected";} echo ">13
				<option value=\"14\""; if($xmlp->management->enable_force_reboot_time == "14") {echo " selected";} echo ">14
				<option value=\"15\""; if($xmlp->management->enable_force_reboot_time == "15") {echo " selected";} echo ">15
				<option value=\"16\""; if($xmlp->management->enable_force_reboot_time == "16") {echo " selected";} echo ">16
				<option value=\"17\""; if($xmlp->management->enable_force_reboot_time == "17") {echo " selected";} echo ">17
				<option value=\"18\""; if($xmlp->management->enable_force_reboot_time == "18") {echo " selected";} echo ">18
				<option value=\"19\""; if($xmlp->management->enable_force_reboot_time == "19") {echo " selected";} echo ">19
				<option value=\"20\""; if($xmlp->management->enable_force_reboot_time == "20") {echo " selected";} echo ">20
				<option value=\"21\""; if($xmlp->management->enable_force_reboot_time == "21") {echo " selected";} echo ">21
				<option value=\"22\""; if($xmlp->management->enable_force_reboot_time == "22") {echo " selected";} echo ">22
				<option value=\"23\""; if($xmlp->management->enable_force_reboot_time == "23") {echo " selected";} echo ">23
			</select>
		</td>
		<td id=\"desc\">Enter the Hour of the Day to perform a Scheduled Reboot.</td>
		</tr>

		<tr><td id=\"submit\" colspan=\"3\"><br><input type=\"submit\" name=\"submit\" value=\"Save Settings!\"><br><br></td></tr>
		</table>
		</form>";}



		else {echo "<center><h1>Sorry,</h1>This feature has not yet been implemented.</center>";}
		?>
		</div>
		<div id="sidebar"></div>	
	</div>
</div>

<script>
function pwdchanger(element) {
	var current_state = document.getElementById(element).type;
	
	if(current_state == 'password') {document.getElementById(element).type = 'text';}
	else {document.getElementById(element).type = 'password';}
}
</script>
<?php echo $tracker; ?>
</body>
</html>