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

// If we have the Ad Manager extension, use it:
if(file_exists($dir . "resources/extras/ad-splash.php")) {require($dir . "resources/extras/ad-splash.php");$hasadmanager = true;}
else {$hasadmanager = false;}


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

// Prevent people using up heaps of bandwidth by uploading bogus files
set_time_limit(10);


if(isset($_FILES['file']) && strlen($_FILES['file']['name']) > 4) {	
	if($_FILES['file']['size'] > 1024000) {echo "<script>alert('Your Splash Page/File is too large to be uploaded.');</script>";}
	else if($_FILES['file']['type'] == "text/plain" || $_FILES['file']['type'] == "text/html") {
		if($_FILES['file']['name'] == "multi-list.txt") {
			if(move_uploaded_file($_FILES['file']['tmp_name'], $dir . "data/uploads/" . $networkname . "/multi-list.txt")) {echo "<html><body><script>alert('Your URL List has been uploaded!');</script></body></html>";}
			else {echo "<script>alert('There was an error uploading your URL List.');</script>";}
		}
		else {
			if(move_uploaded_file($_FILES['file']['tmp_name'], $dir . "data/" . $networkname . ".txt")) {echo "<html><body><script>alert('Your Splash Page has been uploaded!');</script></body></html>";}
			else {echo "<script>alert('There was an error uploading your Splash Page.');</script>";}
		}
	}
	else if($_FILES['file']['type'] == "image/jpeg" || $_FILES['file']['type'] == "image/gif" || $_FILES['file']['type'] == "image/png" || $_FILES['file']['type'] == "image/bmp") {
		$name = substr(strtolower($_FILES['file']['name']), -3);
		
		if($name == 'jpg' || $name == 'gif' || $name == 'png' || $name == 'bmp') {
			if(move_uploaded_file($_FILES['file']['tmp_name'], $dir . "data/uploads/" . $networkname . "/" . $_FILES['file']['name'])) {echo "<html><body><script>alert('Your file has been uploaded!');</script></body></html>";}
			else {echo "<script>alert('There was an error uploading your file.');</script>";}
		}
		else {echo "<script>alert('Your have sent a file that is not a valid file.');</script>";}
	}
	else {echo "<script>alert('Your have sent a file that is not a Splash Page or a valid file.');</script>";}
}
else if(isset($_POST['contents']) && $_POST['type'] == "admanager" && $hasadmanager == true) {
	$fh = fopen($dir . "data/uploads/" . $networkname . "/multi-list.txt", 'w') or die("Can't write to the data file.");
	fwrite($fh, $_POST['contents']);
	fclose($fh);
	
	die("<html><body><script>alert('Your changes to the Ad Manager have been saved!');window.close();</script></body></html>");
}
else if(isset($_POST['contents'])) {	
	$fh = fopen($dir . "data/" . $networkname . ".txt", 'w') or die("Can't write to the data file.");
	fwrite($fh, $_POST['contents']);
	fclose($fh);
	
	if(file_exists($dir . "data/uploads/" . $networkname . "/multi-list.txt")) {unlink($dir . "data/uploads/" . $networkname . "/multi-list.txt");}
	
	die("<html><body><script>alert('Your changes to the Splash Page have been saved!');window.close();</script></body></html>");
}


if($hasadmanager == true) {$label = "Upload a Splash Page, File, or URL List (URL Lists <i>must</i> be named multi-list.txt)";}
else {$label = "Upload a Splash Page or File";}


