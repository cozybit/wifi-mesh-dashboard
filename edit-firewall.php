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


if($_SESSION['user'] && $_SESSION['pass'] && file_exists($dir . "data/" . $_SESSION['user'] . ".xml")) {
	$xmlp = simplexml_load_file($dir . "data/" . $_SESSION['user'] . ".xml");

	if($_SESSION['pass'] == $xmlp->robindash->password) {$networkname = $_SESSION['user'];}
	else {
		session_destroy();
		header("Location: " . $wdir);
		exit;
	}
}
else {
	session_destroy();
	header("Location: " . $wdir);
	exit;
}

if($xmlp->robindash->firewall->http_authenticated == "") {
	$fc = file_get_contents($dir . "data/" . $networkname . ".xml");
	$fc = str_replace("</robindash>", "<firewall>\n<http_authenticated>1</http_authenticated>\n<https_unauthenticated>0</https_unauthenticated>\n<https_authenticated>1</https_authenticated>\n\n<ftp_unauthenticated>0</ftp_unauthenticated>\n<ftp_authenticated>1</ftp_authenticated>\n<ssh_unauthenticated>0</ssh_unauthenticated>\n<ssh_authenticated>1</ssh_authenticated>\n<telnet_unauthenticated>0</telnet_unauthenticated>\n<telnet_authenticated>1</telnet_authenticated>\n\n<smtp_unauthenticated>0</smtp_unauthenticated>\n<smtp_authenticated>1</smtp_authenticated>\n<pop_unauthenticated>0</pop_unauthenticated>\n<pop_authenticated>1</pop_authenticated>\n<imap_unauthenticated>0</imap_unauthenticated>\n<imap_authenticated>1</imap_authenticated>\n\n<irc_unauthenticated>0</irc_unauthenticated>\n<irc_authenticated>1</irc_authenticated>\n<torrents_unauthenticated>0</torrents_unauthenticated>\n<torrents_authenticated>1</torrents_authenticated>\n</firewall>\n</robindash>", $fc);

	$fh = fopen($dir . "data/" . $networkname . ".xml", 'w') or die("Can't write to the user file.");
	fwrite($fh, $fc);
	fclose($fh);
	
	$xmlp = simplexml_load_file($dir . "data/" . $networkname . ".xml");
}

