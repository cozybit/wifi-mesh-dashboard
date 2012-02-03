<?php
/*
		   _	 _				_		   _	 
		  | |   (_)			  | |		 | |	
 _ __ ___ | |__  _ _ __ ______ __| | __ _ ___| |__  
| '__/ _ \| '_ \| | '_ \______/ _` |/ _` / __| '_ \ 
| | | (_) | |_) | | | | |	| (_| | (_| \__ \ | | |
|_|  \___/|_.__/|_|_| |_|	 \__,_|\__,_|___/_| |_|

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
else {die("# We need to setup the server first");}

$wdir = str_replace($_SERVER['DOCUMENT_ROOT'], "", $dir);

if(!$_GET['network']) {
	if(file_exists($dir . "data/mac2net/" . base64_encode($_GET['mac']) . ".txt")) {$networkname = file_get_contents($dir . "data/mac2net/" . base64_encode($_GET['mac']) . ".txt");}
	else {exit;}
}
else {$networkname = $_GET['network'];}


if($has_https == "true" && !$_SERVER['HTTPS']) {header("Location: https://" . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI']);exit;}

if(!file_exists($dir . "data/" . $networkname . ".xml")) {die("# Network name does not exist");}
else if(!$_GET['ip']) {die($error_correct);}
else if(!$_GET['mac']) {die($error_correct);}
else {
// Load in the network data
$xmlp = simplexml_load_file($dir . "data/" . $networkname . ".xml");

// Set the correct timezone
setTimezoneByOffset($xmlp->management->enable_gmt_offset);

// Do the dual-checkin if we need to...
if($xmlp->robindash->forwardcheck == "1") {$fc = file_get_contents("http://checkin.open-mesh.com/checkin-batman.php?" . str_replace("network=" . $networkname, "", $_SERVER['QUERY_STRING']));}

$outfile = $dir . "data/" . $networkname . ".csv";
$fh = fopen($outfile, 'a') or die("# Cant write input from node to csv output");

$keys = array('ip','mac','robin','batman','memfree','ssid','pssid','users','kbup','kbdown','top_users','uptime','gateway','gw-qual','NTR','routes','hops','RTT','nbs','rank','nodes','rssi','role');
$robin_vars = array_fill_keys($keys, '');

foreach($robin_vars as $key => $value) {
	$robin_vars[$key] = $_GET[$key];
	$string .= $key . "=" . $_GET[$key] . "&\n";
}

array_unshift($robin_vars, date("D M j G:i:s T Y"));
$status_string = implode(",",$robin_vars);
fwrite($fh, $status_string . "\n");
fclose($fh);

$filen = $dir . "data/cid/" . $networkname . ".txt";
$newfc = file_get_contents($dir . "data/cid/" . $networkname . ".txt");
$algar = md5($_GET['ip'] . md5(file_get_contents($dir . "data/" . $networkname . ".xml"))) . "\n";

// The node has checked in, so remove any file that says otherwise
if(file_exists($dir . "data/errored/" . base64_encode($_GET['mac']) . ".txt")) {unlink($dir . "data/errored/" . base64_encode($_GET['mac']) . ".txt");}


// Check stats dir exists, if not: make it
if(!is_dir($dir . "data/stats/" . $networkname . "/")) {mkdir($dir . "data/stats/" . $networkname . "/");}

// Save node stats
$fj = fopen($dir . "data/stats/" . $networkname . "/" . base64_encode($_GET['mac']) . ".txt", 'w');// or die("# Cant write node statistics");
fwrite($fj, $_SERVER['QUERY_STRING']);
fclose($fj);


// Save node time stats
$fk = fopen($dir . "data/stats/" . $networkname . "/" . base64_encode($_GET['mac']) . ".date.txt", 'w');// or die("# Cant write node statistics");
fwrite($fk, date('dmyiHa'));
fclose($fk);


// Save node performance stats over time
if(is_dir($dir . "data/stats/" . $networkname . "/" . date('d') . "-" . date('m'))) {echo "";}
else {mkdir($dir . "data/stats/" . $networkname . "/" . date('d') . "-" . date('m'));}

$fk = fopen($dir . "data/stats/" . $networkname . "/" . date('d') . "-" . date('m') . "/" . date('i') . date('G') . "-" . base64_encode($_GET['mac']) . ".usage.txt", 'w');// or die("# Cant write node statistics");
fwrite($fk, $_GET['users'] . "&" . urlencode($_GET['top_users']) . "&" . $_GET['sta_mac'] . "&" . $_GET['sta_ip'] . "&" . $_GET['sta_hostname'] . "&" . $_GET['sta_rssi'] . "&" . $_GET['sta_dbm']);
fclose($fk);

$fk = fopen($dir . "data/stats/" . $networkname . "/" . date('d') . "-" . date('m') . "/" . base64_encode($_GET['mac']) . ".txt", 'w');// or die("# Cant write node statistics");
fwrite($fk, $_GET['NTR'] . "&" . $_GET['nbs'] . "&" . $_GET['rssi'] . "&" . $_GET['RTT']);
fclose($fk);


// Process the resend-reply option
if($_GET['RR'] == "1") {/* The node has been reflashed, or is new, so we send the data again. */}
else if(strpos(file_get_contents($dir . "data/cid/" . $networkname . ".txt"), md5($_GET['ip'] . $_GET['mac'] . $_GET['robin'] . md5(file_get_contents($dir . "data/" . $networkname . ".xml"))) . "\n") !==FALSE) {exit;/* The node already has the data, we don't need to send it again */}
else {/* The data has changed, so we let the script continue so the node gets its data */}