$fc = file_get_contents($dir . "data/" . $networkname . ".txt");
$splashpage = "<html>
<head>
<title>Edit Splash Page: " . $brand . "</title>
<link rel=\"stylesheet\" type=\"text/css\" href=\"" . $wdir . "resources/style.css\">
<link rel=\"shortcut icon\" href=\"" . $wdir . "resources/favicon.ico\"/>
</head>
<body>
<div id=\"wrapper\">
	<div id=\"header\">
		<div id=\"logo\">Splash Page</div>
	</div>
	<div id=\"content\">
		<div id=\"page-content\">
			This page will be shown to users when they first connect to your network.<br />
			<br />

			<form action=\"edit-nds.php\" method=\"POST\" enctype=\"multipart/form-data\">
			<textarea cols=\"75\" rows=\"14\" id=\"contents\" name=\"contents\">" . $fc . "</textarea>";
			
			if($hasadmanager == true) {$splashpage = $splashpage . "<input type=\"radio\" name=\"type\" value=\"splashpage\" onclick=\"window.location = 'edit-nds.php?type=splashpage';\" checked> Splash Page<br /><input type=\"radio\" name=\"type\" value=\"admanager\" onclick=\"window.location = 'edit-nds.php?type=admanager';\"> Ad Manager<br />";}
			
			$splashpage = $splashpage . "<label for=\"file\">" . $label ."</label>
			<input type=\"file\" name=\"file\" />
			
			<br />
			<br />
			
			<input type=\"button\" style=\"font-weight:bold;width:18%;\" onclick=\"window.close();\" value=\"Close Window\" /> <input type=\"submit\" style=\"font-weight:bold;width:80%;\" name=\"sent\" value=\"Save Splash Page\" />
			</form>
		</div>
		<div id=\"sidebar\"></div>	
	</div>
</div>

<script type=\"text/javascript\" src=\"" . $wdir . "resources/ckeditor/ckeditor.js\"></script>
<script type=\"text/javascript\">
//<![CDATA[
	CKEDITOR.replace('contents', {toolbar:[['Undo', 'Redo', '-', 'Bold', 'Italic', 'Underline', 'Strike','Link', '-', 'Format']], height : '22em'});
//]]>
</script>
" . $tracker . "
</body>
</html>";



if($hasadmanager == true) {
if(file_exists($dir . "data/uploads/" . $networkname . "/multi-list.txt")) {$fctwo = file_get_contents($dir . "data/uploads/" . $networkname . "/multi-list.txt");}
else {$fctwo = "";}

$admanager = "<html>
<head>
<title>Ad Manager: " . $brand . "</title>
<link rel=\"stylesheet\" type=\"text/css\" href=\"" . $wdir . "resources/style.css\">
<link rel=\"shortcut icon\" href=\"" . $wdir . "resources/favicon.ico\"/>
</head>
<body>
<div id=\"wrapper\">
	<div id=\"header\">
		<div id=\"logo\">Ad Manager</div>
	</div>
	<div id=\"content\">
		<div id=\"page-content\">
			Entering a URL into this list will make it be shown to your clients at specified time intervals. URLs should be seperated by a new line. This feature could be used to show Advertisments to clients.<br />

			<form action=\"edit-nds.php\" method=\"POST\" enctype=\"multipart/form-data\">
			<textarea cols=\"75\" rows=\"14\" id=\"contents\" name=\"contents\">" . $fctwo . "</textarea>";
			
			if($hasadmanager == true) {$admanager = $admanager . "<input type=\"radio\" name=\"type\" value=\"splashpage\" onclick=\"window.location = 'edit-nds.php?type=splashpage';\"> Splash Page<br /><input type=\"radio\" name=\"type\" value=\"admanager\" onclick=\"window.location = 'edit-nds.php?type=admanager';\" checked> Ad Manager<br />";}
			
			$admanager = $admanager . "<label for=\"file\">Upload a URL List (URL Lists <i>must</i> be named multi-list.txt)</label>
			<input type=\"file\" name=\"file\" />
			
			<br />
			<br />
			
			<input type=\"button\" style=\"font-weight:bold;width:18%;\" onclick=\"window.close();\" value=\"Close Window\" /> <input type=\"submit\" style=\"font-weight:bold;width:80%;\" name=\"sent\" value=\"Save Ad Manager\" />
			</form>
		</div>
		<div id=\"sidebar\"></div>	
	</div>
</div>
" . $tracker . "
</body>
</html>";
}



if(file_exists($dir . "data/uploads/" . $networkname . "/multi-list.txt") && !$_GET['type'] && $hasadmanager == true) {echo $admanager;}
else if($hasadmanager == true && isset($_GET['type']) && $_GET['type'] == "admanager") {echo $admanager;}
else {echo $splashpage;}
?>