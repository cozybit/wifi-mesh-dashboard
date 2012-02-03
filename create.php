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

require_once($dir . "resources/recaptchalib.php");


if(isset($_POST['user']) && strtolower($_POST['user']) == "firmware") {$status = "";}
else if(isset($_POST['sent']) && isset($_POST['email']) && isset($_POST['pass']) && !isset($_POST['editmode']) && !file_exists($dir . "data/masters/" . $_POST['email'] . ".xml")) {
	if($connectivity == "internet") {
		$resp = recaptcha_check_answer ($recaptcha_privatekey,
										$_SERVER["REMOTE_ADDR"],
										$_POST["recaptcha_challenge_field"],
										$_POST["recaptcha_response_field"]);

		if (!$resp->is_valid) {$show_recaptcha_error = true;}
	}

	if(isset($show_recaptcha_error) && $show_recaptcha_error == true) {echo "";}
	else {
$fc = "<?xml version=\"1.0\" ?>
<master>
<password>" . md5($_POST['pass']) . "</password>
<networks>

</networks>
</master>";

		$fh = fopen($dir . "data/masters/" . $_POST['email'] . ".xml", 'w') or die("Can't write to the masters file.");
		fwrite($fh, $fc);
		fclose($fh);
		
		mail($_POST['email'], "Registration Complete at " . $brand, "Congratulations,\n\nYou're account has been successfully created at " . $brand . ".\nYou may now login to your account at: http://" . $sn . $wdir . "\nusing the following account details:\n\nUsername: " . $_POST['email'] . "\nPassword: --entered at signup--\n\n\n\n\nThanks,\nThe " . $brand . " Team");
		header("Location: " . $wdir . "?register=done&id=" . $_POST['email']);
	}
}
else if(isset($_POST['sent']) && isset($_POST['user']) && !file_exists($dir . "data/" . $_POST['user'] . ".xml")) {
$user = strtolower(str_replace(" ", "", $_POST['user']));

if($connectivity == "internet") {
	$resp = recaptcha_check_answer ($recaptcha_privatekey,
									$_SERVER["REMOTE_ADDR"],
									$_POST["recaptcha_challenge_field"],
									$_POST["recaptcha_response_field"]);

	if (!$resp->is_valid) {$show_recaptcha_error = true;}
}

function generatePassword() {
	$vowels = 'aeuy';
	$consonants = 'bdghjmnpqrstvz23456789';
	$password = '';
	$alt = time() % 2;

	for ($i = 0; $i < 9; $i++) {
		if ($alt == 1) {
			$password .= $consonants[(rand() % strlen($consonants))];
			$alt = 0;
		}
		else {
			$password .= $vowels[(rand() % strlen($vowels))];
			$alt = 1;
		}
	}
	return $password;
}
if($show_recaptcha_error == "true") {echo "";}
else {
	if($connectivity == "intranet") {
		// We don't have the Internet available to us
		$enable_custom_firmware = "1";
		
		$lat = "0";
		$lng = "0";
	}
	else {
		// We do have an Internet connection available

		$enable_custom_firmware = "0";
		$location = simplexml_load_string(file_get_contents("http://maps.googleapis.com/maps/api/geocode/xml?sensor=false&address=" . urlencode($_POST['location'])));

		$lat = $location->result->geometry->location->lat;
		$lng = $location->result->geometry->location->lng;
	}

$fc = "<?xml version=\"1.0\" ?>
<settings>
<robindash>
<captivename>nodogsplash</captivename>
<notifymail>" . $_POST['email'] . "</notifymail>
<password>" . md5($_POST['pass']) . "</password>
<usenodename>0</usenodename>
<enable_custom_firmware>" . $enable_custom_firmware . "</enable_custom_firmware>
<editmode>" . $_POST['editmode'] . "</editmode>
<forwardcheck>0</forwardcheck>
<location>" . $lat . "," . $lng . "</location>

<firewall>
<http_authenticated>1</http_authenticated>
<https_unauthenticated>0</https_unauthenticated>
<https_authenticated>1</https_authenticated>

<ftp_unauthenticated>0</ftp_unauthenticated>
<ftp_authenticated>1</ftp_authenticated>
<ssh_unauthenticated>0</ssh_unauthenticated>
<ssh_authenticated>1</ssh_authenticated>
<telnet_unauthenticated>0</telnet_unauthenticated>
<telnet_authenticated>1</telnet_authenticated>

<smtp_unauthenticated>0</smtp_unauthenticated>
<smtp_authenticated>1</smtp_authenticated>
<pop_unauthenticated>0</pop_unauthenticated>
<pop_authenticated>1</pop_authenticated>
<imap_unauthenticated>0</imap_unauthenticated>
<imap_authenticated>1</imap_authenticated>

<irc_unauthenticated>0</irc_unauthenticated>
<irc_authenticated>1</irc_authenticated>
<torrents_unauthenticated>0</torrents_unauthenticated>
<torrents_authenticated>1</torrents_authenticated>
</firewall>
</robindash>

<sputnik />
<system />
<wanif />
<olsr />
<maradns />
<dhcp />
<dhcpd />
<dropbear />
<firewall />
<flags />
<fstab />
<init_6 />

<acl>
<mac_mode_ap1>0</mac_mode_ap1>
</acl>

<batman>
<node_hop_penalty>0</node_hop_penalty>
</batman>

<chilli>
<agent_service></agent_service>
<agent_radiusserver1></agent_radiusserver1>
<agent_radiusserver2></agent_radiusserver2>
<agent_uamserver></agent_uamserver>
<agent_uamurl></agent_uamurl>
<agent_uamsecret></agent_uamsecret>
<agent_radiussecret></agent_radiussecret>
<agent_radiusnasid></agent_radiusnasid>
<agent_admusr></agent_admusr>
<agent_admpwd></agent_admpwd>
<agent_macpasswd></agent_macpasswd>
<agent_uamurlextras></agent_uamurlextras>
<agent_uamdomain></agent_uamdomain>
<agent_custom1></agent_custom1>
<agent_custom2></agent_custom2>
<agent_custom3></agent_custom3>
</chilli>

<nodog>
<RedirectURL></RedirectURL>
<AuthenticateImmediately>1</AuthenticateImmediately>
<ClientIdleTimeout>1440</ClientIdleTimeout>
<ClientForceTimeout>1440</ClientForceTimeout>
<DownloadLimit>22000</DownloadLimit>
<UploadLimit>11000</UploadLimit>
<TrustedMACList></TrustedMACList>
</nodog>

<wifidog>
<gateway_TrustedMACList></gateway_TrustedMACList>
<MainAuthServer_Hostname></MainAuthServer_Hostname>
<MainAuthServer_SSLAvailable>0</MainAuthServer_SSLAvailable>
<MainAuthServer_Path></MainAuthServer_Path>
<SecAuthServer_Hostname></SecAuthServer_Hostname>
<SecAuthServer_SSLAvailable>0</SecAuthServer_SSLAvailable>
<SecAuthServer_Path></SecAuthServer_Path>
</wifidog>

<cp_switch>
<main_which_handler>1</main_which_handler>
</cp_switch>

<general>
<services_ntpd_srv>tick.greyware.com</services_ntpd_srv>
<services_upstream>0</services_upstream>
<services_name_srv></services_name_srv>
<services_upgd_srv>" . $_POST['url'] . "</services_upgd_srv>
<services_upgd_srvtwo>svn6.assembla.com/svn/robin_v2/bin/</services_upgd_srvtwo>
<services_cstm_srv></services_cstm_srv>
<services_updt_srv></services_updt_srv>
<services_customdns_enable>0</services_customdns_enable>
<services_customdns_user></services_customdns_user>
<services_customdns_pass></services_customdns_pass>
</general>

<iprules>
<filter_LAN_BLOCK>0</filter_LAN_BLOCK>
<filter_LAN_BLOCK2>0</filter_LAN_BLOCK2>
<filter_AP1_bridge>0</filter_AP1_bridge>
<filter_AP2_bridge>0</filter_AP2_bridge>
<filter_AP1_isolation>0</filter_AP1_isolation>
<filter_AP2_isolation>0</filter_AP2_isolation>
<filter_port_block>0</filter_port_block>
<filter_SMTP_rdir>0</filter_SMTP_rdir>
<filter_SMTP_dest></filter_SMTP_dest>
<filter_SMTP_block>0</filter_SMTP_block>
<filter_enable_log>0</filter_enable_log>
<filter_log_server></filter_log_server>
</iprules>

<madwifi>
<priv_rate>AUTO</priv_rate>
<priv_distance>2100</priv_distance>
</madwifi>

<eightotwopointelevene>
<general_enable>0</general_enable>
</eightotwopointelevene>

<management>
<enable_base>beta</enable_base>
<enable_rootpwd>" . generatePassword() . "</enable_rootpwd>
<enable_strict_mesh>0</enable_strict_mesh>
<enable_gmt_offset>12</enable_gmt_offset>
<enable_https>0</enable_https>
<enable_public_dns>0</enable_public_dns>
<enable_country_code>0</enable_country_code>
<enable_local_domain></enable_local_domain>
<enable_proxy>0</enable_proxy>
<enable_ap2hidden>0</enable_ap2hidden>
<enable_transparent_bridge>0</enable_transparent_bridge>
<stand_alone_mode>0</stand_alone_mode>
<sag_mode_interval>600</sag_mode_interval>
<custom_update>0</custom_update>
<freeze_version>0</freeze_version>
<enable_sm>0</enable_sm>
<enable_wake_slowly>0</enable_wake_slowly>
<enable_force_reboot>0</enable_force_reboot>
<enable_force_reboot_date>never</enable_force_reboot_date>
<enable_force_reboot_time>0</enable_force_reboot_time>
</management>

<mesh>
<ap_up>1</ap_up>
<Myap_up>0</Myap_up>
<ap_psk>0</ap_psk>
</mesh>

<node>
<general_net>" . $user . "</general_net>
</node>

<radio>
<channel_alternate>5</channel_alternate>
</radio>

<secondary>
<backend_update>1</backend_update>
<backend_server></backend_server>
<backend_ssl>0</backend_ssl>

<pomade_server>1.2.3.4</pomade_server>
<pomade_privatelan>0</pomade_privatelan>
<pomade_https>0</pomade_https>
<pomade_mode>php</pomade_mode>
<pomade_cstmserver>0</pomade_cstmserver>
<pomade_enabled>0</pomade_enabled>
<pomade_privatecfg>1</pomade_privatecfg>
<pomade_errorhandling>1</pomade_errorhandling>
</secondary>

<wireless>
<private_ssid></private_ssid>
<public_ssid>" . str_replace(" ", "*", $user) . "</public_ssid>
<private_key></private_key>
<public_key>" . generatePassword() . "</public_key>
</wireless>

</settings>";

$fh = fopen($dir . "data/" . $user . ".xml", 'w') or die("Can't write to the data file.");
fwrite($fh, $fc);
fclose($fh);

$fh = fopen($dir . "data/" . $user . "_nodes.xml", 'w') or die("Can't write to the data file.");
fwrite($fh, "<?xml version='1.0' encoding='UTF-8' ?>\n<network>\n<name>" . $user . "</name>\n</network>");
fclose($fh);

$fi = fopen($dir . "data/" . $user . ".csv", 'w') or die("Can't write to the data file.");
fwrite($fi, "-\n");
fclose($fi);

$fj = fopen($dir . "data/" . $user . ".txt", 'w') or die("Can't write to the data file.");
fwrite($fj, "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01//EN\" \"http://www.w3.org/TR/html4/strict.dtd\">
<html>
	<head>
		<title>Welcome to $gatewayname</title>
		<meta http-equiv=\"content-type\" content=\"text/html; charset=iso-8859-1\">
		<style type=\"text/css\">
			body{padding: 20px;background: #707883;color: #222;text-align: center;
			font: 85% \"Trebuchet MS\",Arial,sans-serif}
			h1,h2,p{margin: 0;padding: 0 10px;font-weight:normal}
			p{padding: 0 10px 15px}
			h1{font-size: 250%;color: #FFF;letter-spacing: 1px}
			h2{font-size: 200%;line-height:1;color:#002455 }
			div#container{width:550px !important; width /**/:560px;
			margin: 0 auto;padding:5px;text-align:left;background:#FFF}
			div#header{background: #BFDDED;padding: 10px;text-align:center}
			div#content{float:left;width:400px;padding:10px 0;margin:5px 0;background: #778CCA}
			div#nav{float:right;width:145px;padding:10px 0;margin:5px 0;background: #FFD154}
			div#nav h2{font-size: 120%;color: #9E4A24}
			div#footer{clear:both;width:550px;background: #C4E786;padding:5px 0;text-align:center}
		</style>
	</head>
	<body link=\"#000000\" text=\"#000000\">
		<div id=\"container\">
			<div id=\"header\"><h1>Free WiFi!<br/></h1></div>
			<div id=\"content\">
				<p><span style=\"font-size: 12pt;\">Welcome to our open community WiFi network!</span></p>
				<h2><span style=\"font-weight: bold;\">robin-mesh Network</span></h2>
				<br/>
				<p><span style=\"font-size: 12pt;\">Hi welcome to my network.</span></p>
				<p><span style=\"font-size: 12pt;\">Feel free to use this service for web browsing only.<br/></span></p>
				<p><span style=\"font-size: 12pt;\">If you would like to contact:</span></p>
				<p><span style=\"font-size: 12pt;\">" . $_POST['email'] . "</span></p>
				<p><span style=\"font-size: 12pt;\"><span style=\"font-style: italic;\"><span style=\"color: rgb(255, 0, 0);\">Please don't use this service for downloading <span style=\"font-weight: bold;\">P2P files or Porn</span></span><br/></span></span></p>
				<p><span style=\"font-size: 12pt;\">Data and content is monitored and logged<br/></span></p>
				<p><span style=\"font-size: 12pt;\"><span style=\"color: rgb(255, 0, 0);\">Users who use large amount of data will get banned.</span><br/></span></p>
				<p><span style=\"font-size: 12pt;\">Thanks<br/></span></p>
			</div>
			
			<div id=\"nav\">
				<h2>Please...</h2>
				<p><br/>We ask just a few things:&nbsp; Be respectful of others and please refrain from large uploads or downloads to keep the network fast for everyone!<br/></p>
			</div>
			
			<div style=\"font-weight: bold;\" id=\"footer\">
				<table width=\"100%\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\">
				<tr>
				<td>
					<img vspace=\"0\" align=\"bottom\" border=\"0\" hspace=\"0\" src=\"data:image/png;base64,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\" />
				</td>
				<td><a href=\"$authtarget\"><span style=\"font-size: 18pt;\">Enter!</span></a>&nbsp;</td>
				</tr>
				</table>
			</div>
		</div>
	</body>
</html>");
fclose($fj);

$fk = fopen($dir . "data/cid/" . $user . ".txt", 'w') or die("Can't write to the data file.");
fwrite($fk, "-\n");
fclose($fk);

mkdir($dir . "data/stats/" . $user);
mkdir($dir . "data/uploads/" . $user);
mkdir($dir . "data/vouchers/" . $user);

if(!$from) {echo "";}
else {mail($_POST['email'], "Registration Complete at " . $brand, "Congratulations,\n\nYou're account has been successfully created at " . $brand . ".\nYou may now login to your account at: http://" . $sn . $wdir . "\nusing the following account details:\n\nUsername: " . $user . "\nPassword: --entered at signup--\n\n\nTo switch from the open-mesh.com dashboard, signin to your account, click the Advanced tab, scroll to the bottom where it reads\"Alternate Dashboard\", type in the box: " . $sn . $wdir . "\nThen press the \"Update Network Settings\" button at the top right of the page.\n\nYour devices will soon check for new settings at the open-mesh.com dashboard, and then check in at the " . $brand . " dashboard very soon.\n\nThanks,\nThe " . $brand . " Team");}


header("Location: " . $wdir . "?register=done&id=" . $user);
exit;
}
}
else {
?>

<!DOCTYPE HTML>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<title>Register for <?php echo $brand; ?></title>
<link rel="stylesheet" type="text/css" href="<?php echo $wdir; ?>resources/style.css" />
<meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
</head>
<body>
<div id="login-panel" style="margin-left:35%;">
	<h2 class="nospacing">Register for</h2>
	<h1><?php echo $brand; ?></h1>

		<div id="page-content">
			<form action="<?php echo $wdir; ?>create.php" method="post" class="register-form">
				<input type="hidden" name="sent" value="true" />

				<p>To create your account, complete the form below. Alternatively, you can import settings from CloudTrax, <a href="import.php" id="a">Click here</a> to do so.</p>

				<?php
				if(isset($show_recaptcha_error)) {echo "<p><i>The reCAPTCHA wasn't entered correctly.<br />Go back and try it again.</i></p>";}
				else if(isset($_POST['sent']) && isset($_POST['user']) && file_exists($dir . "data/" . $_POST['user'] . ".xml")) {echo "<p><i>This account already exists on this server.</i></p>";}
				else if(isset($_GET['id'])) {echo "<p><i>You're nodes have been imported from<br />open-mesh.com successfully.</i></p>";}
				else {echo "";}
				?>

				<label for="user">Username</label>		
				<input type="text" id="user" name="user" value="<?php if(isset($_GET['id'])) {echo $_GET['id'] . "\" readonly";} else {echo "\"";} ?> />

				<label for="pass">Password</label>
				<input type="password" id="pass" name="pass" value="" />

				<label for="email">Notify Email</label>
				<input type="text" id="email" name="email" value="" />
				
				<label for="location">Location</label>
				<input type="text" id="location" name="location" value="" />
				
				<label for="editmode">Account Type</label>
				<select name="editmode" id="editmode">
					<option disabled>Choose your Account Type</option>
					<option disabled>--------------------------------------------------------------</option>
					<option value="easy">Easy</option>
					<option value="advanced">Advanced</option>
				</select>
				<p>You are free to change this later, this setting determines the functionality you get.</p>
				
				<label for="url">Device Firmware</label>
				<select name="url" id="url">
					<option disabled>Choose your Firmware</option>
					<option disabled>--------------------------------------------------------------</option>
					<option value="svn6.assembla.com/svn/RobinMesh/downloads/firmware/stable/r2690/">Robin-Mesh: Stable</option>
					<option value="svn6.assembla.com/svn/RobinMesh/downloads/firmware/development/" selected>Robin-Mesh: Development</option>
				</select>
				<p>Leave as default if you are not sure which option to choose.</p>

				<?php
				if($connectivity == "internet") {echo "<div id=\"recaptcha\">" . recaptcha_get_html($recaptcha_publickey, $error = null, $use_ssl = true) . "</div>";}
				?>
				
				<br />
				
				<input type="submit" name="submit" value="Create Account" style="width:60%;" />&nbsp;<input type="button" value="Cancel" style="width:20%;" onclick="window.location = '<?php echo $wdir; ?>';" />
				</form>
			
			<p style="color:grey;text-align:center;margin-bottom:-20px;">You're usage of this website is subject to<br />the <a href="<?php echo $wdir; ?>resources/extras/legal.pdf" title="Terms and Conditions" style="color:grey;font-style:italic;text-decoration:underline;">Terms and Conditions</a>.</p>
	</div>
</div>

<?php echo $tracker; ?>
</body>
</html>

<?php
}
?>