// Save the checkin data
$fi = fopen($dir . "data/cid/" . $networkname . ".txt", 'a');// or die("# Cant write node checkin value");
fwrite($fi, md5($_GET['ip'] . $_GET['mac'] . $_GET['robin'] . md5(file_get_contents($dir . "data/" . $networkname . ".xml"))) . "\n");
fclose($fi);

// Save the node role
if(isset($_GET['role'])) {$role = "G";}
else {$role = "R";}

$fi = fopen($dir . "data/role/" . base64_encode($_GET['mac']) . ".txt", 'w');// or die("# Cant write node role");
fwrite($fi, $role);
fclose($fi);

// Update DNS-O-MATIC, if the user opts to
if($xmlp->general->services_customdns_enable = "1") {
	if(file_get_contents($dir . "data/stats/" . $networkname . "/" . base64_encode($_GET['mac']) . ".ip.txt") == $_SERVER['REMOTE_ADDR']) {echo "";}
	else {
		$fj = fopen($dir . "data/stats/" . $networkname . "/" . base64_encode($_GET['mac']) . ".ip.txt", 'w');// or die("# Cant write node statistics");
		fwrite($fj, $_SERVER['REMOTE_ADDR']);
		fclose($fj);
		
		file_get_contents("https://" . $xmlp->general->services_customdns_user . ":" . $xmlp->general->services_customdns_pass . "@updates.dnsomatic.com/nic/update?myip=" . $_SERVER['REMOTE_ADDR']);
	}
}
else {echo "";}
}

// Check if the log file is too big
if(filesize($dir . "data/" . $networkname . ".csv") > 524288) {unlink($dir . "data/" . $networkname . ".csv") or die("# Cant delete the log which is over 0.5 megabytes");}
?>
#@#config dhcp
#@#config dhcpd
#@#config firewall
#@#config flags
#@#config fstab
#@#config init_6
#@#config maradns
#@#config olsr
#@#config wanif
<?php
if($xmlp->secondary->pomade_enabled == "0") {echo "#@#config pomade\n";}

if($xmlp->robindash->captivename == "nodogsplash") {echo "#@#config chilli\n#@#config wifidog\n";}
else if($xmlp->robindash->captivename == "coova" || $xmlp->robindash->captivename == "wifirush") {echo "#@#config nodog\n#@#config wifidog\n";}
else if($xmlp->robindash->captivename == "wifidog") {echo "#@#config nodog\n#@#config coova\n";}
else {echo "#@#config nodog\n#@#config coova\n";}

