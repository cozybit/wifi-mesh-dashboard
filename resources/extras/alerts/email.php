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


if($handle = opendir($dir . "data/")) {
    while (false !== ($file = readdir($handle))) {
		if($file != "." && $file != ".." && strpos($file, '_nodes.xml') !==FALSE) {
			$networkname = str_replace("_nodes.xml", "", $file);
			
			$axml = simplexml_load_file($dir . "data/" . $networkname . ".xml");
			$bxml = simplexml_load_file($dir . "data/" . $networkname . "_nodes.xml");
			
			$nodes = "";
		
			foreach($bxml->node as $item) {
				if(file_exists($dir . "data/stats/" . $networkname . "/" . base64_encode($item->mac) . ".date.txt")) {
					$ts = file_get_contents($dir . "data/stats/" . $networkname . "/" . base64_encode($item->mac) . ".date.txt");

					$day = substr($ts, 0, 2);
					$mon = substr($ts, 2, 2);
					$hor = substr($ts, 8, 2);
					
					$yer = substr($ts, 4, 2);
					$min = substr($ts, 6, 2);
					$anp = substr($ts, 10, 2);
					
					$diff = date(i) - $min;
					
					if($day == date(d) && $mon == date(m) && $hor == date(H) && $diff < 10) {$status = "online";}
					else if($day == date(d) && $mon == date(m) && $hor == date(H) - 1 && $min > 50) {$status = "online";}
					else {$status = "offline";}
					
					if($status == "offline") {
						if(file_exists($dir . "data/errored/e" . base64_encode($item->mac) . ".txt")) {
							// We have ALREADY sent an email about this node not checking in at robin-dash
						}
						else {
							// We have NOT sent an email about this node not checking in at robin-dash
							
							$nodes .= $item->name . " - " . $item->mac . " - " . $day . "/" . $mon . "/" . $yer . " " . $hor . ":" . $min . " " . $anp . "\n";
							
							$fh = fopen($dir . "data/errored/e" . base64_encode($item->mac) . ".txt", 'w') or die("Can't write to the data file.");
							fwrite($fh, "1");
							fclose($fh);
						}
					}
				}
			}
			
			if(strlen($nodes) > 0) {
				// The nodes _are_ down, so send the email to the user
				mail($axml->robindash->notifymail, "Node Outage Notification", "Hi there " . $networkname . ",\n\nThis is an email from " . $brand . " to let you know that the following nodes are down:\n\n" . $nodes . "\nRegards,\nThe " . $brand . " Team", $brand . " <" . $from . ">");
			}
		}
	}
	
	closedir($handle);
}
?>