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


if(isset($_GET['action']) && $_GET['action'] == "test") {die("success");}
else if(isset($_GET['action']) && $_GET['action'] == "demo-user" && file_exists("settings.php")) {
	require("settings.php");
	
	session_start();
	
	$_SESSION['user'] = "test";
	$_SESSION['pass'] = md5("test");

	header("Location: ./edit.php");
	exit;
}
else if(file_exists("settings.php")) {require("settings.php");}
else {header("Location: oobe.php");exit;}


if(isset($_POST['submit']) && $_POST['submit'] == "Register") {header("Location: " . $wdir . "create.php");exit;}
else if(isset($_GET['register'])) {$status = $_LANG['email_account_complete'];}
else if(isset($_GET['setupcomplete'])) {$status = $_LANG['email_server_complete'];}
else if(isset($_GET['action']) && $_GET['action'] == "delete-account") {
	mail($from, $_LANG['email_account_removal_admin_subject'], $_LANG['email_account_removal_admin_body']);
	$status = "<b>" . $_LANG['email_account_removal'] . "</b>";
	session_destroy();
}
else if(isset($_SESSION['user']) && isset($_GET['action']) && $_GET['action'] == "download-backup") {
	$networkname = $_SESSION['user'];	// Set the username
	$zip = new ZipArchive();			// Initiate the zip object
	
	// Create the file, if we cant: say so
	if($zip->open($dir . "data/" . $networkname . ".zip", ZIPARCHIVE::CREATE) !== TRUE) {die("Could not create backup archive.");}

	// Files to zip
	$file = array(
		$dir . "data/" . $networkname . ".xml",			// Main Settings
		$dir . "data/" . $networkname . "_nodes.xml",	// Node Settings
		$dir . "data/" . $networkname . ".txt"			// Splash Page
	);

	// Add aforementioned files to the zip
	foreach($file as $f) {
		$zip->addFile($f) or die("Could not add '" . $f . "' to the zip file.");
	}

	$zip->close();	// Close the zip
	header("Location: " . $wdir . "data/" . $networkname . ".zip");	// Show it to us
}
else if(isset($_GET['user']) && file_exists($dir . "data/" . $_GET['user'] . ".xml") && isset($_SESSION['master_user'])) {
	$xml = simplexml_load_file($dir . "data/" . $_GET['user'] . ".xml");

	if($_SESSION['master_user'] == $xml->robindash->notifymail) {
		$_SESSION['user'] = $_GET['user'];
		$_SESSION['pass'] = (string)$xml->robindash->password;
		
		header("Location: " . $wdir . "edit.php");
	}
	else {header("Location: " . $wdir);}
	
	exit;
}
else if(isset($_SESSION['user']) && isset($_SESSION['pass']) && file_exists($dir . "data/" . $_SESSION['user'] . ".xml")) {
	$xmlp = simplexml_load_file($dir . "data/" . $_SESSION['user'] . ".xml");

	if($_SESSION['pass'] == $xmlp->robindash->password) {
		header("Location: " . $wdir . "edit.php");
		exit;
	}
}
else if(isset($_POST['user']) && $_POST['pass'] == "" && file_exists($dir . "data/" . $_POST['user'] . ".xml")) {
	header("Location: " . $wdir . "overview.php?id=" . $_POST['user']);
	exit;
}
else if(isset($_POST['user']) && isset($_POST['pass']) && file_exists($dir . "data/masters/" . $_POST['user'] . ".xml")) {
	$xmlp = simplexml_load_file($dir . "data/masters/" . $_POST['user'] . ".xml");

	if(md5($_POST['pass']) == $xmlp->password) {
		$network = explode("\n", $xmlp->networks);

		foreach($network as $item) {
			if($item == "") {echo "";}
			else {$network = $item;}
		}
		
		$xmlp = simplexml_load_file($dir . "data/" . $network . ".xml");
		
		$_SESSION['user'] = $network;
		$_SESSION['pass'] = (string)$xmlp->robindash->password;
		$_SESSION['master_user'] = $_POST['user'];
		
		header("Location: " . $wdir . "edit.php");
		exit;
	}
	else {
		$status = "<b>" . $_LANG['incorrect_password'] . "</b>";
		session_destroy();
	}
}
else if(isset($_POST['user']) && isset($_POST['pass']) && file_exists($dir . "data/" . $_POST['user'] . ".xml")) {
	$xmlp = simplexml_load_file($dir . "data/" . $_POST['user'] . ".xml");

	if(md5($_POST['pass']) == $xmlp->robindash->password) {
		$_SESSION['user'] = $_POST['user'];
		$_SESSION['pass'] = md5($_POST['pass']);
		
		if(isset($xmlp->robindash->notifymail)) {$_SESSION['master_user'] = (string)$xmlp->robindash->notifymail;}
		
		header("Location: " . $wdir . "edit.php");
		exit;
	}
	else {
		$status = "<b>" . $_LANG['incorrect_password'] . "</b>";
		session_destroy();
	}
}
else if(isset($_POST['user']) && !file_exists($dir . "data/" . $_POST['user'] . ".xml")) {$status = "<b>" . $_LANG['incorrect_username'] . "</b>";}
else {$status = "";}