echo "#@#config dropbear\n";
if(file_exists($dir . "data/uploads/" . $networkname . "/ssh.key")) {echo "PasswordAuth on\n";}
else {echo "PasswordAuth on\n";}
?>
#@#config acl
mac.mode_ap1 <?php echo $xmlp->acl->mac_mode_ap1 . "\n"; ?>
mac.mode_ap2 <?php echo $xmlp->acl->mac_mode_ap2 . "\n"; ?>
#@#config cp_switch
main.which_handler <?php echo $xmlp->cp_switch->main_which_handler . "\n"; ?>
#@#config ra_switch
main.which_handler 2
<?php
if($xmlp->robindash->captivename == "nodogsplash" && $xmlp->robindash->firewall->http_authenticated) {
?>
#@#config nodog
FirewallRuleSet preauthenticated-users {
	FirewallRule <?php if($xmlp->robindash->firewall->https_unauthenticated == "1") {echo "allow port 443";} else {echo "block port 443";} echo "\n"; ?>
	FirewallRule <?php if($xmlp->robindash->firewall->ftp_unauthenticated == "1") {echo "allow port 20";} else {echo "block port 20";} echo "\n"; ?>
	FirewallRule <?php if($xmlp->robindash->firewall->ftp_unauthenticated == "1") {echo "allow port 21";} else {echo "block port 21";} echo "\n"; ?>
	FirewallRule <?php if($xmlp->robindash->firewall->ssh_unauthenticated == "1") {echo "allow port 22";} else {echo "block port 22";} echo "\n"; ?>
	FirewallRule <?php if($xmlp->robindash->firewall->telnet_unauthenticated == "1") {echo "allow port 23";} else {echo "block port 23";} echo "\n"; ?>
	FirewallRule <?php if($xmlp->robindash->firewall->smtp_unauthenticated == "1") {echo "allow port 25";} else {echo "block port 25";} echo "\n"; ?>
	FirewallRule <?php if($xmlp->robindash->firewall->smtp_unauthenticated == "1") {echo "allow port 465";} else {echo "block port 465";} echo "\n"; ?>
	FirewallRule <?php if($xmlp->robindash->firewall->pop_unauthenticated == "1") {echo "allow port 109";} else {echo "block port 109";} echo "\n"; ?>
	FirewallRule <?php if($xmlp->robindash->firewall->pop_unauthenticated == "1") {echo "allow port 110";} else {echo "block port 110";} echo "\n"; ?>
	FirewallRule <?php if($xmlp->robindash->firewall->imap_unauthenticated == "1") {echo "allow port 143";} else {echo "block port 143";} echo "\n"; ?>
	FirewallRule <?php if($xmlp->robindash->firewall->imap_unauthenticated == "1") {echo "allow port 220";} else {echo "block port 220";} echo "\n"; ?>
	FirewallRule <?php if($xmlp->robindash->firewall->imap_unauthenticated == "1") {echo "allow port 993";} else {echo "block port 993";} echo "\n"; ?>
	FirewallRule <?php if($xmlp->robindash->firewall->irc_unauthenticated == "1") {echo "allow port 194";} else {echo "block port 194";} echo "\n"; ?>
	FirewallRule <?php if($xmlp->robindash->firewall->torrents_unauthenticated == "1") {echo "allow port 6881:6999";} else {echo "block port 6881:6999";} echo "\n"; ?>
<?php
if(file_exists($dir . "data/uploads/" . $networkname . "/multi-list.txt")) {
	$fc = file_get_contents($dir . "data/uploads/" . $networkname . "/multi-list.txt");
	$link = explode("\r\n", $fc);
	$ips = array();

	foreach($link as $url) {
		preg_match("/^(http:\/\/)?([^\/]+)/i", $url, $matches);
		$server = $matches[2];
		$serverips = gethostbynamel($server);
		
		foreach($serverips as $serverip) {
			if($ips[$serverip] == 1) {echo "";}
			else if($serverip == "") {echo "";}
			else {
				echo "	FirewallRule allow all port 80 to " . $serverip . "\n";
				echo "	FirewallRule allow all port 443 to " . $serverip . "\n";
			}
			
			$ips[$serverip] = 1;
		}
	}
}

if($xmlp->general->services_name_srv == "") {echo "";}
else {
	echo "	FirewallRule block port 53\n";
	echo "	FirewallRule allow port 53 to " . $xmlp->general->services_name_srv . "\n";
}
?>
}
FirewallRuleSet authenticated-users {
	FirewallRule <?php if($xmlp->robindash->firewall->http_authenticated == "1") {echo "allow port 80";} else {echo "block port 80";} echo "\n"; ?>
	FirewallRule <?php if($xmlp->robindash->firewall->https_authenticated == "1") {echo "allow port 443";} else {echo "block port 443";} echo "\n"; ?>
	FirewallRule <?php if($xmlp->robindash->firewall->ftp_authenticated == "1") {echo "allow port 20";} else {echo "block port 20";} echo "\n"; ?>
	FirewallRule <?php if($xmlp->robindash->firewall->ftp_authenticated == "1") {echo "allow port 21";} else {echo "block port 21";} echo "\n"; ?>
	FirewallRule <?php if($xmlp->robindash->firewall->ssh_authenticated == "1") {echo "allow port 22";} else {echo "block port 22";} echo "\n"; ?>
	FirewallRule <?php if($xmlp->robindash->firewall->telnet_authenticated == "1") {echo "allow port 23";} else {echo "block port 23";} echo "\n"; ?>
	FirewallRule <?php if($xmlp->robindash->firewall->smtp_authenticated == "1") {echo "allow port 25";} else {echo "block port 25";} echo "\n"; ?>
	FirewallRule <?php if($xmlp->robindash->firewall->smtp_authenticated == "1") {echo "allow port 465";} else {echo "block port 465";} echo "\n"; ?>
	FirewallRule <?php if($xmlp->robindash->firewall->pop_authenticated == "1") {echo "allow port 109";} else {echo "block port 109";} echo "\n"; ?>
	FirewallRule <?php if($xmlp->robindash->firewall->pop_authenticated == "1") {echo "allow port 110";} else {echo "block port 110";} echo "\n"; ?>
	FirewallRule <?php if($xmlp->robindash->firewall->imap_authenticated == "1") {echo "allow port 143";} else {echo "block port 143";} echo "\n"; ?>
	FirewallRule <?php if($xmlp->robindash->firewall->imap_authenticated == "1") {echo "allow port 220";} else {echo "block port 220";} echo "\n"; ?>
	FirewallRule <?php if($xmlp->robindash->firewall->imap_authenticated == "1") {echo "allow port 993";} else {echo "block port 993";} echo "\n"; ?>
	FirewallRule <?php if($xmlp->robindash->firewall->irc_authenticated == "1") {echo "allow port 194";} else {echo "block port 194";} echo "\n"; ?>
	FirewallRule <?php if($xmlp->robindash->firewall->torrents_authenticated == "1") {echo "allow port 6881:6999";} else {echo "block port 6881:6999";} echo "\n"; ?>
<?php
if($xmlp->general->services_name_srv == "") {echo "";}
else {
	echo "	FirewallRule block port 53\n";
	echo "	FirewallRule allow port 53 to " . $xmlp->general->services_name_srv . "\n";
}
?>
}
FirewallRuleSet users-to-router {
	FirewallRule allow
}
GatewayName <?php echo str_replace(".", "", $_GET['ip']) . "\n"; ?>
<?php if(strlen($xmlp->nodog->RedirectURL) > 0) {echo "RedirectURL " . $xmlp->nodog->RedirectURL . "\n";} ?>
ClientIdleTimeout <?php if(strlen($xmlp->nodog->ClientIdleTimeout) > 0) {echo $xmlp->nodog->ClientIdleTimeout;} else {echo "1440";} echo "\n"; ?>
ClientForceTimeout <?php if(strlen($xmlp->nodog->ClientForceTimeout) > 0) {echo $xmlp->nodog->ClientForceTimeout;} else {echo "1440";} echo "\n"; ?>
AuthenticateImmediately <?php
if(file_exists($dir . "data/uploads/" . $networkname . "/multi-list.txt")) {echo "0\n";}
else {echo $xmlp->nodog->AuthenticateImmediately . "\n";}
?>
TrafficControl 1
DownloadLimit <?php echo $xmlp->nodog->DownloadLimit . "\n"; ?>
UploadLimit <?php echo $xmlp->nodog->UploadLimit . "\n"; ?>
MaxClients 200
<?php
if(strlen($xmlp->nodog->TrustedMACList) > 12) {echo "TrustedMACList " . str_replace("\n", ",", $xmlp->nodog->TrustedMACList) . "\n";}

$blocklist = "";
$hasblocks = "";
$i = 0;

if(is_dir($dir . "data/stats/" . $networkname . "/banned/")) {
	if ($dh = opendir($dir . "data/stats/" . $networkname . "/banned/")) {
		while (($file = readdir($dh)) !== false) {
			if($file == "." || $file == "..") {echo "";}
			else {
				$blocklist = $blocklist . base64_decode(str_replace(".txt", "", $file)) . ",";
				$hasblocks = true;
				$i = $i + 1;
			}
		}
		closedir($dh);
	}
}

if($hasblocks == true) {
	$multiplier = 18 * 2 - 1;
	$blocklist = substr($blocklist, 0, $multiplier);
	
	echo "BlockedMACList " . $blocklist . "\n";
}



if(file_exists($dir . "data/uploads/" . $networkname . "/multi-list.txt")) {echo "#@#config splash-HTML\npage "; if($use_https == "true") {echo "https://";} else {echo "http://";} echo $sn . $wdir . "data/ad-splash/" . $networkname . "/splash.html\n";}
else if($xmlp->nodog->AuthenticateImmediately == "1") {echo "";}
else {
	echo "#@#config splash-HTML\n";
	echo "page "; if($use_https == "true") {echo "https://";} else {echo "http://";} echo $sn . $wdir . "data/";

	if(file_exists($dir . "data/uploads/" . $networkname . "/multi-list.txt")) {echo "multi-splash/" . $networkname . "/splash.html\n";}
	else {echo $networkname . ".txt\n";}

	if ($handle = opendir($dir . "data/uploads/" . $networkname . "/")) {
		while (false !== ($file = readdir($handle))) {
			if ($file != "." && $file != ".." && $file != "custom.sh" && $file != "multi-list.txt") {
				if(strpos($file, '.txt') !==FALSE || strpos($file, '.htm') !==FALSE) {$type = "page";}
				else if(strpos($file, '.jp') !==FALSE || strpos($file, '.png') !==FALSE || strpos($file, '.gif') !==FALSE || strpos($file, '.bmp') !==FALSE) {$type = "image";}
				else {$type = "page";}

				echo $type . " "; if($use_https == "true") {echo "https://";} else {echo "http://";} echo $_SERVER['SERVER_NAME'] . $wdir . "data/uploads/" . $networkname . "/" . $file . "\n";
			}
		}
	}
}

echo "#bogus2 " . rand(1000000000, 9999999999) . "\n";
}
else if($xmlp->robindash->captivename == "nodogsplash") {
?>
#@#config nodog
FirewallRuleSet preauthenticated-users {
	FirewallRule allow
}
FirewallRuleSet authenticated-users {
	FirewallRule allow
}
FirewallRuleSet users-to-router {
	FirewallRule allow
}
GatewayName <?php echo str_replace(".", "", $_GET['ip']) . "\n"; ?>
<?php if(strlen($xmlp->nodog->RedirectURL) > 0) {echo "RedirectURL " . $xmlp->nodog->RedirectURL . "\n";} ?>
ClientIdleTimeout <?php if(strlen($xmlp->nodog->ClientIdleTimeout) > 0) {echo $xmlp->nodog->ClientIdleTimeout;} else {echo "1440";} echo "\n"; ?>
ClientForceTimeout <?php if(strlen($xmlp->nodog->ClientForceTimeout) > 0) {echo $xmlp->nodog->ClientForceTimeout;} else {echo "1440";} echo "\n"; ?>
AuthenticateImmediately <?php
if(file_exists($dir . "data/uploads/" . $networkname . "/multi-list.txt")) {echo "0\n";}
else {echo $xmlp->nodog->AuthenticateImmediately . "\n";}
?>
TrafficControl 1
DownloadLimit <?php echo $xmlp->nodog->DownloadLimit . "\n"; ?>
UploadLimit <?php echo $xmlp->nodog->UploadLimit . "\n"; ?>
MaxClients 200
<?php
if(strlen($xmlp->nodog->TrustedMACList) > 12) {echo "TrustedMACList " . str_replace("\n", ",", $xmlp->nodog->TrustedMACList) . "\n";}

if(file_exists($dir . "data/uploads/" . $networkname . "/multi-list.txt")) {echo "#@#config splash-HTML\npage "; if($use_https == "true") {echo "https://";} else {echo "http://";} echo $sn . $wdir . "data/ad-splash/" . $networkname . "/splash.html\n";}
else if($xmlp->nodog->AuthenticateImmediately == "1") {echo "";}
else {
	echo "#@#config splash-HTML\n";
	echo "page "; if($use_https == "true") {echo "https://";} else {echo "http://";} echo $sn . $wdir . "data/";

	if(file_exists($dir . "data/uploads/" . $networkname . "/multi-list.txt")) {echo "multi-splash/" . $networkname . "/splash.html\n";}
	else {echo $networkname . ".txt\n";}
	
	if ($handle = opendir($dir . "data/uploads/" . $networkname . "/")) {
		while (false !== ($file = readdir($handle))) {
			if ($file != "." && $file != ".." && $file != "custom.sh" && $file != "multi-list.txt") {
				if(strpos($file, '.txt') !==FALSE || strpos($file, '.htm') !==FALSE) {$type = "page";}
				else if(strpos($file, '.jp') !==FALSE || strpos($file, '.png') !==FALSE || strpos($file, '.gif') !==FALSE || strpos($file, '.bmp') !==FALSE) {$type = "image";}
				else {$type = "page";}

				echo $type . " "; if($use_https == "true") {echo "https://";} else {echo "http://";} echo $_SERVER['SERVER_NAME'] . $wdir . "data/uploads/" . $networkname . "/" . $file . "\n";
			}
		}
	}
}

echo "#bogus2 " . rand(1000000000, 9999999999) . "\n";
}