if(isset($_POST['sent'])) {
	$fc = file_get_contents($dir . "data/" . $networkname . ".xml");

	// All the rules for the user being authenticated
	if($_POST['http_authenticated'] == "on") {$http_authenticated = "1";}
	else {$http_authenticated = "0";}
	
	if($_POST['https_authenticated'] == "on") {$https_authenticated = "1";}
	else {$https_authenticated = "0";}
	
	if($_POST['ftp_authenticated'] == "on") {$ftp_authenticated = "1";}
	else {$ftp_authenticated = "0";}
	
	if($_POST['ssh_authenticated'] == "on") {$ssh_authenticated = "1";}
	else {$ssh_authenticated = "0";}
	
	if($_POST['telnet_authenticated'] == "on") {$telnet_authenticated = "1";}
	else {$telnet_authenticated = "0";}
	
	if($_POST['smtp_authenticated'] == "on") {$smtp_authenticated = "1";}
	else {$smtp_authenticated = "0";}
	
	if($_POST['pop_authenticated'] == "on") {$pop_authenticated = "1";}
	else {$pop_authenticated = "0";}
	
	if($_POST['imap_authenticated'] == "on") {$imap_authenticated = "1";}
	else {$imap_authenticated = "0";}
	
	if($_POST['irc_authenticated'] == "on") {$irc_authenticated = "1";}
	else {$irc_authenticated = "0";}
	
	if($_POST['torrents_authenticated'] == "on") {$torrents_authenticated = "1";}
	else {$torrents_authenticated = "0";}
	
	$fc = str_replace("<http_authenticated>" . $xmlp->robindash->firewall->http_authenticated . "</http_authenticated>", "<http_authenticated>" . $http_authenticated . "</http_authenticated>", $fc);
	$fc = str_replace("<https_authenticated>" . $xmlp->robindash->firewall->https_authenticated . "</https_authenticated>", "<https_authenticated>" . $https_authenticated . "</https_authenticated>", $fc);
	$fc = str_replace("<ftp_authenticated>" . $xmlp->robindash->firewall->ftp_authenticated . "</ftp_authenticated>", "<ftp_authenticated>" . $ftp_authenticated . "</ftp_authenticated>", $fc);
	$fc = str_replace("<ssh_authenticated>" . $xmlp->robindash->firewall->ssh_authenticated . "</ssh_authenticated>", "<ssh_authenticated>" . $ssh_authenticated . "</ssh_authenticated>", $fc);
	$fc = str_replace("<telnet_authenticated>" . $xmlp->robindash->firewall->telnet_authenticated . "</telnet_authenticated>", "<telnet_authenticated>" . $telnet_authenticated . "</telnet_authenticated>", $fc);
	$fc = str_replace("<smtp_authenticated>" . $xmlp->robindash->firewall->smtp_authenticated . "</smtp_authenticated>", "<smtp_authenticated>" . $smtp_authenticated . "</smtp_authenticated>", $fc);
	$fc = str_replace("<pop_authenticated>" . $xmlp->robindash->firewall->pop_authenticated . "</pop_authenticated>", "<pop_authenticated>" . $pop_authenticated . "</pop_authenticated>", $fc);
	$fc = str_replace("<imap_authenticated>" . $xmlp->robindash->firewall->imap_authenticated . "</imap_authenticated>", "<imap_authenticated>" . $imap_authenticated . "</imap_authenticated>", $fc);
	$fc = str_replace("<irc_authenticated>" . $xmlp->robindash->firewall->irc_authenticated . "</irc_authenticated>", "<irc_authenticated>" . $irc_authenticated . "</irc_authenticated>", $fc);
	$fc = str_replace("<torrents_authenticated>" . $xmlp->robindash->firewall->torrents_authenticated . "</torrents_authenticated>", "<torrents_authenticated>" . $torrents_authenticated . "</torrents_authenticated>", $fc);

	
	// All the rules for the user being unauthenticated
	if($_POST['https_unauthenticated'] == "on") {$https_unauthenticated = "1";}
	else {$https_unauthenticated = "0";}
	
	if($_POST['ftp_unauthenticated'] == "on") {$ftp_unauthenticated = "1";}
	else {$ftp_unauthenticated = "0";}
	
	if($_POST['ssh_unauthenticated'] == "on") {$ssh_unauthenticated = "1";}
	else {$ssh_unauthenticated = "0";}
	
	if($_POST['telnet_unauthenticated'] == "on") {$telnet_unauthenticated = "1";}
	else {$telnet_unauthenticated = "0";}
	
	if($_POST['smtp_unauthenticated'] == "on") {$smtp_unauthenticated = "1";}
	else {$smtp_unauthenticated = "0";}
	
	if($_POST['pop_unauthenticated'] == "on") {$pop_unauthenticated = "1";}
	else {$pop_unauthenticated = "0";}
	
	if($_POST['imap_unauthenticated'] == "on") {$imap_unauthenticated = "1";}
	else {$imap_unauthenticated = "0";}
	
	if($_POST['irc_unauthenticated'] == "on") {$irc_unauthenticated = "1";}
	else {$irc_unauthenticated = "0";}
	
	if($_POST['torrents_unauthenticated'] == "on") {$torrents_unauthenticated = "1";}
	else {$torrents_unauthenticated = "0";}
	
	$fc = str_replace("<https_unauthenticated>" . $xmlp->robindash->firewall->https_unauthenticated . "</https_unauthenticated>", "<https_unauthenticated>" . $https_unauthenticated . "</https_unauthenticated>", $fc);
	$fc = str_replace("<ftp_unauthenticated>" . $xmlp->robindash->firewall->ftp_unauthenticated . "</ftp_unauthenticated>", "<ftp_unauthenticated>" . $ftp_unauthenticated . "</ftp_unauthenticated>", $fc);
	$fc = str_replace("<ssh_unauthenticated>" . $xmlp->robindash->firewall->ssh_unauthenticated . "</ssh_unauthenticated>", "<ssh_unauthenticated>" . $ssh_unauthenticated . "</ssh_unauthenticated>", $fc);
	$fc = str_replace("<telnet_unauthenticated>" . $xmlp->robindash->firewall->telnet_unauthenticated . "</telnet_unauthenticated>", "<telnet_unauthenticated>" . $telnet_unauthenticated . "</telnet_unauthenticated>", $fc);
	$fc = str_replace("<smtp_unauthenticated>" . $xmlp->robindash->firewall->smtp_unauthenticated . "</smtp_unauthenticated>", "<smtp_unauthenticated>" . $smtp_unauthenticated . "</smtp_unauthenticated>", $fc);
	$fc = str_replace("<pop_unauthenticated>" . $xmlp->robindash->firewall->pop_unauthenticated . "</pop_unauthenticated>", "<pop_unauthenticated>" . $pop_unauthenticated . "</pop_unauthenticated>", $fc);
	$fc = str_replace("<imap_unauthenticated>" . $xmlp->robindash->firewall->imap_unauthenticated . "</imap_unauthenticated>", "<imap_unauthenticated>" . $imap_unauthenticated . "</imap_unauthenticated>", $fc);
	$fc = str_replace("<irc_unauthenticated>" . $xmlp->robindash->firewall->irc_unauthenticated . "</irc_unauthenticated>", "<irc_unauthenticated>" . $irc_unauthenticated . "</irc_unauthenticated>", $fc);
	$fc = str_replace("<torrents_unauthenticated>" . $xmlp->robindash->firewall->torrents_unauthenticated . "</torrents_unauthenticated>", "<torrents_unauthenticated>" . $torrents_unauthenticated . "</torrents_unauthenticated>", $fc);
	
	$fh = fopen($dir . "data/" . $networkname . ".xml", 'w') or die("Can't write to the data file.");
	fwrite($fh, $fc);
	fclose($fh);
	
	die("<html><body><script>alert('Your changes to the Firewall Settings have been saved!');window.close();</script></body></html>");
}
?>

