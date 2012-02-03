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
else {header("Location: oobe.php");}

$form = "<br /><br />
<form action=\"super-powers.php\" method=\"POST\" id=\"login-form\">
<label for=\"pass\">Password</label>
<input type=\"password\" name=\"pass\" value=\"\" />
<input type=\"submit\" name=\"submit\" value=\"Login\" class=\"btn-login\" />
</form>";


if($_COOKIE['spass']) {
	if($_COOKIE['spass'] == file_get_contents("super-password.txt")) {
		$status = "logged-in";
	}
	else {$status = "<p id=\"error\">Incorrect Password</p>";setcookie("spass");}
}
else if($_POST['submit'] == "Login" && !$_POST['pass']) {$status = "<p id=\"error\">Incorrect Password</p>";setcookie("spass");}
else if($_POST['pass']) {
	if(md5($_POST['pass']) == file_get_contents("super-password.txt")) {
		setcookie("spass", md5($_POST['pass']));
		$status = "logged-in";
	}
	else {$status = "<p id=\"error\">Incorrect Password</p>";setcookie("spass");}
}
else {$status = "Please enter your Password to login.";}

if($status == "logged-in") {
	if($_POST['action'] == "reset-user") {
		$xmlp = simplexml_load_file($dir . "data/" . $_POST['user'] . ".xml");
		
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
		
		$password = generatepassword();
		
		mail($xmlp->robindash->notifymail, "Your " . $brand . " accounts password has been reset", "Hi there " . $_POST['user'] . ",\n\nThis is an email to let you know that your accounts password at " . $brand . " has been reset.\nYour new password will be: " . $password . "\n\nPlease login to your account at: http://" . $_SERVER['SERVER_NAME'] . str_replace("super-powers.php", "", $_SERVER['REQUEST_URI']) . " to change it to something more memorable soon.\n\nRegards,\nThe " . $brand . " Team");
		
		$fc = file_get_contents($dir . "data/" . $_POST['user'] . ".xml");
		$fc = str_replace($xmlp->robindash->password, md5($password), $fc);
		
		$fh = fopen($dir . "data/" . $_POST['user'] . ".xml", 'w') or die("Can't write to the data file.");
		fwrite($fh, $fc);
		fclose($fh);
		
		header("Location: super-powers.php");
		exit;
	}
	else if($_POST['action'] == "remove-user") {
		$xmlp = simplexml_load_file($dir . "data/" . $_POST['user'] . ".xml");	
		mail($xmlp->robindash->notifymail, "Your " . $brand . " account has been removed", "Hi there " . $_POST['user'] . ",\n\nThis is an email to let you know that your account at " . $brand . " has been removed.\nWe would like to thank you for taking the time to use " . $brand . " and wish you the best of luck with any other dashboard you may wish to try.\n\nRegards,\nThe " . $brand . " Team");
		
		$xmlp = simplexml_load_file($dir . "data/" . $_POST['user'] . "_nodes.xml");	
		foreach($xmlp->node as $node) {
			if(file_exists($dir . "data/mac2net/" . base64_encode($node->mac) . ".xml")) {
				unlink($dir . "data/mac2net/" . base64_encode($node->mac) . ".xml");
			}
		}
		
		unlink($dir . "data/" . $_POST['user'] . ".xml");
		unlink($dir . "data/" . $_POST['user'] . "_nodes.xml");
		unlink($dir . "data/" . $_POST['user'] . ".txt");
		unlink($dir . "data/" . $_POST['user'] . ".csv");
		unlink($dir . "data/cid/" . $_POST['user'] . ".txt");
		
		function rrmdir($dir) {
			if (is_dir($dir)) {
				$objects = scandir($dir);
				foreach ($objects as $object) {
					if ($object != "." && $object != "..") {
						if(filetype($dir . "/" . $object) == "dir") {rrmdir($dir . "/" . $object);}
						else {unlink($dir . "/" . $object);}
					}
				}
			reset($objects);
			rmdir($dir);
			}
		}
		
		rrmdir($dir . "data/stats/" . $_POST['user']);
		rrmdir($dir . "data/uploads/" . $_POST['user']);
		
		header("Location: super-powers.php");
		exit;
	}
	else if($_POST['action'] == "disassociate-node") {
		$user = file_get_contents($dir . "data/mac2net/" . base64_encode($_POST['mac']) . ".txt");
		$xmlp = simplexml_load_file($dir . "data/" . $user . ".xml");	
		mail($xmlp->robindash->notifymail, "A node has been removed from your " . $brand . " account", "Hi there " . $user . ",\n\nThis is an email to let you know that a node has been removed from your account at " . $brand . ".\nIt's MAC address is: " . $_POST['mac'] . ".\n\nRegards,\nThe " . $brand . " Team");

		$xmlp = simplexml_load_file($dir . "data/" . $user . "_nodes.xml");
		foreach($xmlp->node as $node) {
			if($node->mac == $_POST['mac']) {
				$data = "<node>
<name>" . $node->name . "</name>
<notes></notes>
<mac>" . $node->mac . "</mac>
<ip>" . $node->ip . "</ip>
<lat>" . $node->lat . "</lat>
<lng>" . $node->lng . "</lng>
</node>";
				$fc = file_get_contents($dir . "data/" . $user . "_nodes.xml");
				$fc = str_replace($data, "", $fc);

				$fh = fopen($dir . "data/" . $user . "_nodes.xml", 'w') or die("Can't write to the nodes file.");
				fwrite($fh, $fc);
				fclose($fh);
			}
		}

		unlink($dir . "data/mac2net/" . base64_encode($_POST['mac']) . ".txt");
		unlink($dir . "data/hbc/" . base64_encode($_POST['mac']) . ".txt");
		unlink($dir . "data/role/" . base64_encode($_POST['mac']) . ".txt");
		unlink($dir . "data/stats/" . $networkname . "/" . base64_encode($_POST['mac']) . ".txt");
		unlink($dir . "data/stats/" . $networkname . "/" . base64_encode($_POST['mac']) . ".date.txt");
		
		unlink($dir . "data/pnc/" . base64_encode($_POST['mac']) . "/gw.defroute.txt");
		unlink($dir . "data/pnc/" . base64_encode($_POST['mac']) . "/gw.ipaddr.txt");
		unlink($dir . "data/pnc/" . base64_encode($_POST['mac']) . "/gw.netmask.txt");
		unlink($dir . "data/pnc/" . base64_encode($_POST['mac']) . "/node.predef_role.txt");
		unlink($dir . "data/pnc/" . base64_encode($_POST['mac']) . "/node.run_mode.txt");
		unlink($dir . "data/pnc/" . base64_encode($_POST['mac']) . "/staticcheckbox.txt");
		
		unlink($dir . "data/pnc/" . base64_encode($_POST['mac']) . "/");
		
		header("Location: super-powers.php");
		exit;
	}
	else if($_POST['action'] == "login-user") {
		$xmlp = simplexml_load_file($dir . "data/" . $_POST['user'] . ".xml");	
		mail($xmlp->robindash->notifymail, "Your " . $brand . " account has been logged into by an administrator", "Hi there " . $_POST['user'] . ",\n\nThis is an email to let you know that your account at " . $brand . " has been logged into by an administrator. This kind of message is normally only sent to alert you to the usage of your account by an administrator in regards to a support query you may have sent us.\n\nRegards,\nThe " . $brand . " Team");
		
		session_destroy();
		session_start();
		
		$_SESSION["user"] = $_POST['user'];
		$_SESSION["pass"] = (string)$xmlp->robindash->password;
		
		header("Location: edit.php");
		exit;
	}
	else if(!$_GET['action']) {
		$body = "Reset User Password: <a href=\"?action=reset-user\">Click Here</a><br />
				 Remove User Account: <a href=\"?action=remove-user\">Click Here</a><br />
				 Disassociate Node from Account: <a href=\"?action=disassociate-node\">Click Here</a><br />
				 Login to users account: <a href=\"?action=login-user\">Click Here</a><br />";
	}
	else if($_GET['action'] == "logout") {
		setcookie("spass", 1);
		$status = "<p id=\"done\">You have been logged out</p>";
	}
	else if($_GET['action'] == "reset-user") {
		$body = "You may reset a users password here.<br /><br />
				 <form action=\"super-powers.php\" method=\"POST\">
				 <lable for=\"user\">Username</label><input type=\"text\" name=\"user\" value=\"\" /><br />
				 <input type=\"hidden\" name=\"action\" value=\"reset-user\" />
				 <input type=\"button\" name=\"cancel\" value=\"Cancel\" onclick=\"window.location = 'super-powers.php';\" />&nbsp;<input type=\"submit\" name=\"sent\" value=\"Reset User\" />
				 </form>";
	}
	else if($_GET['action'] == "remove-user") {
		$body = "You may remove a users account here.<br /><br />
				 <form action=\"super-powers.php\" method=\"POST\">
				 <lable for=\"user\">Username</label><input type=\"text\" name=\"user\" value=\"\" /><br />
				 <input type=\"hidden\" name=\"action\" value=\"remove-user\" />
				 <input type=\"button\" name=\"cancel\" value=\"Cancel\" onclick=\"window.location = 'super-powers.php';\" />&nbsp;<input type=\"submit\" name=\"sent\" value=\"Remove User\" />
				 </form>";
	}
	else if($_GET['action'] == "disassociate-node") {
		$body = "You may disassociate a node from a network here.<br /><br />
				 <form action=\"super-powers.php\" method=\"POST\">
				 <lable for=\"user\">MAC Address</label><input type=\"text\" name=\"macaddress\" value=\"\" /><br />
				 <input type=\"hidden\" name=\"action\" value=\"disassociate-node\" />
				 <input type=\"button\" name=\"cancel\" value=\"Cancel\" onclick=\"window.location = 'super-powers.php';\" />&nbsp;<input type=\"submit\" name=\"sent\" value=\"Disassociate Node\" />
				 </form>";
	}
	else if($_GET['action'] == "login-user") {
		$body = "You may login to a users account here.<br /><br />
				 <form action=\"super-powers.php\" method=\"POST\">
				 <lable for=\"user\">Username</label><input type=\"text\" name=\"user\" value=\"\" /><br />
				 <input type=\"hidden\" name=\"action\" value=\"login-user\" />
				 <input type=\"button\" name=\"cancel\" value=\"Cancel\" onclick=\"window.location = 'super-powers.php';\" />&nbsp;<input type=\"submit\" name=\"sent\" value=\"Login as User\" />
				 </form>";
	}
}
?>

<html>
<head>
<title><?php echo $brand; ?></title>
<link rel="stylesheet" type="text/css" href="<?php echo $wdir; ?>resources/style.css">
<link rel="shortcut icon" href="<?php echo $wdir; ?>resources/favicon.ico"/>
</head>
<body>
<div id="login-panel">
	<h2 class="nospacing"><?php echo $brand; ?></h2>
	<h1>with Super Powers</h1>
	<div id="page-content">
		<?php
		if($status == "logged-in") {
			echo "<b>Welcome</b> | <a href=\"" . $wdir . "super-powers.php?action=logout\">Logout</a><br />What would you like to do?<hr />";
			echo $body;
		}
		else {echo $status . $form;}
		?>
	</div>	
</div>
</body>
</html>