else if($xmlp->robindash->captivename == "coova" || $xmlp->robindash->captivename == "wifirush") {
?>
#@#config chilli
agent.service <?php echo $xmlp->chilli->agent_service . "\n"; ?>
agent.radiusserver1 <?php echo $xmlp->chilli->agent_radiusserver1 . "\n"; ?>
agent.radiusserver2 <?php echo $xmlp->chilli->agent_radiusserver2 . "\n"; ?>
agent.uamserver <?php echo $xmlp->chilli->agent_uamserver . "\n"; ?>
agent.uamurl <?php echo $xmlp->chilli->agent_uamurl . "\n"; ?>
agent.uamsecret <?php echo $xmlp->chilli->agent_uamsecret . "\n"; ?>
agent.radiussecret <?php echo $xmlp->chilli->agent_radiussecret . "\n"; ?>
agent.radiusnasid <?php echo $xmlp->chilli->agent_radiusnasid . "\n"; ?>
agent.admusr <?php echo $xmlp->chilli->agent_admusr . "\n"; ?>
agent.admpwd <?php echo $xmlp->chilli->agent_admpwd . "\n"; ?>
agent.uamurlextras <?php echo $xmlp->chilli->agent_uamurlextras . "\n"; ?>
agent.uamdomain <?php echo $xmlp->chilli->agent_uamdomain . "\n"; ?>
agent.custom1 <?php echo $xmlp->chilli->agent_custom1 . "\n"; ?>
agent.custom2 <?php echo $xmlp->chilli->agent_custom2 . "\n"; ?>
agent.custom3 <?php echo $xmlp->chilli->agent_custom3 . "\n"; ?>
<?php
if($xmlp->robindash->captivename == "wifirush") {echo "agent.macpasswd " . $xmlp->chilli->agent_macpasswd . "\n";}
}

