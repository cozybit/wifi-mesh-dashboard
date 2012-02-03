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

if(!file_exists($dir . "data/" . $networkname . "_nodes.xml")) {
	$fh = fopen($dir . "data/" . $networkname . "_nodes.xml", 'w') or die("Can't write to the nodes file.");
	fwrite($fh, "<?xml version='1.0' encoding='UTF-8' ?>\n<network>\n<name>" . $networkname . "</name>\n</network>");
	fclose($fh);
}

if(isset($_POST['action']) && $_POST['action'] == "add") {
	if(file_exists($dir . "data/mac2net/" . base64_encode($_POST['mac']) . ".txt")) {$status = "<p id=\"error\">That node already exists on the " . file_get_contents($dir . "data/mac2net/" . base64_encode($_POST['mac']) . ".txt") . " network.</p>";}
	else {
		if(!$_POST['location']) {
			$lat = $_POST['lat'];
			$lng = $_POST['lon'];
		}
		else {
			$latlng = str_replace("(", "", $_POST['location']);
			$latlng = str_replace(")", "", $latlng);
			$latlng = str_replace(",", "", $latlng);
			$latlng = split(" ", $latlng);
			
			$lat = $latlng[0];
			$lng = $latlng[1];
		}

		$ip = str_replace("-", "", str_replace(":", "", $_POST['mac']));
		$ip = "5." . hexdec(substr($ip, -6, 2)) . "." . hexdec(substr($ip, -4, 2)) . "." . hexdec(substr($ip, -2));

		$data = "<node>
<name>" . $_POST['name'] . "</name>
<notes>" . $_POST['notes'] . "</notes>
<mac>" . $_POST['mac'] . "</mac>
<ip>" . $ip . "</ip>
<lat>" . $lat . "</lat>
<lng>" . $lng . "</lng>
</node>
</network>";

		$fc = file_get_contents($dir . "data/" . $networkname . "_nodes.xml");
		$fc = str_replace("</network>", $data, $fc);

		$fh = fopen($dir . "data/" . $networkname . "_nodes.xml", 'w') or die("Can't write to the nodes file.");
		fwrite($fh, $fc);
		fclose($fh);

		$fh = fopen($dir . "data/mac2net/" . base64_encode($_POST['mac']) . ".txt", 'w') or die("Can't write to the nodes file.");
		fwrite($fh, $networkname);
		fclose($fh);
	}
}

