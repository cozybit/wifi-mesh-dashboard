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


// Don't show us any silly errors
ini_set("display_errors", 0);

// Start the session so that users can login...
session_start();

// Set the websites email address details
ini_set("sendmail_from", $brand . " <" . $from . ">");


// Compression settings
ini_set("zlib.output_compression", 1);

if(function_exists("sanitize_output")) {echo "";}
else if($_SERVER['REMOTE_ADDR'] == '::1' || $_SERVER['REMOTE_ADDR'] == '127.0.0.1') {echo "";}
else if(strpos($_SERVER['REQUEST_URI'], 'checkin-batman') !==FALSE || strpos($_SERVER['REQUEST_URI'], 'edit-') !==FALSE || strpos($_SERVER['REQUEST_URI'], 'maps') !==FALSE || strpos($_SERVER['REQUEST_URI'], 'cron.php') !==FALSE) {echo "";}
else {
	ini_set("display_errors", 0);

	function sanitize_output($buffer)
	{
		$search = array('/\>[^\S ]+/s', '/[^\S ]+\</s', '/(\s)+/s');
		$replace = array('>', '<', '\1');
		$buffer = preg_replace($search, $replace, $buffer);
		
		return $buffer;
	}

	ob_start("sanitize_output");
}


// Load in the users language
if(isset($_SESSION['language'])) {require($dir . "resources/languages/" . $_SESSION['language'] . ".inc.php");}
else {require($dir . "resources/languages/english.inc.php");}


// Bug reporter
function sendreport($errno, $errstr) {
	global $errored;
		if($errno == 8 || $errno == 2048 || $errno == 8192) {echo "";}
		else if($errored == 1) {echo "";}
		else if(strpos($errstr, 'file_get_contents(http://checkin.open-mesh.com/') !==FALSE) {echo "";}
		else {
			echo "<b>Error:</b> [" . $errno . "] " . $errstr . "<br />";
			echo "An error report has been seen to the developers of robin-dash.<br />";
			mail("bug-reports@robin-dash.com", "Bug Report", $errno . "\n" . $errstr . "\n" . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI']);
		}

	$errored = 1;
}

set_error_handler("sendreport");

// Input purifier
if(strpos($_SERVER['REQUEST_URI'], 'edit-nds.php') !==FALSE) {echo "";}
else {
	foreach($_POST as $key => $value) {
		$_POST[$key] = is_array($key) ? $_POST[$key]: strip_tags($_POST[$key]);
	}

	foreach($_GET as $key => $value) {
		$_GET[$key] = is_array($key) ? $_GET[$key]: strip_tags($_GET[$key]);
	}
}


// Timezone functions
function setTimezoneByOffset($offset) {
	$testTimestamp = time();
	date_default_timezone_set('UTC');
	$testLocaltime = localtime($testTimestamp,true);
	$testHour = $testLocaltime['tm_hour'];
	$abbrarray = timezone_abbreviations_list();
	
    foreach($abbrarray as $abbr) {
			foreach($abbr as $city) {
                date_default_timezone_set($city['timezone_id']);
                $testLocaltime = localtime($testTimestamp,true);
                $hour = $testLocaltime['tm_hour'];
                $testOffset = $hour - $testHour;

                if($testOffset == $offset) {
					return true;
				}
		}
	}
	return false;
}
?>