else if($xmlp->robindash->captivename == "wifidog") {
?>
#@#config wifidog
<?php
if(strlen($xmlp->wifidog->gateway_TrustedMACList) > 12) {echo "gateway.TrustedMACList " . $xmlp->wifidog->gateway_TrustedMACList . "\n";}
?>
MainAuthServer.Hostname <?php echo $xmlp->wifidog->MainAuthServer_Hostname . "\n"; ?>
MainAuthServer.SSLAvailable <?php echo $xmlp->wifidog->MainAuthServer_SSLAvailable . "\n"; ?>
MainAuthServer.Path <?php echo $xmlp->wifidog->MainAuthServer_Path . "\n"; ?>
SecAuthServer.Hostname <?php echo $xmlp->wifidog->SecAuthServer_Hostname . "\n"; ?>
SecAuthServer.SSLAvailable <?php echo $xmlp->wifidog->SecAuthServer_SSLAvailable . "\n"; ?>
SecAuthServer.Path <?php echo $xmlp->wifidog->SecAuthServer_Path . "\n"; ?>
<?php
}
?>
#@#config general
services.ntpd_srv <?php echo $xmlp->general->services_ntpd_srv . "\n"; ?>
services.upstream <?php echo $xmlp->general->services_upstream . "\n"; ?>
services.name_srv <?php echo $xmlp->general->services_name_srv . "\n"; ?>
services.upgd_srv <?php echo $xmlp->general->services_upgd_srv . "\n"; ?>
services.base_test <?php echo $xmlp->general->services_upgd_srv . "\n"; ?>
services.base_beta <?php echo $xmlp->general->services_upgd_srv . "\n"; ?>
services.cstm_srv <?php echo $xmlp->general->services_cstm_srv . "\n";