else if(isset($_POST['action']) && $_POST['action'] == "update") {
	if(file_get_contents($dir . "data/mac2net/" . base64_encode($_POST['mac']) . ".txt") == $networkname) {
		$xmlp = simplexml_load_file($dir . "data/" . $networkname . "_nodes.xml");
		
		foreach($xmlp->node as $node) {
			if($node->ip == $_POST['ip'] && $node->mac == $_POST['mac'] && file_get_contents($dir . "data/mac2net/" . base64_encode($node->mac) . ".txt") == $networkname) {
				if(!$_POST['location']) {
					$lat = $_POST['lat'];
					$lng = $_POST['lon'];
				}
				else {
					$latlng = str_replace("(", "", $_POST['location']);
					$latlng = str_replace(")", "", $latlng);
					$latlng = str_replace(",", "", $latlng);
					$latlng = split(" ", $latlng);
					
					$lat = $latlng[0];
					$lng = $latlng[1];
				}

$existing_data = "<node>
<name>" . $node->name . "</name>
<notes>" . $node->notes . "</notes>
<mac>" . $node->mac . "</mac>
<ip>" . $node->ip . "</ip>
<lat>" . $node->lat . "</lat>
<lng>" . $node->lng . "</lng>
</node>";

$new_data = "<node>
<name>" . $_POST['name'] . "</name>
<notes>" . $_POST['notes'] . "</notes>
<mac>" . $_POST['mac'] . "</mac>
<ip>" . $_POST['ip'] . "</ip>
<lat>" . $lat . "</lat>
<lng>" . $lng . "</lng>
</node>";

if(!is_dir($dir . "data/pnc/" . base64_encode($_POST['mac']) . "/")) {mkdir($dir . "data/pnc/" . base64_encode($_POST['mac']) . "/");}

				// Run mode
				$fg = fopen($dir . "data/pnc/" . base64_encode($_POST['mac']) . "/node.run_mode.txt", 'w') or die("Can't write to the pnc file.");
				fwrite($fg, $_POST['runmode']);
				fclose($fg);
				
				// Static IP's for Gateways
				// staticcheckbox tells us if the settings are enabled or not
				
				$fg = fopen($dir . "data/pnc/" . base64_encode($_POST['mac']) . "/staticcheckbox.txt", 'w') or die("Can't write to the pnc file.");
				fwrite($fg, $_POST['option']);
				fclose($fg);
				
				if($_POST['option'] == "gatewaystatic") {
					$fg = fopen($dir . "data/pnc/" . base64_encode($_POST['mac']) . "/node.predef_role.txt", 'w') or die("Can't write to the pnc file.");
					fwrite($fg, "2");
					fclose($fg);
				}
				else {
					$fg = fopen($dir . "data/pnc/" . base64_encode($_POST['mac']) . "/node.predef_role.txt", 'w') or die("Can't write to the pnc file.");
					fwrite($fg, "-1");
					fclose($fg);
				}

				// Static IP
				$fg = fopen($dir . "data/pnc/" . base64_encode($_POST['mac']) . "/gw.ipaddr.txt", 'w') or die("Can't write to the pnc file.");
				fwrite($fg, $_POST['staticip']);
				fclose($fg);

				// Subnet IP
				$fg = fopen($dir . "data/pnc/" . base64_encode($_POST['mac']) . "/gw.netmask.txt", 'w') or die("Can't write to the pnc file.");
				fwrite($fg, $_POST['subnetip']);
				fclose($fg);

				// Gateway IP
				$fg = fopen($dir . "data/pnc/" . base64_encode($_POST['mac']) . "/gw.defroute.txt", 'w') or die("Can't write to the pnc file.");
				fwrite($fg, $_POST['gatewayip']);
				fclose($fg);

				$fc = file_get_contents($dir . "data/" . $networkname . "_nodes.xml");
				$fc = str_replace($existing_data, $new_data, $fc);

				$fh = fopen($dir . "data/" . $networkname . "_nodes.xml", 'w') or die("Can't write to the nodes file.");
				fwrite($fh, $fc);
				fclose($fh);
				
				unlink($dir . "data/cid/" . $networkname . ".txt");
			}
		}
	}
	else {$status = "<p id=\"error\">That node already exists on the " . file_get_contents($dir . "data/mac2net/" . base64_encode($_POST['mac']) . ".txt") . " network.</p>";}
}



