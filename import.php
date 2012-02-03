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

ini_set("display_errors", 1);

if(file_exists($dir . "data/" . $_SESSION['user'] . ".xml")) {
	// User has signed up already, so we must verify that they are the ones wanting the import

	if($_SESSION['user'] && $_SESSION['pass']) {
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
}
else {/* User has not been created, so we allow them to import from the signup page */}

if(isset($_POST['network'])) {
	// request the page from cloudtrax
	$ch = curl_init();
	curl_setopt($ch,CURLOPT_URL, "http://www.cloudtrax.com/map.php?id=" . $_POST['network']);
	curl_setopt($ch,CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch,CURLOPT_HEADER, 0);
	$response = curl_exec($ch);
	curl_close($ch);
	
	if($response == "" || strpos($response, 'Not Found') !==FALSE) {$status = "There was an error importing your nodes from CloudTrax.";}
	else {
		// strip out all the excess information we dont need
		$response = str_replace("    //<![CDATA[
	var point;
	var marker;
	var polyline;
	var html;
	var geocoder = null;


function onLoad() 
{
	var map = new GMap2(document.getElementById(\"map\"));
	map.setUIToDefault();
  geocoder = new GClientGeocoder();//	map.addControl(new GLargeMapControl());
//	map.addControl(new GMapTypeControl());
//	map.addControl(new GOverviewMapControl());
	
	var point;
	var marker;
	var polyline;
	var html;
	
	window.onresize=setMapSizePos;  // event handler for non-IE browsers
	sorttable.init();

	map.setCenter(new GLatLng(-37.68719108181173, 176.1677885055542), 17);
	map.setMapType(G_NORMAL_MAP); // HYBRID_MAP); //G_NORMAL_MAP);", "", $response);
		
		$response = str_replace("	", "", $response);
		
		
		// include our parser
		require($dir . "resources/simple_html_dom.php");
		
		// load the cleaned-response into the parser
		$html = str_get_html($response);
		
		$proper_array = array();
		$i = 0;
		
		$string .= "<?xml version='1.0' encoding='UTF-8' ?>\n";
		$string .= "<network>\n";
		$string .= "<name>" . $_POST['network'] . "</name>\n";
		
		// find the right part of the page to get our information from
		foreach($html->find('script') as $element) {
			$content_array = $element->innertext;
			$content_array = explode("\n", $content_array);

			foreach($content_array as $content) {
				if($content == "") {echo "";}
				else if(strpos($content, 'point =') !==FALSE) {
					$content = str_replace("point = new GPoint(", "", $content);
					$content = str_replace(");", "", $content);

					$locations = explode(", ", $content);
					
					$string .= "<node>\n";
					$string .= "<lat>" . $locations[0] . "</lat>\n";
					$string .= "<lng>" . str_replace("\r", "", $locations[1]) . "</lng>\n";
				}
				else if(strpos($content, 'marker =') !==FALSE) {
					$extras = explode(", ", $content);
					
					foreach($extras as $item) {
						if(strpos($item, '"5.') !==FALSE) {
							$string .= "<ip>" . str_replace("\"", "", $item) . "</ip>\n";
							if(!str_replace("\"", "", $item) == "") {$ip = str_replace("\"", "", $item);}
						}
						else if(strpos($item, ' users') !==FALSE) {
							$num = count($extras) - 3;
							$num = $extras[$num];
							$num = str_replace("\"", "", $num);
							
							$string .= "<name>" . str_replace("\"", "", str_replace(" - " . $num . " users", "", $item)) . "</name>\n";
							$string .= "<notes></notes>\n";
							$name = str_replace("\"", "", str_replace(" - " . $num . " users", "", $item));
						}
						else if(strpos($item, 'table table') !==FALSE) {
							$exploded_ip = explode(".", $ip);
							$lastipoctet = $exploded_ip[3];
							
							$string .= "<mac>";
							$string .= substr(str_replace("\"" . $name . "&nbsp;property&nbsp;valueIPMAC5.xx.xx." . $lastipoctet, "", strip_tags($item)), 0, 8);
							
							$string .= ":" . strtoupper(dechex($exploded_ip[1])) . ":" . strtoupper(dechex($exploded_ip[2])) . ":";
							
							if(strlen(strtoupper(dechex($exploded_ip[3]))) == "1") {$string .= "0" . strtoupper(dechex($exploded_ip[3]));}
							else {$string .= strtoupper(dechex($exploded_ip[3]));}
							
							$string .= "</mac>\n";
							$string .= "</node>\n";
						}
					}
				}
			}
		}
		$string .= "</network>";
		
		if($_SESSION['user']) {$networkname = $_SESSION['user'];}
		else {$networkname = strtolower($_POST['network']);}
		
		$node = simplexml_load_string($string);
		
		foreach($node as $item) {
			$mac = $item->mac;
			$string = str_replace("<mac>" . $mac . "</mac>", "<mac>" . strtoupper($mac) . "</mac>", $string);
		}
		
		// save each nodes mac2net
		foreach($node as $item) {	
			$fi = fopen($dir . "data/mac2net/" . base64_encode($item->mac) . ".txt", 'w') or die("Can't write to the mac2net file.");
			fwrite($fi, $networkname);
			fclose($fi);
		}
		
		// save the users node file
		$fk = fopen($dir . "data/" . $networkname . "_nodes.xml", 'w') or die("Can't write to the nodes file.");
		fwrite($fk, $string);
		fclose($fk);

		if(isset($_SESSION['user'])) {
			// return the user to where they were
			if($_POST['return'] == "edit-node") {header("Location: " . $wdir . "edit-node.php");}
			else {header("Location: " . $wdir . "edit.php?status=importdone");}
			
			exit;
		}
		else {header("Location: " . $wdir . "create.php?id=" . $networkname);exit;}
	}
}
?>

<html>
<head>
<title>Import Nodes: <?php echo $brand; ?></title>
<link rel="stylesheet" type="text/css" href="<?php echo $wdir; ?>resources/style.css">
<link rel="shortcut icon" href="<?php echo $wdir; ?>resources/favicon.ico"/>
</head>
<body>
<div id="login-panel">
	<h2 class="nospacing">Import for</h2>
	<h1><?php echo $brand; ?></h1>
	<div id="page-content">
			<form action="<?php echo $wdir; ?>import.php" method="post" class="register-form">
				<input type="hidden" name="sent" value="true" />

				<p><?php if(!isset($status)) {echo "Simply enter the network name you use to login to the server at CloudTrax.com to import your nodes to " . $brand . ".";} else {echo "<b>" . $status . "</b>";} ?></p>

				<label for="network">Username</label>		
				<input type="text" name="network" value="" />
				
				<?php if(isset($_GET['return']) && $_GET['return'] == "edit-node") {echo "<input type=\"hidden\" name=\"return\" value=\"edit-node\" />";} ?>

				<input type="submit" name="sent" value="Import Nodes!" />
				<p><a href="<?php echo $wdir; ?>create.php" title="Return to Create Account">Return to Create Account</a></p>
			</form>
	</div>
</div>

<?php echo $tracker; ?>
</body>
</html>
</html>