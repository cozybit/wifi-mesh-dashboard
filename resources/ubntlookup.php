<?php
/*
		   _	 _				_		   _	 
		  | |   (_)			  | |		 | |	
 _ __ ___ | |__  _ _ __ ______ __| | __ _ ___| |__  
| '__/ _ \| '_ \| | '_ \______/ _` |/ _` / __| '_ \ 
| | | (_) | |_) | | | | |	| (_| | (_| \__ \ | | |
|_|  \___/|_.__/|_|_| |_|	 \__,_|\__,_|___/_| |_|

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


function ubntlookup($mac) {
	$modellist=Array(
		// NanoStation2
		"DCC7"=>"NS2",
		"ABA0"=>"NS2",
		
		// NanoStation2 Loco
		"D45C"=>"Loco2",

		// NanoStation Loco M2
		"7A03"=>"Loco M2",
		"7A04"=>"Loco M2",
		"7A08"=>"Loco M2",
		"7A08"=>"Loco M2",

		// NanoStation M2
		"1838"=>"NS M2",
		"FCBA"=>"NS M2",
		"1839"=>"NS M2",
		"FCB9"=>"NS M2",

		// NanoStation M5
		"8A42"=>"NS M5",
		"8A75"=>"NS M5",
		"8A76"=>"NS M5",
		
		// Bullet2HP
		"FE55"=>"Bullet2HP",
		"EA1E"=>"Bullet2HP",
		
		// Pico2HP
		"F0FA"=>"Pico2HP",
		"F0FB"=>"Pico2HP",
		"F420"=>"Pico2HP"
	);

	$device = strtoupper(substr(preg_replace('`[^a-z0-9]`i','', $mac),6,4));
	$ubntmodel = $modellist[$device];
	return($ubntmodel);
}
?>