else if(isset($_GET['action']) && $_GET['action'] == "remove") {
$xmlp = simplexml_load_file($dir . "data/" . $networkname . "_nodes.xml");

	foreach($xmlp->node as $node) {
		if($node->ip == $_GET['ip'] && $node->mac == $_GET['mac']) {
			if(!file_exists($dir . "data/mac2net/" . base64_encode($node->mac) . ".txt") || file_get_contents($dir . "data/mac2net/" . base64_encode($node->mac) . ".txt") == $networkname) {
$data = "<node>
<name>" . $node->name . "</name>
<notes>" . $node->notes . "</notes>
<mac>" . $node->mac . "</mac>
<ip>" . $node->ip . "</ip>
<lat>" . $node->lat . "</lat>
<lng>" . $node->lng . "</lng>
</node>";

$datatwo = "<node>\n<lat>" . $node->lat . "</lat>\n<lng>" . $node->lng . "</lng>\n<name>" . $node->name . "</name>\n<notes>" . $node->notes . "</notes>\n<ip>" . $node->ip . "</ip>\n<mac>" . $node->mac . "</mac>\n</node>";

				$fc = file_get_contents($dir . "data/" . $networkname . "_nodes.xml");
				$fc = str_replace($data, "", $fc);
				$fc = str_replace($datatwo, "", $fc);
				
				$fh = fopen($dir . "data/" . $networkname . "_nodes.xml", 'w') or die("Can't write to the nodes file.");
				fwrite($fh, $fc);
				fclose($fh);

				if(file_exists($dir . "data/mac2net/" . base64_encode($node->mac) . ".txt")) {unlink($dir . "data/mac2net/" . base64_encode($node->mac) . ".txt");}
				if(file_exists($dir . "data/hbc/" . base64_encode($node->mac) . ".txt")) {unlink($dir . "data/hbc/" . base64_encode($node->mac) . ".txt");}
				if(file_exists($dir . "data/role/" . base64_encode($node->mac) . ".txt")) {unlink($dir . "data/role/" . base64_encode($node->mac) . ".txt");}
				if(file_exists($dir . "data/stats/" . $networkname . "/" . base64_encode($node->mac) . ".txt")) {unlink($dir . "data/stats/" . $networkname . "/" . base64_encode($node->mac) . ".txt");}
				if(file_exists($dir . "data/stats/" . $networkname . "/" . base64_encode($node->mac) . ".date.txt")) {unlink($dir . "data/stats/" . $networkname . "/" . base64_encode($node->mac) . ".date.txt");}
				
				if(file_exists($dir . "data/pnc/" . base64_encode($node->mac) . "/gw.defroute.txt")) {unlink($dir . "data/pnc/" . base64_encode($node->mac) . "/gw.defroute.txt");}
				if(file_exists($dir . "data/pnc/" . base64_encode($node->mac) . "/gw.ipaddr.txt")) {unlink($dir . "data/pnc/" . base64_encode($node->mac) . "/gw.ipaddr.txt");}
				if(file_exists($dir . "data/pnc/" . base64_encode($node->mac) . "/gw.netmask.txt")) {unlink($dir . "data/pnc/" . base64_encode($node->mac) . "/gw.netmask.txt");}
				if(file_exists($dir . "data/pnc/" . base64_encode($node->mac) . "/node.predef_role.txt")) {unlink($dir . "data/pnc/" . base64_encode($node->mac) . "/node.predef_role.txt");}
				if(file_exists($dir . "data/pnc/" . base64_encode($node->mac) . "/node.run_mode.txt")) {unlink($dir . "data/pnc/" . base64_encode($node->mac) . "/node.run_mode.txt");}
				if(file_exists($dir . "data/pnc/" . base64_encode($node->mac) . "/staticcheckbox.txt")) {unlink($dir . "data/pnc/" . base64_encode($node->mac) . "/staticcheckbox.txt");}
				
				if(is_dir($dir . "data/pnc/" . base64_encode($node->mac) . "/")) {rmdir($dir . "data/pnc/" . base64_encode($node->mac) . "/");}
			}
		}
	}
}
?>