if(strpos($xmlp->general->services_updt_srv, $sn) !==FALSE) {echo "";}
else if($xmlp->general->services_updt_srv == "") {echo "";}
else {echo "services.updt_srv " . $xmlp->general->services_updt_srv . "\n";}
?>
#@#config iprules
filter.LAN_BLOCK <?php echo $xmlp->iprules->filter_LAN_BLOCK . "\n"; ?>
filter.LAN_BLOCK2 <?php echo $xmlp->iprules->filter_LAN_BLOCK2 . "\n"; ?>
filter.AP1_bridge 0
filter.AP1_isolation 0
filter.AP2_bridge 0
filter.AP2_isolation 0
filter.port_block <?php echo $xmlp->iprules->filter_port_block . "\n"; ?>
filter.SMTP_rdir <?php echo $xmlp->iprules->filter_SMTP_rdir . "\n"; ?>
filter.SMTP_dest <?php echo $xmlp->iprules->filter_SMTP_dest . "\n"; ?>
filter.SMTP_block <?php echo $xmlp->iprules->filter_SMTP_block . "\n"; ?>
filter.enable_log <?php echo $xmlp->iprules->filter_enable_log . "\n"; ?>
filter.log_server <?php echo $xmlp->iprules->filter_log_server . "\n"; ?>
#@#config madwifi
priv.rate <?php echo $xmlp->madwifi->priv_rate . "\n"; ?>
priv.distance <?php if(strlen($xmlp->madwifi->priv_distance) > 2) {echo $xmlp->madwifi->priv_distance;} else {echo "2100";} echo "\n"; ?>
#@#config nodes
<?php
if(file_exists($dir . "data/" . $networkname . "_nodes.xml")) {
	$xmlstring = simplexml_load_file($dir . "data/" . $networkname . "_nodes.xml");
	$no_of_nodes = "0";

	foreach($xmlstring->node as $node) {
		if(file_exists($dir . "data/role/" . base64_encode($node->mac) . ".txt")) {echo file_get_contents($dir . "data/role/" . base64_encode($node->mac) . ".txt");}
		else {echo "R";}

		echo " " . $node->ip . " " . str_replace(" ", "*", $node->name) . " " . $node->mac . "\n";
		$no_of_nodes = $no_of_nodes + 1;
	}
}
?>
#@#config management
enable.https <?php if($has_https == "true") {echo "1\n";} else {echo "0\n";} ?>
enable.update_rate <?php
if($no_of_nodes > 150) {echo "30";}
else if($no_of_nodes > 100) {echo "20";}
else if($no_of_nodes > 75) {echo "15";}
else if($no_of_nodes > 50) {echo "10";}
else {echo "5";}

