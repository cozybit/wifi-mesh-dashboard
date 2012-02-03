<?php
/*
           _     _                _           _     
          | |   (_)              | |         | |    
 _ __ ___ | |__  _ _ __ ______ __| | __ _ ___| |__  
| '__/ _ \| '_ \| | '_ \______/ _` |/ _` / __| '_ \ 
| | | (_) | |_) | | | | |    | (_| | (_| \__ \ | | |
|_|  \___/|_.__/|_|_| |_|     \__,_|\__,_|___/_| |_|

robin-dash: Centralized Controller for Robin-Mesh networking devices
Copyright (C) 2011 Cody Cooper.

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


This script may take a while to download the list,
if so you may wish to download the below file manually
and change this script to reflect the new oui file.
*/


   $oui = file_get_contents("http://standards.ieee.org/develop/regauth/oui/oui.txt");
// $oui = file_get_contents("my-saved-file.txt");

$ex = explode("\n", $oui);
$fc = "<?php
function ouilookup(\$mac) {
\$ouilist=Array(\n";

foreach($ex as $item) {
	if(strpos($item, '(base 16)') !==FALSE) {
		$oui = explode("     ", $item);
		$realitem = explode("		", $item);
		
		$fc = $fc . "	\"" . addslashes($oui[0]) . "\"=>\"" . addslashes($realitem[1]) . "\",\n";
	}
}

$fc = $fc . ");

\$oui=strtoupper(substr(preg_replace('`[^a-z0-9]`i','',\$mac),0,6));
\$vendor=\$ouilist[\$oui];
return(\$vendor);
}
?>";


$fh = fopen("ouilookup.php", 'w') or die("Can't write to the data file.");
fwrite($fh, $fc);
fclose($fh);
?>