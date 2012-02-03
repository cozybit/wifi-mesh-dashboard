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


if(file_exists("settings.php")) {header("404 Not Found");die("<h1>404 Not found</h1>");}
else {echo "";}

if($_POST['sent'] && $_POST['brand'] && $_POST['email'] && $_POST['directory'] && $_POST['recaptcha_publickey'] && $_POST['recaptcha_privatekey'] && !file_exists("./settings.php")) {
ini_set('default_socket_timeout', 4);
ini_set('display_errors', 0);


// Check SSL connectivity to this server
if(file_get_contents("https://" . $_SERVER['HTTP_HOST'] . "/")) {$has_https = "true";}
else {$has_https = "false";}

// Check URL rewriting capability
if(file_get_contents("http://" . $_SERVER['HTTP_HOST'] .str_replace("oobe.php", "", $_SERVER['SCRIPT_NAME']) . "test") == "success") {$has_htaccess = "true";}
else {$has_htaccess = "false";}

// Check Internet connectivity
if(file_get_contents("https://www.robin-dash.net/")) {$connectivity = "internet";}
else {$connectivity = "intranet";}

$tracking = str_replace('"', '\"', $_POST['tracking']);

$fc = "<?php
\$brand = \"" . $_POST['brand'] . "\";	// Brand name to use in error messages, and in emails
\$sn = \"" . $_POST['sn'] . "\";	// The Internet domain name of the server
\$sip = \"" . $_POST['sip'] . "\";	// The Internet IP address of the server
\$from = \"" . $_POST['email'] . "\";	// Email address to send messages from
\$dir = \"" . $_POST['directory'] . "\";	// Physical directory where robin-dash is installed
\$wdir = \"" . str_replace("oobe.php", "", $_SERVER['SCRIPT_NAME']) . "\";	// Directory where the server is accessible from the web
\$has_https = \"" . $has_https . "\";	// Whether the server has SSL available or not
\$has_htaccess = \"" . $has_htaccess . "\";	// Whether the server has htaccess available or not
\$connectivity = \"" . $connectivity . "\";	// Whether the server has Internet connectivity or not
\$recaptcha_publickey = \"" . $_POST['recaptcha_publickey'] . "\";	// Public key for reCAPTCHA
\$recaptcha_privatekey = \"" . $_POST['recaptcha_privatekey'] . "\";	// Private key for reCAPTCHA (Keep this safe!)

\$tracker = \"" . $tracking . "\";	// Enables the ability to track your web users

require(\"init.php\");
?>";

$fh = fopen("./settings.php", 'w') or die("Can't write to the settings file.");
fwrite($fh, $fc);
fclose($fh);

$fi = fopen("./super-password.txt", 'w') or die("Can't write to the super-powers file.");
fwrite($fi, md5($_POST['password']));
fclose($fi);

header("Location: ./?setupcomplete=true");
}
?>

<html>
<head>
<title>Welcome to robin-dash!</title>
<link rel="stylesheet" type="text/css" href="./resources/style.css">
<link rel="shortcut icon" href="./resources/favicon.ico"/>
</head>
<body>
<div id="login-panel">
	<h2 class="nospacing">Welcome to</h2>
	<h1>robin-dash</h1>
	<div id="page-content">
		<?php
		if($_GET['step'] == 2) {echo "<form action=\"oobe.php\" method=\"post\">
		<table id=\"create\">
		<tr>
		<td colspan=\"2\"><i>Please enter the settings you would like to use for robin-dash.</i><br /><br /></td>
		</tr>

		<tr>
		<td>Brand Name</td>
		<td><input type=\"text\" name=\"brand\" value=\"\" /></td>
		</tr>

		<tr>
		<td>Directory</td>
		<td><input type=\"text\" name=\"directory\" value=\"" . str_replace("oobe.php", "", $_SERVER["SCRIPT_FILENAME"]) . "\" onclick=\"alert('Please make sure you know what you are doing if you are going to change this field.\\nIt should always end with a trailing slash.\\n\\ne.g.\\nD:/xampp/htdocs/robin-dash/ (On Windows)\\n/var/www/robin-dash/ (on Linux)');\" /></td>
		</tr>
		
		<tr>
		<td>Server Domain Name</td>
		<td><input type=\"text\" name=\"sn\" value=\"" . $_SERVER['SERVER_NAME'] . "\" /></td>
		</tr>
		
		<tr>
		<td>Server IP Address</td>
		<td><input type=\"text\" name=\"sip\" value=\"" . $_SERVER['SERVER_ADDR'] . "\" /></td>
		</tr>

		<tr>
		<td>eMail From</td>
		<td><input type=\"text\" name=\"email\" value=\"\" /></td>
		</tr>
		
		<tr>
		<td>Super Password</td>
		<td><input type=\"password\" name=\"password\" value=\"\" /></td>
		</tr>

		<tr>
		<td>Stat. Tracking<br /><small>e.g. Google Analytics,<br />Site Meter</small></td>
		<td><textarea name=\"tracking\"></textarea></td>
		</tr>
		
		<tr>
		<td colspan=\"2\"><b>To prevent automated signups,<br />register at: <a href=\"https://www.google.com/recaptcha/admin/create\" id=\"a\" target=\"_new\">reCAPTCHA</a> for an API key.</b></td>
		</tr>

		<tr>
		<td>reCAPTCHA:<br />Public Key</td>
		<td><input type=\"text\" name=\"recaptcha_publickey\" value=\"\" /></td>
		</tr>

		<tr>
		<td>reCAPTCHA:<br />Private Key</td>
		<td><input type=\"text\" name=\"recaptcha_privatekey\" value=\"\" /></td>
		</tr>

		<tr>
		<td colspan=\"2\">
		<input type=\"hidden\" name=\"sent\" value=\"true\" />
		<input type=\"submit\" name=\"submit\" value=\"Setup!\" id=\"rbutton\" />
		</td>
		</tr>
		</table>
		</form>";
		}
		else {
		// Define the functions for checking module support
		$modules = apache_get_modules();
		$phpmodules = get_loaded_extensions();

		//First, we check the permissions of the directories
		echo "Checking directory permissions:<br />\n";

		echo "data directory: ";
		if(is_writable("./data/")) {$countr = $countr + 1;echo "<font color=\"green\">Pass</font>";}
		else {$countr = $countr - 1;echo "<font color=\"red\">Fail</font>";}
		echo "<br />\n";

		echo "data/cid directory: ";
		if(is_writable("./data/cid/")) {$countr = $countr + 1;echo "<font color=\"green\">Pass</font>";}
		else {$countr = $countr - 1;echo "<font color=\"red\">Fail</font>";}
		echo "<br />\n<br />\n";

		// Perform the SimpleXML check
		echo "SimpleXML extension: ";
		if(in_array("SimpleXML", $phpmodules)) {$countr = $countr + 1;echo "<font color=\"green\">Pass</font>";}
		else {$countr = $countr - 1;echo "<font color=\"red\">Fail</font>";}
		echo "<br />\n";

		// Perform the SimpleXML check
		echo "cURL extension: ";
		if(in_array("curl", $phpmodules)) {$countr = $countr + 1;echo "<font color=\"green\">Pass</font>";}
		else {$countr = $countr - 1;echo "<font color=\"red\">Fail</font>";}
		echo "<br />\n";

		// Perform the mod_rewrite check
		echo "mod_rewrite extension: ";
		if(in_array("mod_rewrite", $modules) == 1) {$countr = $countr + 1;echo "<font color=\"green\">Pass</font>";}
		else {$countr = $countr - 1;echo "<font color=\"red\">Fail</font>";}
		echo "<br />\n<br />\n";


		// Then we add up the score to see if we can install robin-dash
		if($countr == 5) {echo "<b>You may install robin-dash.</b><br /><br />\n<button onclick=\"window.location = 'oobe.php?step=2';\"><b>Continue</b></button>";}
		else {echo "You need to correct the above problems<br />\nbefore you can begin using robin-dash.";}
		}
		?>
	</div>
</div>
</body>
</html>
