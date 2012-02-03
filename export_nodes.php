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

if(isset($_GET['network']) && isset($_GET['passwd'])) {
	$networkname = $_GET['network'];
	$networkpass = $_GET['passwd'];

	if(file_exists($dir . "data/" . $networkname . ".xml")) {
		$sxml = simplexml_load_file($dir . "data/" . $networkname . ".xml");
		
		if(md5($networkpass) == $sxml->robindash->password) {
			header("Content-type: text/xml");
			echo file_get_contents($dir . "data/" . $networkname . "_nodes.xml");
		}
		else {die("Incorrect 'passwd' sent.");}
	}
	else {die("'network' not found.");}
}
else {die("No 'network' sent.");}