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

if(isset($_GET['user']) && isset($_GET['email']) && file_exists($dir . "data/" . $_GET['user'] . ".xml")) {
	$xmlp = simplexml_load_file($dir . "data/" . $_GET['user'] . ".xml");
	
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
	
	if($xmlp->robindash->notifymail == $_GET['email']) {
		$password = generatepassword();
		
		mail($xmlp->robindash->notifymail, "Your " . $brand . " accounts password has been reset", "Hi there " . $_GET['user'] . ",\n\nThis is an email to let you know that your accounts password at " . $brand . " has been reset.\nYour new password will be: " . $password . "\n\nPlease login to your account at: http://" . $sn . $wdir . " to change it to something more memorable soon.\n\nRegards,\nThe " . $brand . " Team");
		
		$fc = file_get_contents($dir . "data/" . $_GET['user'] . ".xml");
		$fc = str_replace($xmlp->robindash->password, md5($password), $fc);
		
		$fh = fopen($dir . "data/" . $_GET['user'] . ".xml", 'w') or die("Can't write to the data file.");
		fwrite($fh, $fc);
		fclose($fh);
		
		header("Location: " . $wdir . "reset-password.php?status=reset&is_modal=" . $_GET['is_modal']);
		exit;
	}
	else {
		header("Location: " . $wdir . "reset-password.php?status=wrong-email&is_modal=" . $_GET['is_modal']);
		exit;
	}
}
else if(isset($_GET['user']) && isset($_GET['email'])) {
	header("Location: " . $wdir . "reset-password.php?status=wrong-user&is_modal=" . $_GET['is_modal']);
	exit;
}
else if($_GET['type'] == "modal") {
?>
Simply enter your username and registered email address in the form below to reset your password:
<br />
<br />
<form action="reset-password.php" id="myform" onsubmit="return false;">
		<label for="user"><strong>Username:</strong></label>
		<input type="text" id="user" name="user" style="width:90%;" /><br />
		
		<label for="email"><strong>Email:</strong></label>
		<input type="text" size="30" id="email" name="email" style="width:90%;" /><br />
		
		<input type="hidden" name="is_modal" value="true" />
		<input type="submit" value="Reset Password" onclick="Modalbox.show('reset-password.php', {title: 'Reset Password', width: 500, params:Form.serialize('myform') }); return false;" />&nbsp;or&nbsp;<a href="#" title="Cancel &amp; Close window" onclick="Modalbox.hide(); return false;">Cancel &amp; close window</a>
</form>
<?php
exit;
}
?>

<!DOCTYPE HTML>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<title>Reset Password: <?php echo $brand; ?></title>
<link rel="stylesheet" type="text/css" href="<?php echo $wdir; ?>resources/style.css" />
<link rel="shortcut icon" href="<?php echo $wdir; ?>resources/favicon.ico"/>
<meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
</head>
<body>
<?php
if(isset($_GET['is_modal']) && $_GET['is_modal'] == "true") {echo "";}
else {
?>
	<div id="login-panel">
		<h2 class="nospacing">Reset Password for</h2>
		<h1><?php echo $brand; ?></h1>
		<div id="page-content">
			<?php
			}
			
			if(!isset($_GET['status'])) {
				echo "You may reset the password for your " . $brand . " account here. Simply enter your username and email address below:<br /><br />";
				echo "<form action=\"" . $wdir . "reset-password.php\" method=\"GET\" id=\"login-form\">";
				echo "<label for=\"user\">Username</label><input type=\"text\" id=\"user\" name=\"user\" value=\"\" />";
				echo "<label for=\"email\">Email</label><input type=\"text\" id=\"email\" name=\"email\" value=\"\" />";
				echo "<input type=\"submit\" name=\"submit\" value=\"Reset Password\" class=\"btn-login\" />";
				echo "</form>";
			}
			else if($_GET['status'] == "reset") {echo "More details have been sent to your registered email address.";}
			else if($_GET['status'] == "wrong-email") {echo "You entered an incorrect email address.<br />Please <a href=\"" . $wdir . "reset-password.php\">try again</a>.";}
			else if($_GET['status'] == "wrong-user") {echo "You entered an incorrect username.<br />Please <a href=\"" . $wdir . "reset-password.php\">try again</a>.";}
			else {echo "Unknown status sent.";}
			
			if(isset($_GET['is_modal']) && $_GET['is_modal'] == "true") {echo "";}
			else {
			?>
			
			<br />	
			<p style="color:grey;text-align:center;margin-bottom:-20px;">You're usage of this website is subject to<br />the <a href="<?php echo $wdir; ?>resources/extras/legal.pdf" title="Terms and Conditions" style="color:grey;font-style:italic;text-decoration:underline;">Terms and Conditions</a>.</p>
		</div>
	</div>
<?php
}

echo $tracker;
?>
</body>
</html>