<html>
<head>
<title>Edit Firewall: <?php echo $brand; ?></title>
<link rel="stylesheet" type="text/css" href="<?php echo $wdir; ?>resources/style.css">
<link rel="shortcut icon" href="<?php echo $wdir; ?>resources/favicon.ico"/>
</head>
<body>
<div id="wrapper">
	<div id="header">
		<div id="logo">Firewall</div>
	</div>
	<div id="content">
		<div id="page-content">
			Allows you to specify which specific services are allowed to be used on your network, if any.<br />
			<br />
			
			<form action="edit-firewall.php" method="POST">
			<table style="width:100%;">
				<thead>
					<tr>
						<th>Service/Port</th>
						<th>Unauthenticated</th>
						<th>Authenticated</th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td>HTTP</td>
						<td><input type="checkbox" name="http_unauthenticated" disabled></td>
						<td><input type="checkbox" name="http_authenticated"<?php if($xmlp->robindash->firewall->http_authenticated == "1") {echo " checked";} ?>></td>
					</tr>
					<td>HTTPS (SSL/TLS)</td>
						<td><input type="checkbox" name="https_unauthenticated"<?php if($xmlp->robindash->firewall->https_unauthenticated == "1") {echo " checked";} ?>></td>
						<td><input type="checkbox" name="https_authenticated"<?php if($xmlp->robindash->firewall->https_authenticated == "1") {echo " checked";} ?>></td>
					</tr>
					
					<tr><td colspan="3"><hr></td></tr>
					
					<tr>
						<td>FTP</td>
						<td><input type="checkbox" name="ftp_unauthenticated"<?php if($xmlp->robindash->firewall->ftp_unauthenticated == "1") {echo " checked";} ?>></td>
						<td><input type="checkbox" name="ftp_authenticated"<?php if($xmlp->robindash->firewall->ftp_authenticated == "1") {echo " checked";} ?>></td>
					</tr>
					<td>SSH</td>
						<td><input type="checkbox" name="ssh_unauthenticated"<?php if($xmlp->robindash->firewall->ssh_unauthenticated == "1") {echo " checked";} ?>></td>
						<td><input type="checkbox" name="ssh_authenticated"<?php if($xmlp->robindash->firewall->ssh_authenticated == "1") {echo " checked";} ?>></td>
					</tr>
					<td>Telnet</td>
						<td><input type="checkbox" name="telnet_unauthenticated"<?php if($xmlp->robindash->firewall->telnet_unauthenticated == "1") {echo " checked";} ?>></td>
						<td><input type="checkbox" name="telnet_authenticated"<?php if($xmlp->robindash->firewall->telnet_authenticated == "1") {echo " checked";} ?>></td>
					</tr>
					
					<tr><td colspan="3"><hr></td></tr>
					
					<td>SMTP</td>
						<td><input type="checkbox" name="smttp_unauthenticated"<?php if($xmlp->robindash->firewall->smtp_unauthenticated == "1") {echo " checked";} ?>></td>
						<td><input type="checkbox" name="smtp_authenticated"<?php if($xmlp->robindash->firewall->smtp_authenticated == "1") {echo " checked";} ?>></td>
					</tr>
					<td>POP</td>
						<td><input type="checkbox" name="pop_unauthenticated"<?php if($xmlp->robindash->firewall->pop_unauthenticated == "1") {echo " checked";} ?>></td>
						<td><input type="checkbox" name="pop_authenticated"<?php if($xmlp->robindash->firewall->pop_authenticated == "1") {echo " checked";} ?>></td>
					</tr>
					<td>IMAP</td>
						<td><input type="checkbox" name="imap_unauthenticated"<?php if($xmlp->robindash->firewall->imap_unauthenticated == "1") {echo " checked";} ?>></td>
						<td><input type="checkbox" name="imap_authenticated"<?php if($xmlp->robindash->firewall->imap_authenticated == "1") {echo " checked";} ?>></td>
					</tr>
					
					<tr><td colspan="3"><hr></td></tr>
					
					<td>IRC</td>
						<td><input type="checkbox" name="irc_unauthenticated"<?php if($xmlp->robindash->firewall->irc_unauthenticated == "1") {echo " checked";} ?>></td>
						<td><input type="checkbox" name="irc_authenticated"<?php if($xmlp->robindash->firewall->irc_authenticated == "1") {echo " checked";} ?>></td>
					</tr>
					
					<td>Torrents</td>
						<td><input type="checkbox" name="torrents_unauthenticated"<?php if($xmlp->robindash->firewall->torrents_unauthenticated == "1") {echo " checked";} ?>></td>
						<td><input type="checkbox" name="torrents_authenticated"<?php if($xmlp->robindash->firewall->torrents_authenticated == "1") {echo " checked";} ?>></td>
					</tr>
				</tbody>
			</table>

			<br />
			<input type="button" style="font-weight:bold;width:18%;" onclick="window.close();" value="Close Window" /> <input type="submit" style="font-weight:bold;width:80%;" name="sent" value="Save Firewall Settings" />
			</form>
		</div>
		<div id="sidebar"></div>
	</div>
</div>

<?php echo $tracker; ?>
</body>
</html>