<html>
<head>
<title>Node Management: <?php echo $brand; ?></title>
<link rel="stylesheet" type="text/css" href="<?php echo $wdir; ?>resources/style.css">
<link rel="shortcut icon" href="<?php echo $wdir; ?>resources/favicon.ico"/>
</head>
<body>
<div id="wrapper">
	<div id="header">
		<div id="logo">Node Management</div>
	</div>
	<div id="content">
		<div id="page-content">
			You need to add your nodes here so that <?php echo $brand; ?> will know which network they belong to.<br />
			Make sure the MAC address is entered exactly as it is on the device.<br />
			<br />
			<?php if(isset($_POST['status'])) {echo $status;} ?>

			<form action="<?php echo $wdir; ?>edit-node.php" method="POST" onsubmit="getnumber();">
			<?php
			if(isset($_GET['action']) && isset($_GET['mac']) && $_GET['action'] == "configure") {
			$xmlp = simplexml_load_file($dir . "data/" . $networkname . "_nodes.xml");
			
			foreach($xmlp->node as $node) {
				if($node->ip == $_GET['ip'] && $node->mac == $_GET['mac'] && file_get_contents($dir . "data/mac2net/" . base64_encode($node->mac) . ".txt") == $networkname) {
					$name = $node->name;
					$notes = $node->notes;
					$ip = $node->ip;
					$lat = $node->lat;
					$lng = $node->lng;
				}
			}
			
			if(file_exists($dir . "data/pnc/" . base64_encode($_GET['mac']) . "/node.run_mode.txt")) {$runmode = file_get_contents($dir . "data/pnc/" . base64_encode($_GET['mac']) . "/node.run_mode.txt");}
			else {$runmode = "0";}
			
			if(file_exists($dir . "data/pnc/" . base64_encode($_GET['mac']) . "/staticcheckbox.txt")) {$staticcheckbox = file_get_contents($dir . "data/pnc/" . base64_encode($_GET['mac']) . "/staticcheckbox.txt");}
			else {$staticcheckbox = "runmode";}
			
			if(file_exists($dir . "data/pnc/" . base64_encode($_GET['mac']) . "/gw.ipaddr.txt")) {$staticip = file_get_contents($dir . "data/pnc/" . base64_encode($_GET['mac']) . "/gw.ipaddr.txt");}
			if(file_exists($dir . "data/pnc/" . base64_encode($_GET['mac']) . "/gw.netmask.txt")) {$subnetip = file_get_contents($dir . "data/pnc/" . base64_encode($_GET['mac']) . "/gw.netmask.txt");}
			if(file_exists($dir . "data/pnc/" . base64_encode($_GET['mac']) . "/gw.defroute.txt")) {$gatewayip = file_get_contents($dir . "data/pnc/" . base64_encode($_GET['mac']) . "/gw.defroute.txt");}
			?>
				<label for="name">Node Name</label>		
				<input type="text" name="name" value="<?php echo $name; ?>" style="width:83%;" />	
				<input type="hidden" name="mac" id="mac" value="<?php echo $_GET['mac']; ?>" />
				<input type="hidden" name="ip" id="ip" value="<?php echo $ip; ?>" />
				
				<label for="option">Enable Runmode</label>
				<input type="radio" name="option" value="runmode" onchange="enable_runmode();"<?php if($staticcheckbox == "runmode") {echo " checked=checked";} ?> style="width:83%;" />
				
				<select name="runmode" id="runmode" style="width:83%;">
					<option value="0"<?php if($runmode == "0") {echo " selected";} ?>>Mesh &amp; Access Points
					<option value="1"<?php if($runmode == "1") {echo " selected";} ?>>Mesh only
				</select>
				
				<label for="option">Enable Gateway Static IP</label>
				<input type="radio" name="option" value="gatewaystatic" onchange="enable_static();"<?php if($staticcheckbox == "gatewaystatic") {echo " checked=checked";} ?> style="width:83%;" />
				
				<label for="staticip">Static IP Address</label>
				<input type="text" name="staticip" id="staticip" value="<?php echo $staticip; ?>" style="width:83%;" />

				<label for="staticip">Subnet IP Address</label>
				<input type="text" name="subnetip" id="subnetip" value="<?php echo $subnetip; ?>" style="width:83%;" />
				
				<label for="staticip">Gateway IP Address</label>
				<input type="text" name="gatewayip" id="gatewayip" value="<?php echo $gatewayip; ?>" style="width:83%;" />
				
				<label for="notes">Notes for this node</label>
				<textarea name="notes" style="width:83%;" /><?php echo $notes; ?></textarea>

				<script>
				function enable_runmode() {
					document.getElementById("runmode").disabled = false;
				
					document.getElementById("staticip").disabled = true;
					document.getElementById("subnetip").disabled = true;
					document.getElementById("gatewayip").disabled = true;
				}
				
				function enable_static() {
					document.getElementById("staticip").disabled = false;
					document.getElementById("subnetip").disabled = false;
					document.getElementById("gatewayip").disabled = false;
					
					document.getElementById("runmode").disabled = true;
				}
				
				<?php
				if($staticcheckbox == "runmode") {echo "enable_runmode();";}
				else if($staticcheckbox == "gatewaystatic") {echo "enable_static();";}
				else {echo "enable_runmode();";}
				?>
				</script>
				<?php if($connectivity == "internet") {echo "<div id=\"map\" style=\"width:83%;height:300px;\"></div>";} ?>

				<?php if($connectivity == "internet") {echo "<input type=\"hidden\" id=\"lat\">";} else {echo "<label for=\"lat\">Latitude</label>\n<input type=\"text\" name=\"lat\" value=\"" . $lat . "\" id=\"lat\" style=\"width:83%;\">";} ?>
				<?php if($connectivity == "internet") {echo "<input type=\"hidden\" id=\"lon\">";} else {echo "<label for=\"lon\">Longitude</label>\n<input type=\"text\" name=\"lon\" value=\"" . $lng . "\" id=\"lon\" style=\"width:83%;\">";} ?>
				<input type="hidden" name="zoom_level" id="zoom_level">
				<input type="hidden" name="location" id="location" />
				<input type="hidden" name="action" value="update" />
			<?php
			echo "<br /><input type=\"button\" style=\"width:20%;\" onclick=\"document.location = './edit-node.php';\" name=\"button\"  value=\"Cancel\"> <input type=\"submit\" style=\"font-weight:bold;width:63%;\" name=\"sent\" value=\"Change Node\" />";
			}
			else if(isset($_GET['action']) && $_GET['action'] == "add") {
			?>
				<label for="name">Node Name</label>		
				<input type="text" name="name" value="" style="width:83%;" />
				
				<label for="mac">MAC Address <i>(NOT the IP Address, enter with : and in CAPITALS)</i></label>		
				<input type="text" name="mac" id="mac" value="" style="width:83%;" />
				
				<label for="notes">Notes for this node</label>
				<textarea name="notes" style="width:83%;" /></textarea>

				<?php if($connectivity == "internet") {echo "<div id=\"map\" style=\"width:83%;height:300px;\"></div>";} ?>

				<?php if($connectivity == "internet") {echo "<input type=\"hidden\" id=\"lat\">";} else {echo "<label for=\"lat\">Latitude</label>\n<input type=\"text\" name=\"lat\" id=\"lat\" style=\"width:83%;\">";} ?>
				<?php if($connectivity == "internet") {echo "<input type=\"hidden\" id=\"lon\">";} else {echo "<label for=\"lon\">Longitude</label>\n<input type=\"text\" name=\"lon\" id=\"lon\" style=\"width:83%;\">";} ?>
				<input type="hidden" name="ip" id="ip" value="" />
				<input type="hidden" id="zoom_level">
				<input type="hidden" name="location" id="location" />
				<input type="hidden" name="action" value="add" />
			<?php
			echo "<br /><input type=\"button\" style=\"width:20%;\" onclick=\"document.location = './edit-node.php';\" name=\"button\"  value=\"Cancel\"> <input type=\"submit\" style=\"font-weight:bold;width:63%;\" name=\"sent\" value=\"Add Node\" />";
			}
			else {
				$xmlp = simplexml_load_file($dir . "data/" . $networkname . "_nodes.xml");
				
				include($dir . "resources/ouilookup.php");	// For looking up the vendor of the device
				include($dir . "resources/ubntlookup.php");	// For looking up the model of the device (Ubiquiti devices only)
				
				$i = 0;

				foreach($xmlp->node as $node) {
					$vendor = ouilookup($node->mac);
					$vendor = explode(" ", $vendor);
					$idstring = $vendor[0];
					
					if(substr(str_replace(":", "", $node->mac), 0, 6) == "00156D" || substr(str_replace(":", "", $node->mac), 0, 6) == "002722") {
						$model = ubntlookup($node->mac);
						$model = explode(" ", $model);
						$idstring .= " " . $model[0];
					}
					
					if($i == 0) {echo "<h2>Nodes</h2>\n\n<table style=\"width:83%;\">\n";}
					echo "<tr style=\"margin-left:2px;border: 2px solid #ccc;\">\n<td style=\"width:15%;\">" . $idstring . "</td>\n<td style=\"text-align:left;\">" . $node->name . "</td>\n<td>" . $node->ip . "</td>\n<td>" . $node->mac . "</td>\n<td><a href=\"edit-node.php?action=configure&ip=" . $node->ip . "&mac=" . $node->mac . "\">Configure</a></td>\n<td><a href=\"edit-node.php?action=remove&ip=" . $node->ip . "&mac=" . $node->mac . "\">Remove</a></td>\n</tr>\n\n";

					$i = $i + 1;
				}

				if($i == 0) {echo "<b>There are currently no nodes.</b><br />Why not <a href=\"./edit-node.php?action=add\" style=\"font-weight:bold;\">add one</a> <i>or</i> <a href=\"" . $wdir . "import.php?return=edit-node\" style=\"font-weight:bold;\">Import from open-mesh.com</a>?<br /><br />";}
				else {echo "</table>\n";}
				
				echo "<br /><input type=\"button\" style=\"width:20%;\" onclick=\"document.location = './edit-node.php?action=add';\" name=\"button\"  value=\"Add Node\"> <input type=\"button\" style=\"font-weight:bold;width:63%;\" onclick=\"window.close();\" name=\"sent\" value=\"Close Window\" />";
			}
			?>
			</form>
		</div>
		<div id="sidebar"></div>	
	</div>