echo "\n";
?>
enable.upgrade_f <?php echo $xmlp->management->upgrade_f . "\n"; ?>
enable.upgrade_t <?php echo $xmlp->management->upgrade_t . "\n"; ?>
enable.ap2hidden <?php echo $xmlp->management->enable_ap2hidden . "\n"; ?>
enable.freeze_version <?php echo $xmlp->management->freeze_version . "\n"; ?>
enable.wake_slowly <?php echo $xmlp->management->enable_wake_slowly . "\n"; ?>
enable.local_domain <?php echo $xmlp->management->enable_local_domain . "\n"; ?>
enable.stand_alone_mode <?php echo $xmlp->management->stand_alone_mode . "\n"; ?>
enable.country_code <?php echo $xmlp->management->enable_country_code . "\n"; ?>
enable.transparent_bridge 0
enable.rootpwd <?php echo $xmlp->management->enable_rootpwd . "\n"; ?>
enable.gmt_offset <?php echo $xmlp->management->enable_gmt_offset . "\n"; ?>
enable.public_dns <?php echo $xmlp->management->enable_public_dns . "\n"; ?>
enable.sag_mode_interval <?php echo $xmlp->management->sag_mode_interval . "\n"; ?>
enable.strict_mesh <?php echo $xmlp->management->enable_strict_mesh . "\n"; ?>
enable.force_reboot <?php
$fr = $xmlp->management->enable_force_reboot;
$fr_date = $xmlp->management->enable_force_reboot_date;
$fr_time = $xmlp->management->enable_force_reboot_time;