if($has_https == "true" && !$_SERVER['HTTPS']) {header("Location: https://" . $sn . $wdir);}
?>

<!DOCTYPE HTML>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<title><?php echo $brand; ?></title>

<link rel="stylesheet" type="text/css" href="<?php echo $wdir; ?>resources/style.css" />
<link rel="shortcut icon" href="<?php echo $wdir; ?>resources/favicon.ico"/>

<meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
<meta http-equiv="PICS-Label" content='(PICS-1.1 "http://www.classify.org/safesurf/" L gen true for "<?php if($has_https == "true") {echo "https";} else {echo "http";} echo "://" . $sn; ?>/" r (SS~~000 1))'>

<script type="text/javascript" src="<?php echo $wdir; ?>resources/modalbox/includes/prototype.js"></script>
<script type="text/javascript" src="<?php echo $wdir; ?>resources/modalbox/includes/scriptaculous.js?load=builder,effects"></script>
<script type="text/javascript" src="<?php echo $wdir; ?>resources/modalbox/modalbox.js"></script>
</head>
<body>
<div id="login-panel" style="margin-left:35%;margin-top:10%;">
	<h2 class="nospacing" style="margin-bottom:3%;">Login to</h2>
	
	<div id="page-content">
		<div style="margin-top:-5%;font-size:400%;font-weight:bold;text-align:center;"><?php echo $brand; ?></div>
		<?php if(isset($status)) {echo "<p style=\"text-align:center;\">" . $status . "</p>";} ?>

		<form action="<?php echo $wdir; ?>" method="post" id="login-form">
			<label for="user"><b><?php echo $_LANG['username']; ?></b></label>
			<input type="text" id="user" name="user" value="<?php if(isset($_GET['id'])) {echo $_GET['id'];} ?>" style="width:95%;" />
				
			<label for="pass"><b><?php echo $_LANG['password']; ?></b></label>
			<input type="password" id="pass" name="pass" value="" style="width:95%;" />
			
			<input type="submit" name="submit" value="<?php echo $_LANG['login']; ?>" class="btn-login" style="width:60%;margin-right:2%;" /><input type="submit" name="submit" value="Register" class="btn-login" style="width:20%;" />
		</form>
		<hr style="margin-top:0%;" />
		<p style="text-align:left;margin-top:-2%;margin-bottom:2%;"><?php echo $_LANG['demo_intro']; ?><br /><strong><a href="<?php echo $wdir; ?>?action=demo-user"><?php echo $_LANG['demo_link']; ?></a></strong><p style="text-align:right;margin-top:<?php if(strpos($_SERVER['HTTP_USER_AGENT'], 'WebKit') !==FALSE) {echo "-14";} else {echo "-15.5";} ?>%;margin-bottom:-7%;"><?php echo $_LANG['forgot_intro']; ?><br /><strong><a href="<?php echo $wdir; ?>reset-password.php" onclick="Modalbox.show('reset-password.php?type=modal', {width: 460, title: 'Forgot Password'}); return false;" title="Reset Password"><?php echo $_LANG['forgot_link']; ?></a></strong></p></p>
	</div>
</div>

<?php echo $tracker; ?>
</body>
</html>