</div>

<?php
if($connectivity == "internet") {
	if(isset($_GET['action']) && $_GET['action'] == "configure" && isset($_GET['mac'])) {
		// The lattitude and longitude are already set above
	}
	else if(strlen($xmlp->robindash->location) > 0) {
		$location = explode(",", $xmlp->robindash->location);
		
		$lat = $location[0];
		$lng = $location[1];
	}
	else {
		$lat = "0";
		$lng = "0";
		
		$fc = file_get_contents($dir . "data/" . $networkname . ".xml");
		$fc = str_replace("<forwardcheck>" . $xmlp->robindash->forwardcheck . "</forwardcheck>", "<forwardcheck>" . $xmlp->robindash->forwardcheck . "</forwardcheck>\n<location>0,0</location>", $fc);

		$fh = fopen($dir . "data/" . $networkname . ".xml", 'w') or die("Can't write to the data file.");
		fwrite($fh, $fc);
		fclose($fh);
	}

	if(isset($_GET['action'])) {
	?>
		<script type="text/javascript" src="http://maps.google.com/maps/api/js?sensor=false"></script>
		<script type="text/javascript">
		var map;
		var marker = false;

		function initialize() {
			var b = new google.maps.LatLng(<?php echo $lat . ", " . $lng; ?>);
			var c = {
				zoom: 14,
				center: b,
				mapTypeId: google.maps.MapTypeId.ROADMAP
			};
			map = new google.maps.Map(document.getElementById("map"), c);
			marker = new google.maps.Marker({
				position: b,
				map: map
			});
			google.maps.event.addListener(map, 'center_changed', function () {
				var a = map.getCenter();
				document.getElementById("lat").value = a.lat();
				document.getElementById("lon").value = a.lng();
				document.getElementById("location").value = a;
				placeMarker(a)
			});
			google.maps.event.addListener(map, 'zoom_changed', function () {
				document.getElementById("zoom_level").value = map.getZoom()
			});
			google.maps.event.addListener(marker, 'dblclick', function () {
				zoomLevel = map.getZoom() + 1;
				document.getElementById("zoom_level").value = zoomLevel;
				map.setZoom(zoomLevel)
			});
		}
		function placeMarker(a) {
			marker.setPosition(a)
		}
		window.onload = function () {
			initialize()
		};
		</script>
		<?php
	}
}
?>

<?php echo $tracker; ?>
</body>
</html>