if($fr == "1") {echo $fr_date . "@" . $fr_time . "\n";}
else {echo "never" . "\n";}
?>
enable.custom_update 1
enable.base trunk
#@#config mesh
ap.up <?php echo $xmlp->mesh->ap_up . "\n"; ?>
Myap.up <?php echo $xmlp->mesh->Myap_up . "\n"; ?>
ap.psk <?php echo $xmlp->mesh->ap_psk . "\n"; ?>
#@#config node
general.net <?php echo $xmlp->node->general_net . "\n"; ?>
#@#config radio
channel.default <?php echo $xmlp->radio->channel_alternate . "\n"; ?>
channel.alternate <?php echo $xmlp->radio->channel_alternate . "\n"; ?>
channel.current <?php echo $xmlp->radio->channel_alternate . "\n"; ?>
#@#config secondary
backend.update 0
backend.server 
backend.ssl 0
#@#config wireless
private.ssid <?php echo $xmlp->wireless->private_ssid . "\n"; ?>
public.ssid <?php
if($xmlp->robindash->usenodename == 1) {
	$xmln = simplexml_load_file($dir . "data/" . $networkname . "_nodes.xml");

	foreach($xmln->node as $node) {
		if($_GET['ip'] == $node->ip && $_GET['mac'] == $node->mac) {
			if(!strlen($node->name)) {echo str_replace(" ", "*", $xmlp->wireless->public_ssid);}
			else {echo str_replace(" ", "*", $node->name);}
		}
	}
}
else {echo $xmlp->wireless->public_ssid;}
echo "\n";
?>
private.key <?php echo $xmlp->wireless->private_key . "\n"; ?>
public.key <?php echo $xmlp->wireless->public_key . "\n"; ?>
<?php
if($xmlp->secondary->pomade_enabled == "1") {
?>
#@#config pomade
server.host <?php echo $xmlp->secondary->pomade_server . "\n"; ?>
server.private_lan <?php echo $xmlp->secondary->pomade_privatelann . "\n"; ?>
server.https <?php echo $xmlp->secondary->pomade_https . "\n"; ?>
server.mode <?php echo $xmlp->secondary->pomade_mode . "\n"; ?>
server.is_cstm_srv <?php echo $xmlp->secondary->pomade_cstmserver . "\n"; ?>
client.enabled <?php echo $xmlp->secondary->pomade_enabled . "\n"; ?>
client.private_cfg <?php echo $xmlp->secondary->pomade_privatecfg . "\n"; ?>
client.on_err_ignore <?php echo $xmlp->secondary->pomade_errorhandling . "\n"; ?>
<?php
}

if(is_dir($dir . "data/pnc/" . base64_encode($_GET['mac']) . "/") && file_exists($dir . "data/pnc/" . base64_encode($_GET['mac']) . "/staticcheckbox.txt")) {
	$sc = file_get_contents($dir . "data/pnc/" . base64_encode($_GET['mac']) . "/staticcheckbox.txt");
	
	if($sc == "runmode") {
		echo "#@#config installation\n";
		echo "node.run_mode " . file_get_contents($dir . "data/pnc/" . base64_encode($_GET['mac']) . "/node.run_mode.txt");
	}
	else if($sc == "gatewaystatic") {
		echo "#@#config installation\n";
		$role = file_get_contents($dir . "data/pnc/" . base64_encode($_GET['mac']) . "/node.predef_role.txt");
		
		if($role == "-1") {echo "node.predef_role -1\n";}
		else {
			echo "node.predef_role " . $role . "\n";
			echo "gw.ipaddr " . file_get_contents($dir . "data/pnc/" . base64_encode($_GET['mac']) . "/gw.ipaddr.txt") . "\n";
			echo "gw.netmask " . file_get_contents($dir . "data/pnc/" . base64_encode($_GET['mac']) . "/gw.netmask.txt") . "\n";
			echo "gw.defroute " . file_get_contents($dir . "data/pnc/" . base64_encode($_GET['mac']) . "/gw.defroute.txt") . "\n";
		}
	}
	else {echo "";}
}
?>